<?php

$a = str_replace('-a=', '', array_pop($argv));
$f = str_replace('-f=', '', array_pop($argv));

define('APPPATH', $a);
require_once $f;

define('BURNER_DRIVER', 'Workerman');

use CodeIgniter\Config\Factories;
use Monken\CIBurner\Workerman\AppContainer;
use Monken\CIBurner\Workerman\Config;
use Workerman\Worker;

// Override is_cli()
if (! function_exists('is_cli')) {
    function is_cli(): bool
    {
        return false;
    }
}

try {
    //Burner Core And Worker Base Settings
    \Monken\CIBurner\App::setConfig(config('Burner'));
    /** @var \Config\Workerman */
    $workermanConfig = Factories::config('Workerman');
    Config::staticSetting($workermanConfig);

    AppContainer::registerWorkers($workermanConfig->serverWorkers);
    AppContainer::init();

} catch (\Throwable $th) {
    //print red text 
    fwrite(STDOUT, sprintf(
        "\033[31mAn error occurred during server initialization%s\033[0m",
        PHP_EOL . PHP_EOL . $th->__toString() . PHP_EOL . PHP_EOL
    ));

}

Worker::runAll();