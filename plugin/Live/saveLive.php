<?php
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$obj = new stdClass();
$obj->error = true;
if(!User::canStream()){
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

$categories_id = intval(@$_POST['categories_id']);
if(empty($categories_id)){
    $categories_id = 1;
}

$l = new LiveTransmition(0);
$l->loadByUser(User::getId());
$l->setTitle($_POST['title']);
$l->setDescription($_POST['description']);
$l->setKey($_POST['key']);
$l->setCategories_id($categories_id);
$l->setPublic((empty($_POST['listed'])|| $_POST['listed']==='false')?0:1);
$l->setSaveTransmition((empty($_POST['saveTransmition'])|| $_POST['saveTransmition']==='false')?0:1);
$l->setUsers_id(User::getId());
$id = $l->save();
$l = new LiveTransmition($id);
$l->deleteGroupsTrasmition();
if(!empty($_POST['userGroups'])){
    foreach ($_POST['userGroups'] as $value) {
        $l->insertGroup($value);    
    }
}
echo '{"status":"'.$id.'"}';
