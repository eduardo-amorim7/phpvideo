<?php
if (empty($config)) {
    return true;
}

// filter some security here
if (!empty($_GET['lang'])) {
    $_GET['lang'] = str_replace(array("'", '"', "&quot;", "&#039;"), array('', '', '', ''), xss_esc($_GET['lang']));
}

if (empty($_SESSION['language'])) {
    $_SESSION['language'] = $config->getLanguage();
}
if (!empty($_GET['lang'])) {
    $_GET['lang'] = strip_tags($_GET['lang']);
    $_SESSION['language'] = $_GET['lang'];
}
@include_once "{$global['systemRootPath']}locale/{$_SESSION['language']}.php";

function __($str, $allowHTML = false) {
    global $t;
    if (empty($t[$str])) {
        if ($allowHTML) {
            return $str;
        }
        return str_replace(array("'", '"', "<", '>'), array('&apos;', '&quot;', '&lt;', '&gt;'), $str);
    } else {
        if ($allowHTML) {
            return $t[$str];
        }
        return str_replace(array("'", '"', "<", '>'), array('&apos;', '&quot;', '&lt;', '&gt;'), $t[$str]);
    }
}

function isRTL() {
    global $t_isRTL;
    return !empty($t_isRTL) && $t_isRTL;
}

function getAllFlags() {
    global $global;
    $dir = "{$global['systemRootPath']}view/css/flag-icon-css-master/flags/4x3";
    $flags = array();
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $flags[] = str_replace(".svg", "", $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

/**
 * Deprecated replaced by Layout::getAvilableFlags()
 * @global type $global
 * @return type
 */
function getEnabledLangs() {
    global $global;
    $dir = "{$global['systemRootPath']}locale";
    $flags = array();
    if (empty($global['dont_show_us_flag'])) {
        $flags[] = 'us';
    }
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != '.' && $entry != '..' && $entry != 'index.php' && $entry != 'function.php' && $entry != 'save.php') {
                $flags[] = str_replace('.php', '', $entry);
            }
        }
        closedir($handle);
    }
    sort($flags);
    return $flags;
}

function textToLink($string, $targetBlank = false) {
    $target = "";
    if ($targetBlank) {
        $target = "target=\"_blank\"";
    }

    return preg_replace(
        "~[[:alpha:]]+://[^<>[:space:]'\"]+[[:alnum:]/]~",
        "<a href=\"\\0\" {$target} >\\0</a>",
        $string
    );
}

function br2nl($html) {
    $nl = preg_replace(array('#<br\s*/?>#i', '#<p\s*/?>#i', '#</p\s*>#i'), array("\n", "\n", ''), $html);
    return $nl;
}
