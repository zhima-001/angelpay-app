<?php

namespace lib;


/**
 * Created by PhpStorm.
 * User: 80752
 * Date: 2018/9/12
 * Time: 21:30
 */

if (!function_exists('url_safe_base64_encode')) {

    function url_safe_base64_encode($data)
    {
        return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($data));
    }
}
if (!function_exists('url_safe_base64_decode')) {
    function url_safe_base64_decode($data)
    {
        $base_64 = str_replace(array('-', '_'), array('+', '/'), $data);
        return base64_decode($base_64);
    }
}

class RSA2

{

    /**
     * 选择在创建CSR时应该使用哪些扩展。可选值有 OPENSSL_KEYTYPE_DSA, OPENSSL_KEYTYPE_DH, OPENSSL_KEYTYPE_RSA 或 OPENSSL_KEYTYPE_EC. 默认值是 OPENSSL_KEYTYPE_RSA.
     */

    const RSA_ALGORITHM_KEY_TYPE = OPENSSL_KEYTYPE_RSA;

    /**
     * 签名算法， 默认为 OPENSSL_ALGO_SHA1
     */

    const RSA_ALGORITHM_SIGN = OPENSSL_ALGO_SHA256;

    /**
     * 对方公钥
     * @var string
     */
//    public static $thirdPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjLAgXd5QI+ZWrgVqX5zdlEf/mR3PEztRyWUXXTUTzRgN9KVznJcEgAnHdC7kt8+xfcg+YJkEirnBUmSfs7h1HUM1t1mUyR1SwetzprUiNAh58HfnNVcYE5qVOiLOEyulv7bXQ6t9LxjdiGfGxsJFew6INQyJRHT7iofEbYX5oeX5/niR1hYxzpfnEyguJxjGI5yurOSoPDQ/nJgG2rUp/JoZtVXJdubmYfDPzpQm/UOz6Qsb8D4sfLPioVUYP2EEgOLC/GerbbzBbiqX6ThVwWeYPM2wYx9WFTdzpdUaM0oNZyBtaU97Za4slmiaehzGGOHRl0q0O24vJC4rF0uvYQIDAQAB';
//    public static $thirdPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjLAgXd5QI+ZWrgVqX5zdlEf/mR3PEztRyWUXXTUTzRgN9KVznJcEgAnHdC7kt8+xfcg+YJkEirnBUmSfs7h1HUM1t1mUyR1SwetzprUiNAh58HfnNVcYE5qVOiLOEyulv7bXQ6t9LxjdiGfGxsJFew6INQyJRHT7iofEbYX5oeX5/niR1hYxzpfnEyguJxjGI5yurOSoPDQ/nJgG2rUp/JoZtVXJdubmYfDPzpQm/UOz6Qsb8D4sfLPioVUYP2EEgOLC/GerbbzBbiqX6ThVwWeYPM2wYx9WFTdzpdUaM0oNZyBtaU97Za4slmiaehzGGOHRl0q0O24vJC4rF0uvYQIDAQAB';
//    public static $thirdPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAla19Ky7Jw8/sF+I/ZEXX7HuWeXairsWMMv4fCPshXOVvHR/nNwu72X3CIQN/C8zB0hay+eusbkjfZHZS3b4TveR7YzJKeXPTVrGHoOleiZp+43lnr7R7/fL2U2cBuF4yiQC7z2ZEYKaDEV8bcgjVMk5k8I0QBhgS/K0z4OZVvmXM+3H4QgnHMC5L6RMkBq5uS2+jKNKmGu2r5fRVQiVvGAmEICWNjwN/RS+jS6P6bYt5FOhjO091gFdyfA46tU/6OKtQMbwJX+2Cc+BqwTcRLCMv3Uz3UifghiEUK5NgwXn0oxa3r78wXpEHR0ooW3M/SSW5g+YUzYevdLkeqNLsrwIDAQAB';
    public static $thirdPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAhw3YlDUe22yY1bHWS4I/tJszwY7KFv94jxi2ousnpEQF74uIZWFe4BXYnfKr4dF6mn5AAOYw6Uap4b+QdgavT4mV8r6tmBi4xbCdcN6RyUWYLSPrUOW95zQCbiJCNySEgQ2HPNJaPwFIqAsNVrr6u7xWS02OyAuDOBVKE13GkjFIK2K7XRRo+Yrov7xJgO9Qa1pA40SW7QweFGhU98pAuRq2KanSv/yWY/xa04rZMJKXoB7XrINK/D34hf+fwaOGQMMYTotXVjNQaa0+W5GZnObpViwBFndRRyCEblrcPUy75lj8Lc1sRwz35Ok0pJ5S3y+pUW7z1P6X+GjlhIyz4wIDAQAB';

