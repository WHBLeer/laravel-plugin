# Laravel Plugin 

### [插件开发文档:https://doc.uhaveshop.vip/plugin](https://doc.uhaveshop.vip/plugin)

## 关于
Laravel Plugin是一个插件机制解决方案，为需要建立自己的生态系统的开发人员，有了它，你可以建立一个类似wordpress的生态系统。它可以帮助您如下:

* 加载插件基于服务注册。
* 通过命令行，插件开发人员可以轻松快速地构建插件并将插件上传到插件市场。
* 提供插件编写器包支持。在创建的插件中单独引用composer。
* 以事件监控的方式执行插件的安装、卸载、启用、禁用逻辑。易于开发人员扩展。
* 插槽式插件市场支持，通过修改配置文件，开发者可以无缝连接到自己的插件市场。
* 附带一个基本的插件市场，开发人员可以上传插件并对其进行审查。

## 适用环境

```yml
"php": "^7.3|^8.0",
"ext-zip": "*",
"laravel/framework": "^8.12",
"spatie/laravel-enum": "^2.5"
```


## installation

* Step 1
```shell
composer require sanlilin/laravel-plugin
```

* Step 2
```php
php artisan vendor:publish --provider="Sanlilin\LaravelPlugin\Providers\PluginServiceProvider"
```














