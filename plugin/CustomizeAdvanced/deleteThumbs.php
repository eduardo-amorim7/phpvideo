<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";


if (!User::isAdmin()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
if(empty($_POST['videos_id'])){    
    $obj->msg = __("Video Not found");
    die(json_encode($obj));
}

$videos_id = intval($_POST['videos_id']);
$video = new Video("","", $videos_id);
$obj->error = empty(Video::deleteThumbs($video->getFilename()));

die(json_encode($obj));

