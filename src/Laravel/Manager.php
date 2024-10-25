<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 4:19 下午.
 */

namespace HughCube\Ynjspx\Laravel;

use HughCube\Ynjspx\Client;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Support\Manager as IlluminateManager;
use InvalidArgumentException;

/**
 * @property callable|ContainerContract|null $container
 * @property callable|Repository|null $config
 */
class Manager extends IlluminateManager
{
    /**
     * @throws BindingResolutionException
     */
    protected function getContainerConfig(): Repository
    {
        if (!property_exists($this, 'config') || null === $this->config) {
            return $this->getContainer()->make('config');
        }

        if (is_callable($this->config)) {
            return call_user_func($this->config);
        }

        return $this->config;
    }

    protected function getPackageFacadeAccessor(): string
    {
        return Ynjspx::getFacadeAccessor();
    }

    protected function getDriversConfigKey(): string
    {
        return 'clients';
    }

    /**
     * @param null|string|int $name
     * @param mixed $default
     * @return array|mixed
     * @throws BindingResolutionException
     */
    protected function getPackageConfig($name = null, $default = null)
    {
        $key = sprintf('%s%s', $this->getPackageFacadeAccessor(), (null === $name ? '' : ".$name"));
        return $this->getContainerConfig()->get($key, $default);
    }

    /**
     * Get the configuration for a client.
     * @param string $name
     * @return array
     * @throws BindingResolutionException
     */
    protected function configuration(string $name): array
    {
        $name = $name ?: $this->getDefaultDriver();
        $config = $this->getPackageConfig(sprintf('%s.%s', $this->getDriversConfigKey(), $name));

        if (null === $config) {
            throw new InvalidArgumentException(sprintf(
                "%s %s[{$name}] not configured.",
                $this->getPackageFacadeAccessor(),
                $this->getDriversConfigKey()
            ));
        }

        return array_merge($this->getDriverDefaultConfig(), $config);
    }

    /**
     * @inheritdoc
     * @throws BindingResolutionException
     */
    public function getDefaultDriver(): string
    {
        return $this->getPackageConfig('default', 'default');
    }

    /**
     * @inheritdoc
     * @throws BindingResolutionException
     */
    protected function createDriver($driver)
    {
        return $this->makeDriver($this->configuration($driver));
    }

    /**
     * @return array
     * @throws BindingResolutionException
     */
    protected function getDriverDefaultConfig(): array
    {
        return $this->getPackageConfig('defaults', []);
    }

    public function client($driver = null)
    {
        return $this->driver($driver);
    }

    protected function makeDriver(array $config): Client
    {
        return new Client($config);
    }
}
