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

        $basePath   = APPPATH . '/Config' . DIRECTORY_SEPARATOR;
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

    public function startServer(string $frontLoader, bool $daemon = false, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $appPath    = APPPATH;
        if ($daemon) {
            self::writeIsDaemon();
            $start = popen("php {$workerPath} start -d -f={$frontLoader} -a={$appPath}", 'w');
        } else {
            $start = popen("php {$workerPath} start -f={$frontLoader} -a={$appPath}", 'w');
        }
        pclose($start);
        if (self::needRestart()) {
            echo PHP_EOL . PHP_EOL;
            $this->startServer($frontLoader, $daemon, '-r=restart');
        }
    }

    public function stopServer(string $frontLoader, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $appPath    = APPPATH;
        self::isDaemon();
        $start = popen("php {$workerPath} stop -f={$frontLoader} -a={$appPath}", 'w');
        pclose($start);
        echo PHP_EOL;
    }

    public function restartServer(string $frontLoader, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $appPath    = APPPATH;
        if (self::isDaemon(false)) {
            $start = popen("php {$workerPath} restart -d -f={$frontLoader} -a={$appPath}", 'w');
        } else {
            $start = popen("php {$workerPath} restart -f={$frontLoader} -a={$appPath}", 'w');
        }
        pclose($start);
        echo PHP_EOL;
    }

    public function reloadServer(string $frontLoader, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $appPath    = APPPATH;
        $start      = popen("php {$workerPath} reload -f={$frontLoader} -a={$appPath}", 'w');
        pclose($start);
        echo PHP_EOL;
    }

    protected static function getTempFilePath(string $fileName): string
    {
        $nowDir      = __DIR__;
        $projectHash = substr(sha1($nowDir), 0, 5);
        $baseDir     = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

        return sprintf('%s%s_%s', $baseDir, $projectHash, $fileName);
    }

    public static function writeRestartSignal()
    {
        $temp = self::getTempFilePath('burner_workerman_restart.tmp');
        file_put_contents($temp, 'restart');
    }

    public static function writeIsDaemon()
    {
        $temp = self::getTempFilePath('burner_workerman_daemon.tmp');
        if (is_file($temp)) {
            unlink($temp);
        }
        file_put_contents($temp, '');
    }

    public static function isDaemon(bool $unlink = true): bool
    {
        $temp   = self::getTempFilePath('burner_workerman_daemon.tmp');
        $result = false;
        if (is_file($temp)) {
            $result = true;
            if ($unlink) {
                unlink($temp);
            }
        }

        return $result;
    }

    public static function needRestart(): bool
    {
        $temp   = self::getTempFilePath('burner_workerman_restart.tmp');
        $result = false;
        if (is_file($temp)) {
            $text = file_get_contents($temp);
            if ($text === 'restart') {
                $result = true;
            }
            unlink($temp);
        }

        return $result;
    }

    public function runCmd(string $frontLoader, string $commands = '')
    {
        $nowDir     = __DIR__;
        $workerPath = $nowDir . DIRECTORY_SEPARATOR . 'Worker.php';
        $appPath    = APPPATH;
        $start      = popen("php {$workerPath} {$commands} -f={$frontLoader} -a={$appPath}", 'w');
        pclose($start);
        echo PHP_EOL;
    }
}
