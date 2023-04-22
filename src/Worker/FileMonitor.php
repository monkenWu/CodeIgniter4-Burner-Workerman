<?php

namespace Monken\CIBurner\Workerman\Worker;

use Config\Workerman;
use Monken\CIBurner\Workerman\Integration;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Workerman\Timer;
use Workerman\Worker;

class FileMonitor extends WorkerRegistrar
{
    protected Workerman $workermanConfig;

    public function __construct()
    {
        $this->workermanConfig = config('Workerman');
    }

    public function initWorker(): Worker
    {
        $worker             = new Worker();
        $worker->name       = 'FileMonitor';
        $worker->reloadable = false;

        global $last_mtime;
        $last_mtime     = time();
        $monitorDir     = $this->workermanConfig->autoReloadDir;
        $scanExtensions = $this->workermanConfig->autoReloadScanExtensions;
        $reloadMode     = $this->workermanConfig->autoReloadMode;

        $worker->onWorkerStart = static function () use ($monitorDir, $scanExtensions, $reloadMode) {
            // watch files only in daemon mode
            if (! Worker::$daemonize) {
                // chek mtime of files per second
                Timer::add(
                    1,
                    static function (string $monitorDir, array $scanExtensions, string $reloadMode) {
                        global $last_mtime;

                        // recursive traversal directory
                        $dir_iterator = new RecursiveDirectoryIterator($monitorDir);
                        $iterator     = new RecursiveIteratorIterator($dir_iterator);

                        foreach ($iterator as $file) {
                            // only check php files
                            if (in_array(pathinfo($file, PATHINFO_EXTENSION), $scanExtensions, true) !== true) {
                                continue;
                            }

                            // check mtime
                            if ($last_mtime < $file->getMTime()) {
                                if ($reloadMode === 'restart') {
                                    echo $file . " update and restart\n";
                                    Integration::writeRestartSignal();
                                    posix_kill(posix_getppid(), SIGINT);
                                } else {
                                    echo $file . " update and reload\n";
                                    posix_kill(posix_getppid(), SIGUSR1);
                                }
                                $last_mtime = $file->getMTime();
                                break;
                            }
                        }
                    },
                    [$monitorDir, $scanExtensions, $reloadMode]
                );
            }
        };

        return $worker;
    }
}
