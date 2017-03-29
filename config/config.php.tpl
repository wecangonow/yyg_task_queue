<?php

$configs = [

    "is_debug"       => true,
    "log_path"       => "/data/logs/tasks",
    "file_path"      => "/data/logs/", //存放程序生成的报表文件
    "timezone"       => "Asia/Shanghai",
    "timer_interval" => 2,   // 执行任务定时器 时间间隔
    "prize"          => [
        "low_ratio"                       => 0.75,
        "loose_ratio"                     => 10,
        "high_ratio"                      => 4,
        "kill_ratio"                      => 0.1,
        "rt_magic_prize"                  => 888888,
        "period_time"                     => 30,  // 天
        "period_money_top"                => 100,  // 天
        // 必须是合法的php表达式
        "user_roi_expression"             => "if(user_roi > 1.5 ||  (0.79 < user_roi && user_roi < 1.15)) {return true;} else { return false;}",
        "user_period_consume_key_scheme"  => "malaysia:user_period_consume:sorted_set#{uid}",
        "nper_prize_key_scheme"           => "malaysia:nper_prize:sorted_set#{nid}",
        "user_life_win_key_scheme"        => "malaysia:user_life_win:set#{uid}",
        "already_consume_user_key_scheme" => "malaysia:already_consume_user",
        "robot_set"                       => "malaysia:robot_set",
        "goods_open_result"               => "malaysia:goods_open_result:set#{gid}",  //存储商品相关的期号 关联一个hash 结构
        "goods_open_result_related_info"  => "malaysia:goods_open_result_related:hash#{nid}",  // nid  nper_id
    ],
    "bonus"          => [
        // 保存每期每用户的购买钱数   value 为uid  score 为花费钱数，当用户多次购买该期后则score要增加
        "nper_user_pay_key"              => "malaysia:bonus:nper_user_pay:sorted_set#{nid}",
        // 保存每期返现的总金额  分配红包后该值递减（注意减到0的判断）
        "nper_bonus_total"               => "malaysia:bonus:nper_bonus_total:kv#{nid}",
        // 保存用户每期的返现详细记录    包括 返现时间， 金额
        "user_get_bonus_record_per_nper" => "malaysia:bonus:user_get_bonus_recored_per_nper:hash#{uid}_{nid}",
        //每期的夺宝用户的记录  该集合的值为  返现的用户的详细记录的hash key
        "nper_get_bonus_user_records"    => "malaysia:bonus:nper_get_bonus_user_record:set#{nid}",
    ],
    "services"       => [
        "mysql" => [
            "host"     => "127.0.0.1",
            "port"     => 3306,
            "user"     => "root",
            "password" => "123456",
            "dbname"   => "yyg",
            "charset"  => "utf8",
        ],
        "redis" => [
            "host" => "127.0.0.1",
            "port" => 6379,
        ],
        "email" => [
            "host"     => "email-smtp.us-east-1.amazonaws.com",
            "port"     => 587,
            "auth"     => true,
            "username" => "AKIAJDAMWWQWXQQBUJKQ",
            "password" => "AiGEbjJx7gqoyw38kQg8AKgBiylQvXgpQna2qaZlaxmK",
            "info"     => [
                "malaysia" => [
                    "sender"   => "hello@1rmhunt.com",
                    "receiver" => "hello@1rmhunt.com",
                ],
                "turkey"   => [
                    "sender"   => "destek@turnavi.com",
                    "receiver" => "destek@turnavi.com",
                ],
                "russia"   => [
                    "sender"   => "",
                    "receiver" => "",
                ],
            ],
        ],
    ],
];

