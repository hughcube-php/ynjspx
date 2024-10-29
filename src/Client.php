<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:21 下午.
 */

namespace HughCube\Ynjspx;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\RequestOptions;
use HughCube\GuzzleHttp\Client as HttpClient;
use HughCube\GuzzleHttp\HttpClientTrait;
use HughCube\Ynjspx\Exceptions\ClientException;
use HughCube\Ynjspx\Exceptions\Exception;
use HughCube\Ynjspx\Exceptions\ServiceException;
use Psr\Http\Client\ClientExceptionInterface as HttpClientException;
use Psr\Http\Message\RequestInterface;
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

    public function getConfig(): Config
    {
        return $this->config;
    }

    protected function createHttpClient(): HttpClient
    {
        $config = $this->getConfig()->getHttp();
        $config['handler'] = $handler = HandlerStack::create();

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

                $request = $request->withBody(Utils::streamFor($content = json_encode($params)));

                return $handler($request, $options);
            };
        });

        return new HttpClient($config);
    }

    /**
     * @throws Exception
     */
    public function request(string $method, $uri = '', array $options = []): Response
    {
        try {
            $response = new Response(
                $this->getHttpClient()->requestLazy($method, $uri, $options)
            );

            if ($this->getConfig()->enableDebug()) {
                $response->isSuccess();
                echo sprintf('%s %s', $method, $uri), PHP_EOL;
                echo json_encode($options[RequestOptions::JSON]), PHP_EOL;
                echo $response->getHttpResponse()->getBodyContents(), PHP_EOL;
                echo PHP_EOL;
            }

            if (!$response->isSuccess()) {
                throw new ServiceException($response, $response->getMessage(), 0);
            }

            return $response;
        } catch (HttpClientException $exception) {
            throw new ClientException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws Throwable
     *
     * @return null|Response
     */
    public function tryRequest(string $method, $uri = '', array $options = [], $times = 3)
    {
        for ($i = 1; $i <= $times; $i++) {
            $response = $exception = null;

            try {
                $response = $this->request($method, $uri, $options);
                break;
            } catch (ClientException $exception) {
            }
        }

        if (isset($exception) && $exception instanceof Throwable) {
            throw $exception;
        }

        return $response ?? null;
    }
}
