<?php

class BotOptions {

    private static array $botInfo = [
        'version' => '0.0.3',
        'build' => 14,
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

    public static function getBotOption($name) : array | string {
        return self::$$name;
    }


    //for voiceClient

    // $executable = rtrim((string) explode(PHP_EOL, shell_exec("{$which} /r ffmpeg {$executable}"))[0]);
    //        $binaries = [
    //            'ffmpeg',
    //            'ffplay',
    //            'ffprobe',
    //        ];
}

