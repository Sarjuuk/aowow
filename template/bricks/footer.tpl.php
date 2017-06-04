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
			<div class="footer-links linklist">
				<a href="?aboutus"><?=Lang::main('aboutUs'); ?></a>|<a href="https://github.com/Sarjuuk/aowow" target="_blank">Github</a>|<a href="#" id="footer-links-language"><?=Lang::main('language'); ?></a>
			</div>
			<div class="footer-copy">
				&#12484; <?=date("Y"); ?> Aowow<br />rev. <?=AOWOW_REVISION; ?>
			</div>
        </div>
    </div><!-- #wrapper .nosidebar -->
    </div><!-- #layout-inner -->
</div><!-- #layout .nosidebar -->

<noscript>
    <div id="noscript-bg"></div>
    <div id="noscript-text"><?php echo Lang::main('noJScript'); ?></div>
</noscript>

<script type="text/javascript">DomContentLoaded.now()</script>
</body>
</html>
