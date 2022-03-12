    <title><?=htmlentities(implode(' - ', $this->title)); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="SHORTCUT ICON" href="<?=STATIC_URL; ?>/images/logos/favicon.ico" />
    <link rel="search" type="application/opensearchdescription+xml" href="<?=STATIC_URL; ?>/download/searchplugins/aowow.xml" title="Aowow" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_URL.'/css/basic.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_URL.'/css/global.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_URL.'/css/aowow.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?=STATIC_URL.'/css/locale_'.User::$localeString.'.css?'.AOWOW_REVISION; ?>" />
<?php
if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO)):
    echo '    <link rel="stylesheet" type="text/css" href="'.STATIC_URL.'/css/staff.css?'.AOWOW_REVISION."\" />\n";
endif;

foreach ($this->css as [$type, $css]):
    if ($type == CSS_FILE):
        echo '    <link rel="stylesheet" type="text/css" href="'.STATIC_URL.'/css/'.$css.'?'.AOWOW_REVISION."\" />\n";
    elseif ($type == CSS_STRING):
        echo '    <style type="text/css">'.$css."</style>\n";
    endif;
endforeach;
?>
    <script type="text/javascript">
        var g_serverTime = new Date('<?=date(Util::$dateFormatInternal); ?>');
        var g_staticUrl = "<?=STATIC_URL; ?>";
        var g_host = "<?=HOST_URL; ?>";
<?php
if ($this->gDataKey):
        echo "        var g_dataKey = '".$_SESSION['dataKey']."'\n";
endif;
?>
    </script>
    <script src="<?=STATIC_URL.'/js/jquery-1.12.4.min.js'; ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/js/basic.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/widgets/power.js?lang='.substr(User::$localeString, 2); ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/js/locale_'.User::$localeString.'.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/js/global.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/js/locale.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?=STATIC_URL.'/js/Markup.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
<?php
if (User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT | U_GROUP_VIDEO)):
    echo '    <script src="'.STATIC_URL.'/js/staff.js?'.AOWOW_REVISION."\" type=\"text/javascript\"></script>\n";
endif;

foreach ($this->js as [$type, $js]):
    if ($type == JS_FILE):
        echo '    <script type="text/javascript" src="'.($js[0] == '?' ? $js.'&' : STATIC_URL.'/js/'.$js.'?').AOWOW_REVISION."\"></script>\n";
    elseif ($type == JS_STRING):
        echo '    <script type="text/javascript">'.$js."</script>\n";
    endif;
endforeach;
?>
    <script type="text/javascript">
        var g_user = <?=Util::toJSON($this->gUser, JSON_UNESCAPED_UNICODE); ?>;
<?php
if ($this->gFavorites):
    echo "        g_favorites = ".Util::toJSON($this->gFavorites).";\n";
endif;
?>
    </script>

<?php
if (CFG_ANALYTICS_USER):
?>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', '<?=CFG_ANALYTICS_USER; ?>', 'auto');
        ga('send', 'pageview');
    </script>
<?php
endif;
?>
