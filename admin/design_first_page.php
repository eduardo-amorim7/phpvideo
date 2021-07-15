<?php
$defaultSwitch = false;
$youtubeSwitch = Plugin::isEnabledByName('YouTube');
$gallerySwitch = Plugin::isEnabledByName('Gallery');
$netflixSwitch = Plugin::isEnabledByName('YouPHPFlix2');
$channelListSwitch = Plugin::isEnabledByName('FirstPageChannelList');
if (empty($netflixSwitch) && empty($gallerySwitch) && empty($youtubeSwitch) && empty($channelListSwitch)) {
    $defaultSwitch = true;
}
?>
<div class="row">
    <div class="col-sm-6 col-md-3 col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                Default
                <div class="material-switch pull-right">
                    <input class="" data-toggle="toggle" type="checkbox" id="defaultSwitch" <?php echo $defaultSwitch ? "checked" : ""; ?>>
                    <label for="defaultSwitch" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <img src="<?php echo $global['webSiteRootURL']; ?>admin/img/default.jpg" class="img-responsive">
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                Netflix
                <div class="material-switch pull-right">
                    <input class="" data-toggle="toggle" type="checkbox" id="netflixSwitch" <?php echo $netflixSwitch ? "checked" : ""; ?>>
                    <label for="netflixSwitch" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <img src="<?php echo $global['webSiteRootURL']; ?>admin/img/netflix.jpg" class="img-responsive img-radio">
            </div>
        </div>
    </div>
    <div class="clearfix hidden-md hidden-lg"></div>
    <div class="col-sm-6 col-md-3 col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                Channel
                <div class="material-switch pull-right">
                    <input class="" data-toggle="toggle" type="checkbox" id="channelListSwitch" <?php echo $channelListSwitch ? "checked" : ""; ?>>
                    <label for="channelListSwitch" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <img src="<?php echo $global['webSiteRootURL']; ?>admin/img/channel.png" class="img-responsive img-radio">
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                Gallery
                <div class="material-switch pull-right">
                    <input class="" data-toggle="toggle" type="checkbox" id="gallerySwitch" <?php echo $gallerySwitch ? "checked" : ""; ?>>
                    <label for="gallerySwitch" class="label-primary"></label>
                </div>
            </div>
            <div class="panel-body">
                <img src="<?php echo $global['webSiteRootURL']; ?>admin/img/gallery.jpg" class="img-responsive">
            </div>
        </div>
    </div>
