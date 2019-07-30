<?php
/**
 * 数据推送目标
 *
 * @author   xiaoqingping <xiaoqingping@qq.com>
 * @date     2019-07-26 10:41:31
 * @modifiedby xiaoqingping
 */
namespace collector\target;

use collector\utils\Sign;

abstract class AbstractTarget
{
    protected $endpoint;
    protected $method;
    protected $sign = null;

    public function __construct($endpoint, $method = 'post', Sign $sign = null)
    {
        $this->endpoint = $endpoint;
        $this->method = (strtolower($method) == 'get') ? 'get' : 'post';
        $this->sign = $sign;
    }

    public function setTarget($endpoint, $method = 'post')
    {
        $this->endpoint = $endpoint;
        $this->method = (strtolower($method) == 'get') ? 'get' : 'post';
    }

    public function setSign(Sign $sign)
    {
        $this->sign = $sign;
    }

    public function getSign()
    {
        return $this->sign;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getMethod()
    {
        return $this->method;
    }

    abstract public function buildParams($params);
}
