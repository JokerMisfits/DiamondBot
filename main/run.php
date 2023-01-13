<?php

error_reporting(E_ALL);

include_once __DIR__ . '/../vendor/autoload.php';
//require_once  __DIR__ . '/autoLoads/autoload_options.php';
require_once  __DIR__ . '/autoLoads/autoload_classes.php';
include_once 'bot/DiamondBot.php';
$start = new DiamondBot;//Запуск бота


//private static function radio(): void {
//
//    $deferred = new Deferred();
//
//    self::$loop->addTimer(0,function () use ($deferred){
//        self::getUsersChannel($deferred, self::$message->author->id);
//    });
//
//    $deferred->promise()->then(function (Channel $channel){
//
//    })->otherwise(function (Exception | Throwable | int $e){
//        if(is_int($e) && $e == 0){
//            self::$loop->addTimer(0, function (){
//                $msg = MessageBuilder::new()
//                    ->setContent('')
//                    ->addEmbed(embeds::createEmbed('Необходимо зайти в голосовой канал','<@' . self::$message['author']['id'] . '>',6738196))
//                    ->setTts(false)
//                    ->setReplyTo(self::$message);
//                self::$channel->sendMessage($msg);
//            });
//        }
//        else{
//            self::$logger->critical($e->getMessage());
//        }
//    });
//
//    $joinToVC = function ($play) use ($channel){
//        self::$discord->joinVoiceChannel($channel, false, true)->done(function (VoiceClient $vc) use ($play){
//            self::$vc = $vc;
//            unset($vc);
//            self::$loop->addTimer(0, $play);
//        });
//    };
//
//    $play = function () use (&$play) {
//        $deferred = new Deferred();
//
//        self::$vc->playFile(self::$audioPath)
//            ->done(function () use($deferred, &$play){
//                if(isset(self::$args[1]) && self::$args[1] == 'repeat'){
//                    $deferred->promise();
//                    self::$loop->addTimer(1, $play);
//                }
//                else{
//                    self::$vc->close();
//                    $deferred->resolve();
//                }
//            });
//        $deferred->reject();
//    };
//
//
//    if(isset(self::$args[1])){
//        if(self::$args[1] == 'stop'){
//            self::$vc->close();
//        }
//        elseif(self::$args[1] == 'pause'){
//            self::$vc->pause();
//        }
//        elseif(self::$args[1] == 'unpause'){
//            self::$vc->unpause();
//        }
//        elseif(self::$args[1] == 'y' && isset(self::$args[2])){
//            self::$loop->addTimer(0.5, function () use ($play, $joinToVC){
//                $deferred = new Deferred();
//                self::$audioPath = self::download($deferred,self::$args[2]);///////////////////////////////////
//                $joinToVC($play);
//            });
//        }
//    }
//
//    if(!isset(self::$args[1]) || (isset(self::$args[1]) && self::$args[1] == 'repeat')){// Переписать условие после настройки (продумать логику)
//        $joinToVC($play);
//    }
//}//radio