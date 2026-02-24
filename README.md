# AngelPay (天使支付) - L4 GitOps 部署

## 项目概述

天使支付 PHP 应用，通过 GitOps 方式部署到 AWS EKS Kubernetes 集群。

| 组件 | 说明 |
|------|------|
| **应用仓库** | [zhima-001/angelpay-app](https://github.com/zhima-001/angelpay-app) |
| **环境仓库** | [zhima-001/angelpay-env](https://github.com/zhima-001/angelpay-env) |
| **镜像仓库** | ghcr.io/zhima-001/angelpay |
| **集群** | AWS EKS `angelpay-cluster` (us-east-1, ARM64 Spot) |
| **CD 工具** | Argo CD (自动同步 env repo) |

## 线上访问地址

| 端点 | URL | 预期 |
|------|-----|------|
| 首页 | http://3.91.149.91/ | 应用页面 |
| 健康检查 | http://3.91.149.91/health | 200 + 版本信息 |
| 就绪检查 | http://3.91.149.91/ready | 200 + DB连接状态 |

> 注：IP 为 EKS 节点公网 IP，Ingress Controller 通过 HostPort 方式暴露。

---

## 1. 从 commit 到上线的流程

```
开发者 push 代码到 angelpay-app (main)
        │
        ▼
GitHub Actions CI/CD Pipeline 自动触发
        │
        ├── 1. PHP Lint & Test   (语法检查)
        ├── 2. Build Docker Image (ARM64, 推送 GHCR, tag=git-sha)
        ├── 3. Trivy Security Scan (漏洞扫描)
        └── 4. Update Env Repo    (自动修改 angelpay-env 的镜像 tag)
                │
                ▼
        angelpay-env 仓库收到新 commit (镜像 tag 更新)
                │
                ▼
        Argo CD 检测到 env repo 变化 → 自动同步
                │
                ▼
        Kubernetes 滚动更新 → 新版本上线 ✅
```

### 关键设计

- **镜像 tag 可追溯**：使用 `git short sha`（如 `fdda52c`），禁止 `latest`
- **发布 = 修改 env repo**：所有部署通过 `angelpay-env` 仓库触发，禁止 `kubectl` 手动修改
- **ARM64 交叉编译**：GitHub Actions 使用 QEMU 构建 ARM64 镜像，适配 t4g.small Graviton 节点
- **定时任务**：容器内通过 Supervisor 管理 crond，执行订单统计和结算

---

## 2. 回滚步骤

### 方法：回退 angelpay-env 的 commit

```bash
# 1. 查看 env repo 最近的 commit
cd angelpay-env
git log --oneline -5

# 2. 找到上一个正常版本的 commit，revert 最新提交
git revert HEAD --no-edit

# 3. 推送回退
git push origin main

# 4. Argo CD 自动检测到 env repo 变化，自动回滚
# 等待 30 秒后验证
kubectl -n angelpay-prod get pods
curl http://3.91.149.91/health
```

### 预期现象

1. Argo CD 检测到 env repo 新 commit（revert commit）
2. 自动同步，将镜像 tag 回退到上一个版本
3. Kubernetes 执行 RollingUpdate，新 Pod 使用旧镜像启动
4. `/health` 返回的 `version` 字段变回旧的 git sha

### 漂移(Drift)策略

- Argo CD 配置了 `selfHeal: true`
- 如果有人用 `kubectl` 手动修改了资源，Argo CD 会在 5 秒内自动恢复为 env repo 定义的状态
- 所有变更必须通过 Git 提交到 env repo

---

## 3. 密钥/权限管理

### 密钥存放位置

| 密钥 | 存放位置 | 谁能看到明文 |
|------|---------|-------------|
| DB_USER / DB_PASS | K8s Secret `angelpay-secret` | 仅集群 RBAC 授权用户 |
| GHCR 拉取凭证 | K8s Secret `ghcr-pull-secret` | 仅集群 RBAC 授权用户 |
| ENV_REPO_TOKEN | GitHub Actions Secret | 仅仓库管理员 |
| GITHUB_TOKEN | GitHub Actions 自动生成 | 仅 workflow 运行时 |

### 安全措施

- **密钥不落地**：`secret.yaml` 仅含占位符，不纳入 Kustomization，真实值通过 `kubectl create secret` 管理
- **Git 仓库无明文**：所有敏感信息仅存在于 GitHub Secrets 和 K8s Secrets 中
- **日志无泄露**：CI/CD 日志自动遮蔽 Secret 值

### Secret 创建命令（运维手册）

```bash
# 数据库凭证
kubectl -n angelpay-prod create secret generic angelpay-secret \
  --from-literal=DB_USER=root \
  --from-literal=DB_PASS=<密码>

# GHCR 拉取凭证
kubectl -n angelpay-prod create secret docker-registry ghcr-pull-secret \
  --docker-server=ghcr.io \
  --docker-username=<用户名> \
  --docker-password=<PAT with read:packages>
```

---

## 4. 技术架构

### Kubernetes 资源

- **Namespace**: `angelpay-prod`
- **Deployment**: 2 replicas, RollingUpdate (maxSurge=1, maxUnavailable=0)
- **Service**: ClusterIP → port 80
- **Ingress**: Nginx Ingress Controller (HostPort 80/443)
- **ConfigMap**: 数据库连接信息 + config.php
- **Secret**: DB 凭证 + GHCR pull secret (kubectl 管理，不入 Git)
- **HPA**: 自动水平扩缩容
- **NetworkPolicy**: 网络安全隔离
- **Probes**: liveness (`/health`) + readiness (`/ready`)
- **Resources**: requests(200m/256Mi) limits(1/1Gi)

### 容器架构

```
php:7.4-fpm-alpine
├── Nginx (端口 80)
├── PHP-FPM (FastCGI)
├── Supervisor (进程管理)
└── Crond (定时任务: 订单统计/结算)
```

### 环境仓库结构 (Kustomize)

```
angelpay-env/
├── argocd/
│   └── application.yaml     # Argo CD Application 定义
├── base/                    # 基础配置
│   ├── deployment.yaml
│   ├── service.yaml
│   ├── ingress.yaml
│   ├── configmap.yaml
│   ├── configmap-dbconfig.yaml
│   ├── hpa.yaml
│   └── networkpolicy.yaml
└── overlays/
    ├── dev/                 # 开发环境 overlay
    └── prod/                # 生产环境 overlay (镜像tag在此更新)
```