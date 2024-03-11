        <div class="footer">
<?php
if (User::isInGroup(U_GROUP_EMPLOYEE) && ($this->time || isset($this->mysql) || $this->isCached)):
    echo "            <table style=\"margin:auto;\">\n";

    if (isset($this->mysql)):
        echo '                <tr><td style="text-align:left;">'.Lang::main('numSQL') .'</td><td>'.$this->mysql['count']."</td></tr>\n";
        echo '                <tr><td style="text-align:left;">'.Lang::main('timeSQL').'</td><td>'.Util::formatTime($this->mysql['time'] * 1000, true)."</td></tr>\n";
    endif;

    if ($this->time):
        echo '                <tr><td style="text-align:left;">Page generated in</td><td>'.Util::formatTime($this->time * 1000, true)."</td></tr>\n";
    endif;

    if ($this->cacheLoaded && $this->cacheLoaded[0] == CACHE_MODE_FILECACHE):
        echo "                <tr><td style=\"text-align:left;\">reloaded from filecache</td><td>created".Lang::main('colon').date(Lang::main('dateFmtLong'), $this->cacheLoaded[1])."</td></tr>\n";
    elseif ($this->cacheLoaded && $this->cacheLoaded[0] == CACHE_MODE_MEMCACHED):
        echo "                <tr><td style=\"text-align:left;\">reloaded from memcached</td><td>created".Lang::main('colon').date(Lang::main('dateFmtLong'), $this->cacheLoaded[1])."</td></tr>\n";
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

<script type="text/javascript">DomContentLoaded.now()</script>
<?php
if (CFG_DEBUG && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN)):
?>
<script type="text/javascript">
    window.open("/", "SqlLog", "width=1800,height=200,top=100,left=100,status=no,location=no,toolbar=no,menubar=no").document.write('<?=DB::getLogs();?>');
</script>
<?php
endif;
?>
</body>
</html>
