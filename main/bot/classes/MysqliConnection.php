<?php

class MysqliConnection
{
    private static array $args = [
        'host' => '127.0.0.1',
        'dbName' => 'diamondbot',
        'login' => 'root',
        'password' => '',
        'port' => '3306'
    ];

    public static function initMysqli() : mysqli{

        $mysqli = new mysqli(self::$args['host'], self::$args['login'], self::$args['password'], self::$args['dbName'], self::$args['port']);
        if (mysqli_connect_error()) {
            DiamondBot::$logger->error("Debugging errno: " . mysqli_connect_errno());
            DiamondBot::$logger->error("Debugging error: " . mysqli_connect_error());
            DiamondBot::$logger->critical('Error: Unable to connect to MySQL.');
        }
        DiamondBot::$logger->info('Success: A proper connection to MySQL was made!');
        DiamondBot::$logger->info("Host information: " . mysqli_get_host_info($mysqli));

        return $mysqli;
    }

}