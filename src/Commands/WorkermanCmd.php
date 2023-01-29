<?php

namespace Monken\CIBurner\Workerman\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;
use Monken\CIBurner\App;
use Monken\CIBurner\Workerman\Integration;
use ReflectionClass;

class WorkermanCmd extends BaseCommand
{
    use GeneratorTrait;

    protected $group       = 'burner';
    protected $name        = 'burner:workerman';
    protected $description = 'Run commands directly to Workerman\'s entry php file.';
    protected $usage       = 'burner:workerman [commands]';

    public function run(array $params)
    {
        $integration       = new Integration();
        $burnerCoreAppPath = (new ReflectionClass(App::class))->getFileName();
        $loaderPath        = dirname($burnerCoreAppPath) . DIRECTORY_SEPARATOR . 'FrontLoader.php';

        $argvs = $_SERVER['argv'];

        foreach ($argvs as $key => $argv) {
            if (in_array($argv, ['spark', $this->name], true)) {
                unset($argvs[$key]);
                if ($argv === '--driver') {
                    unset($argvs[$key + 1]);
                }
            }
        }
        $command = implode(' ', $argvs);
        $integration->runCmd($loaderPath, $command);
    }
}
