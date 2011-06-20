#!/usr/local/bin/php -q
<?php
/**
 * @copyright 2006-2011 LanMediaService, Ltd.
 * @license    http://www.lanmediaservice.com/license/1_0.txt
 * @author Ilya Spesivtsev <macondos@gmail.com>
 * @version $Id: trailers-download.php 700 2011-06-10 08:40:53Z macondos $
 */

require_once dirname(__FILE__) . '/include/init.php';

function downloadCurl($url, $destinationFilePath)
{
    Lms_FileSystem::createFolder(dirname($destinationFilePath), 0777, true); 
    $fileHandle = fopen($destinationFilePath, 'w');

    if (false === $fileHandle) {
        throw new Exception('Could not open filehandle');
    }

    $ch = curl_init();
    $headers = array(
        'Accept: */*',
        'Accept-Language: ru',
        'Accept-Encoding: gzip, deflate',
        'Referer: ' . dirname($url) 
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fileHandle);

    $result = curl_exec($ch);
    curl_close($ch);
    fclose($fileHandle);

    if (false === $result) {
        throw new Exception('Could not download file');
    }
}

$previewFolder = dirname(APP_ROOT) . '/public/media/trailers/preview';
$videoFolder = dirname(APP_ROOT) . '/public/media/trailers/video';

$movies = Lms_Item_Movie::selectNotLocalized();
foreach ($movies as $movie) {
    try {
        $moiveId = $movie->getId();
        echo "\n#{$moiveId}:";
        $trailerStruct = $movie->getTrailer();
        
        $ext = pathinfo($trailerStruct['preview'], PATHINFO_EXTENSION);
        $dstPreview = $previewFolder . "/{$moiveId}.{$ext}";
        echo "\n    {$trailerStruct['preview']}: ";
        downloadCurl($trailerStruct['preview'], $dstPreview);
        echo " OK";
        
        $ext = pathinfo($trailerStruct['video'], PATHINFO_EXTENSION);
        $dstVideo = $videoFolder . "/{$moiveId}.{$ext}";
        echo "\n    {$trailerStruct['video']}: ";
        downloadCurl($trailerStruct['video'], $dstVideo);
        echo " OK";
        
        $trailerStruct['preview'] = $dstPreview;
        $trailerStruct['video'] = $dstVideo;
        $movie->setTrailer($trailerStruct)
              ->setTrailerLocalized(1)
              ->save();
    } catch (Exception $e) {
        echo $e->getMessage();
        Lms_Debug::err($e->getMessage());
        $movie->setTrailerLocalized(0)
              ->save();
    }
}
require_once dirname(__FILE__) . '/include/end.php';