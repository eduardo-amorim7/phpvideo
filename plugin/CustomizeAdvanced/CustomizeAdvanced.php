<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CustomizeAdvanced extends PluginAbstract {


    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        $txt = "Fine Tuning your AVideo";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Advanced-Customization-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "CustomizeAdvanced";
    }

    public function getUUID() {
        return "55a4fa56-8a30-48d4-a0fb-8aa6b3f69033";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->logoMenuBarURL = "";
        $obj->encoderNetwork = "https://network.avideo.com/";
        $obj->useEncoderNetworkRecomendation = false;
        $obj->doNotShowEncoderNetwork = true;
        $obj->doNotShowUploadButton = false;
        $obj->uploadButtonDropdownIcon = "fas fa-video";
        $obj->uploadButtonDropdownText = "";
        $obj->encoderNetworkLabel = "";
        $obj->doNotShowUploadMP4Button = true;
        $obj->disablePDFUpload = false;
        $obj->disableImageUpload = false;
        $obj->disableZipUpload = true;
        $obj->disableMP4Upload = false;
        $obj->disableMP3Upload = false;
        $obj->uploadMP4ButtonLabel = "";
        $obj->doNotShowImportMP4Button = true;
        $obj->importMP4ButtonLabel = "";
        $obj->doNotShowEncoderButton = false;
        $obj->encoderButtonLabel = "";
        $obj->doNotShowEmbedButton = false;
        $obj->embedBackgroundColor = "white";
        $obj->embedButtonLabel = "";
        $obj->embedCodeTemplate = '<div class="embed-responsive embed-responsive-16by9"><iframe width="640" height="360" style="max-width: 100%;max-height: 100%; border:none;" src="{embedURL}" frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay" scrolling="no">iFrame is not supported!</iframe></div>';
        $obj->embedCodeTemplateObject = '<div class="embed-responsive embed-responsive-16by9"><object width="640" height="360"><param name="movie" value="{embedURL}"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="{embedURL}" allowscriptaccess="always" allowfullscreen="true" width="640" height="360"></embed></object></div>';
        $obj->htmlCodeTemplate = '<a href="{permaLink}"><img src="{imgSRC}">{title}</a>';
        $obj->BBCodeTemplate = '[url={permaLink}][img]{imgSRC}[/img]{title}[/url]';
        $obj->doNotShowEncoderHLS = false;
        $obj->doNotShowEncoderResolutionLow = false;
        $obj->doNotShowEncoderResolutionSD = false;
        $obj->doNotShowEncoderResolutionHD = false;
        $obj->openEncoderInIFrame = false;
        $obj->showOnlyEncoderAutomaticResolutions = true;
        $obj->doNotShowEncoderAutomaticHLS = false;
        $obj->doNotShowEncoderAutomaticMP4 = false;
        $obj->doNotShowEncoderAutomaticWebm = false;
        $obj->doNotShowEncoderAutomaticAudio = false;
        $obj->saveOriginalVideoResolution = false;
        self::addDataObjectHelper('saveOriginalVideoResolution', 'Do not save original video', 'This option will make your encoder at the end trancode the video into the original format resolution');
        $obj->doNotShowExtractAudio = false;
        $obj->doNotShowCreateVideoSpectrum = false;
        $obj->doNotShowLeftMenuAudioAndVideoButtons = false;
        $obj->doNotShowWebsiteOnContactForm = false;
        $obj->doNotUseXsendFile = false;
        $obj->makeVideosInactiveAfterEncode = false;
        $obj->makeVideosUnlistedAfterEncode = false;
        $obj->usePermalinks = false;
        $obj->useVideoIDOnSEOLinks = true;
        $obj->disableAnimatedGif = false;
        $obj->removeBrowserChannelLinkFromMenu = false;
        $obj->EnableMinifyJS = false;
        $obj->disableShareAndPlaylist = false;
        $obj->disableShareOnly = false;
        $obj->disableEmailSharing = false;
        $obj->splitBulkEmailSend = 50;
        $obj->disableComments = false;
        $obj->commentsMaxLength = 200;
        $obj->commentsNoIndex = false;
        $obj->disableYoutubePlayerIntegration = false;
        $obj->utf8Encode = false;
        $obj->utf8Decode = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->menuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->underMenuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->footerHTMLCode = $o;
        $obj->signInOnRight = true;
        $obj->signInOnLeft = true;
        $obj->forceCategory = false;
        $obj->showCategoryTopImages = true;
        $obj->autoPlayAjax = false;

        $plugins = Plugin::getAllEnabled();
        //import external plugins configuration options
        foreach ($plugins as $value) {
            $p = AVideoPlugin::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $foreginObjects = $p->getCustomizeAdvancedOptions();
                if ($foreginObjects) {
                    foreach ($foreginObjects as $optionName => $defaultValue)
                        $obj->{$optionName} = $defaultValue;
                }
            }
        }

        $obj->disablePlayLink = false;
        $obj->disableHelpLeftMenu = false;
        $obj->disableAboutLeftMenu = false;
        $obj->disableContactLeftMenu = false;
        $obj->disableNavbar = false;
        $obj->disableNavBarInsideIframe = true;
        $obj->autoHideNavbar = true;
        $obj->autoHideNavbarInSeconds = 0;
        $obj->videosCDN = "";
        $obj->useFFMPEGToGenerateThumbs = false;
        $obj->thumbsWidthPortrait = 170;
        $obj->thumbsHeightPortrait = 250;
        $obj->thumbsWidthLandscape = 640;
        $obj->thumbsHeightLandscape = 360;
        $obj->usePreloadLowResolutionImages = false;
        $obj->showImageDownloadOption = false;
        $obj->doNotDisplayViews = false;
        $obj->doNotDisplayLikes = false;
        $obj->doNotDisplayCategoryLeftMenu = false;
        $obj->doNotDisplayCategory = false;
        $obj->doNotDisplayGroupsTags = false;
        $obj->doNotDisplayPluginsTags = false;
        $obj->showNotRatedLabel = false;
        $obj->showShareMenuOpenByDefault = false;
        $obj->askRRatingConfirmationBeforePlay_G = false;
        $obj->askRRatingConfirmationBeforePlay_PG = false;
        $obj->askRRatingConfirmationBeforePlay_PG13 = false;
        $obj->askRRatingConfirmationBeforePlay_R = false;
        $obj->askRRatingConfirmationBeforePlay_NC17 = true;
        $obj->askRRatingConfirmationBeforePlay_MA = true;
        $obj->filterRRating = false;
        $obj->AsyncJobs = false;


        $obj->doNotShowLeftHomeButton = false;
        $obj->doNotShowLeftTrendingButton = false;

        $obj->CategoryLabel = "Categories";
        $obj->ShowAllVideosOnCategory = false;
        $obj->hideCategoryVideosCount = false;

        //ver 7.1
        $obj->paidOnlyUsersTellWhatVideoIs = false;
        $obj->paidOnlyShowLabels = false;
        $obj->paidOnlyLabel = "Premium";
        $obj->paidOnlyFreeLabel = "Free";
        $obj->removeSubscribeButton = false;
        $obj->removeThumbsUpAndDown = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->videoNotFoundText = $o;
        $obj->siteMapRowsLimit = 100;
        $obj->siteMapUTF8Fix = false;
        $obj->showPrivateVideosOnSitemap = false;
        $obj->enableOldPassHashCheck = true;
        $obj->disableHTMLDescription = false;
        $obj->disableVideoSwap = false;
        $obj->makeSwapVideosOnlyForAdmin = false;
        $obj->disableCopyEmbed = false;
        $obj->disableDownloadVideosList = false;
        $obj->videosManegerRowCount = "[10, 25, 50, -1]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.
        $obj->videosListRowCount = "[10, 20, 30, 40, 50]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.

        $parse = parse_url($global['webSiteRootURL']);
        $domain = str_replace(".", "", $parse['host']);
        $obj->twitter_site = "@{$domain}";
        $obj->twitter_player = true;
        $obj->twitter_summary_large_image = false;
        $obj->footerStyle = "position: fixed;bottom: 0;width: 100%;";
        $obj->disableVideoTags = false;
        $obj->doNotAllowEncoderOverwriteStatus = false;
        $obj->doNotAllowUpdateVideoId = false;
        $obj->makeVideosIDHarderToGuess = false;
        self::addDataObjectHelper('makeVideosIDHarderToGuess', 'Make the videos ID harder to guess', 'This will change the videos_id on the URL to a crypted value. this crypt user your $global[salt] (configuration.php), so make sure you keep it save in case you need to restore your site, otherwise all the shared links will be lost');
        
        
        
        $o = new stdClass();
        $o->type = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $o->value = 0;
        $obj->timeZone = $o;
        
        
        $obj->keywords = "AVideo, videos, live, movies";
        
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "Allow: /plugin/Live/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css
Disallow: /user
Disallow: /plugin
Disallow: /mvideos
Disallow: /usersGroups
Disallow: /charts
Disallow: /upload
Disallow: /comments
Disallow: /subscribes
Disallow: /update
Disallow: /locale
Disallow: /objects/*
Allow: /plugin/Live/?*
Allow: /plugin/LiveLink/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: .js
Allow: .css";
        $obj->robotsTXT = $o;
        self::addDataObjectHelper('robotsTXT', 'robots.txt file content', 'robots.txt is a plain text file that follows the Robots Exclusion Standard. A robots.txt file consists of one or more rules. Each rule blocks (or allows) access for a given crawler to a specified file path in that website.');
        
        
        return $obj;
    }

    public function getHelp() {
        if (User::isAdmin()) {
            return "<h2 id='CustomizeAdvanced help'>CustomizeAdvanced (admin)</h2><p>" . $this->getDescription() . "</p>";
        }
        return "";
    }
    
    public function getModeYouTube($videos_id) {
        global $global, $config;
        $obj = $this->getDataObject();
        $video = Video::getVideo($videos_id, "viewable", true);
        if (!empty($video['rrating']) && empty($_GET['rrating'])) {
            $suffix = strtoupper(str_replace("-", "", $video['rrating']));
            eval("\$show = \$obj->askRRatingConfirmationBeforePlay_$suffix;");
            if (!empty($show)) {
                include "{$global['systemRootPath']}plugin/CustomizeAdvanced/confirmRating.php";
                exit;
            }
        }
    }
    
    public function getEmbed($videos_id) {
        return $this->getModeYouTube($videos_id);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if ($obj->autoHideNavbar && !isEmbed()) {
            $content .= '<script>$(function () {setTimeout(function(){$("#mainNavBar").autoHidingNavbar();},5000);});</script>';
            $content .= '<script>'. file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbar.js').'</script>';
        }
        if ($obj->autoHideNavbarInSeconds && !isEmbed()) {
            $content .= '<script>'
                    . 'var autoHidingNavbarTimeoutMiliseconds = '.intval($obj->autoHideNavbarInSeconds*1000).';'
                    .file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbarInSeconds.js')
                    . '</script>';
        }
        return $content;
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuRight.php';
        }
    }

    public function getHTMLMenuLeft() {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuLeft.php';
        }
    }

    public static function getVideoWhereClause() {
        $sql = "";
        $obj = AVideoPlugin::getObjectData("CustomizeAdvanced");
        if ($obj->filterRRating && isset($_GET['rrating'])) {
            if ($_GET['rrating'] === "0") {
                $sql .= " AND v.rrating = ''";
            } else if (in_array($_GET['rrating'], Video::$rratingOptions)) {
                $sql .= " AND v.rrating = '{$_GET['rrating']}'";
            }
        }
        return $sql;
    }

    public function getVideosManagerListButton() {
        $btn = "";
        if (User::isAdmin()) {
            $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="updateDiskUsage(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="'. __("Update disk usage for this media") .'"><i class="fas fa-chart-line"></i> '. __("Update Disk Usage") .'</button>';
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="removeThumbs(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="'. __("Remove thumbs for this media") .'"><i class="fas fa-images"></i> '. __("Remove Thumbs") .'</button>';
        }
        return $btn;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        
        if($obj->makeVideosIDHarderToGuess){
            if(isVideo()){
                if(!empty($global['makeVideosIDHarderToGuessNotDecrypted'])){
                    unset($global['makeVideosIDHarderToGuessNotDecrypted']);
                    forbiddenPage(__('Invalid ID'));
                }
            }
        }
        
        $baseName = basename($_SERVER['REQUEST_URI']);
        
        $js = "";
        if(empty($obj->autoPlayAjax)){
            $js .= "<script>var autoPlayAjax=false;</script>";
        }else{
            $js .= "<script>var autoPlayAjax=true;</script>";
        }
        if ($baseName === 'mvideos') {
            $js .= "<script>function updateDiskUsage(videos_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/CustomizeAdvanced/updateDiskUsage.php',
                                        data: {\"videos_id\": videos_id},
                                        type: 'post',
                                        success: function (response) {
                                        if(response.error){
                                            swal({
                                                title: \"" . __("Sorry!") . "\",
                                                text: response.msg,
                                                type: \"error\",
                                                html: true
                                            });
                                        }else{
                                            $(\"#grid\").bootgrid('reload');
                                        }
                                            console.log(response);
                                            modal.hidePleaseWait();
                                        }
                                    });}</script>";
            $js .= "<script>function removeThumbs(videos_id){
                                    modal.showPleaseWait();
                                    \$.ajax({
                                        url: '{$global['webSiteRootURL']}plugin/CustomizeAdvanced/deleteThumbs.php',
                                        data: {\"videos_id\": videos_id},
                                        type: 'post',
                                        success: function (response) {
                                        if(response.error){
                                            swal({
                                                title: \"" . __("Sorry!") . "\",
                                                text: response.msg,
                                                icon: \"error\"
                                            });
                                        }else{
                                            swal({
                                                title: \"" . __("Success!") . "\",
                                                text: \"\",
                                                icon: \"success\"
                                            });
                                        }
                                            console.log(response);
                                            modal.hidePleaseWait();
                                        }
                                    });}</script>";
        }
        return $js;
    }
    
    public function afterNewVideo($videos_id) {
        Video::updateFilesize($videos_id);
        return true;
    }

}
