<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
if (empty($_POST['current']) && !empty($_GET['current'])) {
    $_POST['current'] = $_GET['current'];
}
if (empty($_POST['rowCount']) && !empty($_GET['rowCount'])) {
    $_REQUEST['rowCount'] = $_GET['rowCount'];
}
if (empty($_POST['sort']) && !empty($_GET['sort'])) {
    $_POST['sort'] = $_GET['sort'];
}
if (empty($_POST['searchPhrase']) && !empty($_GET['searchPhrase'])) {
    $_POST['searchPhrase'] = $_GET['searchPhrase'];
}
if (!empty($_GET['user']) && !empty($_GET['pass'])) {
    $user = new User(0, $_GET['user'], $_GET['pass']);
    $user->login(false, true);
}

$objMob = AVideoPlugin::getObjectData("MobileManager");
if(!empty($random)){
    $video = Video::getVideo("", "viewableNotUnlisted", true, false, true);
    if (empty($video)) {
        $video = Video::getVideo("", "viewableNotUnlisted", true, true);
    }
    $videos = array($video);
    $total = 1;
}else if ($objMob->netflixStyle) {
    $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
    $total = Video::getTotalVideos("viewableNotUnlisted", false, true);
} else {
    $videos = Video::getAllVideos("viewable");
    $total = Video::getTotalVideos("viewable");
}

foreach ($videos as $key => $value) {
    unset($videos[$key]['password'], $videos[$key]['recoverPass']);
    $images = Video::getImageFromFilename($videos[$key]['filename'], $videos[$key]['type']);
    $videos[$key]['images'] = $images;
    $videos[$key]['Poster'] = !empty($objMob->portraitImage) ? $images->posterPortrait : $images->poster;
    $videos[$key]['Thumbnail'] = !empty($objMob->portraitImage) ? $images->posterPortraitThumbs : $images->thumbsJpg;
    $videos[$key]['imageClass'] = !empty($objMob->portraitImage) ? "portrait" : "landscape";
    $videos[$key]['VideoUrl'] = getVideosURL($videos[$key]['filename']);
    $videos[$key]['createdHumanTiming'] = humanTiming(strtotime($videos[$key]['created']));
    $videos[$key]['pageUrl'] = "{$global['webSiteRootURL']}video/" . $videos[$key]['clean_title'];
    $videos[$key]['embedUrl'] = "{$global['webSiteRootURL']}videoEmbeded/" . $videos[$key]['clean_title'];
    unset($_POST['sort'], $_POST['current'], $_POST['searchPhrase']);
    $_REQUEST['rowCount'] = 10;
    $_POST['sort']['created'] = "desc";
    $videos[$key]['comments'] = Comment::getAllComments($videos[$key]['id']);
    $videos[$key]['commentsTotal'] = Comment::getTotalComments($videos[$key]['id']);
    foreach ($videos[$key]['comments'] as $key2 => $value2) {
        $user = new User($value2['users_id']);
        $videos[$key]['comments'][$key2]['userPhotoURL'] = User::getPhoto($videos[$key]['comments'][$key2]['users_id']);
        $videos[$key]['comments'][$key2]['userName'] = $user->getNameIdentificationBd();
    }
    $videos[$key]['subscribers'] = Subscribe::getTotalSubscribes($videos[$key]['users_id']);

    $videos[$key]['firstVideo'] = "";
    foreach ($videos[$key]['VideoUrl'] as $value2) {
        if ($value2["type"] === 'video') {
            $videos[$key]['firstVideo'] = $value2["url"];
            break;
        }
    }
    $videos[$key]['UserPhoto'] = User::getPhoto($videos[$key]['users_id']);
}

$obj = new stdClass();
$obj->current = $_POST['current'];
$obj->rowCount = $_POST['rowCount'];
$obj->total = $total;
$obj->videos = $videos;
echo json_encode($obj);
//AVideoPlugin::getEnd();
