<?php
    namespace Aowow\Template;

    use \Aowow\Lang;
?>

        <div class="footer">
<?php
if ($this->pageStats):
    echo "            <table style=\"margin:auto;\">\n";

    if ($x = $this->pageStats['sql']):
        echo '                <tr><td style="text-align:left;">'.Lang::main('numSQL') .'</td><td>'.$x['count']."</td></tr>\n";
        echo '                <tr><td style="text-align:left;">'.Lang::main('timeSQL').'</td><td>'.$x['time']."</td></tr>\n";
    endif;

    if ($x = $this->pageStats['time']):
        echo '                <tr><td style="text-align:left;">Page generated in</td><td>'.$x."</td></tr>\n";
    endif;

    if ($this->pageStats['cache'] && $this->pageStats['cache'][0] == CACHE_MODE_FILECACHE):
        echo "                <tr><td style=\"text-align:left;\">Stored in filecache</td><td>".$this->pageStats['cache'][1]."</td></tr>\n";
    elseif ($this->pageStats['cache'] && $this->pageStats['cache'][0] == CACHE_MODE_MEMCACHED):
        echo "                <tr><td style=\"text-align:left;\">Stored in Memcached</td><td>".$this->pageStats['cache'][1]."</td></tr>\n";
    endif;

    echo "            </table>\n";
endif;
?>
        </div>
    </div><!-- #wrapper .nosidebar -->
    </div><!-- #layout-inner -->
</div><!-- #layout .nosidebar -->

<noscript>
    <div id="noscript-bg"></div>
    <div id="noscript-text"><?=Lang::main('noJScript'); ?></div>
</noscript>

<?=$this->localizedBrickIf($this->consentFooter, 'consent'); ?>

<?php if ($this->dbProfiles): ?>

<script type="text/javascript">
    window.open("/", "SqlLog", "width=1800,height=200,top=100,left=100,status=no,location=no,toolbar=no,menubar=no")?.document?.write('<?=$this->dbProfiles;?>');
</script>
<?php endif; ?>
</body>
</html>
