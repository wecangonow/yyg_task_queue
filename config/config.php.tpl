<?php

$configs = [

    "is_debug"          => true,
    "log_path"          => "/data/logs/tasks",
    "file_path"         => "/data/logs/", //存放程序生成的报表文件
    "timezone"          => "Asia/Shanghai",
    "bonus_spend_ratio" => 1,  // 俄罗斯 10   马拉西亚 土耳其 1
    "robot_bonus_queue" => "malaysia:bonus:robot_bonus_queue",
    "robot_buy_url" => "",
    "short_url_basic" => "http://www.yyg.com/trace_url.php?uid=",
    "timer_interval"    => 2,   // 执行任务定时器 时间间隔
    "prize"             => [
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
    "coupon"             => [
        "register" => [
            '1' => [13 => 2],   // key 为 第几天发放 ，value 为要发的优惠券的数据库对应的id序列，严格按照此格式配置
            '2' => [13 => 1,14 => 1],
            '4' => [13 => 1,15 => 1],
            '7' => [13 => 1,14 => 1],
            '10' =>[13 => 1,15 => 1],
            '14' =>[14 => 1,15 => 1],
            '21' =>[14 => 1,15 => 1],
            '28' =>[14 => 1,15 => 1],
            '35' =>[14 => 1,16 => 1],
            '42' =>[14 => 1,16 => 1],
            '49' =>[15 => 1,16 => 1],
            '56' =>[16 => 1,17 => 1],
            '63' =>[17 => 2],
        ]
    ],
    "bonus"             => [
        //每期的所有机器人用户的是否参与抢红包的集合  score 0 1 分别代表未抢和已抢
        "nper_robot_users"                    => "malaysia:bonus:nper_robot_users:sorted_set#{nid}",
        //期待在多久时间内让所有机器人都抢红包单位小时
        "robot_bonus_finish_time"             => 24,
        //红包大小占商品价格的比例
        "bonus_percent"                       => "0.05",
        //最大红包  俄罗斯 must be  50
        "max_bonus"                           => "5",
        //最小红包   俄罗斯 must be 10
        "min_bonus"                           => "1",
        // 保存每期每用户的购买钱数   value 为uid  score 为花费钱数，当用户多次购买该期后则score要增加
        "nper_user_pay_key"                   => "malaysia:bonus:nper_user_pay:sorted_set#{nid}",
        // 保存每期返现的总金额  分配红包后该值递减（注意减到0的判断）
        "nper_bonus_total"                    => "malaysia:bonus:nper_bonus_total:kv#{nid}",
        // 保存用户每期的返现详细记录    包括 返现时间， 金额
        "user_get_bonus_record_per_nper"      => "malaysia:bonus:user_get_bonus_recored_per_nper:hash#{uid}_{nid}",
        //每期的夺宝成功用户的记录  该集合的值为  返现的用户的详细记录的hash key
        "nper_get_bonus_success_user_records" => "malaysia:bonus:nper_get_bonus_success_user_record:sorted_set#{nid}",
        //每期的夺宝失败用户的记录  该集合的值为  返现的用户的详细记录的hash key
        "nper_get_bonus_failed_user_records"  => "malaysia:bonus:nper_get_bonus_failed_user_record:sorted_set#{nid}",
        //每用户所有期的是否夺宝状态记录  score 0 1
        "user_every_nper_get_bonus_state"     => "malaysia:bonus:user_every_nper_get_bonus_state:sorted_set#{uid}",
        "user_info"                           => "malaysia:bonus:user_info:hash#{uid}",
    ],
    "services"          => [
        "aws"          => [
            "domain"     => "",
            "bucket"     => "",
            "region"     => "",
            "access_key" => "",
            "secret_key" => "",
        ],
        "mysql"        => [
            "host"     => "127.0.0.1",
            "port"     => 3306,
            "user"     => "root",
            "password" => "123456",
            "dbname"   => "yyg",
            "charset"  => "utf8",
        ],
        "redis"        => [
            "host" => "127.0.0.1",
            "port" => 6379,
        ],
        "android_push" => [
            "key"     => "AIzaSyAZo3qXaQYEFgxsNfmi2uNp0UYOYTZxeTU",
            "gcm_url" => "https://gcm-http.googleapis.com/gcm/send",
            "tpl"     => [
                "coupon_expired" => [
                    'title'   => '您的代金券还有2天即将过期',
                    'message' => '赶快登录 1RM HUNT 夺宝吧!',
                ],
                "register_coupon_expired" => [
                    'title'   => 'Your coupon will expire soon.',
                    'message' => 'Log in at 1RM HUNT!',
                ],
                "activate_coupon" => [
                    'title'   => "You've received {{total}} LC Coupon!",
                    'message' => 'Use right now or it will expire in 24 hours',
                ],
                "confirm_address" => [
                    'title'   => '您的奖品将与3日后失效',
                    'message' => '请您务必于3日内提交奖品收货地址',
                ],
                "nocheckin"        => [
                    "title"   => "很久不见,甚是想念",
                    "message" => "我们有新的商品上架,欢迎回来看看",
                ],
                "show_order"       => [
                    'title'   => '免费的 Lucky Coins 已经充值到您的账户',
                    'message' => '晒单为您赢得了免费的 Lucky Coins ! 快去夺宝吧!',
                ],
                "show_participate" => [
                    'title'   => '您的参与有晒单了',
                    'message' => '看看谁赢了我的奖品',
                ],
                "shipped"          => [
                    'title'   => '您的奖品已安排发货',
                    'message' => '您的奖品预计1-2周内送到您的手中',
                ],
                "winning_bonus"    => [
                    'win'  => [
                        'title'   => '恭喜您中奖了',
                        'message' => '请赶快登录 1RM HUNT 填写收货地址',
                    ],
                    'fail' => [
                        'title'   => '您有一笔基金未领取!',
                        'message' => '基金总量有限，先到先得，请抓紧时间.',
                    ],
                ],
            ],
        ],
        "email"        => [
            "host"     => "",
            "port"     => ,
            "auth"     => ,
            "username" => "",
            "password" => "",
            "info"     => [
                "sender"      => "hello@1rmhunt.com",
                "receiver"    => "hello@1rmhunt.com",
                "sender_info" => "1RM HUNT",
                "tpl"         => [
                    "coupon_expired"   => [
                        "subject" => "您有一张代金券2日后过期，请尽快使用",
                        "body"    => <<<COUPON_EXPIRED
                                代金券能让您极大的提升中奖几率，这样的好机会您当然不会错过。因此我们善意的提醒您，请马上打开 1RM HUNT 并使用您的代金券，别让幸运与您擦肩而过。
                                1RM HUNT 最近新增了很多很棒的奖品，得奖的幸运儿也越来越多，马上行动您将成为下一个幸运的赢家。看看大家都赢了什么吧{{share_info}}
                                APP 安装/打开按钮
COUPON_EXPIRED
,

                            "is_html" => false,
                    ],
                    "confirm_address"   => [
                        "subject" => "请您务必于3日内提交奖品收货地址",
                        "body"    => <<<CONFIRM_ADDRESS
                                您于{{luck_date}}赢得了{{good_name}},根据夺宝规则,如果您再中奖后7天内仍未提交奖品收货地址,将被视为自动放弃奖品,所以请您务必于收到本邮件3日内完成收件地址的填写,不要让幸运从您身边溜走。
                                我们强烈推荐您关注 1RM HUNT 的 Facebook 主页（www.facebook.com/1rmhunt）以便获取最新的优惠和活动信息！
CONFIRM_ADDRESS
,

                        "is_html" => false,
                    ],
                    "register" => [
                        "subject" => "恭喜您已在 1RM HUNT 成功注册",
                        "body"    => "恭喜您已在 1RM HUNT 成功注册",
                        "is_html" => false,
                    ],

                    "payment"    => [
                        "subject" => "Congratulations on your successful payment.",
                        "body"    => [
                            "recharge" => "You have already paid {{paid}} MYR to  `1RM HUNT` at {{pay_time}}. This is an automatically generated message, no need to reply.",
                            "buy" => "You have successfully paid {{paid}} MYR to  `1RM HUNT` at {{pay_time}} and got the Lucky Numbers, if you win we will immediately through the mail and App inform you, please be sure to pay close attention to the progress of the hunt."
                        ],
                        "is_html" => true,
                    ],
                    "friendPayment"    => [
                        "subject" => "Congratulations on your successful payment.",
                        "body"    => [
                            "recharge" => "You have already paid {{paid}} MYR for your friend {{uid}} at {{pay_time}} . This is an automatically generated message, no need to reply.",
                            "be_charged" => "Your friend {{uid}} have already paid {{paid}} MYR for you at {{pay_time}}. This is an automatically generated message, no need to reply."
                        ],
                        "is_html" => true,
                    ],
                    "receipt"  => [
                        "subject" => "别错过属于您的20个免费 Lucky Coins",
                        "body"    => <<<RECEIPT
                                再次恭喜您赢得了{{good_name}},您无疑是非常幸运的,而且您的幸运还在继续！请赶快到 1RM HUNT 晒出您的奖品,我们将赠送您10个免费的 Lucky Coins.
                                如果您将中奖的信息分享到您的个人 Facebook 主页并将分享截图及您的 1RM HUNT ID 通过 1RM HUNT 的 Facebook 主页（www.facebook.com/1rmhunt） 发送给我们，我们将再赠送您10个免费的 Lucky Coins!!
                        分享会使您的幸运加倍，赶快行动吧
                        晒单教程图
                        分享到Facebook 的按钮
                        我们强烈推荐您关注 1RM HUNT 的 Facebook 主页（www.facebook.com/1rmhunt）以便获取最新的优惠和活动信息!
RECEIPT
                        ,
                        "is_html" => false,
                    ],
                    "shipped"  => [
                        "subject" => "您的奖品已由 1RM HUNT 安排发货",
                        "body"    => <<<SHIPPED
                                您的奖品 {{good_name}} 已经发货，预计1-2周内送到您的手中。请您在签收前务必仔细检查奖品，如有质量问题请拒绝签收。如签收后发现奖品有质量问题请您自行联系卖家协商解决, 1RM HUNT 会尽量为您提供帮助但无义务退换奖品。
                                请不要忘记收到奖品后来 1RM HUNT 和 Facebook 晒单,这样会再得到20个免费的 Lucky Coins!
                                我们强烈推荐您关注 1RM HUNT 的 Facebook 主页（www.facebook.com/1rmhunt）以便获取最新的优惠和活动信息!
SHIPPED
                        ,
                        "is_html" => false,
                    ],
                    "show_order" => [
                        "subject" => "免费的 Lucky Coins 已经充值到您的账户",
                        "body"    => <<<SHOW_ORDER
                                感谢您晒出了您的奖品让更多的人得以分享您的幸运，我们相信这一定会让您的幸运加倍！ 免费的Lucky Coins 已经充值到您的账户，相信幸运的您将用它赢得任何您想要的奖品！赶快去夺宝吧！
                                我们强烈推荐您关注 1RM HUNT 的 Facebook 主页（www.facebook.com/1rmhunt）以便获取最新的优惠和活动信息！
SHOW_ORDER
,
                        "is_html" => false,
                    ],
                ]
            ],
        ],
    ],
];

