<?php

class BotOptions {

    private static array $botInfo = [
        'version' => '1.0.0',
        'build' => 16,
    ];

    private static array $botValidateChannels = [
        0 => '700021655325507604',
        1 => '699322970593558536',
        2 => '190505006359773194',
    ];

    private static array $botStatus = [
        'name' => 'Тестирование',
        'type' => 1,
        'url' => 'https://www.twitch.tv/x0diamondxo',
    ];

    private static int $botId = 699977929534341191;

    private static array $botDiscordOptions = [
        'token' => 'Njk5OTc3OTI5NTM0MzQxMTkx.Xpc3tg.fEFk-juf1MjPokZDEOaZGqO28pY',
        'loadAllMembers' => true,
    ];

    private static int $botGuildId = 190505006359773194;
    private static bool $botTestMode = true;//ToDO задействовать в настройках логгера и define

    public static function getBotOption($name) : array | string {
        return self::$$name;
    }
}
