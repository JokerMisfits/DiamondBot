<?php

include_once __DIR__ . '/../../../../vendor/autoload.php';
include_once __DIR__ . '/../../../autoLoads/autoload_classes.php';

$test = new test();

class test {
    public function __construct() {
    }

//    private static function updateVoiceStates(TimerInterface $timer) : void {
//
//        if(!self::$radioState){
//            //self::$updateVoiceStatesRun = false;
//            self::$loop->cancelTimer($timer);
//        }
//
//        $description = '$audioPath ' . self::$audioPath . PHP_EOL;
//        if(isset(self::$queue[0])){
//            $size = sizeof(self::$queue);
//            for($i = 0;$i < $size;$i++){
//                $description .= $i . ' $queue ' . self::$queue[$i] . PHP_EOL;
//            }
//        }
//        if(isset(self::$args[0])){
//            $size = sizeof(self::$args);
//            for($i = 0;$i < $size;$i++){
//                $description .= $i . ' $args ' . self::$args[$i] . PHP_EOL;
//            }
//        }
//        if(isset(self::$radioArgs[0])){
//            $size = sizeof(self::$radioArgs);
//            for($i = 0;$i < $size;$i++){
//                $description .= $i . ' $radioArgs: ' . self::$radioArgs[$i] . PHP_EOL;
//            }
//        }
//        $description .= '$radioRepeat ' . self::$radioRepeat . PHP_EOL;
//        $description .= '$radioStateDownload ' . self::$radioStateDownload . PHP_EOL;
//        $description .= '$radioCansel ' . self::$radioCansel . PHP_EOL;
//        $description .= '$radioState ' . self::$radioState . PHP_EOL;
//
//        $embed = embeds::createEmbed('VoiceStates', $description,6738196);
//        self::$channel->messages->fetch(1064526488231235655)->done(function (Message $message) use ($embed) {
//            $message->edit(MessageBuilder::new()->addEmbed($embed));
//        });
//
//    }

}