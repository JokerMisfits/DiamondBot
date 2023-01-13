<?php

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Deferred;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Voice\VoiceClient;
use React\EventLoop\LoopInterface;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;
use Psr\Log\LoggerInterface;

class Command {

    private static Discord $discord;
    private static Guild $guild;
    private static LoggerInterface $logger;
    private static Channel $channel;
    private static Message $message;
    private static VoiceClient $vc;
    private static LoopInterface $loop;
    private static string $audioPath = '';
    private static array $args;
    private static array $voiceStates;
    private static bool $radioState = false;

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    public static function init(Channel $channel, Message $message, int $checked = 0): void {

        self::$discord = DiamondBot::$discord;
        self::$guild = &DiamondBot::$guild;
        self::$loop = &DiamondBot::$loop;
        self::$logger = &DiamondBot::$logger;
        self::$voiceStates = &DiamondBot::$voiceStates;
        self::$channel = $channel;
        self::$message = $message;

        $deferred = new Deferred();

        $deferred->promise()->then(function ($result){
            if($result == 0){
                self::$loop->addTimer(0, function (){
                    self::help();
                });
            }
            elseif($result == 1){
                self::$loop->addTimer(0, function (){
                    Command::init(self::$channel, self::$message,1);
                });
            }
            else{
                self::$loop->addTimer(0, function () use ($result){
                    self::$result();
                });
            }
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });

        self::$loop->addTimer(0,function () use ($deferred, $checked){
            try {
                if ($checked == 0) {
                    self::$message->content = str_replace('<@' . BotOptions::getBotOption('botId') . '> ', '', self::$message->content);
                    if (self::$message->content[0] == '/') {
                        $commandList = CommandList::$commands;
                        $size = sizeof($commandList);
                        $args = explode(" ", self::$message->content);

                        if (isset($args[1])) {
                            $command = $args[0];
                        }
                        else {
                            $command = self::$message->content;
                        }

                        for ($i = 0; $i < $size; $i++) {
                            if ($command == $commandList[$i]['command']) { //ToDO Добавить метод на доступные команды + проверка на права
                                self::$args = $args;
                                $deferred->resolve(1);
                            }
                        }
                    }
                    $deferred->resolve(0);
                }
                else {
                    if (isset(self::$args[1])) {
                        $func = trim(self::$args[0], "/");
                    } else {
                        $func = trim(self::$message->content, "/");

                    }
                    $deferred->resolve($func);
                }
            }
            catch (Exception | Throwable $e){
                $deferred->reject($e);
            }
        });
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function roll(): void {

        $deferred = new Deferred();

        self::$loop->addTimer(0, function () use ($deferred){
            try {
                $msg = MessageBuilder::new()
                    ->setContent('')
                    ->addEmbed(embeds::createEmbed('Rolled: ' . rand(0, 100),'<@' . self::$message['author']['id'] . '>',6738196))
                    ->setTts(false)
                    ->setReplyTo(self::$message);

                $deferred->resolve($msg);
            }
            catch (Exception | Throwable $e){
                $deferred->reject($e);
            }
        });

        $deferred->promise()->then(function ($msg){
            self::$loop->addTimer(0,function () use ($msg){
                try {
                    self::$channel->sendMessage($msg);
                }
                catch (Exception | Throwable $e){
                    self::$logger->critical($e->getMessage());
                }
            });
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });
    }//Roll command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function clear(): void {

        $deferred = new Deferred();

        $deferred->promise()->then(function ($count){
            self::$channel->sendMessage('', false,
                embeds::createEmbed('Сообщений успешно удалено ' . $count,'<@' . self::$message['author']['id'] . '>',6738196));
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });

        self::$loop->addTimer(0,function () use ($deferred){
            try {
                $count = self::$args[1] ?? 1;
                self::$channel->limitDelete($count)->done(function () use ($deferred, $count){
                    $deferred->resolve($count);
                });
            }
            catch (Exception | Throwable $e){
                $deferred->reject($e);
            }
        });
    }//Clear command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function help(): void {

