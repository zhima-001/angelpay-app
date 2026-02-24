<?php

/**
 * RSA签名类
 */
class Rsa
{
    public $publicKey = '';
    public $privateKey = '';
    private $_privKey;

    /**
     * * private key
     */
    private $_pubKey;

    /**
     * * public key
     */
    private $_keyPath;

    /**
     * * the keys saving path
     */

    /**
     * * the construtor,the param $path is the keys saving path
     * @param string $publicKey  公钥
     * @param string $privateKey 私钥
     */
    public function __construct($publicKey = null, $privateKey = null)
    {
        $this->setKey($publicKey, $privateKey);
    }

    /**
     * 设置公钥和私钥
     * @param string $publicKey  公钥
     * @param string $privateKey 私钥
     */
    public function setKey($publicKey = null, $privateKey = null)
    {
        if (!is_null($publicKey)) {
            $this->publicKey = $publicKey;
        }
        if (!is_null($privateKey)) {
            $this->privateKey = $privateKey;
        }
    }

    /**
     * * setup the private key
     */
    private function setupPrivKey()
    {
        if (is_resource($this->_privKey)) {
            return true;
        }
        
        if (empty($this->privateKey)) {
            throw new Exception("私钥为空");
        }
        
        // 清理私钥字符串（移除可能的空格和换行）
        $key = trim($this->privateKey);
        
        // 如果私钥已经包含PEM头尾，直接使用
        if (strpos($key, '-----BEGIN') !== false) {
            $pem = $key;
            // 尝试加载
            $this->_privKey = openssl_pkey_get_private($pem);
            if ($this->_privKey !== false) {
                return true;
            }
        }
        
        // 移除可能存在的换行符和空格
        $key = preg_replace('/\s+/', '', $key);
        
        // 尝试PKCS#8格式 (-----BEGIN PRIVATE KEY-----)
        $pem = chunk_split($key, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . rtrim($pem) . "\n-----END PRIVATE KEY-----\n";
        $this->_privKey = openssl_pkey_get_private($pem);
        
        // 如果PKCS#8格式失败，尝试PKCS#1格式 (-----BEGIN RSA PRIVATE KEY-----)
        if ($this->_privKey === false) {
            $pem1 = chunk_split($key, 64, "\n");
            $pem1 = "-----BEGIN RSA PRIVATE KEY-----\n" . rtrim($pem1) . "\n-----END RSA PRIVATE KEY-----\n";
            $this->_privKey = openssl_pkey_get_private($pem1);
        }
        
        // 如果还是失败，尝试直接使用base64解码后再转换
        if ($this->_privKey === false) {
            try {
                $keyData = base64_decode($key, true);
                if ($keyData !== false) {
                    // 尝试PKCS#8格式
                    $pem = "-----BEGIN PRIVATE KEY-----\n" . chunk_split(base64_encode($keyData), 64, "\n") . "-----END PRIVATE KEY-----\n";
                    $this->_privKey = openssl_pkey_get_private($pem);
                    
                    // 如果还是失败，尝试PKCS#1格式
                    if ($this->_privKey === false) {
                        $pem = "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split(base64_encode($keyData), 64, "\n") . "-----END RSA PRIVATE KEY-----\n";
                        $this->_privKey = openssl_pkey_get_private($pem);
                    }
                }
            } catch (Exception $e) {
                // 忽略转换错误
            }
        }
        
        if ($this->_privKey === false) {
            $errors = array();
            while ($error = openssl_error_string()) {
                $errors[] = $error;
            }
            $errorMsg = !empty($errors) ? implode('; ', $errors) : '未知错误';
            // 添加调试信息
            $keyPreview = strlen($key) > 50 ? substr($key, 0, 50) . '...' : $key;
            throw new Exception("无法加载私钥: " . $errorMsg . " (私钥长度: " . strlen($key) . ", 前50字符: " . $keyPreview . ")");
        }
        return true;
    }

    /**
     * * setup the public key
     */
    private function setupPubKey()
    {
        if (is_resource($this->_pubKey)) {
            return true;
        }
        $pem = chunk_split($this->publicKey, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        $this->_pubKey = openssl_pkey_get_public($pem);
        return true;
    }

    /**
     * * encrypt with the private key
     */
    public function privEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPrivKey();
        $r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
        if ($r) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * * decrypt with the private key
     */
    public function privDecrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $this->setupPrivKey();
        $encrypted = base64_decode($encrypted);
        $r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    /**
     * * encrypt with public key
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $this->setupPubKey();
        $r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
        if ($r) {
            return base64_encode($encrypted);
        }
        return null;
    }

    /**
     * * decrypt with the public key
     */
    public function pubDecrypt($crypted)
    {
        if (!is_string($crypted)) {
            return null;
        }
        $this->setupPubKey();
        $crypted = base64_decode($crypted);
        $r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
        if ($r) {
            return $decrypted;
        }
        return null;
    }

    /**
     * 构造签名
     * @param string $dataString 被签名数据
     * @return string
     */
    public function sign($dataString)
    {
        $this->setupPrivKey();
        if (!is_resource($this->_privKey)) {
            throw new Exception("私钥未正确加载");
        }
        
        $signature = false;
        $result = openssl_sign($dataString, $signature, $this->_privKey, OPENSSL_ALGO_SHA256);
        
        if ($result === false) {
            $error = openssl_error_string();
            throw new Exception("签名失败: " . $error);
        }
        
        return base64_encode($signature);
    }

    /**
     * 验证签名
     * @param string $dataString 被签名数据
     * @param string $signString 已经签名的字符串
     * @return number 1签名正确 0签名错误
     */
    public function verify($dataString, $signString)
    {
        $this->setupPubKey();
        $signature = base64_decode($signString);
        $flg = openssl_verify($dataString, $signature, $this->_pubKey,OPENSSL_ALGO_SHA256);
        return $flg;
    }

    public function __destruct()
    {
        is_resource($this->_privKey) && @openssl_free_key($this->_privKey);
        is_resource($this->_pubKey) && @openssl_free_key($this->_pubKey);
    }
}
