    <div id="footer">
{if isset($mysql)}
        {$lang.numSQL}: {$mysql.count}<br>
        {$lang.timeSQL}: {$mysql.time}
{/if}
    </div>
</div>
</div>
<!--[if lte IE 6]></td><th class="ie6layout-th"></th></tr></table><![endif]-->

<noscript>
    <div id="noscript-bg"></div>
    <div id="noscript-text">{$lang.noJScript}</div>
</noscript>

<script type="text/javascript">DomContentLoaded.now()</script>
</body>
</html>
