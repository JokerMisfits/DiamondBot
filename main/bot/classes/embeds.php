<?php


class embeds
{
    public static function createEmbed(string $title, string $description,int $color) : array {
        return [
            'title' => $title,
            'description' => $description,
            'color' => $color,
        ];
    }
    public static function createEmbedWithImage(string $title, string $description,string $image, int $color) : array {
        return [
            'title' => $title,
            'description' => $description,
            'color' => $color,
            'thumbnail' => ['url' => $image],
            'image' => ['url' => $image],
            'footer' => [
                'icon_url' => $image,
            ],
        ];
    }



//    private $embed;
//    public function __construct($author,$type,$users = null,$name = null,$nick = null,$value = null){
//        $this->embed = [
//            'description' => '<@'.$author['user']['id'].'> ',
//            "type" => "rich",
//            'fields' => [],
//            'color' => 14177041,
//        ];
//        if($type == 'top'){
//            $this->generateEmbedsTop($name,$nick,$value);
//        }
//        elseif($type == 'online'){
//            $this->generateEmbedsOnline($users,$author);
//        }
//    }
//    private function generateEmbedsTop($name,$nick,$value){
//        for($i = 0;$i < 10;$i++){
//            $this->embed['fields'][0]['name'] = 'Names';
//            $this->embed['fields'][0]['value'] .= $nick[$i]."\n";
//            $this->embed['fields'][0]['inline'] = true;
//            $this->embed['fields'][1]['name'] = 'Values';
//            $this->embed['fields'][1]['value'] .= $value[$i]."\n";
//            $this->embed['fields'][1]['inline'] = true;
//        }
//        $this->embed['author']['name'] = 'Top 10 '.$name;
//
//    }//Генератор top сообщений
//
//    private function generateEmbedsOnline($users,$author){
//        $size = sizeof($users);
//        for($i = 0;$i < $size;$i++){
//            $this->embed['fields'][0]['name'] = 'Names';
//            $this->embed['fields'][0]['value'] .= '<@!'.$users[$i].'> '."\n";
//            $this->embed['fields'][0]['inline'] = true;
//            $this->embed['fields'][1]['name'] = 'Id';
//            $this->embed['fields'][1]['value'] .= $users[$i]."\n";
//            $this->embed['fields'][1]['inline'] = true;
//        }
//        $this->embed['description'] = '<@!'.$author['author']['user']['id'].'> ';
//    }//Генератор онлайн пользователей
//
//    public function getEmbed(){
//        return $this->embed;
//    }
}