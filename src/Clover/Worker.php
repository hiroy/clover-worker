<?php
namespace Clover;

use Parallel\Prefork;

abstract class Worker
{
    protected $defaultOptions = [
        'max_workers' => 2,
        'trap_signals' => [
            SIGHUP => SIGTERM,
            SIGTERM => SIGTERM,
        ],
    ];

    public abstract function main();

    public function start(array $ppOptions = [])
    {
        $options = array_merge($this->defaultOptions, $ppOptions);
        $pp = new Prefork($options);

        while ($pp->signalReceived() !== SIGTERM) {
            if ($pp->start()) {
                continue;
            }

            $w = new static();
            $w->main();

            $pp->finish();
        }

        $pp->waitAllChildren();
    }
}