    /**
     * 公钥
     * @var string
     */
// 我的公钥
    public static $publicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxoVX1W98+Pg33pQ86bDTFexc0bxlnaOruEz1zI/VktgJ2XJL6G9Aj/C/JakpSYJ5hSD37ajlVu6lrG2vRaiLCml06AO3wtm+XH+dcfjOmOeU8M7boR4vVqLj0dpN1PPX+JSwjzrb2qX2AUxTvMt3mXq5tOj+XE74W2t9s7EZvUWa2l+Q8ChnQzphY6Ev8btYg5/KE4wyROjcgr1OIKWr50z5iuAWVzNEehLlNapTBQNM2jRQIJKwOuhqtBk/7Z0OeuX+p4gJ3FAe8/If+p8nUt9YYbMTER2bouFlwNWZgE8pm6w/ZKueKZHhu23mt5pruYgkFBUQxR0ouYH/KnqGbwIDAQAB';
    /**
     * 私钥
     * @var string
     */
// 我的私钥
// 测试私钥
    public static $privateKey = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDGhVfVb3z4+DfelDzpsNMV7FzRvGWdo6u4TPXMj9WS2AnZckvob0CP8L8lqSlJgnmFIPftqOVW7qWsba9FqIsKaXToA7fC2b5cf51x+M6Y55TwztuhHi9WouPR2k3U89f4lLCPOtvapfYBTFO8y3eZerm06P5cTvhba32zsRm9RZraX5DwKGdDOmFjoS/xu1iDn8oTjDJE6NyCvU4gpavnTPmK4BZXM0R6EuU1qlMFA0zaNFAgkrA66Gq0GT/tnQ565f6niAncUB7z8h/6nydS31hhsxMRHZui4WXA1ZmATymbrD9kq54pkeG7bea3mmu5iCQUFRDFHSi5gf8qeoZvAgMBAAECggEAMb3VfTg7IxLmxNF11cQlj9eyhibjezU4YKx/5iqgA/Q3u5FE7c96aNaUvnX9T1ru4emZ1aW9GSQgxOySvyx08j3sOfo8gAkZBoo/RABom61jB1d9doheqiGUXc+KMvKKSdHPz4Oa9NUip76dOK/unrk+cNL0cOKknht32p9kaKTn5XbXmurucHJ5O5vSmrDl3uXTrYUc0BbyCVAQqS/0SfwLJyW6xknKSk1+fXthwehgPcPJYTQowKc84z16ruL2oeJLZzaj0IF4aaNHSez2K/5t+05abJEdcOJQI4J5dEOJvkl7g/u/RRdDs5m1cU/JW6HUySOHNukSzjlGxiPsQQKBgQDxRL42lX/jUZegogNw8397bp2SdFqFN53vk6Jq9RJay/iIL8L8oMY4V9nF/mgVopuHDSdgoNOwaaB+4BM2/LxJLBPnNUtfR0lizIEd0tEYRLMNLFlf9fr3vDXUP+rvpMmOb6OBLTd/Hrb0sX/UGwrKuClqM3Ar7h8vqVWwHXX7YQKBgQDSpGnaKbqoB0KZn+kCoRO2DGRojYS7tAOBuA5OjHzUM8j5b62ucF9JxjctK0msITjsSjxzc3/sdTkuL7+KAMz6rJQuFx0srTuZ6s1NF6pjSaeAE9e7UrRboHUk4Gz+p4uvoOhFxzf0z+rcp/EUs0WVIo7IbFreORAftKTlWHYjzwKBgGg+J2E2Htd8vWKuHYaD7qTKGlLY6vN8IEUPKLHFyXRphKxy1nCIlpxpeLJPRXFznHcxe74IPu6N9MZc0nCDqmaDIOZY5IP6LP7/FRppp+YwaJxceRE5GoJHU4qtQzjfniZoneCGROArySjYOD7QoE0OXPaB2wlgDSFurJM3Z26BAoGBALh+CrxrWjI7kaiud862uGX1+qfcc8pXg87FH7rKr7bI2JkoqK2lfMBIHSGxzWg2/P1wk/vmyL+ZeIish43e36obJ/oqgoIUBKTuE/0W4kTuSQgT2RsX+CJcqt2ut6hfpSghve8H60nAJgw4CB7CWgqiZv7CcOA8iJPMi7TYwzjfAoGAOkpdP79+tiIQMbsg2lvYh/Cp8GbElvbUfv3Vp8SiwclYeLffIOldIOY8JDgoar8U/qg5PIbKM4rLEgGiEAAaqlqvf7YSjU48UhVgXj8k+vfiCMjStTcXMuKSsIhOqtZ5pUYrP1mUDhSfp/QvILZLMD6a7Gteqg1GO7Ji2riWWfA=';



