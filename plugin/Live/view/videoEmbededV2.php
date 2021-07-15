<?php
global $isLive;
$isLive = 1;
$isEmbed = 1;
require_once '../../videos/configuration.php';
/**
 * this was made to mask the main URL
 */
if (!empty($_GET['webSiteRootURL'])) {
    $global['webSiteRootURL'] = $_GET['webSiteRootURL'];
}
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');

$livet =  LiveTransmition::getFromDbByUserName($_GET['u']);
setLiveKey($livet['key'], Live::getLiveServersIdRequest(), @$_REQUEST['live_index']);
$uuid = LiveTransmition::keyNameFix($livet['key']);
$p = AVideoPlugin::loadPlugin("Live");

$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if(!empty($objSecure)){
    $objSecure->verifyEmbedSecurity();
}
$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$video['users_id'] = $user_id;
AVideoPlugin::getModeYouTubeLive($user_id);
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
$poster = Live::getPosterImage($livet['users_id'], $_REQUEST['live_servers_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo getCDN(); ?>view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?> </title>
        <link href="<?php echo getCDN(); ?>bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getCDN(); ?>js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <link href="<?php echo getCDN(); ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
        <style>
            #chatOnline {
                width: 25vw !important;
                position: relative !important;
                margin: 0;
                padding: 0;
            }
            .container-fluid {
                padding-right: 0 !important;
                padding-left: 0 !important;
            }
            .liveChat .messages{
                -webkit-transition: all 1s ease; /* Safari */
                transition: all 1s ease;
            }
            #embedVideo-content .embed-responsive{
                max-height: 98vh;
            }
            body {
                padding: 0 !important;
                margin: 0 !important;
                <?php
                if (!empty($customizedAdvanced->embedBackgroundColor)) {
                    echo "background-color: $customizedAdvanced->embedBackgroundColor;";
                }
                ?>

            }
        </style>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var player;
        </script>
    </head>

    <body style="background-color: black; overflow-x: hidden;">
        <div class="container">
            <div class="col-md-9 col-sm-9 col-xs-9" style="margin: 0; padding: 0;" id="embedVideo-content">
                <?php
                echo getAdsLeaderBoardTop();
                ?>
                <div class="embed-responsive  embed-responsive-16by9" >
                    <video poster="<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>" controls autoplay="autoplay"  playsinline webkit-playsinline="webkit-playsinline" 
                           class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered"
                           id="mainVideo" data-setup='{ "aspectRatio": "16:9",  "techorder" : ["flash", "html5"] }'>
                        <source src="<?php echo Live::getM3U8File($uuid); ?>" type='application/x-mpegURL'>
                    </video>

                    <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="LiveEmbed2">
                        <?php
                        $streamName = $uuid;
                        include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
                        echo getLiveUsersLabel();
                        ?>
                    </div>
                </div>

                <?php
                echo getAdsLeaderBoardFooter();
                ?>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-3" style="margin: 0; padding: 0;">
                <?php
                $p->getChat($uuid);
                ?>
            </div>
        </div>
        <script>
            $(function () {
                $('.liveChat .messages').css({"height": ($(window).height() - 128) + "px"});
                window.addEventListener('resize', function () {
                    $('.liveChat .messages').css({"height": ($(window).height() - 128) + "px"});
                })
            });
        </script>
        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <script src="<?php echo getCDN(); ?>view/js/script.js" type="text/javascript"></script>
        <script>

<?php
echo PlayerSkins::getStartPlayerJS();
?>
        </script>
        <?php
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
        echo AVideoPlugin::getFooterCode();
        showCloseButton();
        ?>  
    </body>
</html>
