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
        host: email-smtp.us-east-1.amazonaws.com
        port: 587
        auth: true
        username: AKIAJDAMWWQWXQQBUJKQ
        password: AiGEbjJx7gqoyw38kQg8AKgBiylQvXgpQna2qaZlaxmK
        info:
            malaysia:
                sender: hello@1rmhunt.com
                receiver: hello@1rmhunt.com
                title: 1RM HUNT
            turkey:
                sender: destek@turnavi.com
                receiver: destek@turnavi.com
                title: ÜCRETSİZ TURNAVI PARASI
            russia:
                sender:
                receiver:
                title:
