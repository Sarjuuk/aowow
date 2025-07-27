<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

    <title><?=$this->concat('title', ' - '); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="SHORTCUT ICON" href="<?=$this->gStaticUrl; ?>/images/logos/favicon.ico" />
    <link rel="search" type="application/opensearchdescription+xml" href="<?=$this->gStaticUrl; ?>/download/searchplugins/aowow.xml" title="<?=Lang::main('search');?>" />
<?=$this->renderArray('css', 4); ?>
    <script type="text/javascript">
        var g_serverTime = <?=$this->gServerTime; ?>;
        var g_staticUrl = "<?=$this->gStaticUrl; ?>";
        var g_host = "<?=$this->gHost; ?>";
<?php
if ($this->gDataKey):
        echo "        var g_dataKey = '".$_SESSION['dataKey']."'\n";
endif;
?>
    </script>
<?=$this->renderArray('js', 4); ?>
<script type="text/javascript">
        var g_user = <?=$this->json($this->user::getUserGlobal()); ?>;
<?php
if ($fav = $this->user::getFavorites()):
    echo "        g_favorites = ".$this->json($fav).";\n";
endif;
?>
    </script>

<?php if ($this->analyticsTag): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?=$this->analyticsTag; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?=$this->analyticsTag; ?>');
    </script>
<?php
endif;

if ($this->rss):
?>
    <link rel="alternate" type="application/rss+xml" title="<?=$this->concat('title', ' - '); ?>" href="<?=$this->rss; ?>"/>
<?php
endif;
?>
