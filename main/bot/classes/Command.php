<?php

//ToDo поискать скачивание плейлистов по тегам

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Helpers\Deferred;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Voice\VoiceClient;
use React\EventLoop\LoopInterface;
use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use React\EventLoop\TimerInterface;

class Command {
    private static Discord $discord;
    private static Guild $guild;
    private static LoggerInterface $logger;
    private static Channel $channel;
    private static Message $message;
    private static VoiceClient $vc;
    private static LoopInterface $loop;
    private static string $audioPath = '';
    private static array $queue = [];
    private static array $queueMetaData = [];
    private static int $queueCounter = 0;
    private static array $args;
    private static array $radioArgs;
    private static bool $radioRepeat = false;
    private static bool $radioStateDownload = false;
    private static bool $radioCansel = false;
    private static array $voiceStates;
    private static bool $radioState = false;
    private static bool $radioStarted = false;
    private static bool $checkForNewFile = false;
    private static bool $updateVoiceStatesRun = false;

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
                    self::help();
            }
            elseif($result == 1){
                    Command::init(self::$channel, self::$message,1);
            }
            else{
                    self::$result();
            }
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });
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
    }

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function roll(): void {
        $msg = MessageBuilder::new()
            ->setContent('')
            ->addEmbed(embeds::createEmbed('Rolled: ' . rand(0, 100),'<@' . self::$message['author']['id'] . '>',6738196))
            ->setTts()
            ->setReplyTo(self::$message);
        self::$channel->sendMessage($msg);
    }//Roll command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function clear(): void {
        $count = self::$args[1] ?? 1;
        self::$channel->limitDelete($count)->done(function () use ($count) {
            self::$channel->sendMessage('', false,
                embeds::createEmbed('Сообщений успешно удалено ' . $count,'<@' . self::$message['author']['id'] . '>',6738196));
        });
    }//Clear command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    private static function help(): void {

        $deferred = new Deferred();
        self::getEnabledCommands($deferred);

        $deferred->promise()->then(function ($commandList){
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
                ->setTts()
                ->setReplyTo(self::$message);
            self::$channel->sendMessage($msg);
        })->otherwise(function (Exception | Throwable $e){
            self::$logger->critical($e->getMessage());
        });
    }//Help command

    /**
     * @throws Exception
     * @throws Throwable
     * @throws NoPermissionsException
     */

    //ToDO протестировать как ведет себя, когда командуешь из других комнат
    private static function radio(): void {
        if(!self::$radioStarted){
            self::$radioStarted = true;

            if (self::$message['channel_id'] == 700021655325507604 && !self::$updateVoiceStatesRun){
                self::$updateVoiceStatesRun = true;
                self::$loop->addPeriodicTimer(10,function (){
                    self::updateVoiceStates();
                });
            }

        }
        if(((self::$args[1] == 'add' && !self::$radioState) || (self::$args[1] == 'y')) && isset(self::$radioArgs[0]) && self::$radioState){
            self::$args = self::$radioArgs;
            if(self::$args[1] == 'y'){
                self::sendRadioErrorToChannel(0);
            }
            else{
                self::sendRadioErrorToChannel(1);
            }
            return;
        }
        else{
            self::$radioArgs = self::$args;
        }
        $deferred = new Deferred();

        $play = function () use (&$play) {
            self::$radioState = true;
            self::$vc->playFile(self::$audioPath)->done(function () use (&$play){
                if(self::$radioCansel){
                    self::$radioState = false;
                    self::$radioCansel = false;
                    self::deleteAudio();
                    self::$vc->close();
                    return;
                }
                $size = sizeof(self::$queue);
                self::checkQueue();
                if(self::$queueCounter != $size || self::$radioRepeat){
                    self::$radioState = true;
                    $play();
                }
                else{
                    if(self::$radioStateDownload){
                        $deferred = new Deferred();
                        self::awaitDownload($deferred);
                        $deferred->promise()->then(function () use (&$play){
                            self::checkQueue();
                            $play();
                        })->otherwise(function () use ($size){
                            self::$radioState = false;
                            self::deleteAudio();
                            self::$vc->close();
                        });
                    }
                    else{
                        self::$radioState = false;
                        self::deleteAudio();
                        self::$vc->close();
                    }
                }
            });
        };

        if(!self::$radioState && self::$audioPath != ''){
                self::getUsersChannel($deferred, self::$message->author->id);
            $deferred->promise()->then(function (Channel $channel) use ($play){
                    self::$discord->joinVoiceChannel($channel)->done(function (VoiceClient $vc) use ($play){
                        self::$vc = $vc;
                        $play();
                    });
            })->otherwise(function (Exception | Throwable | int | string $e){
                if(is_int($e) && $e == 0){
                    self::sendRadioErrorToChannel(2);
                }
                elseif(is_string($e)){
                    self::$logger->critical($e);
                    self::sendRadioErrorToChannel(3);
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
                    self::deleteAudio();
                    self::$vc->close();
                }
                elseif(self::$args[1] == 'pause'){
                    self::$vc->pause();
                }
                elseif(self::$args[1] == 'unpause'){
                    self::$vc->unpause();
                }
                elseif(self::$args[1] == 'next'){
                    if(isset(self::$queue[self::$queueCounter + 1])){
                        self::$queueCounter++;
                        self::$audioPath = self::$queue[self::$queueCounter];
                        self::$vc->stop();
                        $play();
                    }
                    else{
                        self::sendRadioErrorToChannel(10);
                    }
                }
                elseif(self::$args[1] == 'past'){
                    if(isset(self::$queue[self::$queueCounter - 1])){
                        self::$queueCounter--;
                        self::$audioPath = self::$queue[self::$queueCounter];
                        self::$vc->stop();
                        $play();
                    }
                    else{
                        self::sendRadioErrorToChannel(11);
                    }

                }
                elseif(self::$args[1] == 'skip'){
                    if(self::$radioStateDownload){
                        $deferred = new Deferred();
                        self::awaitDownload($deferred);
                        $deferred->promise()->then(function () use (&$play){
                            if(isset(self::$queue[self::$queueCounter + 1])){
                                self::resetRadioOptions(0);
                                self::$vc->stop();
                                $play();
                            }
                            else{
                                self::sendRadioErrorToChannel(4);
                            }

                        })->otherwise(function (){
                            self::sendRadioErrorToChannel(5);
                        });
                    }
                    else{
                        if(isset(self::$queue[self::$queueCounter + 1])){
                            self::resetRadioOptions(0);
                            self::$vc->stop();
                            $play();
                        }
                        else{
                            self::sendRadioErrorToChannel(4);
                        }
                    }
                }
                elseif(self::$args[1] == 'prev'){
                    if(isset(self::$queue[self::$queueCounter - 1])){
                        self::resetRadioOptions(1);
                        self::$vc->stop();
                        $play();
                    }
                    else{
                        self::sendRadioErrorToChannel(4);
                    }
                }
                elseif(self::$args[1] == 'cansel'){
                    if(self::$radioStateDownload){
                        $deferred = new Deferred();
                        self::awaitDownload($deferred);
                        $deferred->promise()->then(function (){
                            self::$radioCansel = true;
                        })->otherwise(function (){
                            self::sendRadioErrorToChannel(5);
                        });
                    }
                    else{
                        self::$radioCansel = true;
                    }
                }
                elseif(self::$args[1] == 'repeat'){
                    if(!self::$radioRepeat){
                        self::$radioRepeat = true;
                    }
                    else{
                        self::$radioRepeat = false;
                    }
                }
                elseif(self::$args[1] == 'queue'){
                    if(self::$radioStateDownload){
                        self::sendRadioErrorToChannel(14);
                    }
                    else{
                        self::setQueueMetaData();
                    }
                }
                elseif(self::$args[1] == 'to' && isset(self::$args[2])){
                $counter = (int)self::$args[2];
                    if(isset(self::$queue[$counter])){
                        self::$queueCounter = $counter;
                        self::$audioPath = self::$queue[self::$queueCounter];
                        self::$vc->stop();
                        $play();
                    }
                    else{
                        self::sendRadioErrorToChannel(12);
                    }
                }
                elseif(self::$args[1] == 'now'){
                    if(self::$radioState){
                        self::sendPlayNow();
                    }
                    else{
                        self::sendRadioErrorToChannel(1);
                    }
                }
                elseif(self::$args[1] == 'y' && isset(self::$args[2]) && !self::$radioState || (self::$args[1] == 'add' && self::$radioState)){

                    $deferred = new Deferred();
                    self::download($deferred,self::$args[2]);

                    $deferred->promise()->then(function ($audioPath) use (&$play){
                        if(self::$args[1] == 'add'){
                            self::setQueue($audioPath);
                        }
                        else{
                            if(!isset(self::$audioPath) && isset($audioPath)){
                                self::$audioPath = $audioPath;
                            }
                            $deferred = new Deferred();
                            self::getUsersChannel($deferred, self::$message->author->id);

                            $deferred->promise()->then(function (Channel $channel) use (&$play){
                                self::$discord->joinVoiceChannel($channel)->done(function (VoiceClient $vc) use (&$play){
                                    self::$vc = $vc;
                                    $play();
                                });
                            });
                        }
                    })->otherwise(function (Exception | Throwable | int $e){
                        if(is_int($e)){
                            if($e == 0){
                                self::sendRadioErrorToChannel(6);
                            }
                            elseif($e == 1){
                                self::sendRadioErrorToChannel(7);
                            }
                            elseif($e == 2){
                                self::sendRadioErrorToChannel(9);
                            }
                        }
                        else{
                            self::$logger->critical($e->getMessage());
                        }
                    });
                }
                else{
                    self::$args = self::$radioArgs;//Восстановление аргументов, если команды не было в sub commands
                    self::sendRadioErrorToChannel(8);
                }
            }
            else{
                //ToDO Вывод sub команд
            }
        }
    }//radio

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function download(Deferred $deferred, string $link): void {

        $deferredDownload = new Deferred();

        if(!self::checkUrl($link)){
            $deferred->reject(0);
            $deferredDownload->reject(0);
        }
        else{
            $deferredDownload->resolve($link);
        }

        $deferredDownload->promise()->then(function ($link) use ($deferred){
            if(strlen($link) >= 43){
                try {
                    self::$radioStateDownload = true;
                    self::$loop->addPeriodicTimer(10,function ($timer) use ($deferred){
                        $counter = 0;
                        self::checkForNewFile($deferred);
                        if(!self::$checkForNewFile){
                            $counter++;
                            if($counter >= 3){
                                if(self::$audioPath == self::$queue[0]){
                                    unset(self::$queue[0]);
                                    if(isset(self::$queue[1])){
                                        sort(self::$queue);
                                    }
                                }
                                self::$loop->cancelTimer($timer);
                            }
                        }
                        self::$checkForNewFile = false;
                    });

                    $process = new Process("php bot/classes/extProcess/ytDownload.php $link", null, null, array());
                    $process->start();
                    $process->on('exit', function () {
                        self::$radioStateDownload = false;
                    });
                }
                catch (Exception | Throwable $e){
                    $deferred->reject($e);
                }
            }
        });
    }//download

    private static function checkUrl(string $url) : bool {
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
        if(strlen($url) >= 43)
            return true;
        return false;
    } //ToDo Переписать

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function getUsersChannel(Deferred $deferred, $userId) : void {
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

    private static function getEnabledCommands(Deferred $deferred) : void {
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
    }

    /**
     * @throws Exception
     * @throws Throwable
     */

    private static function deleteAudio() : void { //ToDo Когда появятся директории
            $files = glob('music/*');
            foreach ($files as $file){
                if (is_file($file)) {
                    unlink($file);
                }
            }
            self::$audioPath = '';
            self::$queue = [];
            self::$radioArgs = [];
            self::$queueMetaData = [];
    }

    private static function setQueue($audioPath) : void {
        if(self::$queueCounter == 0 && !isset(self::$queue[1])){
            self::$queue[0] = self::$audioPath;
            self::$queue[1] = $audioPath;
        }
        else{
            $size = sizeof(self::$queue);
            self::$queue[$size] = $audioPath;
        }
    }

    private static function checkQueue() : void{
        if(!self::$radioRepeat){
            self::$queueCounter++;
        }
        if(isset(self::$queue[self::$queueCounter])){
            self::$audioPath = self::$queue[self::$queueCounter];
        }
        else{
            self::$queueCounter = sizeof(self::$queue);
        }
    }

    private static function regJson(string $path) : void {
        if(file_exists($path)){
            $json = file_get_contents($path);
            $json = json_decode($json);
            $newJson = (object)[];
            $newJson->title = $json->title;
            $newJson->description = $json->description;
            $newJson->view_count = $json->view_count;
            $newJson->like_count = $json->like_count;
            $newJson->channel_follower_count = $json->channel_follower_count;
            $newJson->upload_date = $json->upload_date;
            $newJson->duration_string = $json->duration_string;
            $newJson->logo = 'https://i.ytimg.com/vi/'.$json->id.'/maxresdefault.jpg';
            if(isset($json->filesize)){
                $newJson->filesize = $json->filesize;
            }
            $newJson->fulltitle = $json->fulltitle;
            file_put_contents($path, json_encode($newJson));
        }
    }

    private static function awaitDownload(Deferred $deferred) : void {
        $counter = 0;
        self::$loop->addPeriodicTimer(5,function (TimerInterface $timer) use ($deferred, &$counter){
            if(!self::$radioStateDownload){
                self::$loop->cancelTimer($timer);
                $deferred->resolve();
            }
            if($counter >= 2){
                self::$loop->cancelTimer($timer);
                $deferred->reject();
            }
            $counter++;
        });
    }

    private static function resetRadioOptions(int $mode) : void {
        if($mode == 0){
            self::prepareQueueForSkip();
        }
        else{
            self::prepareQueueForPrev();
        }
        self::$queueCounter = 0;
        self::$radioState = false;
        self::$radioRepeat = false;
        self::$audioPath = self::$queue[0];
        self::$radioArgs = [];
    }

    private static function prepareQueueForSkip() : void {
        $count = self::$queueCounter;
        for($i = 0; $i <= $count;$i++){
            unset(self::$queue[$i]);
        }
        sort(self::$queue);
    }

    private static function prepareQueueForPrev() : void {
        $count =self::$queueCounter;
        for($i = $count;$i > 0;$i--){
            unset(self::$queue[$i]);
        }
        sort(self::$queue);
    }

    /**
     * @throws NoPermissionsException
     */

    private static function sendRadioErrorToChannel(int $from) : void {
        if($from == 0){
            $title = 'Музыка уже играет, воспользуйтесь командой /radio add';
        }
        elseif($from == 1){
            $title = 'Подождите пока бот зайдет в канал';
        }
        elseif($from == 2){
            $title = 'Необходимо зайти в голосовой канал';
        }
        elseif($from == 3){
            $title = 'Произошла ошибка при загрузке';
        }
        elseif($from == 4){
            $title = 'Очередь пуста, используйте команду /radio add';
        }
        elseif($from == 5){
            $title = 'Данный параметр недоступен во время загрузки файла, попробуйте позже';
        }
        elseif($from == 6){
            $title = 'Некорректная ссылка';
        }
        elseif($from == 7){
            $title = 'ffmpeg/yt-dlp.exe не найден';
        }
        elseif($from == 8){
            $title = 'Параметр: ' . self::$args[1] . ' не найден.';
        }
        elseif($from == 9){
            $title = 'Ошибка загрузки';
        }
        elseif($from == 10){
            $title = 'Конец очереди, попробуйте /radio past';
        }
        elseif($from == 11){
            $title = 'Конец очереди, попробуйте /radio next';
        }
        elseif($from == 12){
            $title = 'Вашего номера нет в очереди, чтобы узнать номер нужной вам песни воспользуйтесь командой: /radio queue';
        }
        elseif($from == 13){
            $title = 'Очередь пуста';
        }
        elseif($from == 14){
            $title = 'Очередь формируется, попробуйте позже';
        }
        else{
            $title = 'Неизвестная ошибка';
        }
        $msg = MessageBuilder::new()
            ->setContent('')
            ->addEmbed(embeds::createEmbed($title,'<@' . self::$message['author']['id'] . '>',6738196))
            ->setTts()
            ->setReplyTo(self::$message);
        self::$channel->sendMessage($msg);
    }

    /**
     * @throws Exception
     */
    private static function updateVoiceStates() : void{

        $description = '$audioPath ' . self::$audioPath . PHP_EOL;
        if(isset(self::$queue[0])){
            $size = sizeof(self::$queue);
            for($i = 0;$i < $size;$i++){
                $description .= $i . ' $queue ' . self::$queue[$i] . PHP_EOL;
            }
        }
        if(isset(self::$args[0])){
            $size = sizeof(self::$args);
            for($i = 0;$i < $size;$i++){
                $description .= $i . ' $args ' . self::$args[$i] . PHP_EOL;
            }
        }
        if(isset(self::$radioArgs[0])){
            $size = sizeof(self::$radioArgs);
            for($i = 0;$i < $size;$i++){
                $description .= $i . ' $radioArgs: ' . self::$radioArgs[$i] . PHP_EOL;
            }
        }
        $description .= '$radioRepeat ' . self::$radioRepeat . PHP_EOL;
        $description .= '$radioStateDownload ' . self::$radioStateDownload . PHP_EOL;
        $description .= '$radioCansel ' . self::$radioCansel . PHP_EOL;
        $description .= '$radioState ' . self::$radioState . PHP_EOL;

        $embed = embeds::createEmbed('VoiceStates', $description,6738196);
        self::$channel->messages->fetch(1064526488231235655)->done(function (Message $message) use ($embed) {
            $message->edit(MessageBuilder::new()->addEmbed($embed));
        });

    }

    private static function checkForNewFile(Deferred $deferred) : void {

        $files = glob('music/*.mp3');
        $size1 = sizeof($files);

        for($i = 0;$i < $size1;$i++){
            $continue = true;
            $size = sizeof(self::$queue);
            for ($j = 0;$j < $size;$j++){
                if(self::$queue[$j] == $files[$i]){
                    $continue = false;
                    break;
                }
            }
            if($continue){
                if(isset(self::$queue)){
                    self::$queue[$size] = $files[$i];
                }
                else{
                    self::$queue[0] = $files[$i];
                }
                self::regJson(str_replace('.mp3', '',$files[$i]).'.info.json');
                self::$checkForNewFile = true;
            }
        }

        if(self::$audioPath == '' && isset(self::$queue[0])){
            self::$audioPath = self::$queue[0];
            if(!self::$radioState){
                $deferred->resolve();
            }
        }
    }

    /**
     * @throws NoPermissionsException
     */
    private static function setQueueMetaData($mode = 0) : void {
        if(isset(self::$queue)){
            $size = sizeof(self::$queue);
            $titles = '';
            for($i = 0;$i < $size;$i++){
                $path = str_replace('mp3','info.json',self::$queue[$i]);
                $json = json_decode(file_get_contents($path));
                self::$queueMetaData[$i] = json_encode($json);
                $titles .= $i . ' ' . $json->title . PHP_EOL;
            }

            if($mode == 0){
                $msg = MessageBuilder::new()
                    ->setContent('')
                    ->addEmbed(embeds::createEmbed('Список очереди:',$titles,6738196))
                    ->setTts()
                    ->setReplyTo(self::$message);
                self::$channel->sendMessage($msg);
            }
        }
        else{
            self::sendRadioErrorToChannel(13);
        }
    }

    /**
     * @throws NoPermissionsException
     */
    private static function sendPlayNow() : void {

        if(!isset(self::$queueMetaData[0])){
            self::setQueueMetaData(1);
        }
        if(isset(self::$queue[self::$queueCounter])){
            $json = json_decode(self::$queueMetaData[self::$queueCounter]);

            $now = $json->title . PHP_EOL;
            $now .= 'Описание:' . PHP_EOL . $json->description . PHP_EOL;
            $now .= 'Количество просмотров: ' . $json->view_count . PHP_EOL;
            $now .= 'Количество лайков: ' . $json->like_count . PHP_EOL;
            $now .= 'Количество подписчиков на канал: ' . $json->channel_follower_count . PHP_EOL;
            $now .= 'Дата загрузки: ' . $json->upload_date . PHP_EOL;
            $now .= 'Продолжительность: ' . $json->duration_string . PHP_EOL;
            $msg = MessageBuilder::new()
                ->setContent('')
                ->addEmbed(embeds::createEmbedWithImage('Сейчас играет:', $now, $json->logo,6738196))
                ->setTts()
                ->setReplyTo(self::$message);
            self::$channel->sendMessage($msg);
        }
        else{
            self::sendRadioErrorToChannel(13);
        }

    }

    private static function test() : void {
            $process = new Process("php bot/classes/extProcess/test.php ", null, null, array());
            $process->start();
            $process->on('exit', function ($res){
                self::$logger->info('test.php завершился кодом состояния: ' . $res);
            });
    }
}