    public static function createRsaKey($key_size = 2048)
    {
        $config = array(
            "private_key_bits" => $key_size,
            "private_key_type" => self::RSA_ALGORITHM_KEY_TYPE
        );
        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $private_key);
        $public_key_detail = openssl_pkey_get_details($res);
        $public_key = $public_key_detail["key"];
        self::$publicKey = $public_key;
        self::$privateKey = $private_key;
        return [
            "public_key" => $public_key,
            "private_key" => $private_key,
        ];
    }

    /**
     * 获取rsa密钥加密位数
     * @param $source
     * @return mixed
     */
    private static function getKeyBitDetail($source)
    {
        return openssl_pkey_get_details($source)['bits'];
    }

    /**
     * 获取私钥
     * @return bool|resource
     */
    public static function getPrivateKey()
    {
//        __FILE__.'/rsa_private_key.pem'
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(self::$privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        $source = openssl_pkey_get_private($res);
        if (!$source) {
            $source = openssl_pkey_get_private(self::$privateKey);
        }
        return $source;
    }

    /**
     * 获取第三方私钥
     * @return bool|resource
     */
    public static function getThirdPrivateKey()
    {
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(file_get_contents('rsa_third_private_key.pem'), 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        $source = openssl_pkey_get_private($res);
        if (!$source) {
            $source = openssl_pkey_get_private(self::$privateKey);
        }
        return $source;
    }

    /**
     * 获取公钥
     * @return resource
     */
    public static function getPublicKey()
    {
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap(self::$publicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $source = openssl_pkey_get_public($res); //解析公钥
        if (!$source) {
            $source = openssl_pkey_get_public(self::$publicKey);
        }
        return $source;
    }
    /**
     * 获取对方公钥
     * @return resource
     */
    public static function getThirdPublicKey()
    {
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap(self::$thirdPublicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $source = openssl_pkey_get_public($res); //解析公钥
        if (!$source) {
            $source = openssl_pkey_get_public(self::$publicKey);
        }
        return $source;
    }

    /**
     * 私钥加密
     * @param $data
     * @return bool|null
     */
    public static function privEncrypt($data = '')
    {
        $privKey = self::getPrivateKey();
        $partLen = self::getKeyBitDetail($privKey) / 8 - 11;
        $parts = str_split($data, $partLen);
        $encrypted = '';
        foreach ($parts as $part) {
            openssl_private_encrypt($part, $partEncrypt, $privKey);
            $encrypted .= $partEncrypt;
        }
        openssl_free_key($privKey);
        return $encrypted ? url_safe_base64_encode($encrypted) : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return bool|null
     */

    public static function publicDecrypt($encrypted = '')
    {
        $pubKey = self::getPublicKey();
        $partLen = self::getKeyBitDetail($pubKey) / 8;
        $parts = str_split(url_safe_base64_decode($encrypted), $partLen);
        $decrypted = '';
        foreach ($parts as $part) {
            openssl_public_decrypt($part, $partDecrypt, $pubKey);
            $decrypted .= $partDecrypt;
        }
        openssl_free_key($pubKey);
        return $decrypted ?: null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return bool|null
     */

    public static function publicEncrypt($data = '')
    {
        $pubKey = self::getPublicKey();
        $partLen = self::getKeyBitDetail($pubKey) / 8 - 11;
        $parts = str_split($data, $partLen);
        $encrypted = '';
        foreach ($parts as $part) {
            openssl_public_encrypt($part, $partEncrypt, $pubKey);
            $encrypted .= $partEncrypt;
        }
        openssl_free_key($pubKey);
        return $encrypted ? url_safe_base64_encode($encrypted) : null;
    }

    /**
     * 使用对方公钥加密
     * @param string $data
     * @return bool|null
     */

    public static function thirdPublicEncrypt($data = '')
    {
        $pubKey = self::getThirdPublicKey();
        $partLen = self::getKeyBitDetail($pubKey) / 8 - 11;
        $parts = str_split($data, $partLen);
        $encrypted = '';
        foreach ($parts as $part) {
            openssl_public_encrypt($part, $partEncrypt, $pubKey);
            $encrypted .= $partEncrypt;
        }
        openssl_free_key($pubKey);
        return $encrypted ? base64_encode($encrypted) : null;
    }


    /**
     * 私钥解密
     * @param string $encrypted
     * @return bool|null
     */
    public static function privDecrypt($encrypted = '')
    {
        $privKey = self::getPrivateKey();
        $partLen = self::getKeyBitDetail($privKey) / 8;
        $parts = str_split(url_safe_base64_decode($encrypted), $partLen);
        $decrypted = '';
        foreach ($parts as $part) {
            openssl_private_decrypt($part, $partDecrypt, $privKey);
            $decrypted .= $partDecrypt;
        }
        openssl_free_key($privKey);
        return $decrypted ?: null;
    }

    /**
     * 第三方私钥解密
     * @param string $encrypted
     * @return bool|null
     */
    public static function thirdPrivDecrypt($encrypted = '')
    {
        $privKey = self::getThirdPrivateKey();
        $partLen = self::getKeyBitDetail($privKey) / 8;
        $parts = str_split(url_safe_base64_decode($encrypted), $partLen);
        $decrypted = '';
        foreach ($parts as $part) {
            openssl_private_decrypt($part, $partDecrypt, $privKey);
            $decrypted .= $partDecrypt;
        }
        openssl_free_key($privKey);
        return $decrypted ?: null;
    }

    /**
     * 私钥签名
     * @param string $data
     * @return null|string
     */

    public static function privSign($data = '')
    {
        $privKey = self::getPrivateKey();
        openssl_sign($data, $sign, $privKey, self::RSA_ALGORITHM_SIGN);
        openssl_free_key($privKey);
        return $sign ? base64_encode($sign) : null;
    }

    /**
     * 使用自己的公钥验签
     * @param string $data
     * @param string $sign
     * @return int 如果签名正确，则为1；如果签名不正确，则为0；如果签名错误，则为-1。
     */
    public static function publicVerifySign($data = '', $sign = '')
    {
        $pubKey = self::getPublicKey();
        $res = openssl_verify($data, url_safe_base64_decode($sign), $pubKey, self::RSA_ALGORITHM_SIGN);
        openssl_free_key($pubKey);
        return $res;
    }
    /**
     * 使用对方的公钥验签
     * @param string $data
     * @param string $sign
     * @return int 如果签名正确，则为1；如果签名不正确，则为0；如果签名错误，则为-1。
     */
    public static function thirdPublicVerifySign($data = '', $sign = '')
    {
        $pubKey = self::getThirdPublicKey();
        $res = openssl_verify($data, url_safe_base64_decode($sign), $pubKey, self::RSA_ALGORITHM_SIGN);
        openssl_free_key($pubKey);
        return $res;
    }
    /**
     * 使用指定的对方的公钥验签
     * @param string $data
     * @param string $sign
     * @return int 如果签名正确，则为1；如果签名不正确，则为0；如果签名错误，则为-1。
     */
    public static function thirdPublicVerifySignByPubKey($data = '', $sign = '', $pubKey)
    {
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $source = openssl_pkey_get_public($res); //解析公钥
        if (!$source) {
            $source = openssl_pkey_get_public($pubKey);
        }
        $res = openssl_verify($data, url_safe_base64_decode($sign), $source, self::RSA_ALGORITHM_SIGN);
        
        // var_dump($res);
        // exit(); 
        
        openssl_free_key($source);
        return $res;
    }

    /**
     * 使用对方公钥加密
     * @param string $data
     * @return bool|null
     */

    public static function thirdPublicEncryptPublicKey($data = '', $pubKeyStr)
    {
//        $pubKey = self::getThirdPublicKey();
//        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap(self::getThirdPublicKey(), 64, "\n", true) . "\n-----END PUBLIC KEY-----";
//        $source = openssl_pkey_get_public($res); //解析公钥
//        if (!$source) {
//            $source = openssl_pkey_get_public(self::$publicKey);
//        }
        $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($pubKeyStr, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $pubKey = openssl_pkey_get_public($res); //解析公钥
        if (!$pubKey) {
            $pubKey = openssl_pkey_get_public(self::$thirdPublicKey);
        }
//        $partLen = self::getKeyBitDetail($pubKey) / 8 - 11;
//        $parts = str_split($data, $partLen);
//        $encrypted = '';
//        foreach ($parts as $part) {
//            openssl_public_encrypt($part, $partEncrypt, $pubKey);
//            $encrypted .= $partEncrypt;
//        }
//        openssl_free_key($pubKey);
//        return $encrypted ? base64_encode($encrypted) : null;



//        $pubKey = self::getThirdPublicKey();
        $partLen = self::getKeyBitDetail($pubKey) / 8 - 11;
        $parts = str_split($data, $partLen);
        $encrypted = '';
        foreach ($parts as $part) {
            openssl_public_encrypt($part, $partEncrypt, $pubKey);
            $encrypted .= $partEncrypt;
        }
        openssl_free_key($pubKey);
        return $encrypted ? base64_encode($encrypted) : null;
    }
}

//echo '创建秘钥对：' . RSA2::createRsaKey();

//$param = [
//    'mechant_id'=>'10000',
//    'content_type'=>'text',
//    'thoroughfare'=>'leshua_auto',
//    'type'=>'13',
//    'out_trade_no'=>'20110302012425',
//    'robin'=>'2',
//    'amount'=>'300.00',
//    'callback_url'=>'http://api.97pay.cc:8989/go/notifyurl.go?sys_channel_id=98_20220120013002873406',
//    'success_url'=>'http://api.97pay.cc:8989/go/notifyurl.go?sys_channel_id=98_20220120013002873406',
//    'error_url'=>'http://api.97pay.cc:8989/go/notifyurl.go?sys_channel_id=98_20220120013002873406',
//];

//$param = [
//            "merchant_id" =>  "106" ,
//            "content_type" =>  "json" ,
//            "thoroughfare" =>  "leshua_auto" ,
//            "out_trade_no" =>  "2022032423274190660" ,
//            "amount" =>  "100.00" ,
//            "type" =>  "13" ,
//            "callback_url" =>  "http://huitiao.freewing123.xyz/pay/nvjing/notify/2022032423274190660/"
//         ];
//ksort($param);
//reset($param);
//
//$signStr = '';
//foreach ($param as $key => $value) {
//    if ($key == 'sign' || $value == '') continue;
//    $signStr .= $key . '=' . $value . '&';
//}
//// 待签名数据
//$signStr = substr($signStr, 0, -1);
//echo '待签名数据：' . $signStr;
//echo PHP_EOL;
//
//// 使用私钥签名
//$sign = RSA2::privSign($signStr);
//echo '签名结果：' . $sign;
//echo PHP_EOL;
//// 使用对方公钥,对签名进行加密
//$pubSec = RSA2::thirdPublicEncrypt($sign);
//echo '加密结果：' . $pubSec;
//echo PHP_EOL;
//// 加密数据放入参数列表
//$param['sign'] = $pubSec;
//echo '最终要发送的数据：' . json_encode($param);
//echo PHP_EOL;
//
//
//
//// 对方接收到数据
//// 解密签名
//$thirdDec = RSA2::thirdPrivDecrypt($pubSec);
//echo '第三方私钥解密,得到签名：' . $thirdDec;
//echo PHP_EOL;
//// 验证签名
//ksort($param);
//reset($param);
//$signStr = '';
//foreach ($param as $key => $value) {
//    if ($key == 'sign' || $value == '') continue;
//    $signStr .= $key . '=' . $value . '&';
//}
//// 待签名数据
//$signStr = substr($signStr, 0, -1);
//echo '待验证签名数据：' . $signStr;
//echo PHP_EOL;
//echo '使用对方公钥验签：' . RSA2::publicVerifySign($signStr, $thirdDec);
//echo PHP_EOL;
//$enSign = "FQMEQIyG0+H7upy/URiLoUUyRwWeYCniRT5Acw3fTtOgsL0MAl/5fWvl3Qv71CM0vdprVIRCP4A+oZmyPYodHgMbtEMM1E1NPx7b995/LZ+TpWblIRDYUh9uPa3rPwFwWDo/8p6dqJ1em+3OzI8RVVN7tqaLkuNDXiM8PZslS6kXJxZn3JkcJ3+C3O6VqyVp9b7IO6m9nTuZlmALgfq+iENXj77ZvqEJAOLtC4GkdQueY2pGW3oWM1whY0LPdNmedxOXJ5Pyz5imq6h+YYqQxZdgrCsNzmpJwQclC3onXUik6w6wcAICBgzZP4wWwrc/zTXKyXePwMjfh9NcMCTt3w1KvzUXuXJ+UwyKlNGaP8m7a4urG9qRIMsLr5XivABANjN2OUocFRiziWzJukiSJ2hppKoH6/rUiYjkgR7z/karwmWIwobcuPSlpeoqdc8MTJa8FK/dwhq6A7z53tFbK7hcsWpsIXXVAgUKHQjkrzrR76hWuCQU83PeZAfIYw1Sf3R2DpqiKg+7RrVjI7h8EDfTcB47p8FbsPTmCvDDfgeeS52JO4Iih7G8buNopZ4ZDM9YObAUNlwyuEcvpf4HB4Et3r2KJh1xYYcuNGzk2kTzT87eZ5z8FGsZEO3YZXjpDue8Vw09o92GDmmwAkNhA+5yPhA2FmYMMRntQD8xrCo=";
////String privateKey = "MIIEuwIBADANBgkqhkiG9w0BAQEFAASCBKUwggShAgEAAoIBAQCQw+FIGoLMUvoU13VKt60UABDH7nNuz3BNbeEP48w0UPdagdK8xFkVvU82LpSYiPjwfrXYHZc/0RYfm0Vwkl7nclzUq38qSO8XCuA0MIiBugVfYBV5Sv9mD/3ZPj7p3+E9nqVD5xDtIWk3400oP4LcoVhcFp7kutqsu3bjNb2o2YHRjP10LKQ1FpIQZp1w8Xx9GwCye+njVsWBQ8R1f5v1PXMxsTzr+rx4kspvnc4q3R+fZCWS0wk/alSoDRPcLqc68As6e+Y+wfhBs/tSw7227xsk4ShXN2puGX7DDJLWgPSAYnAokkttLy3uWmXOc7899ygMH+0ZkW4ciObHISyBAgMBAAECggEANEsuBBffi+OlhwXVzunO0dy8MxzGAg9ZJ/87P7wwNe3RjJY9BAeBjsLV7GKjNv6zvlxnX+xAiMME1OIIEYQuWDkNo51X0HSMOayqFPA7P3FtI2jYGPqoi7vXHTTJET7YZKP8Wy0LBj39fjzT0Ggw43Y9LvbU6xc8iw0lAyqUmnt7h9al0F3AG0npF27U552AJuoZfBeYB8q+xRYmk9nwDDc5Hbizl+gFprHEqa14eDbPs4zVszDTww35QYJugfrqstKoRSCQF3pU0VajGKzN5mhj0nrw/vHCYFqjK2b2yqGLMOsDmZKLUJ+9QCnsrkhkFZsI+lD8Rmy99WlEItp2aQKBgQDbrHGdroSRiZlYIjN6jaPLc5k2wNtAn5mBZoauZKNgwV4NybYGRSAjWB+ZN8VFW5Ea94YU3CngUDHeYO82PQBDi+83KKZHpCabuyp6DfVYqnGD2NMn3p9TJNENDpBrmY3WHbprEZ/EmBY5OVd+XsCpDj2RYDsiemF6RI6FdXHQBwKBgQCotExMYhyYOkVnEsWidhqW15xMomVWM5jcjkK5Q5H8jBjz+8Lh3fE5kgqycjG+nxOHc9k1EYKyVSxvGmq05L19E56WFraSTDQ9foT1ZGID3Xha0XxTzTDv4WWlWaDJmNx6H19IR1jmWMB7E8pL+n2e2R4xB/XvdYtNHmH1DXHtNwKBgQCklO1GszL1iz8NTgp9nlMCoig/YnyiTTEIRxVO4W2alyVtdRvgVLgAYwzBkYxGK5Vqu9qEFHN3cP3722o9m7Rv5w6hp930vOKEONVZPs370S9dFf0V2PBLrcDGZIwFYbVnnzxE7Z5i/4Ne+jw4HKuBvX6ZRZzodwJDRcv154kOzwKBgBzjMqVpr2fJopSxvDHDc24c4WCl9iA6mZQ6r+Y1Ucwi2Sr+EzLF5EZtYXOI5kezyY5KIglaRDzxJLipl1f+Swwdzev5W63VaqSVA4NZewcaZz9124ol+pk4yUT1AflDOY2XzaL8xJY84Eiy7NLxw4zttKPErzIfuIiuCSwC710lAn8UxqZ/0W8kY7M4B/baavlXHBxoB05+va39ltNQyDNXwgHmkOsTLjHLolK+haGs2+x/c7kGXl302prj3j3kliVqbfAvnRgSzbgVvH2sD98nRCg6EdjIWyBQP1+HarErKWiLE+mBlcwvx8dJUox6ISjs+b59Ku0O+G6GUY8Quith";
//echo RSA2::privDecrypt($enSign);
//echo PHP_EOL;

