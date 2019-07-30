<?php
namespace collector;

use GuzzleHttp\Client;
use collector\origin\AbstractOrigin;
use collector\target\AbstractTarget;

abstract class AbstractCollector
{
    protected $client = null;
    protected $origin = null;
    protected $target = null;

    public $startHandler = null;
    public $beforeFetchHandler = null;
    public $afterFetchHandler = null;
    public $beforePushHandler   = null;
    public $afterPushHandler   = null;
    public $endHandler = null;

    public function __construct(Client $client = null)
    {
        if ($client == null) {
            $this->client = new Client;
        } else {
            $this->client = $client;
        }
    }

    public function addOrigin(AbstractOrigin $origin)
    {
        $this->origin[] = $origin;
    }

    public function setTarget(AbstractTarget $target)
    {
        $this->target = $target;
    }

    public function execute()
    {
        // 如果没有配置采集源
        if (is_null($this->origin) || empty($this->origin)) {
            return;
        }

        if ($this->startHandler && is_callable($this->startHandler)) {
            $status = call_user_func($this->startHandler, $this);
            if ($status === Constant::EXIT) {
                return;
            }
        }

        foreach ($this->origin as $origin) {
            $urls = $origin->getUrls();
            foreach ($urls as $url) {
                // 抓取数据前的回调
                if ($this->beforeFetchHandler && is_callable($this->beforeFetchHandler)) {
                    $status = call_user_func($this->beforeFetchHandler, $url);
                    if ($status === Constant::NEXT_LOOP) {
                        continue;
                    } elseif ($status === Constant::BREAK_LOOP) {
                        break;
                    } elseif ($status === Constant::EXIT) {
                        return;
                    }
                }
                // 抓取数据
                try {
                    $resp = $this->client->get($url);
                } catch (\Exception $e) {
                    $resp = $this->client->get($url);
                }

                // 格式转化
                $type = $resp->getHeader('content-type');
                $parsed = \GuzzleHttp\Psr7\parse_header($type);
                $original_body = (string)$resp->getBody();
                $utf8_body = mb_convert_encoding($original_body, 'UTF-8', $parsed[0]['charset'] ?: 'UTF-8');
                // 生成请求体
                $body_data = $origin->parseHandler($utf8_body);
                
                if ($this->afterFetchHandler && is_callable($this->afterFetchHandler)) {
                    $status = call_user_func($this->afterFetchHandler, $body_data);
                    if ($status === Constant::NEXT_LOOP) {
                        continue;
                    } elseif ($status === Constant::BREAK_LOOP) {
                        break;
                    } elseif ($status === Constant::EXIT) {
                        return;
                    }
                }

                // 如果有配置数据推送目标
                if (!is_null($this->target) && $this->target instanceof AbstractTarget) {
                    if ($origin->isList()) {
                        foreach ($body_data as $item) {
                            $status = $this->push($item);
                            if ($status === Constant::NEXT_LOOP) {
                                continue;
                            } elseif ($status === Constant::BREAK_LOOP) {
                                break;
                            } elseif ($status === Constant::EXIT) {
                                return;
                            }
                        }
                    } else {
                        $status = $this->push($body_data);
                        if ($status === Constant::NEXT_LOOP) {
                            continue;
                        } elseif ($status === Constant::BREAK_LOOP) {
                            break;
                        } elseif ($status === Constant::EXIT) {
                            return;
                        }
                    }
                }

                sleep($origin->getInterval());
            }
        }

        if ($this->endHandler && is_callable($this->endHandler)) {
            $status = call_user_func($this->endHandler, $this);
            if ($status === Constant::EXIT) {
                return;
            }
        }
    }

    protected function push($data)
    {
        $body = $this->target->buildParams($data);

        if ($this->beforePushHandler && is_callable($this->beforePushHandler)) {
            $status = call_user_func($this->beforePushHandler, $body);
            if ($status === Constant::NEXT_LOOP || $status === Constant::BREAK_LOOP || $status === Constant::EXIT) {
                return $status;
            }
        }

        try {
            $result = $this->client->request($this->target->getMethod(), $this->target->getEndpoint(), [
                'form_params' => $body,
            ]);
        } catch (\Exception $e) {
            return Constant::NEXT_LOOP;
        }

        if ($this->afterPushHandler && is_callable($this->afterPushHandler)) {
            $status = call_user_func($this->afterPushHandler, $result);
            if ($status === Constant::NEXT_LOOP || $status === Constant::BREAK_LOOP || $status === Constant::EXIT) {
                return $status;
            }
        }
    }
}
