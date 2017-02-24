is_debug: true
log_path: /data/logs/tasks
timezone: Asia/Shanghai
queues: emails,logs
services:
    redis:
        host: 127.0.0.1
        port: 6379
