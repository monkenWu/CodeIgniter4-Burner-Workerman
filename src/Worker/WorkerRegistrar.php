<?php

namespace Monken\CIBurner\Workerman\Worker;

use Workerman\Worker;

abstract class WorkerRegistrar
{
    protected Worker $worker;

    abstract public function initWorker(): Worker;
}
