<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/bootGrid.php';
require_once $global['systemRootPath'] . 'objects/user.php';

class Subscribe {

    private $id;
    private $email;
    private $status;
    private $ip;
    private $users_id;
    private $notify;
    private $subscriber_users_id;

    function __construct($id, $email = "", $user_id = "", $subscriber_users_id = "") {
        if (!empty($id)) {
            $this->load($id);
        }
        if (!empty($subscriber_users_id)) {
            $this->email = $email;
            $this->users_id = $user_id;
            $this->subscriber_users_id = $subscriber_users_id;
            if (empty($this->id)) {
                $this->loadFromId($this->subscriber_users_id, $user_id, "");
            }
        }
    }

    private function load($id) {
        $obj = self::getSubscribe($id);
        if (empty($obj))
            return false;
        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    private function loadFromEmail($email, $user_id, $status = "a") {
        $obj = self::getSubscribeFromEmail($email, $user_id, $status);
        if (empty($obj))
            return false;
        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    private function loadFromId($subscriber_users_id, $user_id, $status = "a") {
        $obj = self::getSubscribeFromID($subscriber_users_id, $user_id, $status);
        if (empty($obj))
            return false;
        foreach ($obj as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }

    function save() {
        global $global;
        if (!empty($this->id)) {
            $sql = "UPDATE subscribes SET status = '{$this->status}',  notify = '{$this->notify}',ip = '" . getRealIpAddr() . "', modified = now() WHERE id = {$this->id}";
        } else {
            $sql = "INSERT INTO subscribes ( users_id, email,status,ip, created, modified, subscriber_users_id) VALUES ('{$this->users_id}','{$this->email}', 'a', '" . getRealIpAddr() . "',now(), now(), '$this->subscriber_users_id')";
        }
        return sqlDAL::writeSql($sql);
    }

    static function getSubscribe($id) {
        global $global;
        $id = intval($id);
        $sql = "SELECT * FROM subscribes WHERE  id = ? LIMIT 1";
        $res = sqlDAL::readSql($sql, "i", array($id));
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    static function getSubscribeFromEmail($email, $user_id, $status = "a") {
        global $global;
        $status = str_replace("'", "", $status);
        $sql = "SELECT * FROM subscribes WHERE  email = '$email' AND users_id = {$user_id} ";
        if (!empty($status)) {
            $sql .= " AND status = '{$status}' ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    static function getSubscribeFromID($subscriber_users_id, $user_id, $status = "a") {
        global $global;
        $status = str_replace("'", "", $status);
        $sql = "SELECT * FROM subscribes WHERE  subscriber_users_id = '$subscriber_users_id' AND users_id = {$user_id} ";
        if (!empty($status)) {
            $sql .= " AND status = '{$status}' ";
        }
        $sql .= " LIMIT 1";
        $res = sqlDAL::readSql($sql, "", array(), true);
        $data = sqlDAL::fetchAssoc($res);
        sqlDAL::close($res);
        if ($res != false) {
            $subscribe = $data;
        } else {
            $subscribe = false;
        }
        return $subscribe;
    }

    static function isSubscribed($subscribed_to_user_id, $user_id = 0) {
        if (empty($user_id)) {
            if (User::isLogged()) {
                $user_id = User::getId();
            } else {
                return false;
            }
        }
        $s = self::getSubscribeFromID($subscribed_to_user_id, $user_id);
        return !empty($s['users_id']);
    }

    /**
     * return all subscribers that has subscribe to an user channel
     * @global type $global
     * @param type $user_id
     * @return boolean
     */
    static function getAllSubscribes($user_id = "", $status = "a") {
        global $global;
        $cacheName = "getAllSubscribes_{$user_id}_{$status}_" . getCurrentPage() . "_" . getRowCount();
        $subscribe = ObjectYPT::getCache($cacheName, 300); // 5 minutes
        if (empty($subscribe)) {
            $status = str_replace("'", "", $status);
            $sql = "SELECT subscriber_users_id as subscriber_id, s.id, s.status, s.ip, s.users_id, s.notify, "
                    . " s.subscriber_users_id , s.created , s.modified, suId.email as email, suId.emailVerified as emailVerified FROM subscribes as s "
                    //. " LEFT JOIN users as su ON s.email = su.email   "
                    . " LEFT JOIN users as suId ON suId.id = s.subscriber_users_id   "
                    . " LEFT JOIN users as u ON users_id = u.id  WHERE 1=1 AND subscriber_users_id > 0 ";
            if (!empty($user_id)) {
                $sql .= " AND users_id = {$user_id} ";
            }
            if (!empty($status)) {
                $sql .= " AND u.status = '{$status}' ";
                $sql .= " AND suId.status = '{$status}' ";
                //$sql .= " AND su.status = '{$status}' ";
            }

            //$sql .= " GROUP BY subscriber_id ";

            $sql .= BootGrid::getSqlFromPost(array('email'));


            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            $subscribe = array();
            if ($res != false) {
                $emails = array();
                foreach ($fullData as $row) {
                    $row = cleanUpRowFromDatabase($row);
                    if (in_array($row['email'], $emails)) {
                        //continue;
                    }
                    $emails[] = $row['email'];
                    $row['identification'] = User::getNameIdentificationById($row['subscriber_id']);
                    if ($row['identification'] === __("Unknown User")) {
                        $row['identification'] = $row['email'];
                    }
                    $row['backgroundURL'] = User::getBackground($row['subscriber_id']);
                    $row['photoURL'] = User::getPhoto($row['subscriber_id']);

                    $subscribe[] = $row;
                }
                //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
            } else {
                $subscribe = false;
                die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
            }
            ObjectYPT::setCache($cacheName, $subscribe);
        } else {
            $subscribe = object_to_array($subscribe);
        }
        return $subscribe;
    }

    /**
     * return all channels that a user has subscribed
     * @global type $global
     * @param type $user_id
     * @return boolean
     */
    static function getSubscribedChannels($user_id, $limit=0, $page=0) {
        global $global;
        $limit = intval($limit);
        $page = intval($page)-1;
        if($page<0){
            $page=0;
        }
        $offset = $limit*$page;
        $sql = "SELECT s.*, (SELECT MAX(v.created) FROM videos v WHERE v.users_id = s.users_id) as newestvideo "
                . " FROM subscribes as s WHERE status = 'a' AND subscriber_users_id = ? "
                . " ORDER BY newestvideo DESC ";

        if(!empty($limit)){
            $sql .= " LIMIT {$offset},{$limit} ";
        }
        //var_dump($sql, $user_id);exit;
        $res = sqlDAL::readSql($sql, "i", array($user_id));
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $subscribe = array();
        if ($res != false) {
            foreach ($fullData as $row) {
                $row['identification'] = User::getNameIdentificationById($row['users_id']);
                if ($row['identification'] === __("Unknown User")) {
                    $row['identification'] = $row['email'];
                }
                $row['channelName'] = User::_getChannelName($row['users_id']);
                $row['backgroundURL'] = User::getBackground($row['users_id']);
                $row['photoURL'] = User::getPhoto($row['users_id']);

                $current = getCurrentPage();
                $rowCount = getRowCount();
                $sort = @$_POST['sort'];

                $_POST['current'] = 1;
                $_REQUEST['rowCount'] = 6;
                $_POST['sort']['created'] = "DESC";
                $row['latestVideos'] = Video::getAllVideos("viewable", $row['users_id']);
                foreach ($row['latestVideos'] as $key => $video) {
                    $images = Video::getImageFromFilename($video['filename'], $video['type']);
                    $row['latestVideos'][$key]['Thumbnail'] = $images->thumbsJpg;
                    $row['latestVideos'][$key]['createdHumanTiming'] = humanTiming(strtotime($video['created']));
                    $row['latestVideos'][$key]['pageUrl'] = Video::getLink($video['id'], $video['clean_title'], false);
                    $row['latestVideos'][$key]['embedUrl'] = Video::getLink($video['id'], $video['clean_title'], true);
                    $row['latestVideos'][$key]['UserPhoto'] = User::getPhoto($video['users_id']);
                }
                $_POST['current'] = $current;
                $_REQUEST['rowCount'] = $rowCount;
                $_POST['sort'] = $sort;

                $row['totalViewsIn30Days'] = VideoStatistic::getChannelsTotalViews($row['users_id']);

                $subscribe[] = $row;
            }
            //$subscribe = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $subscribe = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $subscribe;
    }

    static function getTotalSubscribes($user_id = "") {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE status = 'a' AND subscriber_users_id > 0 ";
        if (!empty($user_id)) {
            $sql .= " AND users_id = '{$user_id}' ";
        }

        $sql .= BootGrid::getSqlSearchFromPost(array('email'));
        $res = sqlDAL::readSql($sql);
        $numRows = sqlDAL::num_rows($res);
        sqlDAL::close($res);


        return $numRows;
    }

    static function getTotalSubscribedChannels($user_id = "") {
        global $global;
        $sql = "SELECT id FROM subscribes WHERE status = 'a' AND subscriber_users_id = ? ";

        //$sql .= BootGrid::getSqlSearchFromPost(array('email'));
        $res = sqlDAL::readSql($sql, "i", array($user_id));
        $numRows = sqlDAL::num_rows($res);
        sqlDAL::close($res);


        return $numRows;
    }

    function toggle() {
        if (empty($this->status) || $this->status == "i") {
            $this->status = 'a';
        } else {
            $this->status = 'i';
        }
        $this->save();
    }

    function notifyToggle() {
        if (empty($this->notify)) {
            $this->notify = 1;
        } else {
            $this->notify = 0;
        }
        $this->save();
    }

    function getStatus() {
        return $this->status;
    }

    function getNotify() {
        return $this->notify;
    }

    function setNotify($notify) {
        $this->notify = $notify;
    }

    static function getButton($user_id) {
        global $global, $advancedCustom;

        if (!empty($advancedCustom->removeSubscribeButton)) {
            return "";
        }

        $total = static::getTotalSubscribes($user_id);

        $subscribe = "<div class=\"btn-group\" >"
                . "<button class='btn btn-xs subsB subs{$user_id} subscribeButton{$user_id}' "
                . "title=\"" . __("Want to subscribe to this channel?") . "\" "
                . "data-content=\"" . __("Sign in to subscribe to this channel") . "<hr><center><a class='btn btn-success btn-sm' href='{$global['webSiteRootURL']}user'>" . __("Sign in") . "</a></center>\"  "
                . "tabindex=\"0\" role=\"button\" data-html=\"true\"  data-toggle=\"popover\" data-placement=\"bottom\" ><i class='fas fa-play-circle'></i> <b class='text'>" . __("Subscribe") . "</b></button>"
                . "<button class='btn btn-xs subsB subs{$user_id}'><b class='textTotal{$user_id}'>{$total}</b></button>"
                . "</div>";

        //show subscribe button with mail field
        $popover = "";
        $script = "";
        if (User::isLogged()) {
            //check if the email is logged
            $email = User::getMail();
            $subs = Subscribe::getSubscribeFromID(User::getId(), $user_id);
            $popover = "<input type=\"hidden\" placeholder=\"E-mail\" class=\"form-control\"  id=\"subscribeEmail{$user_id}\" value=\"{$email}\">";
            // show unsubscribe Button
            $subscribe = "<div class=\"btn-group\">";
            if (!empty($subs) && $subs['status'] === 'a') {
                $subscribe .= "<button class='btn btn-xs subsB subscribeButton{$user_id} subscribed subs{$user_id}'><i class='fas fa-play-circle'></i> <b class='text'>" . __("Subscribed") . "</b></button>";
                $subscribe .= "<button class='btn btn-xs subsB subscribed subs{$user_id}'><b class='textTotal{$user_id}'>$total</b></button>";
            } else {
                $subscribe .= "<button class='btn btn-xs subsB subscribeButton{$user_id} subs{$user_id}'><i class='fas fa-play-circle'></i> <b class='text'>" . __("Subscribe") . "</b></button>";
                $subscribe .= "<button class='btn btn-xs subsB subs{$user_id}'><b class='textTotal{$user_id}'>$total</b></button>";
            }
            $subscribe .= "</div>";

            if (!empty($subs['notify'])) {
                $notify = '';
                $notNotify = 'hidden';
            } else {
                $notify = 'hidden';
                $notNotify = '';
            }
            $subscribe .= '<span class=" notify' . $user_id . ' ' . $notify . '"><button onclick="toogleNotify' . $user_id . '();" class="btn btn-default btn-xs " data-toggle="tooltip"
                                   title="' . __("Stop getting notified for every new video") . '">
                                <i class="fa fa-bell" ></i>
                            </button></span><span class=" notNotify' . $user_id . ' ' . $notNotify . '"><button onclick="toogleNotify' . $user_id . '();" class="btn btn-default btn-xs "  data-toggle="tooltip"
                                   title="' . __("Click to get notified for every new video") . '">
                                <i class="fa fa-bell-slash"></i>
                            </button></span>';
            $script = "<script>
                    function toogleNotify{$user_id}(){
                        email = $('#subscribeEmail{$user_id}').val();
                        subscribeNotify(email, '{$user_id}');
                    }
                    $(document).ready(function () {
                        $(\".subscribeButton{$user_id}\").off(\"click\");
                        $(\".subscribeButton{$user_id}\").click(function () {
                            email = $('#subscribeEmail{$user_id}').val();
                            subscribe(email, '{$user_id}');
                        });
                    });
                </script>";
        }

        return $subscribe . $popover . $script;
    }

    function getSubscriber_users_id() {
        return $this->subscriber_users_id;
    }

    function setSubscriber_users_id($subscriber_users_id) {
        $this->subscriber_users_id = $subscriber_users_id;
    }
    
    function getUsers_id() {
        return $this->users_id;
    }

    function setUsers_id($users_id) {
        $this->users_id = $users_id;
    }



}
