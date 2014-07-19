        <div id="footer">
<?php
if (User::isInGroup(U_GROUP_EMPLOYEE) && ($this->time || isset($this->mysql) || $this->isCached)):
    echo "            <table style=\"margin:auto;\">\n";

    if (isset($this->mysql)):
        echo '                <tr><td style="text-align:left;">'.Lang::$main['numSQL'] .'</td><td>'.$this->mysql['count']."</td></tr>\n";
        echo '                <tr><td style="text-align:left;">'.Lang::$main['timeSQL'].'</td><td>'.Util::formatTime($this->mysql['time'] * 1000, true)."</td></tr>\n";
    endif;

    if ($this->time):
        echo '                <tr><td style="text-align:left;">Page generated in</td><td>'.Util::formatTime($this->time * 1000, true)."</td></tr>\n";
    endif;

    if ($this->isCached):
        echo "                <tr><td style=\"text-align:left;\">(rebuild from cache)</td><td></td></tr>\n";
    endif;

    echo "            </table>\n";
endif;
?>
        </div>
    </div><!-- #wrapper .nosidebar -->
</div><!-- #layout -->
<!--[if lte IE 6]></td><th class="ie6layout-th"></th></tr></table><![endif]-->

<noscript>
    <div id="noscript-bg"></div>
    <div id="noscript-text"><?php echo Lang::$main['noJScript']; ?></div>
</noscript>

<script type="text/javascript">DomContentLoaded.now()</script>
</body>
</html>
