<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}");
}

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$obj = $plugin->getDataObject();
$options = _json_decode($obj->withdrawFundsOptions);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Withdraw Funds") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo __("Withdraw Funds"); ?>
                        <?php
                        if ($obj->enableAutoWithdrawFundsPagePaypal) {
                            ?>
                            <label class="label label-success pull-right"><i class="fab fa-paypal"></i> <?php echo __('Automatic Withdraw'); ?></label>    
                            <?php
                        }
                        ?>
                    </div>
                    <div class="panel-body">
                        <div class="col-sm-6">
                            <?php echo $obj->withdraw_funds_text ?>
                            <?php echo AVideoPlugin::getWalletConfigurationHTML(User::getId(), $plugin, $obj); ?>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            if (!empty($_GET['status'])) {
                                $text = "unknow";
                                $class = "danger";
                                switch ($_GET['status']) {
                                    case "fail":
                                        $text = $obj->add_funds_success_fail;
                                        break;
                                    case "success":
                                        $text = $obj->add_funds_success_success;
                                        $class = "success";
                                        break;
                                    case "cancel":
                                        $text = $obj->add_funds_success_cancel;
                                        $class = "warning";
                                        break;
                                }
                                ?>
                                <div class="alert alert-<?php echo $class; ?>">
                                    <?php echo $text; ?>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="form-group">
                                <label for="value"><?php echo __("Specify Amount"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
                                <select class="form-control" id="value" >
                                    <?php
                                    foreach ($options as $value) {
                                        ?>
                                        <option value="<?php echo $value; ?>"><?php echo $obj->currency_symbol; ?> <?php echo $value; ?> <?php echo $obj->currency; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="information"><?php echo __("Information"); ?></label>
                                <textarea class="form-control" id="information" name="information"></textarea>
                            </div>
                            <button class="btn btn-primary btn-block" id="manualWithdrawFundsPageButton">
                                <i class="fas fa-dollar-sign"></i>
                                <?php echo $obj->manualWithdrawFundsPageButton; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {
                $('#manualWithdrawFundsPageButton').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/YPTWallet/view/manualWithdrawFunds.json.php',
                        type: "POST",
                        data: {
                            value: $('#value').val(),
                            information: $('#information').val()
                        },
                        success: function (response) {
                            $(".walletBalance").text(response.walletBalance);
                            modal.hidePleaseWait();
                            if (response.error) {
                                setTimeout(function () {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                }, 500);
                            } else {
                                setTimeout(function () {
                                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your request was sent"); ?>", "success");
                                }, 500);
                            }
                        }
                    });
                });
            });
        </script>

    </body>
</html>
