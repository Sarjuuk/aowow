    <title><?php echo htmlentities(implode(' - ', $this->title)); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <link rel="SHORTCUT ICON" href="<?php echo STATIC_URL; ?>/images/logos/favicon.ico" />
    <link rel="search" type="application/opensearchdescription+xml" href="<?php echo STATIC_URL; ?>/download/searchplugins/aowow.xml" title="Aowow" />
    <link rel="stylesheet" type="text/css" href="<?php echo STATIC_URL.'/css/basic.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo STATIC_URL.'/css/global.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo STATIC_URL.'/css/aowow.css?'.AOWOW_REVISION; ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo STATIC_URL.'/css/locale_'.User::$localeString.'.css?'.AOWOW_REVISION; ?>" />
<?php
if (User::isInGroup(U_GROUP_STAFF)):
    echo '    <link rel="stylesheet" type="text/css" href="'.STATIC_URL.'/css/staff.css?'.AOWOW_REVISION."\" />\n";
endif;

foreach ($this->css as $css):
    if (!empty($css['string'])):
        echo '    <style type="text/css">'.$css['string']."</style>\n";
    elseif (!empty($css['path'])):
        echo '    '.(!empty($css['ieCond']) ? '<!--[if '.$css['ieCond'].']>' : null) .
                '<link rel="stylesheet" type="text/css" href="'.STATIC_URL.'/css/'.$css['path'].'?'.AOWOW_REVISION.'" />' .
                (!empty($css['ieCond']) ? '<![endif]-->' : null)."\n";
    endif;
endforeach;
?>
    <script type="text/javascript">
        var g_serverTime = new Date('<?php echo date(Util::$dateFormatInternal); ?>');
        var g_staticUrl = "<?php echo STATIC_URL; ?>";
        var g_host = "<?php echo HOST_URL; ?>";
<?php
if ($this->gDataKey):
        echo "        var g_dataKey = '".$_SESSION['dataKey']."'\n";
endif;
?>
    </script>
    <script src="<?php echo STATIC_URL.'/js/jquery-1.4.2.min.js'; ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/js/basic.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/widgets/power.js?lang='.substr(User::$localeString, 2); ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/js/locale_'.User::$localeString.'.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/js/global.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/js/locale.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
    <script src="<?php echo STATIC_URL.'/js/Markup.js?'.AOWOW_REVISION; ?>" type="text/javascript"></script>
<?php
if (User::isInGroup(U_GROUP_STAFF)):
    echo '    <script src="'.STATIC_URL.'/js/staff.js?'.AOWOW_REVISION."\" type=\"text/javascript\"></script>\n";
endif;

foreach ($this->js as $js):
    if (!empty($js)):
        echo '    <script src="'.($js[0] == '?' ? $js.'&' : STATIC_URL.'/js/'.$js.'?').AOWOW_REVISION."\" type=\"text/javascript\"></script>\n";
    endif;
endforeach;
?>
    <script type="text/javascript">
        var g_user = <?php echo json_encode($this->gUser, JSON_NUMERIC_CHECK); ?>;
    </script>
