<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2024/10/25
 * Time: 16:22
 */

namespace HughCube\Ynjspx;

use InvalidArgumentException;

class Openssl
{
    public static function makeContent(array $params): string
    {
        ksort($params);
        $content = [];
        foreach ($params as $key => $value) {
            if (!empty($key) && !is_null($value)) {
                $content[] = sprintf('%s=%s', $key, $value);
            }
        }
        return implode('&', $content);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function hashContent(string $type, string $privateKey, string $content): string
    {
        /** 私钥 */
        $privateKey = str_replace(["-----BEGIN PRIVATE KEY-----", "-----END PRIVATE KEY-----", "\n", "\r"], '', $privateKey);
        $privateKey = "-----BEGIN PRIVATE KEY-----\n" . chunk_split($privateKey, 64, "\n") . "-----END PRIVATE KEY-----\n";
        $privateKeyId = openssl_pkey_get_private($privateKey);
        if (!$privateKeyId) {
            throw new InvalidArgumentException('The private key format is incorrect!');
        }

        /** 加密方式 */
        $digest = 'RSA2' === strtoupper($type) ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;

        /** 签名 */
        openssl_sign($content, $sign, $privateKeyId, $digest);

        /** 转base64 */
        return base64_encode($sign);
    }
}
