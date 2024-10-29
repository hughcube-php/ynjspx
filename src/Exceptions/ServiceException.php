<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2024/10/29
 * Time: 16:24
 */

namespace HughCube\Ynjspx\Exceptions;

use HughCube\Ynjspx\Response;

class ServiceException extends Exception
{
    protected $response;

    public function __construct(Response $response, $message = "", $code = 0, $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getResponseCode()
    {
        return $this->getResponse()->getCode();
    }
}
