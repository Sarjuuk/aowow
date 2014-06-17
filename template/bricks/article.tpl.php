<?php
/* Util::jsEscape gets a bit greedy here */
if (!empty($this->article)):
?>
    <div id="article-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?php echo strtr(Util::jsEscape($this->article['text']), ['scr\\"+\\"ipt' => 'scr"+"ipt']); ?>", "article-generic", {
<?php
    foreach ($this->article['params'] as  $k => $v):
        echo $k.': '.($v[0] == '$' ? substr($v, 1) : "'".$v."'").",\n";
    endforeach;
?>
            allow: Markup.CLASS_ADMIN,
            dbpage: true
        });
    //]]></script>
    <div class="pad2"></div>
<?php
endif;
?>
