<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2024/10/26
 * Time: 11:11.
 */

namespace HughCube\Ynjspx;

use BadMethodCallException;
use HughCube\GuzzleHttp\LazyResponse;

class Response
{
    /**
     * @var LazyResponse
     */
    protected $httpResponse;

    public function __construct(LazyResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    public function getHttpResponse(): LazyResponse
    {
        return $this->httpResponse;
    }

    /**
     * @return array|null
     */
    public function getBodyArray()
    {
        return $this->getHttpResponse()->toArray(false);
    }

    public function getHttpStatusCode(): int
    {
        return $this->getHttpResponse()->getStatusCode();
    }

    public function getCode()
    {
        return $this->getBodyArray()['code'] ?? null;
    }

    public function getMessage()
    {
        return $this->getBodyArray()['msg'] ?? null;
    }

    public function getTimestamp()
    {
        return $this->getBodyArray()['timestamp'] ?? null;
    }

    public function isSuccess(): bool
    {
        return '000000' === $this->getCode();
    }

    public function getData()
    {
        return $this->getBodyArray()['data'] ?? null;
    }

    public function __call($name, $arguments = [])
    {
        if (str_starts_with($name, $prefix = 'getData')) {
            $key = substr($name, strrpos($name, $prefix) + strlen($prefix));

            return $this->getBodyArray()['data'][lcfirst($key)] ?? null;
        }

        return new BadMethodCallException();
    }
}
