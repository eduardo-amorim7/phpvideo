<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';

class Menu extends ObjectYPT {
    
    static $typeLeftMenu = 1;
    static $typeTopMenu = 2;
    static $typeActionMenu = 3;
    static $typeActionMenuCustomURL = 4;
    static $typeActionMenuCustomURLForLoggedUsers = 5;
    static $typeActionMenuCustomURLForUsersThatCanWatchVideo = 6;
    static $typeActionMenuCustomURLForUsersThatCanNotWatchVideo = 7;
    
    static $typeName = array(
        1=>'Left', 
        2=>'Top', 
        3=>'Action', 
        4=>'Action Per Video', 
        5=>'Action Per Video For Logged Users Only', 
        6=>'Action Per Video For Users That can Watch Video', 
        7=>'Action Per Video For Users That can NOT Watch Video');

    protected $id, $menuName, $categories_id, $users_groups_id, $menu_order, $status, $position, $type, $icon, $menuSeoUrl;

    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'topMenu';
    }
    
    function setMenuName($menuName) {
        $this->menuName = $menuName;
    }

    function setCategories_id($categories_id) {
        $this->categories_id = $categories_id;
    }

    function setUsers_groups_id($users_groups_id) {
        $this->users_groups_id = $users_groups_id;
    }

    function setMenu_order($menu_order) {
        $this->menu_order = $menu_order;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setPosition($position) {
        $this->position = $position;
    }

    function setType($type) {
        $this->type = $type;
    }
    
    function setIcon($icon) {
        $this->icon = $icon;
    }
    
    function setmenuSeoUrl($menuSeoUrl){
        $this->menuSeoUrl=$menuSeoUrl;
    }    
    
    static function getAllActive($type=false) {
        global $global;
        $sql = "SELECT * FROM  ".static::getTableName()." WHERE status = 'active' ";
        if(!empty($type)){
            $sql .= " AND type = $type ";
        }
        $sql .= " ORDER BY menu_order ";
        _mysql_connect();
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        } 
        return $rows;
    }
    
    function save() {
        global $global;
        if(empty($this->menuName)){
            $this->menuName = 'Unknow Menu Name';
        }
        if(empty($this->categories_id)){
            $this->categories_id = 'null';
        }
        if(empty($this->users_groups_id)){
            $this->users_groups_id = 'null';
        }
        
        if(empty($this->menu_order)){
            $this->menu_order = 0;
        }
        if(empty($this->status)){
            $this->status = "active";
        }
        
        if(empty($this->position)){
            $this->position = "right";
        }
        if(empty($this->type)){
            $this->type = 1;
        }
        if(empty($this->menuSeoUrl)){
            $this->menuSeoUrl=$this->menuName;
        }
        
        $this->menuSeoUrl=$global['mysqli']->real_escape_string(preg_replace('/[^a-z0-9]+/', '_', strtolower($this->menuSeoUrl)));
        
        return parent::save();
    }

    
}
