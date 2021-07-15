<?php
CustomizeUser::autoIncludeBGAnimationFile();
?>
<br>
<style>
    .loginPage{
    }
</style>
<?php
if (empty($_COOKIE) && empty($_GET['cookieLogin'])) {
    // TODO implement a popup login for cross domain cookie block
}
if (empty($_GET['redirectUri'])) {
    if (!empty($_SERVER["HTTP_REFERER"])) {
        // if comes from the streamer domain
        if (preg_match('#^' . $global['webSiteRootURL'] . '#i', $_SERVER["HTTP_REFERER"]) === 1) {
            $_GET['redirectUri'] = $_SERVER["HTTP_REFERER"];
        }
    }
}
if (empty($_COOKIE) && get_browser_name() !== 'Other (Unknown)') {
    ?>
    <div style="padding: 10px;">
        <div class="alert alert-warning">
            <h1><i class="fas fa-exclamation-circle"></i> <?php echo __("Login Alert"); ?></h1>
            <h2><?php echo __("Please Login in the window pop up"); ?></h2>
            <button class="btn btn-block btn-warning" onclick="openLoginWindow()"><i class="fas fa-sign-in-alt"></i> <?php echo __("Open pop-up Login window"); ?></button><br>      
            <?php echo __("In case the login window does not open, check how do I disable the pop-up blocker in your browser"); ?>:<br>        
            <a href="https://support.mozilla.org/en-US/kb/pop-blocker-settings-exceptions-troubleshooting" target="_blank">Mozilla Firefox</a><br>
            <a href="https://support.google.com/chrome/answer/95472" target="_blank">Google Chrome</a>
        </div>
    </div>
    <script>
        function openLoginWindow() {
            win = window.open('<?php echo $global['webSiteRootURL']; ?>user?redirectUri=<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""; ?>', 'Login Page', "width=640,height=480,scrollbars=no");
                }
                var win;
                openLoginWindow();
                var logintimer = setInterval(function () {
                    if (win.closed) {
                        clearInterval(logintimer);
                        document.location = "<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : $global['webSiteRootURL']; ?>";
                    }
                }, 1000);
                $(document).ready(function () {
                    if (!win || win.closed || typeof win.closed == 'undefined') {
                        //avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("In order to enjoy our login feature, you need to allow our pop-ups in your browser."); ?>", "error");
                    }
                });
    </script>
    <?php
    return false;
}
?>
<div class="row loginPage">
    <div class="hidden-xs col-sm-2 col-md-3 "></div>
    <div class="col-xs-12 col-sm-8  col-md-6 addWidthOnMenuOpen">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h2>
                    <?php echo __('Welcome back!'); ?>
                </h2>
                <div class="">
                    <?php
                    if (!empty($advancedCustomUser->userMustBeLoggedInCloseButtonURL)) {
                        ?>
                        <div class="pull-right">
                            <a id="buttonMyNavbar" class=" btn btn-default navbar-btn" style="padding: 6px 12px; margin-right: 40px;" href="<?php echo $advancedCustomUser->userMustBeLoggedInCloseButtonURL; ?>">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="panel-body">
                <?php
                if (empty($advancedCustomUser->disableNativeSignIn)) {
                    ?>
                    <form class="form-horizontal"  id="loginForm">
                        <input type="hidden" name="redirectUri" value=""/>
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __("User"); ?></label>
                            <div class="col-sm-8 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    <input  id="inputUser" placeholder="<?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="text" value="" required >
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label class="col-sm-4 control-label"><?php echo __("Password"); ?></label>
                            <div class="col-sm-8 inputGroupContainer">
                                <?php
                                getInputPassword("inputPassword");
                                ?>
                            </div>
                        </div>

                        <?php
                        $captcha = User::getCaptchaForm();
                        ?>
                        <div class="form-group captcha" style="<?php echo User::isCaptchaNeed() ? "" : "display: none;" ?>" id="captchaForm">
                            <?php echo $captcha; ?>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-4 text-right">
                                <label for="inputRememberMe" ><?php echo __("Remember me"); ?></label>
                            </div>
                            <div class="col-xs-8" >
                                <div class="material-switch" data-toggle="tooltip" title="<?php echo __("Check this to stay signed in"); ?>">
                                    <input  id="inputRememberMe" class="form-control"  type="checkbox">
                                    <label for="inputRememberMe" class="label-success" ></label>
                                </div>
                            </div>
                        </div>
                        <!-- Button -->
                        <div class="form-group">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success  btn-block" id="mainButton" ><span class="fas fa-sign-in-alt"></span> <?php echo __("Sign in"); ?></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12 inputGroupContainer text-center">
                                <?php
                                if (empty($advancedCustomUser->disableNativeSignUp)) {
                                    ?>
                                    <a href="#" class="btn btn-default btn-xs" id="forgotPassword" data-toggle="tooltip" title="<?php echo __("Use this to recover your password"); ?>"><i class="fas fa-redo-alt"></i> <?php echo __("I forgot my password"); ?></a>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>

                    </form>
                    <?php
                }
                ?>
            </div>
            <div class="panel-footer">
                <?php
                if (empty($advancedCustomUser->disableNativeSignUp)) {
                    ?>
                    <div class="row" data-toggle="tooltip" title="<?php echo __("Are you new here?"); ?>">
                        <div class="col-md-12">
                            <a href="<?php echo $global['webSiteRootURL']; ?>signUp?redirectUri=<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""; ?>" 
                               class="btn btn-default btn-block"><span class="fa fa-user-plus"></span> <?php echo __("Sign up"); ?></a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>


        <?php
        $login = AVideoPlugin::getLogin();

        $totalLogins = 0;
        foreach ($login as $value) {
            if (is_string($value) && file_exists($value)) { // it is a include path for a form
                include $value;
            } else if (is_array($value)) {
                $totalLogins++;
            }
        }

        $columSize = 12;
        if ($totalLogins > 1) {
            switch ($totalLogins) {
                case 2:
                case 4:
                case 5:
                case 7:
                case 8:
                case 10:
                case 11:
                    $columSize = 6;
                    break;
                case 3:
                case 6:
                case 9:
                case 12:
                    $columSize = 4;
                    break;
            }
        }

        foreach ($login as $value) {
            if (is_string($value) && file_exists($value)) {
                //include $value;
            } else if (is_array($value)) {
                $uid = uniqid();
                $oauthURL = "{$global['webSiteRootURL']}login?type={$value['parameters']->type}&redirectUri=" . (isset($_GET['redirectUri']) ? $_GET['redirectUri'] : "");
                ?>
                <div class="col-md-<?php echo $columSize; ?>">
                    <button id="login<?php echo $uid; ?>" class="<?php echo $value['parameters']->class; ?>" ><span class="<?php echo $value['parameters']->icon; ?>"></span> <?php echo $value['parameters']->type; ?></button>
                </div>
                <script>
                    $(document).ready(function () {
                        $('#login<?php echo $uid; ?>').click(function () {
                            modal.showPleaseWait();
                            if (typeof inIframe !== 'undefined' && inIframe()) {
                                var popup = window.open('<?php echo $oauthURL; ?>', 'loginYPT');
                                var popupTick = setInterval(function () {
                                    if (popup.closed) {
                                        clearInterval(popupTick);
                                        console.log('window closed!');
                                        location.reload();
                                    }
                                }, 500);
                            } else {
                                document.location = "<?php echo $oauthURL; ?>";
                            }
                        });
                    });
                </script>
                <?php
            }
        }
        ?>
        <?php
        if (!empty($advancedCustomUser->messageToAppearBelowLoginBox->value)) {
            echo "<div class='alert alert-info'> <i class=\"fas fa-info-circle\"></i> ";
            echo $advancedCustomUser->messageToAppearBelowLoginBox->value;
            echo "</div>";
        }
        ?>
    </div>
    <div class="hidden-xs col-sm-2 col-md-3"></div>
</div>
<script>

    function loginFormActive() {
        
    }
    function loginFormReset() {

    }
    $(document).ready(function () {
<?php
if (!empty($_GET['error'])) {
    ?>
            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo addslashes($_GET['error']); ?>", "error");
    <?php
}
?>
        $('#loginForm').submit(function (evt) {
            evt.preventDefault();
            if (!$('#inputUser').val()) {
                avideoAlertError('<?php echo __('Please type your username'); ?>');
                return false;
            }
            if (!$('#inputPassword').val()) {
                avideoAlertError('<?php echo __('Please type your password'); ?>');
                return false;
            }
<?php
if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
    ?>
                var email = $("#inputUser").val();
                if (!validateEmail(email) && email.toLowerCase() !== "admin") {
                    // if the user is admin, let it go
                    //avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("The username must be an email"); ?>", "error");
                    //return false;
                    avideoToastWarning('<?php echo __('This is not a valid email'); ?>');
                }
    <?php
}
?>
            modal.showPleaseWait();
            loginFormActive();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/login.json.php',
                data: {"user": $('#inputUser').val(), "pass": $('#inputPassword').val(), "rememberme": $('#inputRememberMe').is(":checked"), "captcha": $('#captchaText').val(), "redirectUri": "<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""; ?>"},
                type: 'post',
                success: function (response) {
                    if (!response.isLogged) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user or password is wrong!"); ?>", "error");
                        }
                        if (response.isCaptchaNeed) {
                            $("#btnReloadCapcha").trigger('click');
                            $('#captchaForm').slideDown();
                        }
                        loginFormReset();
                    } else {

                        document.location = response.redirectUri;
                    }
                }
            });
        });
        $('#forgotPassword').click(function () {
            var user = $('#inputUser').val();
            if (!user) {
                avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("You need to inform what is your user!"); ?>", "error");
                return false;
            }
            var capcha = '<span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha?<?php echo time(); ?>" id="captcha"></span><span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span><input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText2">';
            var span = document.createElement("span");
            span.innerHTML = "<?php echo __("We will send you a link, to your e-mail, to recover your password!"); ?>" + capcha;
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                content: span,
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                    .then(function (willDelete) {
                        if (willDelete) {

                            modal.showPleaseWait();
                            $.ajax({
                                url: '<?php echo $global['webSiteRootURL']; ?>objects/userRecoverPass.php',
                                data: {"user": $('#inputUser').val(), "captcha": $('#captchaText2').val()},
                                type: 'post',
                                success: function (response) {
                                    if (response.error) {
                                        avideoAlert("<?php echo __("Error"); ?>", response.error, "error");
                                    } else {
                                        avideoAlert("<?php echo __("E-mail sent"); ?>", "<?php echo __("We sent you an e-mail with instructions"); ?>", "success");
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                        }
                    });
            $('#btnReloadCapcha').click(function () {
                $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                $('#captchaText').val('');
            });
        });
    });

</script>
