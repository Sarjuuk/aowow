<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>
<!DOCTYPE html>
<html>
<head>
<?php $this->brick('head'); ?>

</head>
<body class="home<?=($this->user::isPremium() ? ' premium-logo' : ''); ?>">
    <div id="layers"></div>
<?php
if ($this->homeTitle):
    echo "    <script>document.title = '".$this->homeTitle."';</script>\n";
endif;

if ($this->altHomeLogo):
?>
    <style type="text/css">
    .home-logo {
        background: url(<?=$this->altHomeLogo; ?>) no-repeat center 0 !important;
        margin-bottom: 1px !important;
    }
    </style>
<?php endif; ?>
    <div class="home-wrapper">
        <h1><?=$this->concat('title'); ?></h1>
        <div class="home-logo" id="home-logo"></div>

<?php $this->brick('announcement'); ?>

        <div class="home-search" id="home-search">
            <form method="get">
                <input type="text" name="search" />
            </form>
        </div>

        <div class="home-menu" id="home-menu"></div>

<?php if ($this->oneliner): ?>
        <p class="home-oneliner text" id="home-oneliner"></p>
        <script type="text/javascript">//<![CDATA[
            <?=$this->oneliner; ?>
        //]]></script>

<?php elseif ($this->featuredBox): ?>
       <div class="pad"></div>
<?php
endif;

if ($this->featuredBox):
?>
        <div class="home-featuredbox<?=$this->featuredBox['extended'] ? ' home-featuredbox-extended' : ''; ?>" style="background-image: url(<?=$this->featuredBox['boxBG']; ?>);" id="home-featuredbox">
<?php if ($this->featuredBox['overlays']): ?>
            <div class="home-featuredbox-links">
<?php
        foreach ($this->featuredBox['overlays'] as ['url' => $u, 'title' => $t, 'left' => $l, 'width' => $w]):
                echo '                <a href="'.$u.'" title="'.$t.'" style="left: '.$l.'px; top: 18px; width:'.$w.'px; height: 160px"></a>'."\n";
                echo '                <var style="left: '.$l.'px; top: 18px; width:'.$w.'px; height: 160px"></var>'."\n";
        endforeach;
?>
            </div>
<?php endif; ?>
            <div class="home-featuredbox-inner text" id="news-generic"></div>
        </div>

<?php
endif;
?>
        <script type="text/javascript">//<![CDATA[
<?php
if ($this->locale->value):
    echo "            Locale.set(".$this->locale->value.");\n";
endif;
echo $this->renderGlobalVars(12);

if ($this->featuredBox):
    echo "            ".$this->featuredBox['markup'];
endif;
?>
        //]]></script>
    </div>

    <div class="toplinks linklist"><?php $this->brick('headerMenu'); ?></div>

    <div class="footer">
        <div class="footer-links linklist">
            <a href="?aboutus"><?=Lang::main('aboutUs'); ?></a>|<a href="https://github.com/Sarjuuk/aowow" target="_blank">Github</a>|<a href="#" id="footer-links-language"><?=Lang::main('language'); ?></a>
        </div>
        <div class="footer-copy">
            &#12484; 2026 Aowow<br />rev. <?=AOWOW_REVISION; ?>
        </div>
    </div>

<?php $this->brick('pageTemplate'); ?>

<?php $this->localizedBrickIf($this->consentFooter, 'consent'); ?>

    <noscript><div id="noscript-bg"></div><div id="noscript-text"><b><?=Lang::main('jsError'); ?></b></div></noscript>
</body>
</html>
