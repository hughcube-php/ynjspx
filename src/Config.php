<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2024/10/25
 * Time: 15:50.
 */

namespace HughCube\Ynjspx;

use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

class Config
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function getSignType()
    {
        return $this->config['SignType'] ?? null ?: 'RSA2';
    }

    public function getAppId()
    {
        return $this->config['AppId'] ?? null;
    }

    public function getSdkAppId()
    {
        return ($this->config['SdkAppId'] ?? null) ?: $this->getAppId();
    }

    public function getPrivateKey()
    {
        return $this->config['PrivateKey'] ?? null;
    }

    public function enableDebug(): bool
    {
        return boolval($this->config['debug'] ?? false);
    }

    public function getSsoUrl()
    {
        return $this->config['SsoUrl'] ?? null;
    }

    public function getHttp(): array
    {
        return array_merge($this->getDefaultHttp(), $this->config['Http'] ?? []);
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->config['logger'] ?? null;
    }

    public function getDefaultHttp(): array
    {
        return [
            RequestOptions::TIMEOUT         => 10.0,
            RequestOptions::CONNECT_TIMEOUT => 1.0,
            RequestOptions::READ_TIMEOUT    => 10.0,
            RequestOptions::HEADERS         => ['User-Agent' => null],
        ];
    }
}
