<?php
/**
 * 爬虫采集
 *
 * @author   xiaoqingping <xiaoqingping@qq.com>
 * @date     2019-07-25 19:23:17
 * @modifiedby xiaoqingping
 */
namespace collector;

use collector\origin\AbstractOrigin;
use collector\target\AbstractTarget;

interface CollectorInterface
{
    /**
     * 添加采集源
     *
     * @param Origin $origin
     */
    public function addOrigin(AbstractOrigin $origin);

    /**
     * 设置数据推送目标地址
     *
     * @param Target $target
     */
    public function setTarget(AbstractTarget $target);

    // 执行爬取
    public function execute();
}
