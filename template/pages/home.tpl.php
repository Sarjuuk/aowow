<!DOCTYPE html>
<html>
<head>
<?php $this->brick('head'); ?>

</head>
<body class="home<?php echo User::isPremium() ? ' premium-logo' : null; ?>">
    <div id="layers"></div>
    <div class="home-wrapper">
        <h1>Aowow</h1>
        <div class="home-logo" id="home-logo"></div>

<?php $this->brick('announcement'); ?>

        <div class="home-search" id="home-search">
            <form method="get" action="index.php">
                <input type="text" name="search" />
            </form>
        </div>

        <div class="home-menu" id="home-menu"></div>

<?php if ($this->news): ?>
        <div class="pad"></div>

        <div class="home-featuredbox<?php echo empty($this->news['extraWide']) ? null : ' home-featuredbox-extended';?>" style="background-image: url(<?php echo $this->news['bgImgUrl'];?>);" id="home-featuredbox">
<?php if ($this->news['overlays']): ?>
            <div class="home-featuredbox-links">
<?php
        foreach ($this->news['overlays'] as $o):
                echo '                <a href="'.$o['url'].'" title="'.$o['title'].'" style="left: '.$o['left'].'px; top: 18px; width:'.$o['width'].'px; height: 160px"></a>'."\n";
                echo '                <var style="left: '.$o['left'].'px; top: 18px; width:'.$o['width'].'px; height: 160px"></var>'."\n";
        endforeach;
?>
            </div>
<?php endif; ?>
            <div class="home-featuredbox-inner text" id="news-generic"></div>
        </div>

        <script type="text/javascript">//<![CDATA[
<?php
    if (User::$localeId):
        echo "            Locale.set(".User::$localeId.");\n";
    endif;
    echo $this->writeGlobalVars();
?>
            Markup.printHtml(<?php echo json_encode($this->news['text']); ?>, 'news-generic', { allow: Markup.CLASS_ADMIN });
        //]]></script>
<?php endif; ?>
    </div>

    <div class="toplinks linklist"><?php $this->brick('headerMenu'); ?></div>

    <div class="footer">
        <div class="footer-links linklist">
            <a href="?aboutus"><?php echo lang::$main['aboutUs']; ?></a>|
            <a href="#" id="footer-links-language"><?php echo Lang::$main['language']; ?></a>
        </div>
        <div class="footer-copy">&#12484; 2014 Aowow</div>
    </div>

<?php $this->brick('pageTemplate'); ?>

    <noscript><div id="noscript-bg"></div><div id="noscript-text"><b><?php echo Lang::$main['jsError']; ?></div></noscript>
</body>
</html>
