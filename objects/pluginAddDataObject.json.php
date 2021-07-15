<?php

header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
if(empty($_POST['id'])){
    die('{"error":"'.__("ID can't be blank").'"}');
}
$obj = new Plugin($_POST['id']);

// verify if some field needs to be encrypted
$_POST['object_data'] = Plugin::encryptIfNeed($_POST['object_data']);

$obj->setObject_data(addcslashes($_POST['object_data'],'\\'));

echo '{"status":"'.$obj->save().'"}';
