<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage subscribes"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Subscribes") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo getCDN(); ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __("Subscribes"); ?>
                </div>
                <div class="panel-body">
                    <button type="button" class="btn btn-success pull-right" id="sendSubscribeBtn">
                        <i class="fas fa-envelope-square"></i> <?php echo __("Notify Subscribers"); ?>
                    </button>
                    <div class="clearfix hidden-sm hidden-md hidden-lg"></div>
                    <textarea id="emailMessage" placeholder="<?php echo __("Enter text"); ?> ..." style="width: 100%;"></textarea>
                </div>
                <div class="panel-footer">
                    <table id="grid" class="table table-condensed table-hover table-striped">
                        <thead>
                            <tr>
                                <th data-column-id="identification" ><?php echo __("My Subscribers"); ?></th>
                                <th data-column-id="created" ><?php echo __("Created"); ?></th>
                                <th data-column-id="modified" ><?php echo __("Modified"); ?></th>
                                <th data-column-id="status" data-formatter="status" data-sortable="false"><?php echo __("Status"); ?></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo getCDN(); ?>view/js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
        <script>
            function _subscribe(email, user_id, id) {
                $('#subscribe' + id + ' span').addClass("fa-spinner");
                $('#subscribe' + id + ' span').addClass("fa-spin");
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/subscribe.json.php',
                    method: 'POST',
                    data: {'email': email, 'user_id': user_id},
                    success: function (response) {
                        console.log(response);
                        $('#subscribe' + id + ' span').removeClass("fa-spinner");
                        $('#subscribe' + id + ' span').removeClass("fa-spin");
                        if (response.subscribe == "i") {
                            $('#subscribe' + id).removeClass("btn-success");
                            $('#subscribe' + id).addClass("btn-danger");
                            $('#subscribe' + id + ' span').removeClass("fa-check");
                            $('#subscribe' + id + ' span').addClass("fa-times-circle");
                        } else {
                            $('#subscribe' + id).removeClass("btn-danger");
                            $('#subscribe' + id).addClass("btn-success");
                            $('#subscribe' + id + ' span').removeClass("fa-times-circle");
                            $('#subscribe' + id + ' span').addClass("fa-check");
                        }
                    }
                });
            }

            function notify() {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/notifySubscribers.json.php',
                    method: 'POST',
                    data: {'message': $('#emailMessage').val()},
                    success: function (response) {
                        console.log(response);
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Success"); ?>", "<?php echo __("You have sent the notification"); ?>", "success");
                        }
                        modal.hidePleaseWait();
                    }
                });
            }
            $(document).ready(function () {
                $('#emailMessage').wysihtml5();
                var grid = $("#grid").bootgrid({
                    labels: {
                        noResults: "<?php echo __("No results found!"); ?>",
                        all: "<?php echo __("All"); ?>",
                        infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                        loading: "<?php echo __("Loading..."); ?>",
                        refresh: "<?php echo __("Refresh"); ?>",
                        search: "<?php echo __("Search"); ?>",
                    },
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "objects/subscribes.json.php"; ?>",
                    formatters: {
                        "status": function (column, row) {
                            var subscribe = '<button type="button" class="btn btn-xs btn-success command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Unsubscribe"><span class="fa fa-check" aria-hidden="true"></span></button>'
                            if (row.status == 'i') {
                                subscribe = '<button type="button" class="btn btn-xs btn-danger command-status" id="subscribe' + row.id + '" data-toggle="tooltip" data-placement="left" title="Subscribe"><span class="fa fa-times-circle" aria-hidden="true"></span></button>'
                            }
                            return subscribe;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    /* Executes after data is loaded and rendered */
                    grid.find(".command-status").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        _subscribe(row.email, row.users_id, row.id);
                    });
                });
                $("#sendSubscribeBtn").click(function () {
                    notify();
                });

            });

        </script>
    </body>
</html>
