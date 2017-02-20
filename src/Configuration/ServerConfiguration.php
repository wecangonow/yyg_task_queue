<?php

namespace Yyg\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Oasis\Mlib\Config\AbstractYamlConfiguration;

class ServerConfiguration extends AbstractYamlConfiguration
{
    public $is_debug;
    public $timezone;
    public $queues;
    public $log_path;
    public $swoole_server_info;
    public $redis_server_info;

    public function load()
    {
        $this->loadYaml(
            "config.yml",
            [
                PROJECT_DIR . "/config",
                "../../"
            ]
        );
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root("server");
        {
            $rootNode->children()->booleanNode("is_debug")->defaultValue(false);
            $rootNode->children()->scalarNode("log_path")->defaultValue("/tmp");
            $rootNode->children()->scalarNode("timezone")->defaultValue("GMT");
            $rootNode->children()->scalarNode("queues")->defaultValue("default");

            $services = $rootNode->children()->arrayNode("services");
            {
                $swoole_server = $services->children()->arrayNode("swoole_server");
                {
                    $swoole_server->children()->scalarNode("ip");
                    $swoole_server->children()->integerNode("port");
                    $swoole_server->children()->scalarNode("pack_type");
                    $swoole_server->children()->scalarNode("mode");
                    $set = $swoole_server->children()->arrayNode("set");
                    {
                        $set->children()->scalarNode("user");
                        $set->children()->scalarNode("group");
                        $set->children()->integerNode("worker_num");
                        $set->children()->integerNode("task_worker_num");
                        $set->children()->integerNode("dispatch_mode");
                        $set->children()->booleanNode("open_cpu_affinity")->defaultTrue();
                        $set->children()->booleanNode("open_tcp_nodelay")->defaultTrue();
                        $set->children()->scalarNode("package_max_length");
                        $set->children()->booleanNode("daemonize")->defaultTrue();
                        $set->children()->scalarNode("log_file")->defaultFalse(PROJECT_DIR . "/logs/swoole_server.log");
                    }
                }

                $redis_server = $services->children()->arrayNode("redis");
                {
                    $redis_server->children()->scalarNode("host");
                    $redis_server->children()->scalarNode("port");
                }
            }
        }

        return $treeBuilder;

    }

    public function assignProcessedConfig()
    {
        $this->is_debug           = $this->processedConfig["is_debug"];
        $this->log_path           = $this->processedConfig["log_path"];
        $this->timezone           = $this->processedConfig["timezone"];
        $this->queues             = $this->processedConfig["queues"];
        $this->swoole_server_info = $this->processedConfig["services"]["swoole_server"];
        $this->redis_server_info  = $this->processedConfig["services"]["redis_server"];

    }

    public function dumpConfig()
    {
        print_r($this->processedConfig);
    }
    
}