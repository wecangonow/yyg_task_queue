is_debug: true
log_path: /data/logs/tasks
timezone: Asia/Shanghai
queues: emails,logs
services:
    mysql:
        host: 127.0.0.1
        port: 3306
        user: root
        password: 123456
        dbname: yyg
        charset: utf8
    redis:
        host: 127.0.0.1
        port: 6379
    email:
        host:
        port: 587
        auth: true
        username:
        password:
        info:
            malaysia:
                sender:
                receiver:
                title:
            turkey:
                sender:
                receiver:
                title:
            russia:
                sender:
                receiver:
                title:
