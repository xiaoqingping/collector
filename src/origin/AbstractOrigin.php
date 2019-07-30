<?php
/**
 * 采集源
 *
 * @author   xiaoqingping <xiaoqingping@qq.com>
 * @date     2019-07-26 09:53:07
 * @modifiedby xiaoqingping
 */
namespace collector\origin;

abstract class AbstractOrigin
{
    protected $origin;
    protected $isList = false;
    protected $urls = [];
    protected $interval = 0;
    protected $method = 'get';

    public function __construct($origin, $options = [])
    {
        $this->origin = $origin;
        if (array_key_exists('list', $options)) {
            $this->isList = $options['list'];
        }
        if (array_key_exists('interval', $options)) {
            $this->interval = $options['interval'];
        }
        if (array_key_exists('method', $options) && strtolower($options['method']) == 'post') {
            $this->method = strtolower($options['method']);
        }
    }

    /**
     * 设置数据源
     *
     * @param string $origin
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    /**
     * 返回数据源
     *
     * @param [type] $origin
     * @return void
     */
    public function getOrigin($origin)
    {
        return $this->origin;
    }

    /**
     * 添加url
     *
     * @param string $url
     * @return void
     */
    public function addUrl($url)
    {
        $this->urls[] = $url;
        $this->urls = array_unique($this->urls);
    }

    /**
     * 获取所有的url
     *
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * 设置爬取内容是否是列表（单次爬取能获取多组内容）
     *
     * @param boolean $isList
     * @return void
     */
    public function setList($isList = true)
    {
        $this->isList = $isList;
    }

    /**
     * 返回爬取内容是否列表配置
     *
     * @return boolean
     */
    public function isList()
    {
        return $this->isList;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * 拼接请求url
     */
    abstract protected function buildUrl($params);

    /**
     * 数据处理
     *
     * @param string $data
     */
    abstract public function parseHandler($data);
}
