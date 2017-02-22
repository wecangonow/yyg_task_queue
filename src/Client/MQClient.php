<?php

namespace Yyg\Client;


class MqClient
{
    /**
     * @var swoole_client
     */
    protected $client = null;
    /**
     * @var string
     */
    protected $queueName = '';
    /**
     * @var int
     */
    protected $blockTime = false;
    /**
     * 获取客户端实例
     *
     * @param  array $config
     * @return self
     */
    public static function getInstance(array $config = array())
    {
        static $instance = null;
        if (isset($instance)) {
            return $instance;
        }
        $instance = new self($config);
        return $instance;
    }
    /**
     * 构造
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {

        $this->client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
        $this->client->set(
            [
                'open_length_check' => true,
                'package_length_type' => MqClientPacket::HEADER_PACK,
                'package_length_offset' => 0,
                'package_body_offset' => MqClientPacket::HEADER_SIZE]);
        if (! $this->client->connect($config['ip'], $config['port'], 30)) {
            throw new \Exception("connect failed. error: {$this->client->errCode}\n");
        }

        $this->client->on("connect", function($cli){
            echo "I connected the server\n";
        });

        $this->client->on("receive", function($cli, $data){
            echo "Data from server: $data\n";
        });
    }
    /**
     * 设置队列
     *
     * @param string $name
     */
    public function setQueue($name)
    {
        $this->queueName = $name;
    }
    /**
     * pop时是否采取block模式
     *
     * @param int $time
     */
    public function block($time)
    {
        $this->blockTime = $time;
    }
    /**
     * 同步推送
     *
     * @param string $data
     * @return array
     */
    public function push($data)
    {
        $data = ['method' => 'push', 'queue' => $this->queueName, 'data' => $data];
        return $this->run($data);
    }
    /**
     * 获取队列
     *
     * @return array
     */
    public function pop()
    {
        $data = ['method' => 'pop', 'queue' => $this->queueName, 'block' => $this->blockTime];
        return $this->run($data);
    }
    /**
     * 确认消息
     *
     * @param string $id
     * @return array
     */
    public function ack($id)
    {
        $data = ['method' => 'ack', 'queue' => $this->queueName, 'id' => $id];
        return $this->run($data);
    }
    /**
     * 确认消息失败
     *
     * @param string $id
     * @return array
     */
    public function nack($id)
    {
        $data = ['method' => 'nack', 'queue' => $this->queueName, 'id' => $id];
        return $this->run($data);
    }
    /**
     * 返回数据
     *
     * @return mixed
     */
    protected function run($data)
    {
        $this->client->send(MqClientPacket::encode(json_encode($data)));
        $r = $this->client->recv();
        MqClientPacket::decode($r);
        return json_decode($r, true);
    }
}
