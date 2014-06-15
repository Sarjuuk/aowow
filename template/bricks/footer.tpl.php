        <div id="footer">
<?php
if (isset($this->mysql)):
    echo Lang::$main['numSQL'] . Lang::$main['colon']. $this->mysql['count']."<br>\n";
    echo Lang::$main['timeSQL']. Lang::$main['colon']. Util::formatTime($this->mysql['time'] * 1000)."\n";
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
