<?php

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Guild\Guild;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\Activity;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class DiamondBot {
    public static mysqli $mysqli;
    public static LoggerInterface $logger;
    public static array $voiceStates;
    public static Guild $guild;
    public static LoopInterface $loop;
    public static Discord $discord;
    public function __construct() {
        try {
            $discord = new Discord([
                'token' => BotOptions::getBotOption('botDiscordOptions')['token'],
                'intents' => Intents::getAllIntents(),
                'loadAllMembers' => BotOptions::getBotOption('botDiscordOptions')['loadAllMembers'],
            ]);
        }
        catch (Exception | Throwable $e){
            exit($e->getMessage());
        }

        $discord->on('ready', function (Discord $discord) {
            self::$loop = $discord->getloop();
            try {
                self::$logger = $discord->getLogger();
                self::$mysqli = MysqliConnection::initMysqli();
                $status = new Activity($discord);
                $status->name = BotOptions::getBotOption('botStatus')['name'];
                $status->type = BotOptions::getBotOption('botStatus')['type'];
                $status->url = BotOptions::getBotOption('botStatus')['url'];
                $discord->updatePresence($status);
                unset($status);
                self::$guild = $discord->guilds->get('id', BotOptions::getBotOption('botGuildId'));

                if(is_array(self::$guild->voice_states)){
                    self::$voiceStates =  self::$guild->voice_states;
                }
                else{
                    exit('Не подключены voice_states в Guild.php');
                }
                self::$discord = &$discord;
            }
            catch (Exception | Throwable $e){
                exit($e->getMessage());
            }
        });

        $discord->on(Event::VOICE_STATE_UPDATE, function (VoiceStateUpdate $state) {
            if(isset(self::$voiceStates[0])){
                $size = sizeof(self::$voiceStates);
                if($state->channel_id != null){
                    for($i = 0;$i < $size;$i++){
                        if(self::$voiceStates[$i]->user_id == $state->user_id){
                            self::$voiceStates[$i]->channel_id = $state->channel_id;
                            return;
                        }
                    }
                }
                else{
                    for($i = 0;$i < $size;$i++){
                        if(self::$voiceStates[$i]->user_id == $state->user_id){
                            unset(self::$voiceStates[$i]);
                            sort(self::$voiceStates);
                            return;
                        }
                    }
                }
                self::$voiceStates[$size] = $state;
            }
            else{ self::$voiceStates[0] = $state; }
        });

        $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
            if(in_array((int)$message->channel_id, BotOptions::getBotOption('botValidateChannels'))){
                if (str_contains($message->content, "<@" . BotOptions::getBotOption('botId') . ">")) {
                    CommandHandler::init($message, $discord->getChannel($message['channel_id']));
                }
            }
        });

        $discord->on(Event::INTERACTION_CREATE, function (Interaction $interaction, Discord $discord){
            if (in_array($interaction['data']->custom_id,InteractionList::$interactions)){
                InteractionHandler::init($interaction, $discord->getChannel($interaction['channel_id']));
            }
        });

        $discord->run();
    }
}