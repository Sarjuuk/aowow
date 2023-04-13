    <title><?=Util::htmlEscape(implode(' - ', $this->title)); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="SHORTCUT ICON" href="<?=STATIC_URL; ?>/images/logos/favicon.ico" />
    <link rel="search" type="application/opensearchdescription+xml" href="<?=STATIC_URL; ?>/download/searchplugins/aowow.xml" title="Aowow" />
<?php
foreach ($this->css as [$type, $css]):
    if ($type == SC_CSS_FILE):
        echo '    <link rel="stylesheet" type="text/css" href="'.$css."\" />\n";
    elseif ($type == SC_CSS_STRING):
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
<?php
foreach ($this->js as [$type, $js]):
    if ($type == SC_JS_FILE):
        echo '    <script type="text/javascript" src="'.$js."\"></script>\n";
    elseif ($type == SC_JS_STRING):
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