</div>
<script>
    function checkSwitch() {
        var defaultSwitch = $('#defaultSwitch').is(":checked");
        var netflixSwitch = $('#netflixSwitch').is(":checked");
        var gallerySwitch = $('#gallerySwitch').is(":checked");
        var youtubeSwitch = $('#youtubeSwitch').is(":checked");
        var channelListSwitch = $('#channelListSwitch').is(":checked");
        if (!defaultSwitch && !netflixSwitch && !gallerySwitch && !youtubeSwitch && !channelListSwitch) {
            $('#netflixSwitch').prop('checked', false);
            $('#gallerySwitch').prop('checked', false);
            $('#youtubeSwitch').prop('checked', false);
            $('#channelListSwitch').prop('checked', false);
            $('#defaultSwitch').prop('checked', true);
        }
    }
    $(document).ready(function () {
        $('#defaultSwitch').change(function (e) {
            if ($(this).is(":checked")) {
                $('#netflixSwitch').prop('checked', false);
                $('#gallerySwitch').prop('checked', false);
                $('#youtubeSwitch').prop('checked', false);
                $('#channelListSwitch').prop('checked', false);
            }
            checkSwitch();

            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                data: {"uuid": "a06505bf-3570-4b1f-977a-fd0e5cab205d", "name": "Gallery", "dir": "Gallery", "enable": false},
                type: 'post',
                success: function (response) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "e3a568e6-ef61-4dcc-aad0-0109e9be8e36", "name": "YouPHPFlix2", "dir": "YouPHPFlix2", "enable": false},
                        type: 'post',
                        success: function (response) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": "youu05bf-3570-4b1f-977a-fd0e5cabtube", "name": "YouTube", "dir": "YouTube", "enable": false},
                                type: 'post',
                                success: function (response) {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                        data: {"uuid": "channel-first-page-977a-fd0e5cab205d", "name": "FirstPageChannelList", "dir": "FirstPageChannelList", "enable": false},
                                        type: 'post',
                                        success: function (response) {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                                                data: {"theme": 'default'},
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        $('#netflixSwitch').change(function (e) {
            if ($(this).is(":checked")) {
                $('#gallerySwitch').prop('checked', false);
                $('#defaultSwitch').prop('checked', false);
                $('#youtubeSwitch').prop('checked', false);
                $('#channelListSwitch').prop('checked', false);
            }
            checkSwitch();

            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                data: {"uuid": "a06505bf-3570-4b1f-977a-fd0e5cab205d", "name": "Gallery", "dir": "Gallery", "enable": false},
                type: 'post',
                success: function (response) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "youu05bf-3570-4b1f-977a-fd0e5cabtube", "name": "YouTube", "dir": "YouTube", "enable": false},
                        type: 'post',
                        success: function (response) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": "e3a568e6-ef61-4dcc-aad0-0109e9be8e36", "name": "YouPHPFlix2", "dir": "YouPHPFlix2", "enable": true},
                                type: 'post',
                                success: function (response) {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                        data: {"uuid": "channel-first-page-977a-fd0e5cab205d", "name": "FirstPageChannelList", "dir": "FirstPageChannelList", "enable": false},
                                        type: 'post',
                                        success: function (response) {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                                                data: {"theme": 'netflix'},
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        $('#gallerySwitch').change(function (e) {
            if ($(this).is(":checked")) {
                $('#netflixSwitch').prop('checked', false);
                $('#defaultSwitch').prop('checked', false);
                $('#youtubeSwitch').prop('checked', false);
                $('#channelListSwitch').prop('checked', false);
            }
            checkSwitch();

            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                data: {"uuid": "a06505bf-3570-4b1f-977a-fd0e5cab205d", "name": "Gallery", "dir": "Gallery", "enable": true},
                type: 'post',
                success: function (response) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "youu05bf-3570-4b1f-977a-fd0e5cabtube", "name": "YouTube", "dir": "YouTube", "enable": false},
                        type: 'post',
                        success: function (response) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": "e3a568e6-ef61-4dcc-aad0-0109e9be8e36", "name": "YouPHPFlix2", "dir": "YouPHPFlix2", "enable": false},
                                type: 'post',
                                success: function (response) {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                        data: {"uuid": "channel-first-page-977a-fd0e5cab205d", "name": "FirstPageChannelList", "dir": "FirstPageChannelList", "enable": false},
                                        type: 'post',
                                        success: function (response) {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                                                data: {"theme": 'default'},
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        $('#youtubeSwitch').change(function (e) {
            if ($(this).is(":checked")) {
                $('#gallerySwitch').prop('checked', false);
                $('#defaultSwitch').prop('checked', false);
                $('#netflixSwitch').prop('checked', false);
                $('#channelListSwitch').prop('checked', false);
            }
            checkSwitch();

            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                data: {"uuid": "a06505bf-3570-4b1f-977a-fd0e5cab205d", "name": "Gallery", "dir": "Gallery", "enable": false},
                type: 'post',
                success: function (response) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "e3a568e6-ef61-4dcc-aad0-0109e9be8e36", "name": "YouPHPFlix2", "dir": "YouPHPFlix2", "enable": false},
                        type: 'post',
                        success: function (response) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": "youu05bf-3570-4b1f-977a-fd0e5cabtube", "name": "YouTube", "dir": "YouTube", "enable": true},
                                type: 'post',
                                success: function (response) {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                        data: {"uuid": "channel-first-page-977a-fd0e5cab205d", "name": "FirstPageChannelList", "dir": "FirstPageChannelList", "enable": false},
                                        type: 'post',
                                        success: function (response) {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                                                data: {"theme": 'default'},
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        
        
        $('#channelListSwitch').change(function (e) {
            if ($(this).is(":checked")) {
                $('#gallerySwitch').prop('checked', false);
                $('#defaultSwitch').prop('checked', false);
                $('#netflixSwitch').prop('checked', false);
                $('#youtubeSwitch').prop('checked', false);
            }
            checkSwitch();

            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                data: {"uuid": "a06505bf-3570-4b1f-977a-fd0e5cab205d", "name": "Gallery", "dir": "Gallery", "enable": false},
                type: 'post',
                success: function (response) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "e3a568e6-ef61-4dcc-aad0-0109e9be8e36", "name": "YouPHPFlix2", "dir": "YouPHPFlix2", "enable": false},
                        type: 'post',
                        success: function (response) {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                data: {"uuid": "youu05bf-3570-4b1f-977a-fd0e5cabtube", "name": "YouTube", "dir": "YouTube", "enable": false},
                                type: 'post',
                                success: function (response) {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                                        data: {"uuid": "channel-first-page-977a-fd0e5cab205d", "name": "FirstPageChannelList", "dir": "FirstPageChannelList", "enable": true},
                                        type: 'post',
                                        success: function (response) {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>admin/themeUpdate.json.php',
                                                data: {"theme": 'default'},
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });


    });
</script>