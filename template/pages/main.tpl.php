<html>
<head>
<?php $this->brick('head'); ?>
    <style type="text/css">
        .menu-buttons a { border-color: black }
        .news { position: relative; text-align: left; width: 415px; height: 191px; margin: 30px auto 0 auto; background: url(static/images/<?php echo User::$localeString; ?>/mainpage-bg-news.jpg) no-repeat }
        .news-list { padding: 26px 0 0 26px; margin: 0 }
        .news-list li { line-height: 2em }
        .news-img1 { position: absolute; left: 60px; top: 155px; width: 172px; height: 17px }
        .news-img2 { position: absolute; left: 246px; top: 48px; width: 145px; height: 127px }
        .news-talent { position: absolute; left: 240px; top: 29px; width: 152px; height: 146px }
        .announcement { margin: auto; max-width: 1200px; padding: 0px 15px 15px 15px }
    </style>
</head>
<body>
    <div id="layers"></div>
    <div id="home">

<?php $this->brick('announcements'); ?>

        <span id="menu_buttons-generic" class="menu-buttons"></span>
        <script type="text/javascript">
            Menu.addButtons($WH.ge('menu_buttons-generic'), mn_path);
        </script>

        <div class="pad"></div>

        <form method="get" action="." onsubmit="if($WH.trim(this.elements[0].value) == '') return false">
            <input type="text" name="search" size="38" id="livesearch-generic" /><input type="submit" value="<?php echo Lang::$main['searchButton']; ?>" />
        </form>

        <script type="text/javascript">var _ = $WH.ge('livesearch-generic'); LiveSearch.attach(_); _.focus();</script>

<?php
if (!empty($this->news)):
?>
        <div class="news">
            <div class="news-list text">
                <ul>
<?php
    foreach ($this->news as $item):
        echo '                    <li><div>'.$item['text']."</div></li>\n";
    endforeach;
?>
                </ul>
            </div>
        </div>
<?php
endif;
?>

        <div id="toplinks" class="toplinks">
<?php $this->brick('headerMenu'); ?>
        </div>
    </div>

    <div id="footer">
    </div>
    <noscript><div id="noscript-bg"></div><div id="noscript-text"><b><?php echo Lang::$main['jsError']; ?></div></noscript>
    <script type="text/javascript">DomContentLoaded.now()</script>
</body>
</html>