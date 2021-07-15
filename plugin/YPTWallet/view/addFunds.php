<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';


$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
$obj = $plugin->getDataObject();
if (!empty($paypal)) {
    $paypalObj = $paypal->getDataObject();
}
$options = _json_decode($obj->addFundsOptions);
//unset($_SESSION['addFunds_Success']);
//unset($_SESSION['addFunds_Fail']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Add Funds") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
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
                    <div class="panel-heading"><?php echo __("Add Funds"); ?></div>
                    <div class="panel-body">
                        <div class="col-sm-6">
                            <?php echo $obj->add_funds_text ?>
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
                                <label for="value"><?php echo __("Add Funds"); ?> <?php echo $obj->currency_symbol; ?> <?php echo $obj->currency; ?></label>
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
                            <?php
                            $plugin->getAvailablePayments();
                            ?>
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

            });
        </script>

    </body>
</html>
