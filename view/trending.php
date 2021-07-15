<?php
global $global, $config;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else
    if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        $_SESSION['type'] = "";
        unset($_SESSION['type']);
    }
} else {
    unset($_SESSION['type']);
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/video.php';

$total = Video::getTotalVideos();

$_REQUEST['rowCount'] = 10;
$_REQUEST['current'] = getCurrentPage();

//$_POST['sort']['likes'] = "DESC";
$_GET['sort']['trending'] = 1;

$pages = ceil($total / $_REQUEST['rowCount']);
$videos = Video::getAllVideos();
unset($_POST['sort']);
$metaDescription = __("Trending");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Trending") . getSEOComplement() . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <link href="<?php echo getCDN(); ?>plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container">
            <div class="row results gallery">
                <?php
                //var_dump($rows);
                foreach ($videos as $key => $value) {
                    ?>
                    <div class="col-lg-12 searchResult thumbsImage mb-2" style="overflow: hidden;">


                        <a class="galleryLink col-sm-4 col-md-4 col-lg-4" videos_id="<?php echo $value['id']; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title']); ?>" title="<?php echo $value['title']; ?>">
                            <?php
                            $images = Video::getImageFromFilename($value['filename'], $value['type']);
                            $imgGif = $images->thumbsGif;
                            $poster = $images->thumbsJpg;
                            ?>
                            <div class="aspectRatio16_9">
                                <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo @$img_portrait; ?>  rotate<?php echo $value['rotation']; ?>  <?php echo ($poster != $images->thumbsJpgSmall) ? "blur" : ""; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />
                                <?php
                                if (!empty($imgGif)) {
                                    ?>
                                    <img src="<?php echo getCDN(); ?>view/img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive" height="196" />
                                <?php } ?>
                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                            </div>
                            <div class="progress" style="height: 3px; margin-bottom: 2px;">
                                <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </a>
                        <div class=" col-sm-8 col-md-8 col-lg-8">
                            <a class="h6 galleryLink  col-lg-12" style="font-size: 1.5em;" videos_id="<?php echo $value['id']; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title']); ?>" title="<?php echo $value['title']; ?>">
                                <h2><?php echo $value['title']; ?></h2>
                            </a>

                            <div class="text-muted galeryDetails col-lg-12" style="overflow: hidden;">
                                <div>
                                    <?php if (empty($_GET['catName'])) { ?>
                                        <a class="label label-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/">
                                            <?php
                                            if (!empty($value['iconClass'])) {
                                                ?>
                                                <i class="<?php echo $value['iconClass']; ?>"></i>
                                                <?php
                                            }
                                            ?>
                                            <?php echo $value['category']; ?>
                                        </a>
                                    <?php } ?>
                                    <?php
                                    if (!empty($obj->showTags)) {
                                        $value['tags'] = Video::getTags($value['id']);
                                        foreach ($value['tags'] as $value2) {
                                            if (!empty($value2->label) && $value2->label === __("Group")) {
                                                ?><span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
                                            }
                                        }
                                    }
                                    ?>
                                </div>

                                <?php
                                if (empty($advancedCustom->doNotDisplayViews)) {
                                    ?>
                                    <div>
                                        <i class="fa fa-eye"></i>
                                        <span itemprop="interactionCount">
                                            <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                        </span>
                                    </div>
                                <?php } ?>
                                <div>
                                    <i class="far fa-clock"></i>
                                    <?php echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago'); ?>
                                </div>
                                <div>
                                    <i class="fa fa-user"></i>
                                    <a class="text-muted" href="<?php echo User::getChannelLink($value['users_id']); ?>">
                                        <?php echo User::getNameIdentificationById($value['users_id']); ?>
                                    </a>
                                    <?php if ((!empty($value['description'])) && !empty($obj->Description)) { ?>
                                        <button type="button" data-trigger="focus" class="label label-danger" data-toggle="popover" data-placement="top" data-html="true" title="<?php echo $value['title']; ?>" data-content="<div> <?php echo str_replace('"', '&quot;', $value['description']); ?> </div>" ><?php echo __("Description"); ?></button>
                                    <?php } ?>
                                </div>
                                <?php if (Video::canEdit($value['id'])) { ?>
                                    <div>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary">
                                            <i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?>
                                        </a>
                                    </div>
                                <?php
                                }
                                AVideoPlugin::getgalleryActionButton($value['id']);
                                ?>
                            </div>
                            <div class="mainAreaDescriptionContainer  col-lg-12">
                                <h4 class="mainAreaDescription" itemprop="description" style="max-height: 7vw; padding: 0; margin: 5px 0;"><?php echo $value['description']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            if (!empty($pages)) {
                ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php
                        if ($_REQUEST['current'] == 1) {
                            echo "disabled";
                        }
                        ?>">
                            <a class="page-link" href="<?php echo "{$global['webSiteRootURL']}trending?current=" . ($_REQUEST['current'] - 1); ?>" tabindex="-1"><?php echo __('Previous') ?></a>
                        </li>
                        <?php
                        $size = 5;
                        $i = 1;
                        $end = $pages;

                        if ($_REQUEST['current'] - $size > $i) {
                            $i = $_REQUEST['current'] - $size;
                        }

                        if ($_REQUEST['current'] + $size < $end) {
                            $end = $_REQUEST['current'] + $size;
                        }

                        for (; $i <= $end; $i++) {
                            ?>
                            <li class="page-item  <?php
                            if ($_REQUEST['current'] == $i) {
                                echo "active";
                            }
                            ?>"><a class="page-link" href="<?php echo "{$global['webSiteRootURL']}trending?current={$i}"; ?>"><?php echo $i; ?></a></li>
                                <?php
                            }
                            ?>
                        <li class="page-item <?php
                        if ($_REQUEST['current'] == $pages) {
                            echo "disabled";
                        }
                        ?>">
                            <a class="page-link" href="<?php echo "{$global['webSiteRootURL']}trending?current=" . ($_REQUEST['current'] + 1); ?>"><?php echo __('Next') ?></a>
                        </li>
                    </ul>
                </nav>
                <?php
            }
            ?>
        </div>
        <!-- status elements -->
        <div class="scroller-status">
            <div class="infinite-scroll-request loader-ellips text-center">
                <img src="img/loading.gif" alt=""/>
            </div>
            <p class="infinite-scroll-last text-center text-muted"><?php echo __('End of content')?></p>
            <p class="infinite-scroll-error text-center text-muted"><?php echo __('No more pages to load')?></p>
        </div>
        <?php
        if ($_REQUEST['current'] + 1 <= $pages) {
            ?>
            <!-- pagination has path -->
            <p class="pagination hidden">
                <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>trending?current=<?php echo $_REQUEST['current'] + 1; ?>"><?php echo __('Next page') ?></a>
            </p>
            <?php
        }
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo getCDN(); ?>plugin/Gallery/script.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function () {
                $container = $('.results').infiniteScroll({
                    path: '.pagination__next',
                    append: '.searchResult',
                    status: '.scroller-status',
                    hideNav: '.pagination',
                });
                $container.on('append.infiniteScroll', function (event, response, path, items) {
                    mouseEffect();
                    lazyImage();
                });
                mouseEffect();
                lazyImage();
            });
        </script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
