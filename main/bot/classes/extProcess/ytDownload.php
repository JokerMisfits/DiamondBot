<?php

include_once __DIR__ . '/../../../../vendor/autoload.php';
include_once __DIR__ . '/../../../autoLoads/autoload_classes.php';

use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

if(isset($argv[1]) && isset($argv[2])){
    $name = $argv[1];
    $link = $argv[2];
    $yt = new YoutubeDl();
    if(file_exists('ffmpeg/yt-dlp.exe')){
        $yt->setBinPath('ffmpeg/yt-dlp.exe');
        $audioFormat = 'mp3';

        if (file_exists('music/' . $name . '.mp3')) {
            try {
                $name = Command::generateRandomString(null);
            }
            catch (Throwable $e) {
                throw $e;
            }
        }

        $collection = $yt->download(
            Options::create()
                ->downloadPath('/music')
                ->extractAudio(true)
                ->audioFormat($audioFormat)
                ->cleanupMetadata(false)
                ->audioQuality('0') // 0 is the best
                ->output($name.'.%(ext)s')
                ->url($link)
        );
        foreach ($collection->getVideos() as $audio) {
            if ($audio->getError() !== null) {
                exit('[' . date('Y-m-d\TH:i:s.u') . '] YTDownload.php: ошибка загрузки ' . $audio->getError() . PHP_EOL);
            }
            else{
                exit('[' . date('Y-m-d\TH:i:s.u') . '] YTDownload.php: music/'.$name.'.'.$audioFormat . ' загружен' . PHP_EOL);
            }
        }
    }
    else{
        exit('[' . date('Y-m-d\TH:i:s.u') . '] YTDownload.php: ffmpeg/yt-dlp.exe не найден' . PHP_EOL);
    }
}
else{
    exit('[' . date('Y-m-d\TH:i:s.u') . '] YTDownload.php: нет аргументов' . PHP_EOL);
}