<?php

require_once __DIR__ . '/bootstrap.php';

use  Yyg\Tasks\OpenBonusTask;

require_once __DIR__ . '/config/config.php';

$uid = 17;

$redis   = new Predis\Client(
    [
        'scheme' => 'tcp',
        'host'   => "127.0.0.1",
        'port'   => "6379",
    ]
);

$db      = new Workerman\MySQL\Connection(
    $configs['services']['mysql']['host'],
    $configs['services']['mysql']['port'],
    $configs['services']['mysql']['user'],
    $configs['services']['mysql']['password'],
    $configs['services']['mysql']['dbname']
);

$user_info_key = str_replace("{uid}", $uid, $configs['bonus']['user_info']);


$exists = $redis->executeRaw(['exists', $user_info_key]);

if ($exists) {
    $user_info = $redis->hgetall($user_info_key);
}
else {
    $sql       = "select nick_name as name, reg_ip as ip, type from sp_users where id = $uid";
    $user_info = $db->row($sql);

    $redis->executeRaw(
        [
            'hmset',
            $user_info_key,
            'name',
            $user_info['name'],
            'ip',
            $user_info['ip'],
            'type',
            $user_info['type'],
        ]
    );
    $redis->executeRaw(['expire', $user_info_key, 3600 * 24 * 7]); //过期时间为一周
}
print_r($user_info);