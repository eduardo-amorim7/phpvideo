<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';

if(empty($_GET['video_id']) && !empty($_POST['videos_id'])){
    $_GET['video_id'] = $_POST['videos_id'];
}

$obj = new stdClass();
$obj->error = true;
if (!Video::canEdit($_GET['video_id'])) {
    $obj->msg = 'You cant edit this file';
    die(json_encode($obj));
}
$obj->videos_id = intval($_GET['video_id']);

header('Content-Type: application/json');
// A list of permitted file extensions
$allowed = array('jpg', 'jpeg', 'gif', 'pjpg', 'pgif', 'webp', 'png', 'bmp');
if (!in_array(strtolower($_GET['type']), $allowed)) {
    $obj->msg = "UploadPoster FIle extension not allowed";
    _error_log($obj->msg );
    die(json_encode($obj));
}
if (isset($_FILES['file_data']) && $_FILES['file_data']['error'] == 0) {
    $extension = pathinfo($_FILES['file_data']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['file_data']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    //var_dump($extension, $type);exit;
    $video = new Video("", "", $_GET['video_id']);
    if (!empty($video)) {
        $ext = ".jpg";
        switch ($_GET['type']) {
            case "jpg":
            case "jpeg":
                $ext = ".jpg";
                if($extension == 'png' || $extension == 'bmp'){
                    $ext = "_convertToJPG.{$extension}";
                }
                break;
            case "pjpg":
                $ext = "_portrait.jpg";
                if($extension == 'png' || $extension == 'bmp'){
                    $ext = "_portrait_convertToJPG.{$extension}";
                }
                break;
            case "gif":
                $ext = ".gif";
                break;
            case "webp":
                $ext = ".webp";
                break;
            case "pgif":
                $ext = "_portrait.gif";
                break;
        }
        /**
         * This is when is using in a non file_dataoaded movie
         */
        $paths = Video::getPaths($video->getFilename());
        $destination = $paths['path'] . $video->getFilename() . $ext;
        _error_log("Try to move " . $destination . " \n " . print_r($video, true));
        if (!move_uploaded_file($_FILES['file_data']['tmp_name'], $destination)) {
            $obj->msg = "Error on move_file_uploaded_file(" . $_FILES['file_data']['tmp_name'] . ", " . $destination;
            die(json_encode($obj));
        } else {
            if(preg_match('/_convertToJPG/', $ext)){
                $new_destination = str_replace('_convertToJPG.'.$extension, '.jpg', $destination);
                if(convertImage($destination, $new_destination, 100)){
                    unlink($destination);
                }
            }
            // delete thumbs from poster
            Video::deleteThumbs($video->getFilename());
        }
        $obj->error = false;
        echo "{}";
        exit;
    } else {
        $obj->msg = "Video Not found";
        die(json_encode($obj));
    }
}
$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
