<?php

class Logger
{

    private static array $colors = [
        0 => "\e[0;41m", //red for exception
        1 => "\e[01;42m", //green for data
        2 => "\e[01;43m", //yellow for route
        3 => "\e[01;44m", //blue for proxy
        4 => "\e[01;45m", //pink for server
        5 => "\e[01;46m" //Cyan for db
    ];

    private static array $from = [
        0 => ' Exception: ',
        1 => ' Data: ',
        2 => ' Route: ',
        3 => ' Proxy: ',
        4 => ' Server: ',
        5 => ' DB: ',
    ];

    private static string $endMessage = "\e[0m";

    private static bool $status = false;

    public function __construct()
    {
        self::$status = true;
        self::createMessage('Started.',4);
    }

    public static function getStatus() : bool{
        return self::$status;
    }

    public static function createMessage($message, $from, $object = null) : void{

        echo self::$colors[$from] . date('d.m.Y | H:i:s |') . self::$from[$from] . "$message " . self::$endMessage . PHP_EOL;

        if($object != null){
            var_dump($object);
        }

    }

    public static function clearConsole(){
        echo chr(27).chr(91).'H'.chr(27).chr(91).'J';
    }

}