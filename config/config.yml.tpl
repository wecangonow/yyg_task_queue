is_debug: true
log_path: /data/logs/tasks
timezone: Asia/Shanghai
queues: emails,logs
services:
    swoole_server:
        ip: 127.0.0.1
        port: 9570
        mode: SWOOLE_BASE
        pack_type: packet
        set:
            user: www
            group: www
            worker_num: 2
            task_worker_num: 2
            dispatch_mode: 3
            open_cpu_affinity: true
            open_tcp_nodelay: true
            package_max_length: 81290
            daemonize: false
            log_file: PROJECT_DIR . '/logs/swoole_server.log'
    redis:
        host: 127.0.0.1
        port: 6379
