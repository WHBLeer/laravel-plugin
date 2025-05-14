# Laravel Plugin

### [插件开发文档:https://doc.uhaveshop.vip/plugin](https://doc.uhaveshop.vip/plugin)

## 关于

Laravel-Plugin是一个laravel框架适用插件机制解决方案，为需要建立自己的生态系统的开发人员而生，有了它，你可以建立一个类似wordpress的生态系统。它可以帮助您:

* 加载插件基于服务注册。
* 通过命令行，插件开发人员可以轻松快速地构建插件并将插件上传到插件市场。
* 提供插件编写器包支持。在创建的插件中单独引用composer。
* 以事件监控的方式执行插件的安装、卸载、启用、禁用逻辑。易于开发人员扩展。
* 插槽式插件市场支持，通过修改配置文件，开发者可以无缝连接到自己的插件市场。
* 附带一个基本的插件市场，开发人员可以上传插件并对其进行审查。

## 适用环境

```yml
"php": "^8.2",
"ext-zip": "*",
"ext-json": "*",
"laravel/framework": "^11.0",
"artesaos/seotools": "^1.3",
"mcamara/laravel-localization": "^2.0",
"illuminate/config": "^10.0 || ^11.0 || ^12.0",
"illuminate/support": "^10.0 || ^11.0 || ^12.0"
```

## installation

* Step 1

```shell
composer require sanlilin/laravel-plugin:^2.0
```

* Step 2

```shell
php artisan plugins-system:install
```

## 插件管理器命令

``` shell
php artisan plugin
```

| 命令                      | 参数                 | 解释                    |
|-------------------------|--------------------|-----------------------|
| plugin:list             |                    | 显示所有插件的列表。            |
| plugin:make             | [plugin]           | 创建一个新插件。              |
| plugin:make-provider    | [plugin]           | 为指定的插件创建一个新的服务提供者类。   |
| plugin:route-provider   | [plugin]           | 为指定的插件创建一个新的路由服务提供商。  |
| plugin:make-controller  | [plugin]           | 为指定的插件生成新的restful控制器。 |
| plugin:make-model       | [plugin]           | 为指定的插件创建一个新模型。        |
| plugin:make-migration   | [table] [plugin]   | 为指定的插件创建新的迁移。         |
| plugin:make-seed        | [plugin]           | 为指定的插件生成新的播种器。        |
| plugin:migrate          | [?plugin]          | 从指定插件或从所有插件迁移迁移。      |
| plugin:composer-require | [plugin] [package] | 安装插件composer包。        |
| plugin:composer-remove  | [plugin] [package] | 删除插件composer包。        |
| plugin:disable          | [?plugin]          | 禁用指定的插件。              |
| plugin:enable           | [?plugin]          | 启用指定的插件。              |
| Plugin:restart          | [?plugin]          | 重载指定的插件。              |
| plugin:delete           | [plugin]           | 从应用程序中删除插件            |
| plugin:install          | [path]             | 通过文件目录安装插件。           |

- `[plugin]` 插件名称，区分大小写
- `[package]` composer扩展名称，区分大小写和版本
- `[path]` 插件在本地存放的目录路径

### 命令详解

#### 查看本地插件列表
```shell
php artisan plugin:list
# 此命令会列出当前系统已安装的插件；包括插件名称，状态，优先级，存放路径
 
+-------------+---------+----------+----------------------------------------------+
| Name        | Status  | Priority | Path                                         |
+-------------+---------+----------+----------------------------------------------+
| UCenter     | Enabled | 0        | /www/wwwroot/enewshop/plugins/UCenter        |
| UeditorPlus | Enabled | 0        | /www/wwwroot/enewshop/plugins/UeditorPlus    |
+-------------+---------+----------+----------------------------------------------+
```

#### 创建插件
```shell
php artisan plugin:make ExamplePlugin
```
| 选项      | 说明       |
|---------|----------|
| name    | 插件名称     |
| --force | 覆盖已存在的插件 |

### 创建服务提供者
```shell
php artisan plugin:make-provider ExampleServiceProvider ExamplePlugin
```
| 选项       | 说明      |
|----------|---------|
| name     | 服务提供者名称 |
| plugin   | 插件名称    |
| --master | 主服务提供者  |


### 创建路由服务提供者
```shell
php artisan plugin:route-provider ExamplePlugin
```
| 选项     | 说明   |
|--------|------|
| plugin | 插件名称 |


### 创建控制器
```shell
php artisan plugin:make-controller ExampleController ExamplePlugin
```
| 选项         | 说明    |
|------------|-------|
| controller | 控制器类名 |
| plugin     | 插件名称  |

