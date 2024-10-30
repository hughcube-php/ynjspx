<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:21 下午.
 */

namespace HughCube\Ynjspx;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Utils;
use HughCube\GuzzleHttp\Client as HttpClient;
use HughCube\GuzzleHttp\HttpClientTrait;
use HughCube\Ynjspx\Exceptions\Exception;
use HughCube\Ynjspx\Exceptions\ServiceException;
use Psr\Http\Client\ClientExceptionInterface as HttpClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Client
{
    use HttpClientTrait {
        getHttpClient as public;
    }

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    public function getVersion(): string
    {
        return 'v1.0.22';
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    protected function createHttpClient(): HttpClient
    {
        $config = $this->getConfig()->getHttp();
        $config['handler'] = $handler = HandlerStack::create();

        /** User-Agent */
        $handler->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $userAgent = $request->getHeader('User-Agent');
                if (empty($userAgent) || (is_array($userAgent) && empty(array_filter($userAgent)))) {
                    $request = $request->withHeader('User-Agent', sprintf('Yn/%s', $this->getVersion()));
                }
                return $handler($request, $options);
            };
        });

        /** 签名 */
        $handler->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $params = json_decode($request->getBody()->getContents(), true);
                $params = (JSON_ERROR_NONE === json_last_error() ? $params : null) ?: [];

                unset($params['sign']);
                $params = array_merge(['charset' => 'UTF-8'], $params);
                $params = array_merge(['appId' => $this->getConfig()->getAppId()], $params);
                $params = array_merge(['signType' => $this->getConfig()->getSignType()], $params);

                $params['sign'] = Openssl::hashContent(
                    $this->getConfig()->getSignType(),
                    $this->getConfig()->getPrivateKey(),
                    Openssl::makeContent($params)
                );

                $request = $request->withBody(Utils::streamFor(json_encode($params)));

                return $handler($request, $options);
            };
        });

        /** 输出请求debug信息 */
        $handler->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {

                /** @var Promise $promise */
                $promise = $handler($request, $options);

                return $promise->then(function (ResponseInterface $response) use ($request, $options) {
                    if ($this->getConfig()->enableDebug() || true === boolval($options['extra']['debug'] ?? false)) {
                        echo sprintf('> %s %s', $request->getMethod(), strval($request->getUri())), PHP_EOL;
                        foreach ($request->getHeaders() as $name => $values) {
                            foreach ($values as $value) {
                                echo sprintf('> %s: %s', $name, $value), PHP_EOL;
                            }
                        }
                        $request->getBody()->rewind();
                        echo sprintf('> %s', $request->getBody()->getContents()), PHP_EOL;

                        echo PHP_EOL;

                        echo sprintf('< %s %s', $response->getStatusCode(), $response->getReasonPhrase()), PHP_EOL;
                        foreach ($response->getHeaders() as $name => $values) {
                            foreach ($values as $value) {
                                echo sprintf('< %s: %s', $name, $value), PHP_EOL;
                            }
                        }
                        echo sprintf('< %s', $response->getBody()->getContents()), PHP_EOL;
                        $response->getBody()->rewind();
                        echo PHP_EOL, PHP_EOL;
                    }

                    return $response;
                });
            };
        });

        return new HttpClient($config);
    }

    /**
     * @throws Exception
     */
    public function request(string $method, $uri = '', array $options = []): Response
    {
        $response = new Response(
            $this->getHttpClient()->requestLazy($method, $uri, $options)
        );

        if (null == $response->getCode()) {
            throw new ServiceException($response, 'The interface response is incorrect.');
        } elseif (!$response->isSuccess()) {
            throw new ServiceException($response, sprintf('%s(%s)', $response->getMessage(), $response->getCode()));
        }

        return $response;
    }

    /**
     * @return null|Response
     * @throws Throwable
     *
     */
    public function tryRequest(string $method, $uri = '', array $options = [], $times = 3)
    {
        for ($i = 1; $i <= $times; $i++) {
            $response = $exception = null;

            try {
                $response = $this->request($method, $uri, $options);
                break;
            } catch (HttpClientException $exception) {
            }
        }

        if (isset($exception) && $exception instanceof Throwable) {
            throw $exception;
        }

        return $response ?? null;
    }
}