        $deferred = new Deferred();

        self::$loop->addTimer(0, function () use ($deferred){
            self::getEnabledCommands($deferred);
        });

        $deferred->promise()->then(function ($commandList){
            self::$loop->addTimer(0,function () use ($commandList){
                $embed = embeds::createEmbed('Список команд:','',6738196);
                $size = sizeof($commandList);

                for ($i = 0; $i < $size; $i++) {
                    $embed['description'] .= $commandList[$i]['command'] . PHP_EOL;
                    $embed['description'] .= $commandList[$i]['description'] . PHP_EOL . PHP_EOL;
                }

                $embed['description'] .= 'Разработчик бота: <@216058366689148930>';
                $msg = MessageBuilder::new()
                    ->setContent('')
                    ->addEmbed($embed)
                    ->setTts(false)
                    ->setReplyTo(self::$message);

                self::$channel->sendMessage($msg);
            });
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });
    }//Help command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    //ToDO возможно добавить проверку на cansel и не перезаписывать аргументы, если их нет в списке sub-command
    //ToDO потестить как ведет себя, когда командуешь из других комнат
    private static function radio(): void {

        $deferred = new Deferred();

        $play = function () use (&$play) {
            self::$radioState = true;
            self::$vc->playFile(self::$audioPath)->done(function () use (&$play){
                if(isset(self::$args[1]) && self::$args[1] == 'repeat'){
                    self::$radioState = true;
                    self::$loop->addTimer(0, $play);
                }
                else{
                    self::$radioState = false;
                    self::$vc->close();
                }
            });
        };

        if(!self::$radioState && self::$audioPath != ''){
            self::$loop->addTimer(0,function () use ($deferred){
                self::getUsersChannel($deferred, self::$message->author->id);
            });

            $deferred->promise()->then(function (Channel $channel) use ($play){
                self::$loop->addTimer(0,function () use ($channel, $play){
                    self::$discord->joinVoiceChannel($channel, false, true)->done(function (VoiceClient $vc) use ($play){
                        self::$vc = $vc;
                        self::$loop->addTimer(0, $play);
                    });
                });
            })->otherwise(function (Exception | Throwable | int | string $e){
                if(is_int($e) && $e == 0){
                    self::$loop->addTimer(0, function (){
                        $msg = MessageBuilder::new()
                            ->setContent('')
                            ->addEmbed(embeds::createEmbed('Необходимо зайти в голосовой канал','<@' . self::$message['author']['id'] . '>',6738196))
                            ->setTts(false)
                            ->setReplyTo(self::$message);
                        self::$channel->sendMessage($msg);
                    });
                }
                elseif(is_string($e)){
                    self::$logger->critical($e);
                    $msg = MessageBuilder::new()
                        ->setContent('')
                        ->addEmbed(embeds::createEmbed('Произошла ошибка при загрузке','<@' . self::$message['author']['id'] . '>',6738196))
                        ->setTts(false)
                        ->setReplyTo(self::$message);
                    self::$channel->sendMessage($msg);
                }
                else{
                    self::$logger->critical($e->getMessage());
                }
            });
        }
        else{
            if(isset(self::$args[1])){
                if(self::$args[1] == 'stop'){
                    self::$radioState = false;
                    self::$vc->close();
                }
                elseif(self::$args[1] == 'pause'){
                    self::$vc->pause();
                }
                elseif(self::$args[1] == 'unpause'){
                    self::$vc->unpause();
                }
                elseif(self::$args[1] == 'y' && isset(self::$args[2])){

                    $deferred = new Deferred();

                    self::$loop->addTimer(0, function () use ($deferred){
                        self::download($deferred,self::$args[2]);
                    });

                    $deferred->promise()->then(function ($audioPath) use ($play){
                        self::$audioPath = $audioPath;

                        $deferred = new Deferred();

                        self::$loop->addTimer(0,function () use ($deferred){
                            self::getUsersChannel($deferred, self::$message->author->id);
                        });

                        $deferred->promise()->then(function (Channel $channel) use ($play){
                            self::$loop->addTimer(0,function () use ($channel, $play){
                                self::$discord->joinVoiceChannel($channel, false, true)->done(function (VoiceClient $vc) use ($play){
                                    self::$vc = $vc;
                                    self::$loop->addTimer(0, $play);
                                });
                            });
                        });


                    })->otherwise(function (Exception | Throwable | int $e){
                        if(is_int($e)){
                            if($e == 0){
                                self::$loop->addTimer(0, function (){
                                    $msg = MessageBuilder::new()
                                        ->setContent('')
                                        ->addEmbed(embeds::createEmbed('Некорректная ссылка','<@' . self::$message['author']['id'] . '>',6738196))
                                        ->setTts(false)
                                        ->setReplyTo(self::$message);

                                    self::$channel->sendMessage($msg);
                                });
                            }
                            elseif($e == 1){
                                $msg = MessageBuilder::new()
                                    ->setContent('')
                                    ->addEmbed(embeds::createEmbed('ffmpeg/yt-dlp.exe не найден','<@' . self::$message['author']['id'] . '>',6738196))
                                    ->setTts(false)
                                    ->setReplyTo(self::$message);

                                self::$channel->sendMessage($msg);
                            }
                        }
                        else{
                            self::$logger->critical($e->getMessage());
                        }
                    });
                }
            }
        }
    }//radio

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function download(Deferred $deferred, string $link): void { //ToDO Добавить проверку на плейлист(работа с очередью) добавить проверку на продолжительность и вес файла

        $deferredDownload = new Deferred();

        self::$loop->addTimer(0,function () use ($deferred, $deferredDownload, $link){
            if(!self::checkUrl($link)){
                $deferred->reject(0);
                $deferredDownload->reject(0);
            }
            else{
                $deferredDownload->resolve($link);
            }
        });

        $deferredDownload->promise()->then(function ($link) use ($deferred){
            self::$loop->addTimer(0, function () use ($link, $deferred){
                try {
                    $yt = new YoutubeDl();
                    if(file_exists('ffmpeg/yt-dlp.exe')){
                        $yt->setBinPath('ffmpeg/yt-dlp.exe');


                        $deferredGenerateString = new Deferred();
                        self::$loop->addTimer(0,function () use ($deferredGenerateString){
                            self::generateRandomString($deferredGenerateString, 20);
                        });

                        $deferredGenerateString->promise()->then(function ($name) use ($deferred, $yt, $link){
                            $audioFormat = 'mp3';
                            $collection = $yt->download(
                                Options::create()
                                    ->downloadPath('/music')
                                    ->extractAudio(true)
                                    ->audioFormat($audioFormat)
                                    ->audioQuality('0') // 0 is best
                                    ->output($name.'.%(ext)s')
                                    ->url($link)
                            );
                            foreach ($collection->getVideos() as $audio) {
                                if ($audio->getError() !== null) {
                                    $deferred->reject('Error downloading video: ' . $audio->getError());
                                }
                                else{
                                    $deferred->resolve('music/'.$name.'.'.$audioFormat);
                                }
                            }
                        });
                    }
                    else{
                        $deferred->reject(1);
                    }
                }
                catch (Exception | Throwable $e){
                    $deferred->reject($e);
                }
            });
        });

//            $yt->onProgress(static function (?string $progressTarget, string $percentage, string $size, string $speed, string $eta, ?string $totalTime): void {
//              echo "Download percentage: $percentage" . ' Speed: ' . $speed . PHP_EOL;
//            });

    }//download

    private static function checkUrl(string $url) : bool{
        if($url[0] == 'h')
        if($url[1] == 't')
        if($url[2] == 't')
        if($url[3] == 'p')
        if($url[4] == 's')
        if($url[5] == ':')
        if($url[6] == '/')
        if($url[7] == '/')
        if($url[8] == 'w')
        if($url[9] == 'w')
        if($url[10] == 'w')
        if($url[11] == '.')
        if($url[12] == 'y')
        if($url[13] == 'o')
        if($url[14] == 'u')
        if($url[15] == 't')
        if($url[16] == 'u')
        if($url[17] == 'b')
        if($url[18] == 'e')
        if($url[19] == '.')
        if($url[20] == 'c')
        if($url[21] == 'o')
        if($url[22] == 'm')
        if($url[23] == '/')
        if($url[24] == 'w')
        if($url[25] == 'a')
        if($url[26] == 't')
        if($url[27] == 'c')
        if($url[28] == 'h')
        if($url[29] == '?')
        if($url[30] == 'v')
        if($url[31] == '=')
            return true;
        return false;
    } //ToDo Переписать

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function getUsersChannel(Deferred $deferred, $userId) : void{
        try {
            if(isset(self::$voiceStates[0])){
                $size = sizeof(self::$voiceStates);
                for($i = 0; $i < $size;$i++){
                    if(self::$voiceStates[$i]->user_id == $userId)
                        $deferred->resolve(self::$discord->getChannel(self::$voiceStates[$i]->channel_id));
                }
            }
            else{
                $deferred->reject(0);
            }
        }
        catch (Exception | Throwable $e){
            $deferred->reject($e);
        }
    }

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function getEnabledCommands(Deferred $deferred) : void  {

        self::$loop->addTimer(0,function () use ($deferred){
            try {
                $commandList = CommandList::$commands;
                $size = sizeof($commandList);

                for ($i = 0; $i < $size; $i++) {
                    if (!$commandList[$i]['enable']) {
                        unset($commandList[$i]);
                    }
                }
                sort($commandList);
                $deferred->resolve($commandList);
            }
            catch (Exception | Throwable $e){
                $deferred->reject($e);
            }
        });
    }

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function generateRandomString(Deferred $deferred, int $strength) : void {
        try {
            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $input_length = strlen($permitted_chars);
            $random_string = '';
            for($i = 0; $i < $strength; $i++) {
                $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
                $random_string .= $random_character;
            }
            $deferred->resolve($random_string);
        }
        catch (Throwable | Exception $e){
            $deferred->resolve($e);
        }
    }

    private static function deleteAudio(Deferred $deferred, $path) : void {

    }

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function test() : void{

//        $deferred = new Deferred();
//
//        $promise = $deferred->promise()->then(function ($result){
//            echo 'result: ' . $result;
//        });
//
//        self::$loop->addTimer(0,function (/*$timer Нужно если periodicTimer */) use ($deferred){
//            //self::$loop->cancelTimer($timer); // Нужно если PeriodicTimer
//            $commandList = CommandList::$commands;
//            $size = sizeof($commandList);
//
//            for ($i = 0; $i < $size; $i++) {
//                if (!$commandList[$i]['enable']) {
//                    unset($commandList[$i]);
//                }
//            }
//
//            sort($commandList);
//
//            $embed = embeds::createEmbed('Список команд:','',6738196);
//            $size = sizeof($commandList);
//
//            for ($i = 0; $i < $size; $i++) {
//                $embed['description'] .= $commandList[$i]['command'] . PHP_EOL;
//                $embed['description'] .= $commandList[$i]['description'] . PHP_EOL . PHP_EOL;
//            }
//
//            $embed['description'] .= 'Разработчик бота: <@216058366689148930>';
//            $msg = MessageBuilder::new()
//                ->setContent('')
//                ->addEmbed($embed)
//                ->setTts(false)
//                ->setReplyTo(self::$message);
//
//            self::$channel->sendMessage($msg)->then(function () use ($deferred){
//                $deferred->resolve('Сообщение отправлено');
//            })->otherwise(function () use ($deferred){
//                $deferred->reject('Сообщение не отправлено');
//            });
//        });
//
    }

}