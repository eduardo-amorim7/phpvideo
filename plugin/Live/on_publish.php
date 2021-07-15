<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once './Objects/LiveTransmitionHistory.php';
$obj = new stdClass();
$obj->error = true;
$obj->liveTransmitionHistory_id = 0;

_error_log("NGINX ON Publish POST: " . json_encode($_POST));
_error_log("NGINX ON Publish GET: " . json_encode($_GET));
_error_log("NGINX ON Publish php://input" . file_get_contents("php://input"));

// get GET parameters
$url = $_POST['tcurl'];
if (empty($url)) {
    $url = $_POST['swfurl'];
}
$parts = parse_url($url);
if(!empty($parts["query"])){
    parse_str($parts["query"], $_GET);
}

if(!empty($_GET['e']) && empty($_GET['p'])){
    $objE = json_decode(decryptString($_GET['e']));
    if(!empty($objE->users_id)){
        $user = new User($objE->users_id);
        $_GET['p'] = $user->getPassword();
    }
}

if(empty($_GET['p']) && !empty($_POST['p'])){
    $_GET['p'] = $_POST['p'];
}

_error_log("NGINX ON Publish parse_url: " . json_encode($parts));
_error_log("NGINX ON Publish parse_str: " . json_encode($_GET));

$_GET = object_to_array($_GET);

if ($_POST['name'] == 'live') {
    _error_log("NGINX ON Publish wrong name {$_POST['p']}");
    // fix name for streamlab
    $pParts = explode("/", $_POST['p']);
    if (!empty($pParts[1])) {
        _error_log("NGINX ON Publish like key fixed");
        $_POST['name'] = $pParts[1];
    }
}

if (empty($_POST['name']) && !empty($_GET['name'])) {
    $_POST['name'] = $_GET['name'];
}
if (empty($_POST['name']) && !empty($_GET['key'])) {
    $_POST['name'] = $_GET['key'];
}
if (strpos($_GET['p'], '/') !== false) {
    $parts = explode("/", $_GET['p']);
    if (!empty($parts[1])) {
        $_GET['p'] = $parts[0];
        if(empty($_POST['name'])){
            $_POST['name'] = $parts[1];
        }
    }
}

if (!empty($_GET['p'])) {
    $_GET['p'] = str_replace("/", "", $_GET['p']);
    _error_log("NGINX ON Publish check if key exists ({$_POST['name']})");
    $obj->row = LiveTransmition::keyExists($_POST['name']);
    //_error_log("NGINX ON Publish key exists return " . json_encode($obj->row));
    if (!empty($obj->row)) {
        _error_log("NGINX ON Publish new User({$obj->row['users_id']})");
        $user = new User($obj->row['users_id']);
        if (!$user->thisUserCanStream()) {
            _error_log("NGINX ON Publish User [{$obj->row['users_id']}] can not stream");
        } else if (!empty($_GET['p']) && $_GET['p'] === $user->getPassword()) {
            _error_log("NGINX ON Publish get LiveTransmitionHistory");
            $lth = new LiveTransmitionHistory();
            $lth->setTitle($obj->row['title']);
            $lth->setDescription($obj->row['description']);
            $lth->setKey($_POST['name']);
            $lth->setUsers_id($user->getBdId());
            $lth->setLive_servers_id(Live_servers::getServerIdFromRTMPHost($url));
            _error_log("NGINX ON Publish saving LiveTransmitionHistory");
            $obj->liveTransmitionHistory_id = $lth->save();
            _error_log("NGINX ON Publish saved LiveTransmitionHistory");
            $obj->error = false;
        } else if (empty($_GET['p'])) {
            _error_log("NGINX ON Publish error, Password is empty");
        } else {
            _error_log("NGINX ON Publish error, Password does not match ({$_GET['p']}) expect (" . $user->getPassword() . ")");
        }
    } else {
        _error_log("NGINX ON Publish error, Transmition name not found ({$_POST['name']}) ", AVideoLog::$SECURITY);
    }
} else {
    _error_log("NGINX ON Publish error, Password not found ", AVideoLog::$SECURITY);
}
_error_log("NGINX ON Publish deciding ...");
if (!empty($obj) && empty($obj->error)) {
    /*
    if(strpos($_POST['name'], '-')===false){
        _error_log("NGINX ON Publish redirect");
        http_response_code(302);
        header("HTTP/1.0 302 Publish Here");
        $newKey = $_POST['name'].'-'. uniqid();
        header("Location: rtmp://192.168.1.18/live/$newKey/?p={$_GET['p']}");
        exit;
    }
     * 
     */
    
    _error_log("NGINX ON Publish success");
    http_response_code(200);
    header("HTTP/1.1 200 OK");
    
    outputAndContinueInBackground();
    Live::deleteStatsCache(true);
    _error_log("NGINX Live::on_publish start");
    Live::on_publish($obj->liveTransmitionHistory_id);
    _error_log("NGINX Live::on_publish end");
    if (AVideoPlugin::isEnabledByName('YPTSocket')) {
        $array = setLiveKey($lth->getKey(), $lth->getLive_servers_id());
        ob_end_flush();
        ob_start();
        $lth = new LiveTransmitionHistory($obj->liveTransmitionHistory_id);
        $m3u8 = Live::getM3U8File($lth->getKey());                
        $users_id = $obj->row['users_id'];
        $liveTransmitionHistory_id = $obj->liveTransmitionHistory_id;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            include "{$global['systemRootPath']}plugin/Live/on_publish_socket_notification.php";
        }else{
            $command = "php {$global['systemRootPath']}plugin/Live/on_publish_socket_notification.php '$users_id' '$m3u8' '{$obj->liveTransmitionHistory_id}'";

            _error_log("NGINX Live::on_publish YPTSocket start  ($command)");
            $pid = execAsync($command);        
            _error_log("NGINX Live::on_publish YPTSocket end {$pid}");
        }
    }
    //exit;
} else {
    _error_log("NGINX ON Publish denied ", AVideoLog::$SECURITY);
    http_response_code(401);
    header("HTTP/1.1 401 Unauthorized Error");
    exit;
}
//_error_log(print_r($_POST, true));
//_error_log(print_r($obj, true));
//echo json_encode($obj);