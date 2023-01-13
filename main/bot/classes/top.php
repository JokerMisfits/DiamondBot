<?php
//
//class top extends DiamondBot
//{
//
//    public function __construct($name = '', $mysqli = '', $channel = '', $author = ''){
//        if($name == 'points'){
//            $this->topPoints($mysqli,$channel,$author);
//        }
//        elseif($name = 'messages'){
//            $this->topMessages($mysqli,$channel,$author);
//        }
//}
//
//    private function topPoints($mysqli,$channel,$author){
//        $query = "SELECT * FROM users ORDER BY points DESC limit 12";
//        $res = $mysqli->query($query);
//        $res->data_seek(0);
//        $i = 0;
//        while ($row = $res->fetch_assoc()) {
//            if($row['discord_id'] == $GLOBALS['bot_id'] || $row['discord_id'] == $GLOBALS['groovy_id']){}
//            else{
//                $resultName[$i] = $row['discord_name'];
//                $resultPoints[$i] = $row['points'];
//                $i++;
//            }
//        }
//        $embeds = new embeds($author, 'top','points', null, $resultName, $resultPoints);
//        $channel->sendMessage('', false, $embeds->getEmbed());
//    }//Топ 10 поинтов
//
//    private function topMessages($mysqli,$channel,$author){
//        $query = "SELECT * FROM users ORDER BY messages_count DESC limit 12";
//        $res = $mysqli->query($query);
//        $res->data_seek(0);
//        $i = 0;
//        while ($row = $res->fetch_assoc()) {
//            if($row['discord_id'] == $GLOBALS['bot_id'] || $row['discord_id'] == $GLOBALS['groovy_id']){}
//            else{
//                $resultName[$i] = $row['discord_name'];
//                $resultMessages[$i] = $row['points'];
//                $i++;
//            }
//        }
//        $embeds = new embeds($author, 'top','messages', null, $resultName, $resultMessages);
//        $channel->sendMessage('', false, $embeds->getEmbed());
//    }//Топ 10 сообщений
//}