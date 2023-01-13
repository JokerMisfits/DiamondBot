<?php
use \Discord\Discord;
use React\Promise\Deferred;
class logging extends DiamondBot
{

    private $deferred;

    public function __construct(){
        $this->deferred = new Deferred();
        return $this->deferred->promise();
    }

    public function MemoryUsage(Discord $discord){
        $loop = $discord->__get('loop');
        $logger = $discord->__get('logger');
        $deferred = new Deferred();
        $logging = function () use ($deferred,$logger){
            $memory = memory_get_usage() / 1024;
            $formatted = number_format($memory, 3).'K';
            return $deferred->resolve($logger->info("Current memory usage: {$formatted}\n"));
        };
        $loop->addPeriodicTimer(5, $logging);
        $loop->run();
        return $deferred->promise();
    }

}