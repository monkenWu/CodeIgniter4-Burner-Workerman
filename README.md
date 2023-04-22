# CodeIgniter4-Burner-Workerman

<p align="center">
  <a href="https://ciburner.com//">
    <img src="https://i.imgur.com/YI4RqdP.png" alt="logo" width="200" />
  </a>
</p>

This Library is the Workerman Driver for [CodeIgniter4 Burner](https://github.com/monkenWu/CodeIgniter4-Burner).

[English Document](https://ciburner.com/en/workerman/)

[正體中文文件](https://ciburner.com/zh_TW/workerman/)

## Install

### Prerequisites
1. CodeIgniter Framework 4.3.0^
2. CodeIgniter4-Burner 1.0.0^
3. Composer
4. PHP8^
5. Enable `php-pcntl` extension
6. Enable `php-posix` extension
7. We recommend you to install [php-event](https://www.php.net/manual/en/book.event.php) extension

### Composer Install

You can install this Driver with the following command.

```
composer require monken/codeigniter4-burner-workerman:1.0.0-beta.1
```

Initialize Server files using built-in commands in the library

```
php spark burner:init Workerman
```

Start the server.

```
php spark burner:start
```
