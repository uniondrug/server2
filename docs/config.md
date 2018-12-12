# 服务配置


### extends


### config

| 键名 | 值类型 | 键值/参考项 | 备注 |
| -- | -- | -- | -- |
| class | string | Http::class | HTTP/WebSocket启动时的类名 |
| host | string | 192.168.1.100:8080 | 服务监听地址<br />1. IPv4+端口<br />   例如: 192.168.1.100:8080<br />2. 网卡名:端口<br />   例如: eth0:8080, 以内网IP提供服务<br />   例如: eth1:8080, 以公网IP提供服务|
| options | array | [] | Swoole启动参数, 详情[参阅](https://wiki.swoole.com/wiki/page/274.html) |
| options.pid_file | string | server.pid | 服务启时记录Master进程的ID的值 |
| options.log_file | string | server.log | 由Swoole维护的Log文件 |
| options.log_level | int | 0 | Log级别<br />0: DEBUG<br />1: TRACE<br />2: INFO<br />3: NOTICE<br />4: WARNING<br />5: ERROR |
| options.worker_num | int | 2 | Worker进程数量 |
| options.max_request | int | 2000 | Worker进程完成2000次请求时重启, 防内泄漏 |
| options.task_worker_num | int | 2 | Tasker进程数量 |
| options.task_max_request | int | 2 | Tasker进程完成2000次Task时重启, 防内泄漏 |
| settings | array | [] | 运行参数 |
| settings.startMode | int | 3 | Swoole运行的模式 |
| settings.startSockType | int | 1 | Swoole下的Socket类型 |
| settings.reconnectMysqlSeconds | int | 10 | 每隔10秒, 进行MySQL连接的健康检查, 防止GoneAway错误 |
| settings.reconnectRedisSeconds | int | 10 | 每隔10秒, 进行Redis连接的健康检查, 防止GoneAway错误 |
| processes | array | Process类名 | 外挂Process进程 |
| tables | array | Table类名/空间 | 指定内存表 |

```php
<?php
return [
    'default' => [
        'class' => Http::class,
        'options' => [
            'pid_file' => __DIR__.'/../tmp/server.pid',
            'log_file' => __DIR__.'/../log/server.log',
            'log_level' => 0,
            'worker_num' => 2,
            'task_worker_num' => 2,
            'max_request' => 5000,
            'task_max_request' => 5000
        ],
        'settings' => [
            'startMode' => SWOOLE_PROCESS,
            'startSockType' => SWOOLE_SOCK_TCP,
            'reconnectMysqlSeconds' => 10,
            'reconnectRedisSeconds' => 10
        ],
        'processes' => [
            ExampleProcess::class
        ],
        'tables' => [
            ExampleTable::class => 256
        ]
    ],
    'development' => [
        'host' => '0.0.0.0:18101'
    ]
];
```




