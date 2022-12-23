<?php

namespace Monken\CIBurner\Workerman;

use CodeIgniter\CLI\CLI;
use Monken\CIBurner\IntegrationInterface;

class Integration implements IntegrationInterface
{
    public function initServer(string $configType = 'basic', string $frontLoader = '')
    {
        $allowConfigType = ['basic'];
        if (in_array($configType, $allowConfigType, true) === false) {
            CLI::write(
                CLI::color(
                    sprintf(
                        'Error config type! We only support: %s. The config type you have entered is: %s.',
                        implode(', ', $allowConfigType),
                        $configType
                    ),
                    'red'
                )
            );
            echo PHP_EOL;

            exit;
        }

        $basePath   = ROOTPATH . 'app/Config' . DIRECTORY_SEPARATOR;
        $configPath = $basePath . 'Workerman.php';

        if (file_exists($configPath)) {
            rename($configPath, $basePath . 'Workerman.backup.' . time() . '.php');
        }

        $cnf = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Workerman.php.' . $configType);
        $cnf = str_replace('{{static_path}}', ROOTPATH . 'public', $cnf);
        $cnf = str_replace('{{reload_path}}', realpath(APPPATH . '../'), $cnf);
        $cnf = str_replace('{{log_path}}', realpath(WRITEPATH . 'logs') . DIRECTORY_SEPARATOR . 'workerman.log', $cnf);
        file_put_contents($configPath, $cnf);
    }

    public function startServer(string $frontLoader, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        if ($commands === '') {
            $start = popen("php {$workerPath} start -f={$frontLoader}", 'w');
        } else {
            $start = popen("php {$workerPath} {$commands} -f={$frontLoader}", 'w');
        }
        pclose($start);
        echo PHP_EOL;
    }
}
