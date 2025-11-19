# 微付团购商城

## 安装

1. 安装composer依赖

~~~shell
composer install
~~~

2. 运行

```shell
php think run -H 0.0.0.0 -p 8000
```

- 在打包或部署之前可以缓存配置和路由。

```shell
php think optimize:config && php think optimize:route
```

3. 访问 http://localhost:8000/install/install.php 执行完安装的步骤后执行迁移命令

```shell
php think migrate:run
```