### 创建模型
```shell
php artisan plugin:make-model ExampleModel ExamplePlugin
```
| 选项          | 说明        |
|-------------|-----------|
| model       | 模型类名      |
| plugin      | 插件名称      |
| --fillable  | 可填充属性     |
| --migration | 创建关联迁移的标志 |

```shell
php artisan plugin:make-model UcenterUser Ucenter --migration --fillable="username,email,mobile,status"
```

### 创建迁移
```shell
php artisan plugin:make-migration migration_name ExamplePlugin
```
| 选项       | 说明                                               |
|----------|--------------------------------------------------|
| name     | 迁移的名称                                            |
| plugin   | 插件名称                                             |
| --fields | 指定要操作的字段，格式为逗号分隔的字段列表，例如：name:string,age:integer |
| --plain  | 创建一个空白的迁移文件                                      |

> 根据迁移名（migration_name）前缀，系统会自动生成不同类型的迁移模板：
> create_xxx_table: 创建新表（使用 /migration/create.stub 模板）
> add_xxx_to_table: 添加字段（使用 /migration/add.stub 模板）
> delete_xxx_from_table: 删除字段（使用 /migration/delete.stub 模板）
> drop_xxx_table: 删除整个数据表（使用 /migration/drop.stub 模板）
> 如果没有匹配特定前缀，则会使用 /migration/plain.stub 模板生成一个空迁移文件。

```shell
php artisan plugin:make-migration create_customers_table Ucenter --fields="name:string,email:string:unique"
```

### 创建种子文件
```shell
php artisan plugin:make-seed ExamplePlugin
```
| 选项      | 说明                                               |
|---------|--------------------------------------------------|
| name    | 迁移的名称                                            |
| plugin  | 插件名称                                             |
| --master | 将创建的播种程序是主数据库播种程序                        |


### 插件数据库迁移
```shell
php artisan plugin:migrate ExamplePlugin
```
| 选项       | 说明                  |
|----------|---------------------|
| ?plugin  | 插件名称(不填写时为所有插件执行迁移) |
| --d | 排序方向,默认`asc`        |
| --database | 要使用的数据库连接           |
| --pretend | 转储将要运行的SQL查询        |
| --force | 在生产环境中强制执行          |
| --seed | 是否执行种子任务            |
| --subpath | 要执行迁移的子路径           |


### 安装插件依赖
```shell
php artisan plugin:composer-require ExamplePlugin package
```
| 选项        | 说明            |
|-----------|---------------|
| plugin    | 插件名称          |
| package   | Composer包的名称。 |
| --dev     | 在开发环境中使用      |
| --v       | 包的版本          |
```shell
php artisan plugin:composer-require FileManager tinify/tinify --v='*'
```


### 删除插件依赖
```shell
php artisan plugin:composer-remove ExamplePlugin packages
```
| 选项       | 说明                  |
|----------|---------------------|
| plugin    | 插件名称          |
| packages   | Composer包的名称。 |
```shell
php artisan plugin:composer-remove FileManager tinify/tinify league/flysystem
```


### 禁用插件
```shell
php artisan plugin:disable ExamplePlugin
```
| 选项       | 说明                   |
|----------|----------------------|
| ?plugin  | 插件名称(不填写时禁用所有插件) |

### 禁用插件

```shell
php artisan plugin:disable ExamplePlugin
```
| 选项       | 说明                   |
|----------|----------------------|
| ?plugin  | 插件名称(不填写时禁用所有插件) |

### 启用插件

```shell
php artisan plugin:enable ExamplePlugin
```
| 选项       | 说明               |
|----------|------------------|
| ?plugin  | 插件名称(不填写时启用所有插件) |

### 重启插件

```shell
php artisan plugin:restart ExamplePlugin
```
| 选项       | 说明               |
|----------|------------------|
| ?plugin  | 插件名称(不填写时重启所有插件) |

  | plugin:install          | [path]             | 通过文件目录安装插件。           |

### 删除插件

```shell
php artisan plugin:delete ExamplePlugin
```
| 选项      | 说明               |
|---------|------------------|
| plugin  | 插件名称 |


### 重启插件

```shell
php artisan plugin:install PluginPath
```
| 选项   | 说明     |
|------|--------|
| path | 本地插件目录 |
| --force   | 是否覆盖安装 |



### 重启插件

```shell
php artisan plugin:restart ExamplePlugin
```
| 选项       | 说明               |
|----------|------------------|
| ?plugin  | 插件名称(不填写时重启所有插件) |











