<?php

class InteractionList
{
    public static array $interactions = [
        0 => 'youtube',
        1 => 'vk',
        2 => 'spotify',
        3 => 'next',
        4 => 'stop',
        5 => 'past',
        6 => 'skip',
        7 => 'repeat',
        8 => 'prev',
        9 => 'queue',
        10 => 'diamond_bot',
        11 => 'now',
        12 => 'to',
        13 => 'cansel',
        14 => 'pause',
        15 => 'unpause',
        16 => 'help',
    ];
}

//$msg = MessageBuilder::new();
//$row = ActionRow::new()
//    ->addComponent(Button::new(Button::STYLE_DANGER,'youtube')->setLabel(' YouTube '))
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'vk')->setLabel(' 　VK　 ')->setDisabled(true))
//    ->addComponent(Button::new(Button::STYLE_SUCCESS,'spotify')->setLabel('　Spotify　')->setDisabled(true));
//$msg->addComponent($row);
//
//$row = ActionRow::new()
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'past')->setLabel('   Past    ⁣'))
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'stop')->setLabel('⁣   Stop   ⁣'))
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'next')->setLabel('   Next   '));
//$msg->addComponent($row);
//
//$row = ActionRow::new()
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'pause')->setLabel('　Pause　'))
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'cansel')->setLabel('　Cansel　'))
//    ->addComponent(Button::new(Button::STYLE_PRIMARY,'unpause')->setLabel('⁣ Unpause ⁣'));
//$msg->addComponent($row);
//
//$row = ActionRow::new()
//    ->addComponent(Button::new(Button::STYLE_DANGER,'skip')->setLabel('⁣    Skip    ⁣')->setDisabled(true))
//    ->addComponent(Button::new(Button::STYLE_DANGER,'repeat')->setLabel('⁣  Repeat  '))
//    ->addComponent(Button::new(Button::STYLE_DANGER,'prev')->setLabel('⁣    Prev    ⁣')->setDisabled(true));
//$msg->addComponent($row);
//
//$row = ActionRow::new()
//    ->addComponent(Button::new(Button::STYLE_SUCCESS,'queue')->setLabel('⁣   Queue   ⁣'))
//    ->addComponent(Button::new(Button::STYLE_SUCCESS,'to')->setLabel('⁣     To     ⁣'))
//    ->addComponent(Button::new(Button::STYLE_SUCCESS,'now')->setLabel('⁣    Now   '));
//$msg->addComponent($row);
//
//self::$channel->sendMessage($msg);