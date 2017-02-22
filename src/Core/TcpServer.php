<?php

namespace Yyg\Core;

use Yyg\Configuration\ServerConfiguration;

class TcpServer
{
    protected static $instance = null;

    protected $serv = null;

    public function __construct()
    {

        $conf = ServerConfiguration::instance()->swoole_server_info;

        $ip   = $conf['ip'];
        $port = $conf['port'];
        $mode = constant($conf['mode']);
        $set  = $conf['set'];

        if ($conf['pack_type'] == 'eof') {
            $set['open_eof_check'] = true;
            $set['open_eof_split'] = true;
            empty($set['package_eof']) && $set['package_eof'] = "\r\n\r\n";
        }
        else {
            $set['open_length_check']     = true;
            $set['package_length_type']   = Packet::HEADER_PACK;
            $set['package_length_offset'] = 0;
            $set['package_body_offset']   = Packet::HEADER_SIZE;
        }

        $this->serv = new \Swoole\Server($ip, $port, $mode);

        $this->serv->on('Start', [$this, 'onStart']);
        $this->serv->on('Receive', [$this, 'onReceive']);
        $this->serv->on('Connect', [$this, 'onConnect']);
        $this->serv->on('Close', [$this, 'onClose']);
        $this->serv->on('Task', [$this, 'onTask']);
        $this->serv->on('Finish', [$this, 'onFinish']);

        $this->serv->set($set);

    }

    public static function getInstance()
    {
        return isset(self::$instance) ? self::$instance : (self::$instance = new self());
    }

    public function run()
    {
        $this->serv->start();
    }

    public function onStart(\swoole_server $serv)
    {
        echo SWOOLE_VERSION . " onStart\n";
    }

    public function onConnect(\swoole_server $serv, $fd)
    {
        echo $fd . " Client Connected.\n";
    }

    public function onReceive(\swoole_server $serv, $fd, $from_id, $data)
    {
        Packet::decode($data);

        if (ServerConfiguration::instance()->is_debug) {
            mdebug(
                "PID: %d -- MEM_USAGE: %s -- METHOD: %s -- FD: %d -- FROM: %d -- DATA: %s",
                posix_getpid(),
                memory_get_usage(),
                __METHOD__,
                $fd,
                $from_id,
                $data
            );

        }


        $task_info = ['fd' => $fd, 'request_data' => json_decode($data, true)];

        //$serv->send($fd , "hello world");
        $serv->task(json_encode($task_info));

    }

    public function onTask(\swoole_server $serv, $task_id, $from_id, $data)
    {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        echo "Data: {$data}\n";
        for($i = 0 ; $i < 2 ; $i ++ ) {
            sleep(1);
            echo "Task {$task_id} Handle {$i} times...\n";
        }
        $fd = json_decode($data, true);

        $serv->send($fd['fd'] , $data);

        return $data;

    }

    public function onFinish(\swoole_server $serv, $task_id, $data)
    {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";

    }

    public function onClose(\swoole_server $serv, $fd)
    {
        if (ServerConfiguration::instance()->is_debug) {
            mdebug(
                "PID: %d -- MEM_USAGE: %s -- METHOD: %s -- FD: %d is closed --",
                posix_getpid(),
                memory_get_usage(),
                __METHOD__,
                $fd
            );

        }
        $serv->close($fd);
    }

    public function send($fd, $data)
    {
        return $this->serv->send($fd, Packet::encode($data));
    }

    public function close($fd)
    {
        return $this->serv->close($fd);
    }
}