<?php
$lifetime = 300;

if (empty($_REQUEST['format'])) {
    $_REQUEST['format'] = "png";
    header('Content-Type: image/x-png');
} else if ($_REQUEST['format'] === 'jpg') {
    header('Content-Type: image/jpg');
} else if ($_REQUEST['format'] === 'gif') {
    header('Content-Type: image/gif');
    $lifetime *= 3;
} else if ($_REQUEST['format'] === 'webp') {
    header('Content-Type: image/webp');
    $lifetime *= 3;
} else {
    $_REQUEST['format'] = "png";
    header('Content-Type: image/x-png');
}


$f = md5(@$_REQUEST['u'] . @$_REQUEST['live_servers_id'] . @$_REQUEST['live_index']);

$cacheFileImageName = dirname(__FILE__) . "/../../videos/cache/liveImage_{$f}.{$_REQUEST['format']}";
if (file_exists($cacheFileImageName) && (time() - $lifetime <= filemtime($cacheFileImageName))) {
    $content = file_get_contents($cacheFileImageName);
    if(!empty($content)){
        echo $content;
        exit;
    } 
}

require_once dirname(__FILE__) . '/../../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$livet = LiveTransmition::getFromDbByUserName($_GET['u']);
//_error_log('getImage: start');
if (empty($livet)) {
    $uploadedPoster = $global['systemRootPath'] . Live::getOfflineImage(false);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster, empty($livet), Live::isLive($livet['users_id']) );exit;
    if (file_exists($uploadedPoster)) {
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        _error_log('getImage: showing offline poster');
        exit;
    } else {
        _error_log('getImage: File NOT exists 1 ' . $uploadedPoster);
    }
} else if (!Live::isLive($livet['users_id'])) {
    $uploadedPoster = $global['systemRootPath'] . Live::getPoster($livet['users_id'], $_REQUEST['live_servers_id']);
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster, empty($livet), Live::isLive($livet['users_id']) );exit;
    if (file_exists($uploadedPoster)) {
        _error_log('getImage: File NOT exists 2 ' . $uploadedPoster);
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    } else {
        _error_log('getImage: File NOT exists 3 ' . $uploadedPoster);
    }
}
//_error_log('getImage: continue '. getSelfURI());
$filename = $global['systemRootPath'] . Live::getPosterThumbsImage($livet['users_id'], $_REQUEST['live_servers_id'], $_REQUEST['live_index']);

if (Live::isLiveThumbsDisabled()) {
    $uploadedPoster = $filename;
    //var_dump($livet['users_id'], $_REQUEST['live_servers_id'],$uploadedPoster );exit;
    if (file_exists($uploadedPoster)) {
        header('Content-Type: image/jpg');
        echo file_get_contents($uploadedPoster);
        exit;
    }
}

$uuid = $livet['key'];

if (!empty($_REQUEST['live_index']) && $_REQUEST['live_index'] !== 'false') {
    $uuid = "{$uuid}-{$_REQUEST['live_index']}";
}

$name = "getLiveImage_{$uuid}_{$_REQUEST['format']}";
$result = ObjectYPT::getCache($name, $lifetime, true);

$socketMessage = array();
$socketMessage['cacheName1'] = $name;
$socketMessage['iscache'] = !empty($result);
$socketMessage['src'] = getSelfURI();
//$socketMessage['src'] = addQueryStringParameter(getSelfURI(), 'cache', time());
$socketMessage['live'] = $livet;
$socketMessage['live_servers_id'] = $_REQUEST['live_servers_id'];

if (!empty($result) && !Live::isDefaultImage($result)) {
    file_put_contents($cacheFileImageName, $result);
    echo $result;
} else {
    $socketMessage['key'] = $uuid;
    $socketMessage['autoEvalCodeOnHTML'] = "if(typeof refreshGetLiveImage == 'function'){refreshGetLiveImage('.live_{$socketMessage['live_servers_id']}_{$socketMessage['key']}');}";

    //$uuid = LiveTransmition::keyNameFix($livet['key']);
    $p = AVideoPlugin::loadPlugin("Live");
    $video = Live::getM3U8File($uuid);

    $encoderURL = $config->_getEncoderURL();
    //$encoderURL = $config->getEncoderURL();

    //$url = "{$encoderURL}getImage/" . base64_encode($video) . "/{$_REQUEST['format']}";
    $url = "{$encoderURL}objects/getImage.php";
    $url = addQueryStringParameter($url, 'base64Url', base64_encode($video));
    $url = addQueryStringParameter($url, 'format', $_REQUEST['format']);
    
    //_error_log("Live:getImage $url");
    //header('Content-Type: text/plain');var_dump($url);exit;
    session_write_close();
    _mysql_close();
    $content = url_get_contents($url, '', 2);

    if (empty($content)) {
        echo file_get_contents($filename);
    } else {
        
    }

    ob_end_clean();

    if (!empty($content)) {
        if (Live::isDefaultImage($content)) {
            //header('Content-Type: text/plain');var_dump(__LINE__, $url);exit;
            //_error_log("Live:getImage  It is the default image, try to show the poster ");
            echo $content;
        } else {
            //header('Content-Type: text/plain');var_dump(__LINE__, $url);exit;
            $socketMessage['cacheName2'] = $name;
            $socketMessage['cacheName3'] = ObjectYPT::setCache($name, $content);
            $socketMessage['cacheName4'] = strlen($content);
            echo $content;
            $socketObj = sendSocketMessageToAll($socketMessage, 'socketLiveImageUpdateCallback');
        }
    } else {
        
        $result = file_get_contents($filename);
        if(!Live::isDefaultImage($result)){
            copy($filename, $cacheFileImageName);
        }
        echo $result;
        //_error_log("Live:getImage  Get default image ");
    }
}