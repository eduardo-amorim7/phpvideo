<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$isSerie = 1;
$isPlayList = true;
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';

if (!PlayList::canSee($_GET['playlists_id'], User::getId())) {
    forbiddenPage(_('You cannot see this playlist'));
}

$video = PlayLists::isPlayListASerie($_GET['playlists_id']);
if (!empty($video)) {
    $video = Video::getVideo($video['id']);
    include $global['systemRootPath'] . 'view/modeYoutube.php';
    exit;
}

$playListObj = new PlayList($_GET['playlists_id']);

$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

$playListData = array();
$videoStartSeconds = array();
$users_id = User::getId();
foreach ($playList as $key => $value) {
    if(!User::isAdmin() && !Video::userGroupAndVideoGroupMatch($users_id, $value['videos_id'])){
        unset($playList[$key]);
        continue;
    }
    if ($value['type'] === 'embed') {
        $sources[0]['type'] = 'video';
        $sources[0]['url'] = $value["videoLink"];
    } else {
        $sources = getVideosURL($value['filename']);
    }
    $images = Video::getImageFromFilename($value['filename'], $value['type']);
    $externalOptions = _json_decode($value['externalOptions']);

    $src = new stdClass();
    $src->src = $images->thumbsJpg;
    $thumbnail = array($src);

    $playListSources = array();
    foreach ($sources as $value2) {
        if ($value2['type'] !== 'video' && $value2['type'] !== 'audio' && $value2['type'] !== 'serie') {
            continue;
        }
        $playListSources[] = new playListSource($value2['url'], $value['type'] === 'embed');
    }
    if (empty($playListSources)) {
        continue;
    }
    $playListData[] = new PlayListElement($value['title'], $value['description'], $value['duration'], $playListSources, $thumbnail, $images->poster, parseDurationToSeconds(@$externalOptions->videoStartSeconds), $value['cre'], $value['likes'], $value['views_count'], $value['videos_id']);
}

$video = PlayLists::isPlayListASerie($_GET['playlists_id']);

$playlist_index = intval(@$_REQUEST['playlist_index']);

