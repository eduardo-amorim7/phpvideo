<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class BulkEmbed extends PluginAbstract {
    
    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        global $global;
        //$str = 'Set DEVELOPER_KEY to the "API key" value from the "Access" tab of the<br>Google Developers Console https://console.developers.google.com<br>Please ensure that you have enabled the YouTube Data API for your project.';
        //$str.= '<br>Add the Redirect URI '.$global['webSiteRootURL'].'plugin/BulkEmbed/youtubeSearch.json.php';
        $str = 'Create your API Key here https://console.developers.google.com/apis/credentials/key';
        $str .= "<br> Also make sure you enable the API YouTube Data API v3";
        return $str;
    }

    public function getName() {
        return "BulkEmbed";
    }

    public function getUUID() {
        return "bulkembed-8c31-4f15-a355-48715fac13f3";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();

        $obj->API_KEY = "AIzaSyCIqxE86BawU33Um2HEGtX4PcrUWeCh_6o";
        $obj->onlyAdminCanBulkEmbed = true;
        return $obj;
    }    
    
    public function getPluginMenu() {
        global $global;
        $menu = '<button onclick="avideoModalIframe(webSiteRootURL +\'plugin/BulkEmbed/search.php\');" class="btn btn-primary btn-xs btn-block" target="_blank">Search</button>';
        return $menu;
    }
    
    public function getUploadMenuButton(){
        global $global;
        $obj = $this->getDataObject();
        if(($obj->onlyAdminCanBulkEmbed && !User::isAdmin()) || !User::canUpload()){
            return '';
        }
        
        return '<li><a  href="'.$global['webSiteRootURL'].'plugin/BulkEmbed/search.php" ><span class="fa fa-link"></span> '.__("Bulk Embed").'</a></li>';
    }


}
