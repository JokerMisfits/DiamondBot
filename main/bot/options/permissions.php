<?php
//Permissions
$permissions = [
    695714569200337048 => [
        'name' => 'Hokaga',
        'Allow' => true,
        'Deny' => null,
    ], //Hokaga
    696890523226734634 => [
        'name' => 'VIP',
        'Allow' => false,
        'Deny' => [
            'commands' => [
                0 => '/clear',
                1 => '/ban '
            ]
        ],
    ]//VIP
];// Переписать