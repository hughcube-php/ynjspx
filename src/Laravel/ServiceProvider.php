<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:32 下午.
 */

namespace HughCube\Ynjspx\Laravel;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Boot the provider.
     */
    public function boot()
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->configure($this->getPackageFacadeAccessor());
        }
    }

    /**
     * Register the provider.
     */
    public function register()
    {
        $this->app->singleton($this->getPackageFacadeAccessor(), function ($app) {
            return $this->createPackageFacadeRoot($app);
        });
    }

    protected function getPackageFacadeAccessor(): string
    {
        return Ynjspx::getFacadeAccessor();
    }

    protected function createPackageFacadeRoot($app): Manager
    {
        return new Manager($app);
    }
}
