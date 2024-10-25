<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 11:45 下午.
 */

namespace HughCube\Ynjspx\Tests;

use HughCube\GuzzleHttp\Client as HttpClient;
use HughCube\Ynjspx\Client;
use HughCube\Ynjspx\Laravel\Manager;
use HughCube\Ynjspx\Laravel\Ynjspx;

class FacadeTest extends TestCase
{
    public function testIsFacade()
    {
        $this->assertInstanceOf(Manager::class, Ynjspx::getFacadeRoot());
    }

    public function testClient()
    {
        $this->assertInstanceOf(Client::class, Ynjspx::client());
    }

    public function testGetHttpClient()
    {
        $this->assertInstanceOf(HttpClient::class, Ynjspx::getHttpClient());
    }
}
