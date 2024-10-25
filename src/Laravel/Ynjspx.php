<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/18
 * Time: 10:31 下午.
 */

namespace HughCube\Ynjspx\Laravel;

use HughCube\GuzzleHttp\Client as HttpClient;
use HughCube\Ynjspx\Client;
use Illuminate\Support\Facades\Facade as IlluminateFacade;
use Illuminate\Support\Str;

/**
 * Class Package.
 *
 * @method static Client     client(string $name = null)
 * @method static HttpClient getHttpClient()
 *
 * @see \HughCube\Ynjspx\Laravel\Manager
 * @see \HughCube\Ynjspx\Laravel\ServiceProvider
 */
class Ynjspx extends IlluminateFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor(): string
    {
        return lcfirst(Str::afterLast(static::class, '\\'));
    }

    protected static function registerServiceProvider($app)
    {
        $app->register(ServiceProvider::class);
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @inheritdoc
     */
    protected static function resolveFacadeInstance($name)
    {
        if (!isset(static::$resolvedInstance[$name])
            && null !== static::$app && !isset(static::$app[$name])
        ) {
            static::registerServiceProvider(static::$app);
        }

        return parent::resolveFacadeInstance($name);
    }
}
