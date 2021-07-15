<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

//$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
//$advancedCustom = _json_decode($json_file);
if (!empty($advancedCustomUser->disableNativeSignUp)) {
    die(__("Sign Up Disabled"));
}

$agreement = AVideoPlugin::loadPluginIfEnabled("SignUpAgreement");

$signInLink = "{$global['webSiteRootURL']}user?redirectUri=" . urlencode(isset($_GET['redirectUri']) ? $_GET['redirectUri'] : "");
if (!empty($_GET['siteRedirectUri'])) {
    $signInLink = $_GET['siteRedirectUri'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Sign Up") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        CustomizeUser::autoIncludeBGAnimationFile();
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
                <div class="col-xs-12 col-sm-12 col-lg-8">
                    <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                        <fieldset>
                            <legend class="hidden-xs"><?php echo __("Sign Up"); ?></legend>
                            <div class="form-group">
                                <div class="col-md-12 inputGroupContainer">
                                    <div class="input-group">
                                        <?php
                                        if (!empty($advancedCustomUser->messageToAppearAboveSignUpBox->value)) {
                                            echo $advancedCustomUser->messageToAppearAboveSignUpBox->value;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label hidden-xs"><?php echo __("Name"); ?></label>
                                <div class="col-sm-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                        <input  id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control"  type="text" value="" required >
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label hidden-xs"><?php echo __("User"); ?></label>
                                <div class="col-sm-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                        <input  id="inputUser" placeholder="<?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="<?php echo empty($advancedCustomUser->forceLoginToBeTheEmail) ? "text" : "email"; ?>" value="" required >
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (empty($advancedCustomUser->forceLoginToBeTheEmail)) {
                                ?>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label hidden-xs"><?php echo __("E-mail"); ?></label>
                                    <div class="col-sm-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                            <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email" value="" required >
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label hidden-xs"><?php echo __("New Password"); ?></label>
                                <div class="col-sm-8 inputGroupContainer">
                                    <?php
                                    getInputPassword("inputPassword", 'class="form-control" autocomplete="off" ', __("New Password"));
                                    ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label hidden-xs"><?php echo __("Confirm New Password"); ?></label>
                                <div class="col-sm-8 inputGroupContainer">
                                    <?php
                                    getInputPassword("inputPasswordConfirm", 'class="form-control" autocomplete="off" ', __("Confirm New Password"));
                                    ?>
                                </div>
                            </div>

                            <?php
                            if (!empty($agreement)) {
                                $agreement->getSignupCheckBox();
                            }
                            ?>

                            <div class="form-group">
                                <label class="col-sm-4 control-label hidden-xs"><?php echo __("Type the code"); ?></label>
                                <div class="col-sm-8 inputGroupContainer captcha">
                                    <div class="input-group">
                                        <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha?PHPSESSID=<?php echo session_id(); ?>&<?php echo time(); ?>" id="captcha"></span>
                                        <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                        <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                                    </div>
                                </div>
                            </div>


                            <!-- Button -->
                            <div class="form-group">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary btn-block" ><i class="fas fa-user-plus"></i> <?php echo __("Sign Up"); ?></button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <a href="<?php echo $signInLink; ?>" class="btn btn-success btn-block" ><i class="fas fa-sign-in-alt"></i> <?php echo __("Sign In"); ?></a>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
            </div>
            <script>
                $(document).ready(function () {

                    $('#btnReloadCapcha').click(function () {
                        $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?PHPSESSID=<?php echo session_id(); ?>&' + Math.random());
                        $('#captchaText').val('');
                    });
                    $('#updateUserForm').submit(function (evt) {
                        evt.preventDefault();
                        modal.showPleaseWait();
                        var pass1 = $('#inputPassword').val();
                        var pass2 = $('#inputPasswordConfirm').val();
                        // Password doesn't match
                        if (pass1 != '' && pass1 != pass2) {
                            modal.hidePleaseWait();
                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your password does not match!"); ?>", "error");
                            return false;
                        } else {
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/userCreate.json.php?PHPSESSID=<?php echo session_id(); ?>',
                                                    data: {
                                                        "user": $('#inputUser').val(),
                                                        "pass": $('#inputPassword').val(),
                                                        "email": $('#inputEmail').val(),
                                                        "name": $('#inputName').val(),
                                                        "captcha": $('#captchaText').val()
                                                    },
                                                    type: 'post',
                                                    success: function (response) {
                                                        if (response.status > 0) {
                                                            var span = document.createElement("span");
                                                            span.innerHTML = "<?php echo __("Your user account has been created!"); ?><br><?php echo!empty($advancedCustomUser->unverifiedEmailsCanNOTLogin) ? __("Sign in to your email to verify your account!") : ""; ?>";
                                                                                        swal({
                                                                                            title: "<?php echo __("Congratulations!"); ?>",
                                                                                            content: span,
                                                                                            icon: "success",
                                                                                        }).then(function () {
<?php
if (!empty($_GET['siteRedirectUri'])) {
    ?>
                                                                                                window.location.href = '<?php echo $_GET['siteRedirectUri']; ?>';
    <?php
} else {
    ?>
                                                                                                window.location.href = '<?php echo $global['webSiteRootURL']; ?>user?redirectUri=<?php echo urlencode(isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""); ?>';
    <?php
}
?>

                                                                                                                        });

                                                                                                                    } else {
                                                                                                                        if (response.error) {
                                                                                                                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                                                                                                                        } else {
                                                                                                                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been created!"); ?>", "error");
                                                                                                                        }
                                                                                                                    }
                                                                                                                    modal.hidePleaseWait();
                                                                                                                }
                                                                                                            });
                                                                                                            return false;
                                                                                                        }
                                                                                                    });
                                                                                                });
            </script>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