if (!empty($video['id'])) {
    AVideoPlugin::getEmbed($video['id']);
    setVideos_id($video['id']);
} else if (!empty($playListData[$playlist_index])) {
    setVideos_id($playListData[$playlist_index]->getVideos_id());
    $video = Video::getVideo($playListData[$playlist_index]->getVideos_id());
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $playListObj->getName() . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getCDN(); ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/social.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.css" rel="stylesheet">

        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <style>
            .next-button:before {
                -moz-osx-font-smoothing: grayscale;
                -webkit-font-smoothing: antialiased;
                display: inline-block;
                font-style: normal;
                font-variant: normal;
                text-rendering: auto;
                line-height: 1;
                content: "\f051";
                font-family: 'Font Awesome 5 Free';
                font-weight: 900;
            }

            .video-js .next-button {width: 2em !important;}
        </style>
        <?php
        if (!empty($video['id'])) {
            getLdJson($video['id']);
            getItemprop($video['id']);
        }
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <?php
        if (!empty($advancedCustomUser->showChannelBannerOnModeYoutube)) {
            ?>
            <div class="container" style="margin-bottom: 10px;">
                <img src="<?php echo User::getBackground($video['users_id']); ?>" class="img img-responsive" />
            </div>
            <?php
        }
        ?>
        <div class="container-fluid principalContainer">
            <?php
            if (!empty($playListObj)) {
                if (!empty($advancedCustom->showAdsenseBannerOnTop)) {
                    ?>
                    <style>
                        .compress {
                            top: 100px !important;
                        }
                    </style>
                    <div class="row">
                        <div class="col-lg-12 col-sm-12 col-xs-12">
                            <center style="margin:5px;">
                                <?php
                                echo getAdsLeaderBoardTop();
                                ?>
                            </center>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <!-- playlist player -->
                <?php
                $htmlMediaTag = '<video playsinline preload="auto"
                                       controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo"
                                       data-setup=\'{"techOrder": ["youtube","html5"]}\'>
                                </video>';
                echo PlayerSkins::getMediaTag($video['filename'], $htmlMediaTag);
                ?>
                <!-- playlist player END -->

                <div class="row" id="modeYoutubeBottom">
                    <div class="col-sm-1 col-md-1"></div>
                    <div class="col-sm-8 col-md-8" id="modeYoutubeBottomContent">
                    </div>
                    <div class="col-sm-2 col-md-2 bgWhite list-group-item rightBar">
                        <div class="col-lg-12 col-sm-12 col-xs-12">
                            <?php echo getAdsSideRectangle(); ?>
                        </div>
                        <input type="search" id="playListSearch" class="form-control" placeholder=" <?php echo __("Search"); ?>"/>
                        <select class="form-control" id="embededSortBy" >
                            <option value="default"> <?php echo __("Default"); ?></option>
                            <option value="titleAZ" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Title (A-Z)"); ?></option>
                            <option value="titleZA" data-icon="glyphicon-sort-by-attributes-alt"> <?php echo __("Title (Z-A)"); ?></option>
                            <option value="newest" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Date added (newest)"); ?></option>
                            <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt" > <?php echo __("Date added (oldest)"); ?></option>
                            <option value="popular" data-icon="glyphicon-thumbs-up"> <?php echo __("Most popular"); ?></option>
                            <?php
                            if (empty($advancedCustom->doNotDisplayViews)) {
                                ?> 
                                <option value="views_count" data-icon="glyphicon-eye-open"  <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
                            <?php } ?>
                        </select>
                        <div class="vjs-playlist" style="" id="playList">
                            <!--
                              The contents of this element will be filled based on the
                              currently loaded playlist
                            -->
                        </div>
                    </div>
                    <div class="col-sm-1 col-md-1"></div>
                </div>   
                <?php
            } else {
                ?>
                <br>
                <br>
                <br>
                <br>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Attention"); ?>!</strong> <?php echo empty($advancedCustom->videoNotFoundText->value) ? __("We have not found any videos or audios to show") : $advancedCustom->videoNotFoundText->value; ?>.
                </div>
            <?php } ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <?php
        echo AVideoPlugin::afterVideoJS();
        include $global['systemRootPath'] . 'view/include/footer.php';
        $videoJSArray = array(
            "view/js/BootstrapMenu.min.js");
        $jsURL = combineFiles($videoJSArray, "js");
        ?>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script><script src="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist/videojs-playlist.js"></script>
        <script src="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.js"></script>
        <script src="<?php echo getCDN(); ?>view/js/videojs-youtube/Youtube.js"></script>
        <script>

                                            var playerPlaylist = <?php echo json_encode($playListData); ?>;
                                            var originalPlayerPlaylist = playerPlaylist;

                                            if (typeof player === 'undefined') {
                                                player = videojs('mainVideo'<?php echo PlayerSkins::getDataSetup(); ?>);
                                            }

                                            var videos_id = playerPlaylist[0].videos_id;

                                            player.on('play', function () {
                                                addView(videos_id, this.currentTime());
                                            });

                                            player.on('timeupdate', function () {
                                                var time = Math.round(this.currentTime());
                                                if (time >= 5 && time % 5 === 0) {
                                                    addView(videos_id, time);
                                                }
                                            });

                                            player.on('ended', function () {
                                                var time = Math.round(this.currentTime());
                                                addView(videos_id, time);
                                            });

                                            player.playlist(playerPlaylist);
                                            player.playlist.autoadvance(0);
                                            player.playlist.repeat(true);
                                            // Initialize the playlist-ui plugin with no option (i.e. the defaults).
                                            player.playlistUi();
                                            player.playlist.currentItem(<?php echo $playlist_index; ?>);

                                            var timeout;
                                            $(document).ready(function () {

                                                $("#playListSearch").keyup(function () {
                                                    var filter = $(this).val();
                                                    $(".vjs-playlist-item-list li").each(function () {
                                                        if ($(this).find('.vjs-playlist-name').text().search(new RegExp(filter, "i")) < 0) {
                                                            $(this).slideUp();
                                                        } else {
                                                            $(this).slideDown();
                                                        }
                                                    });
                                                });

                                                $('#embededSortBy').click(function () {
                                                    setTimeout(function () {
                                                        clearTimeout(timeout);
                                                    }, 2000);
                                                });

                                                $('#embededSortBy').change(function () {
                                                    var value = $(this).val();
                                                    playerPlaylist.sort(function (a, b) {
                                                        return compare(a, b, value);
                                                    });
                                                    player.playlist.sort(function (a, b) {
                                                        return compare(a, b, value);
                                                    });
                                                });

                                                //Prevent HTML5 video from being downloaded (right-click saved)?
                                                $('#mainVideo').bind('contextmenu', function () {
                                                    return false;
                                                });

                                                player.currentTime(playerPlaylist[0].videoStartSeconds);
                                                $("#modeYoutubeBottomContent").load("<?php echo $global['webSiteRootURL']; ?>view/modeYoutubeBottom.php?videos_id=" + playerPlaylist[0].videos_id);
                                                $(".vjs-playlist-item ").click(function () {


                                                });

                                                player.on('playlistitem', function () {
                                                    index = player.playlist.currentIndex();
                                                    videos_id = playerPlaylist[index].videos_id;
                                                    $("#modeYoutubeBottomContent").load("<?php echo $global['webSiteRootURL']; ?>view/modeYoutubeBottom.php?videos_id=" + playerPlaylist[index].videos_id);
                                                    setTimeout(function () {
                                                        player.currentTime(playerPlaylist[index].videoStartSeconds);
                                                    }, 500);
                                                    if (typeof enableDownloadProtection === 'function') {
                                                        enableDownloadProtection();
                                                    }
                                                });
                                                setTimeout(function () {
                                                    var Button = videojs.getComponent('Button');
                                                    var nextButton = videojs.extend(Button, {
                                                        //constructor: function(player, options) {
                                                        constructor: function () {
                                                            Button.apply(this, arguments);
                                                            //this.addClass('vjs-chapters-button');
                                                            this.addClass('next-button');
                                                            this.addClass('vjs-button-fa-size');
                                                            this.controlText("Next");
                                                        },
                                                        handleClick: function () {
                                                            player.playlist.next();
                                                        }
                                                    });

// Register the new component
                                                    videojs.registerComponent('nextButton', nextButton);
                                                    player.getChild('controlBar').addChild('nextButton', {}, getPlayerButtonIndex('PlayToggle') + 1);
                                                }, 30);

                                            });
                                            function compare(a, b, type) {
                                                console.log(type);
                                                switch (type) {
                                                    case "titleAZ":
                                                        return strcasecmp(a.name, b.name);
                                                        break;
                                                    case "titleZA":
                                                        return strcasecmp(b.name, a.name);
                                                        break;
                                                    case "newest":
                                                        return a.created > b.created ? 1 : (a.created < b.created ? -1 : 0);
                                                        break;
                                                    case "oldest":
                                                        return b.created > a.created ? 1 : (b.created < a.created ? -1 : 0);
                                                        break;
                                                    case "popular":
                                                        return a.likes > b.likes ? 1 : (a.likes < b.likes ? -1 : 0);
                                                        break;
                                                    default:
                                                        return 0;
                                                        break;
                                                }
                                            }
                                            function strcasecmp(s1, s2) {
                                                s1 = (s1 + '').toLowerCase();
                                                s2 = (s2 + '').toLowerCase();
                                                return s1 > s2 ? 1 : (s1 < s2 ? -1 : 0);
                                            }
        </script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
