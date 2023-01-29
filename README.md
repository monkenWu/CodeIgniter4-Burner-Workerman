# CodeIgniter4-Burner-Workerman

This Library is the Workerman Driver for [CodeIgniter4 Burner](https://github.com/monkenWu/CodeIgniter4-Burner).

## Install

### Prerequisites
1. CodeIgniter Framework 4.2.0^
2. Composer
3. PHP8^
7. Enable `php-pcntl` extension
8. Enable `php-posix` extension
9. We recommend you to install [php-event](https://www.php.net/manual/en/book.event.php) extension

### Composer Install

You can install this Driver with the following command.

```
composer require monken/codeigniter4-burner-workerman
```

Initialize Server files using built-in commands in the library

```
php spark burner:init Workerman
```

## Command

When you do not pass in any parameters, the server is preset to start in debug mode.

```
php spark burner:start
```

By default, burner reads the default driver written in `app/Burner.php`. Of course, you can force Burner to execute commands with the `Workerman` driver by using a parameter like this：

```
php spark burner:start --driver Workerman
```

> `--driver Workerman` This parameter also applies to all the commands mentioned below.


### daemon mode

```
php spark burner:start --daemon
```

### stop server

```
php spark burner:stop
```

### restart server

```
php spark burner:restart
```

### reload server

```
php spark burner:reload
```

### server status

```
php spark burner:workerman status
```

### more command

Run commands directly to Workerman's entry php file.

```
php spark burner:workerman [workerman_comands]
```

You can refer to the official [Workerman documentation](https://github.com/walkor/workerman#available-commands) to construct your commands. 

## Workerman Server Settings

The server settings are all in the `app/Config` directory `Workerman.php`. The default file will look like this:

```php
class Workerman extends BaseConfig
{
    /**
     * Public static files location path.
     *
     * @var string
     */
    public $staticDir = '/app/dev/public';

    /**
     * Public access to files with these filename-extension is prohibited.
     *
     * @var array
     */
    public $staticForbid = ['htaccess', 'php'];

    /** hide **/
}
```

You can create your configuration file according to the [Workerman document](https://github.com/walkor/workerman-manual/tree/master/english).

## Development Suggestions

### Automatic reload

In the default circumstance of RoadRunner and Workerman, you must restart the server everytime after you revised any PHP files so that your revision will effective.
It seems not that friendly during development.

#### Workerman 

You can modify your `app/Config/Workerman.php` configuration file, add the following settings and restart the server.

```php
public $autoReload = true;
```

> The `reload` function is very resource-intensive, please do not activate the option in the formal environment.

### Developing and debugging in a environment with only one Worker

Since the RoadRunner and Workerman has fundamentally difference with other server software(i.e. Nginx, Apache), every Codeigniter4 will persist inside RAMs as the form of Worker, HTTP requests will reuse these Workers to process. Hence, we have better develop and test stability under the circumstance with only one Worker to prove it can also work properly under serveral Workers in the formal environment.

#### Workerman

You can reference the `app/Config/Workerman.php` settings below to lower the amount of Worker to the minimum:

```php
public $workerCount = 1;
```
