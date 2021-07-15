<?php
/**
 * https://support.google.com/adsense/answer/4455881
 * https://support.google.com/adsense/answer/1705822
 * AdSense for video: Publisher Approval Form
 * https://services.google.com/fb/forms/afvapproval/
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';

class AD_Server extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$ADS,
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }

    public function getDescription() {
        return "VAST Ad Server<br><small><a href='https://github.com/WWBN/AVideo/wiki/Ad-Server-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
    }

    public function getName() {
        return "AD_Server";
    }

    public function getUUID() {
        return "3f2a707f-3c06-4b78-90f9-a22f2fda92ef";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->start = true;
        $obj->mid25Percent = true;
        $obj->mid50Percent = true;
        $obj->mid75Percent = true;
        $obj->end = true;
        $obj->skipoffset = "10%";
        $obj->showMarkers = true;
        $obj->showAdsOnEachVideoView = 1;
        $obj->showAdsOnRandomPositions = 2;

        $obj->autoAddNewVideosInCampaignId = 0;
        return $obj;
    }

    public function afterNewVideo($videos_id) {
        _error_log("AD_Server:afterNewVideo start");
        $obj = $this->getDataObject();
        if (!empty($obj->autoAddNewVideosInCampaignId)) {
            $vc = new VastCampaigns($obj->autoAddNewVideosInCampaignId);
            if (!empty($vc->getName())) {
                $video = new Video("", "", $videos_id);
                if (!empty($video->getTitle())) {
                    _error_log("AD_Server:afterNewVideo saving");
                    $o = new VastCampaignsVideos(0);
                    $o->setVast_campaigns_id($obj->autoAddNewVideosInCampaignId);
                    $o->setVideos_id($videos_id);
                    $o->setLink("");
                    $o->setAd_title($video->getTitle());
                    $o->setStatus('a');
                    $id = $o->save();
                    _error_log("AD_Server:afterNewVideo saved {$id}");
                } else {
                    _error_log("AD_Server:afterNewVideo videos_id NOT found {$videos_id}");
                }
            } else {
                _error_log("AD_Server:afterNewVideo autoAddNewVideosInCampaignId NOT found {$obj->autoAddNewVideosInCampaignId}");
            }
        } else {
            _error_log("AD_Server:afterNewVideo is disabled");
        }
        return true;
    }

    public function canLoadAds() {
        //if (empty($_GET['videoName']) && empty($_GET['u'])) {
        if (empty($_GET['videoName'])) {
            return false;
        }
        // count it each 2 seconds
        if (empty($_SESSION['lastAdShowed']) || $_SESSION['lastAdShowed'] + 2 <= time()) {
            _session_start();
            $_SESSION['lastAdShowed'] = time();

            if (!isset($_SESSION['showAdsCount'])) {
                //_error_log("Show Ads Count started");
                $_SESSION['showAdsCount'] = 1;
            } else {
                $_SESSION['showAdsCount']++;
            }
        }
        //_error_log("Show Ads Count {$_SESSION['showAdsCount']}");
        $obj = $this->getDataObject();
        if (!empty($obj->showAdsOnEachVideoView) && $_SESSION['showAdsCount'] % $obj->showAdsOnEachVideoView === 0) {
            return true;
        }
        return false;
    }

    public function getHeadCode() {
        $obj = $this->getDataObject();
        if (!$this->canLoadAds()) {
            return "";
        }
        global $global;
        $_GET['vmap_id'] = session_id();

        $css = '<link href="' . getCDN() . 'js/videojs-contrib-ads/videojs.ads.css" rel="stylesheet" type="text/css"/>'
                . '<link href="' . getCDN() . 'plugin/AD_Server/videojs-ima/videojs.ima.css" rel="stylesheet" type="text/css"/>';

        if (!empty($obj->showMarkers)) {
            $css .= '<link href="' . getCDN() . 'plugin/AD_Server/videojs-markers/videojs.markers.css" rel="stylesheet" type="text/css"/>';
            
        }
        $css .= '<style>.ima-ad-container{z-index:1000 !important;}</style>';
        return $css;
    }

    public function afterVideoJS() {

        $obj = $this->getDataObject();
        if (!$this->canLoadAds() || empty($_GET['vmap_id'])) {
            return "";
        }
        global $global;
        if (empty($_GET['u'])) {
            $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        } else {
            $video['duration'] = "01:00:00";
        }
        $video_length = parseDurationToSeconds($video['duration']);
        $vmap_id = @$_GET['vmap_id'];

        _session_start();
        if (!empty($_GET['vmap_id']) && !empty($_SESSION['user']['vmap'][$_GET['vmap_id']])) {
            $vmaps = unserialize($_SESSION['user']['vmap'][$_GET['vmap_id']]);
        } else {
            $vmaps = $this->getVMAPs($video_length);
            $_SESSION['user']['vmap'][$_GET['vmap_id']] = serialize($vmaps);
        }
        PlayerSkins::setIMAADTag("{$global['webSiteRootURL']}plugin/AD_Server/VMAP.php?video_length={$video_length}&vmap_id={$vmap_id}&random=" . uniqid());
        $onPlayerReady = "";

        if (!empty($obj->showMarkers)) {
            $onPlayerReady .= "
                    player.markers({
                        markerStyle: {
                            'width': '5px',
                            'background-color': 'yellow'
                        },
                        markerTip: {
                            display: true,
                            text: function (marker) {
                                return marker.text;
                            }
                        },
                        markers: [";
            foreach ($vmaps as $value) {
                $vastCampaingVideos = new VastCampaignsVideos($value->VAST->campaing);
                $video = new Video("", "", $vastCampaingVideos->getVideos_id());
                $onPlayerReady .= "{time: {$value->timeOffsetSeconds}, text: \"".addcslashes($video->getTitle(), '"')."\"},";
            }
            $onPlayerReady .= "]});";
        }


        PlayerSkins::getStartPlayerJS($onPlayerReady);
        $js = '';
        $js .= '<script src="//imasdk.googleapis.com/js/sdkloader/ima3.js"></script>';
        $js .= '<script src="' . getCDN() . 'js/videojs-contrib-ads/videojs.ads.js" type="text/javascript"></script>';
        $js .= '<script src="' . getCDN() . 'plugin/AD_Server/videojs-ima/videojs.ima.js" type="text/javascript"></script>';
        
        if (!empty($obj->showMarkers)) {
            $js .= '<script src="' . getCDN() . 'plugin/AD_Server/videojs-markers/videojs-markers.js"></script>';
        }
        return $js;
    }

    private function getRandomPositions() {

        if (empty($_GET['vmap_id'])) {
            return "";
        }
        $obj = $this->getDataObject();
        $oldId = session_id();
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
        session_id($_GET['vmap_id']);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $options = array();

        if (!empty($obj->start)) {
            $options[] = 1;
        }
        if (!empty($obj->mid25Percent)) {
            $options[] = 2;
        }
        if (!empty($obj->mid50Percent)) {
            $options[] = 3;
        }
        if (!empty($obj->mid75Percent)) {
            $options[] = 4;
        }
        if (!empty($obj->end)) {
            $options[] = 5;
        }

        $selectedOptions = array();
        if (empty($_SESSION['lastAdRandomPositions']) || $_SESSION['lastAdRandomPositions'] + 20 <= time()) {
            $_SESSION['lastAdRandomPositions'] = time();


            if (empty($obj->showAdsOnRandomPositions)) {
                $selectedOptions = $options;
            } else {
                for ($i = 0; $i < $obj->showAdsOnRandomPositions; $i++) {
                    shuffle($options);
                    $selectedOptions[] = array_pop($options);
                }
            }
            $_SESSION['adRandomPositions'] = $selectedOptions;
        }
        $adRandomPositions = $_SESSION['adRandomPositions'];
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
        session_id($oldId);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        //_error_log("VMAP select those options: " . print_r($adRandomPositions, true));
        return $adRandomPositions;
    }

    public function getVMAPs($video_length) {
        $vmaps = array();

        $obj = $this->getDataObject();

        $selectedOptions = $this->getRandomPositions();

        if (!empty($obj->start) && in_array(1, $selectedOptions)) {
            $vmaps[] = new VMAP("start", new VAST(1));
        }
        if (!empty($obj->mid25Percent) && in_array(2, $selectedOptions)) {
            $val = $video_length * (25 / 100);
            $vmaps[] = new VMAP($val, new VAST(2));
        }
        if (!empty($obj->mid50Percent) && in_array(3, $selectedOptions)) {
            $val = $video_length * (50 / 100);
            $vmaps[] = new VMAP($val, new VAST(3));
        }
        if (!empty($obj->mid75Percent) && in_array(4, $selectedOptions)) {
            $val = $video_length * (75 / 100);
            $vmaps[] = new VMAP($val, new VAST(4));
        }
        if (!empty($obj->end) && in_array(5, $selectedOptions)) {
            $vmaps[] = new VMAP("end", new VAST(5), $video_length);
        }

        return $vmaps;
    }

    public function VMAPsHasVideos() {
        $vmaps = $this->getVMAPs(100);
        //var_dump($vmaps);exit;
        foreach ($vmaps as $value) {
            if (empty($value->VAST->campaing)) {
                return false;
            }
        }
        return true;
    }

    public function showAdsNow() {
        if (!$this->VMAPsHasVideos()) {
            return false;
        }
    }

    static public function getVideos() {
        $campaings = VastCampaigns::getValidCampaigns();
        //var_dump($campaings);
        $videos = array();
        foreach ($campaings as $key => $value) {
            $v = VastCampaignsVideos::getValidVideos($value['id']);
            $videos = array_merge($videos, $v);
            $campaings[$key]['videos'] = $v;
        }
        return array('campaigns' => $campaings, 'videos' => $videos);
    }

    static public function getRandomVideo() {
        $result = static::getVideos();
        $videos = $result['videos'];
        shuffle($videos);
        return array_pop($videos);
    }

    static public function getRandomCampaign() {
        $result = static::getVideos();
        $campaing = $result['campaigns'];
        shuffle($campaing);
        return array_pop($campaing);
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/AD_Server/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getValidCampaignsFromVideo($videos_id) {
        return VastCampaigns::getValidCampaignsFromVideo($videos_id);
    }

}

class VMAP {

    public $timeOffset;
    public $timeOffsetSeconds;
    public $VAST;
    public $idTag = "preroll-ad";

    function __construct($time, VAST $VAST, $video_length = 0) {
        if ($time === 'start') {
            $this->timeOffsetSeconds = 0;
        } else if ($time === 'end') {
            $this->timeOffsetSeconds = $video_length;
        } else {
            $this->timeOffsetSeconds = $time;
        }
        $this->VAST = $VAST;
        $this->setTimeOffset($time);
    }

    function setTimeOffset($time) {
        if (empty($time)) {
            //$time = "start";
        }
        // if is longer then the video lenght will be END
        if (empty($time) || $time == "start") {
            $this->idTag = "preroll-ad-" . $this->VAST->id;
        } else if ($time == "end") {
            $this->idTag = "postroll-ad-" . $this->VAST->id;
        } else if (is_numeric($time)) {
            $time = $this->format($time);
            $this->idTag = "midroll-" . $this->VAST->id;
        }
        // format to 00:00:15.000
        $this->timeOffset = $time;
    }

    private function format($seconds) {
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d:%02d.000', $hours, $mins, $secs);
    }

}

class VAST {

    public $id;
    public $campaing;

    function __construct($id) {
        $this->id = $id;
        $row = AD_Server::getRandomVideo();
        if (!empty($row)) {
            $this->campaing = $row['id'];
        } else {
            $this->campaing = false;
        }
    }

}
