<?php

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\TextInput;
use Discord\Helpers\Collection;
use Discord\Http\Exceptions\NoPermissionsException;
use Discord\Parts\Channel\Message;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Channel\Channel;
use Discord\Factory\Factory;

class InteractionHandler {
    private static Discord $discord;
    private static Interaction $interaction;
    private static Channel $channel;
    private static Factory $factory;

    public static function init(Interaction $interaction, Channel $channel) : void {
        self::$discord = &DiamondBot::$discord;
        self::$interaction = $interaction;
        self::$channel = $channel;
        $function = self::$interaction['data']->custom_id;
        self::$factory = self::$discord->getFactory();
        self::$function();
    }

    /**
     * @throws Throwable
     */
    private static function youtube() : void {
        $tr = ActionRow::new()->addComponent(TextInput::new('Вставьте ссылку на видео или плейлист', TextInput::STYLE_SHORT, 'youtube_link')
            ->setMinLength(43)
            ->setMaxLength(100)
            ->setPlaceholder('Пример: https://www.youtube.com/watch?v=wK-8TCDrbV8')
            ->setRequired(true));

        self::$interaction->showModal('Play music from youtube', 'youtube_modal', [$tr], function (Interaction $interaction, Collection $components) use ($tr) {
            /** @var Message $message */ $message = self::$factory->create(Message::class);
            $msg = MessageBuilder::new();

            if(CommandHandler::getRadioState()){
                $msg->setContent('Песни добавлены в очередь');
                $message->content = '/radio add ' . $components['youtube_link']->value;
            }
            else{
                $msg->setContent('Загрузка началась, скоро бот зайдет к вам на канал');
                $message->content = '/radio y ' . $components['youtube_link']->value;
            }
            $interaction->respondWithMessage($msg, true);

            CommandHandler::init($message, self::$channel,0, $interaction['member']->user->id);
        });
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function stop() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio stop';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws NoPermissionsException
     * @throws Throwable
     */
    private static function next() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio next';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function past() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio past';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function now() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio now';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function queue() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio queue';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function repeat() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio repeat';
        $msg = MessageBuilder::new();
        if(CommandHandler::getRepeatState()){
            $msg->setContent('Повтор песни отключен');
        }
        else{
            $msg->setContent('Повтор песни включен');
        }
        self::$interaction->respondWithMessage($msg, true);
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function cansel() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio cansel';
        $msg = MessageBuilder::new();
        if(CommandHandler::getCanselState()){
            $msg->setContent('Музыка не будет остановлена');
        }
        else{
            $msg->setContent('Музыка будет полностью остановлена после песни');
        }
        self::$interaction->respondWithMessage($msg, true);
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function pause() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio pause';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function unpause() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/radio unpause';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     * @throws NoPermissionsException
     */
    private static function help() : void {
        /** @var Message $message */ $message = self::$factory->create(Message::class);
        $message->content = '/help';
        self::$interaction->acknowledge();
        CommandHandler::init($message, self::$channel,0, self::$interaction['member']->user->id);
    }

    /**
     * @throws Throwable
     */
    private static function to() : void {
        $tr = ActionRow::new()->addComponent(
        TextInput::new('Введите номер трека в очереди', TextInput::STYLE_SHORT, 'to_queue')
            ->setMinLength(1)
            ->setMaxLength(3)
            ->setPlaceholder('Пример: 2')
            ->setRequired(true));

        self::$interaction->showModal('Переключение песни', 'to_modal', [$tr], function (Interaction $interaction, Collection $components) {
            /** @var Message $message */ $message = self::$factory->create(Message::class);
            $message->content = '/radio to ' . $components['to_queue']->value;
            $interaction->acknowledge();
            CommandHandler::init($message, self::$channel,0, $interaction['member']->user->id);
        });
    }

}