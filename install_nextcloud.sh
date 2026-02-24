#!binbash

# 安装 Docker
sudo yum install -y yum-utils
sudo yum-config-manager --add-repo httpsdownload.docker.comlinuxcentosdocker-ce.repo
sudo yum install -y docker-ce docker-ce-cli containerd.io
sudo systemctl start docker
sudo systemctl enable docker

# 安装 Docker Compose
sudo curl -L httpsgithub.comdockercomposereleasesdownloadv2.20.3docker-compose-linux-x86_64 -o usrlocalbindocker-compose
sudo chmod +x usrlocalbindocker-compose

# 创建目录
mkdir -p wwwwwwrootnextcloud

# 创建 docker-compose.yml 文件
cat EOL wwwwwwrootnextclouddocker-compose.yml
version '3'

services
  db
    image mariadb
    command --transaction-isolation=READ-COMMITTED --binlog-format=ROW
    restart always
    volumes
      - dbvarlibmysql
    environment
      - MYSQL_ROOT_PASSWORD=sd1Wl@#@kkh232@
      - MYSQL_PASSWORD=sd1Wl@#@kkh232@
      - MYSQL_DATABASE=nextcloud
      - MYSQL_USER=nextcloud

  app
    image nextcloud
    ports
      - 808080
    links
      - db
    volumes
      - nextcloudvarwwwhtml
    restart always

volumes
  db
  nextcloud
EOL

# 启动 Nextcloud
cd wwwwwwrootnextcloud
sudo usrlocalbindocker-compose up -d

echo Nextcloud 安装完成!
echo 默认数据库用户名 nextcloud
echo 默认数据库密码和 root 密码 sd1Wl@#@kkh232@
echo 请访问 httpyour_server_ip8080 完成安装.
