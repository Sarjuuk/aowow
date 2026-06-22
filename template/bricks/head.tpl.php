<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    /** @var PageTemplate $this */
?>
    <title><?=$this->concat('title', ' - '); ?></title>
    <meta charset="UTF-8">
<?=$this->renderMetaTags(4);?>
    <link rel="canonical" href="<?=PageTemplate::buildQuery(); ?>">
    <link rel="alternate" hreflang="x-default" href="<?=PageTemplate::buildQuery(add: ['locale' => Lang::getLocale()->getFallback()->value]); ?>">
<?php
foreach ($this->locale::cases() as $l):
    if ($l->validate()):
        echo '    <link rel="alternate" hreflang="'.$l->hreflang('-').'" href="'.PageTemplate::buildQuery(add: ['locale' => $l->value]).'">'.PHP_EOL;
    endif;
endforeach;
?>
    <link rel="SHORTCUT ICON" href="<?=$this->gStaticUrl; ?>/images/logos/favicon.ico" />
    <link rel="search" type="application/opensearchdescription+xml" href="<?=$this->gStaticUrl; ?>/download/searchplugins/aowow.xml" title="<?=Lang::main('search');?>" />
<?php
if ($this->ldIntangible):
    echo '    <script type="application/ld+json">'.$this->json($this->ldIntangible).'</script>'.PHP_EOL;
endif;
if ($this->headIcons):
    echo '    <link rel="image_src" href="'.$this->gStaticUrl.'/images/wow/icons/large/'.$this->escJS($this->headIcons[0]).'.jpg">'.PHP_EOL;
endif;
echo $this->renderArray('css', 4);
?>
    <script type="text/javascript">
        var g_serverTime = <?=$this->gServerTime; ?>;
        var g_staticUrl = "<?=$this->gStaticUrl; ?>";
        var g_host = "<?=$this->gHost; ?>";
<?php
if ($this->gDataKey):
        echo "        var g_dataKey = '".$_SESSION['dataKey']."'".PHP_EOL;
endif;
?>
    </script>

<?=$this->renderArray('js', 4); ?>
    <script type="text/javascript">
        var g_user = <?=$this->gUser; ?>;
<?php
if ($this->gFavorites):
    echo '        g_favorites = '.$this->gFavorites.';'.PHP_EOL;
endif;
?>
    </script>

<?php if ($this->hasAnalytics): ?>
    <script>
        $WH.Track.gaInit();
    </script>

<?php
endif;

if ($this->rss):
?>

    <link rel="alternate" type="application/rss+xml" title="<?=$this->concat('title', ' - '); ?>" href="<?=$this->rss; ?>"/>

<?php
endif;
?>
