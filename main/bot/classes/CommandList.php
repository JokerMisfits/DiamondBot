<?php

class CommandList
{
    public static array $commands = [
        0 => [
            'command' => '/roll',
            'enable' => true,
            'description' => 'Команда выводит случайное число от 0 до 100',
        ],
        1 => [
            'command' => '/maps ',
            'enable' => false,
        ],
        2 => [
            'command' => '/pool',
            'enable' => false,
        ],
        3 => [
            'command' => '/clear',
            'enable' => true,
            'description' => 'Команда удаляет последние 1-100 сообщений из чата' . PHP_EOL . 'Пример /clear 10',
        ],
        4 => [
            'command' => '/biba',
            'enable' => false,
        ],
        5 => [
            'command' => '/duel ',
            'enable' => false,
            'subCommand' => [
                0 => 'mute',
                1 => 'kick',
                2 => 'points',
            ],
        ],
        6 => [
            'command' => '/pk ',
            'enable' => false,
        ],
        7 => [
            'command' => '/radio',
            'enable' => true,
            'description' => 'Команда запускает музыку',
        ],
        8 => [
            'command' => '/add ',
            'enable' => false,
        ],
        9 => [
            'command' => '/top ',
            'enable' => false,
        ],
        10 => [
            'command' => '/ban ',
            'enable' => false,
        ],
        11 => [
            'command' => '/unban ',
            'enable' => false,
        ],
        12 => [
            'command' => '/move ',
            'enable' => false,
        ],
        13 => [
            'command' => '/switch ',
            'enable' => false,
        ],
        14 => [
            'command' => '/online',
            'enable' => false,
        ],
        15 => [
            'command' => '/test',
            'enable' => true,
            'description' => 'Команда для тестирования'
        ],
        16 => [
            'command' => '/help',
            'enable' => true,
            'description' => 'Команда вывода описаний команд'
        ]
    ];

}