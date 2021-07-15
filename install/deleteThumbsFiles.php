<?php

//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$users_ids = array();
$sql = "SELECT * FROM  videos ";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
$total = count($fullData);
sqlDAL::close($res);
$rows = array();
if ($res != false) {
    $count = 0;
    foreach ($fullData as $key => $row) {
        $count++;
        $filename = $row['filename'];
        Video::deleteThumbs($filename, true);
        echo "{$total}/{$count} Thumbs deleted from {$row['title']}".PHP_EOL;
        ob_flush();
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}