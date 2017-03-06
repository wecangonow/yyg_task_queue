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
    public $email;
    public $redis_server_info;

    public function load()
    {
        $this->loadYaml(
            "config.yml",
            [
                PROJECT_DIR . "/config",
                "../../",
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
                $mysql_server = $services->children()->arrayNode("mysql");
                {
                    $mysql_server->children()->scalarNode("host");
                    $mysql_server->children()->integerNode("port");
                    $mysql_server->children()->scalarNode("user");
                    $mysql_server->children()->scalarNode("password");
                    $mysql_server->children()->scalarNode("dbname");
                    $mysql_server->children()->scalarNode("charset");
                }

                $redis_server = $services->children()->arrayNode("redis");
                {
                    $redis_server->children()->scalarNode("host");
                    $redis_server->children()->scalarNode("port");
                }

                $email = $services->children()->arrayNode("email");
                {
                    $email->children()->scalarNode("host");
                    $email->children()->integerNode("port");
                    $email->children()->scalarNode("username");
                    $email->children()->scalarNode("password");
                    $email->children()->booleanNode("auth");

                    $info = $email->children()->arrayNode("info");
                    {
                        $malaysia_info = $info->children()->arrayNode("malaysia");
                        {
                            $malaysia_info->children()->scalarNode("sender");
                            $malaysia_info->children()->scalarNode("receiver");
                            $malaysia_info->children()->scalarNode("title");
                        }
                        $turkey_info = $info->children()->arrayNode("turkey");
                        {
                            $turkey_info->children()->scalarNode("sender");
                            $turkey_info->children()->scalarNode("receiver");
                            $turkey_info->children()->scalarNode("title");
                        }
                        $russia_info = $info->children()->arrayNode("russia");
                        {
                            $russia_info->children()->scalarNode("sender");
                            $russia_info->children()->scalarNode("receiver");
                            $russia_info->children()->scalarNode("title");
                        }
                    }
                }
            }
        }

        return $treeBuilder;

    }

    public function assignProcessedConfig()
    {
        $this->is_debug          = $this->processedConfig["is_debug"];
        $this->log_path          = $this->processedConfig["log_path"];
        $this->email             = $this->processedConfig['services']["email"];
        $this->timezone          = $this->processedConfig["timezone"];
        $this->queues            = $this->processedConfig["queues"];
        $this->redis_server_info = $this->processedConfig["services"]["redis_server"];

    }

    public function dumpConfig()
    {
        print_r($this->processedConfig);
    }
    
}