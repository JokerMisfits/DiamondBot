<?php

$GLOBALS['commands'] = [
    0 => [
        'command' => '/roll',
        'enable' => true,
    ],
    1 => [
        'command' => '/maps ',
        'enable' => true,
    ],
    2 => [
        'command' => '/pool',
        'enable' => true,
    ],
    3 => [
        'command' => '/clear ',
        'enable' => true,
    ],
    4 => [
        'command' => '/biba',
        'enable' => true,
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
    ],
    8 => [
        'command' => '/add ',
        'enable' => true,
    ],
    9 => [
        'command' => '/top ',
        'enable' => true,
    ],
    10 => [
        'command' => '/ban ',
        'enable' => true,
    ],
    11 => [
        'command' => '/unban ',
        'enable' => true,
    ],
    12 => [
        'command' => '/move ',
        'enable' => true,
    ],
    13 => [
        'command' => '/switch ',
        'enable' => true,
    ],
    14 => [
        'command' => '/online',
        'enable' => true,
    ],
    15 => [
        'command' => '/test',
        'enable' => true,
    ],
];