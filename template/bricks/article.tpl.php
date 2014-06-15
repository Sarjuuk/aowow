<?php
if (!empty($this->article)):
?>
    <div id="article-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?php echo Util::jsEscape($this->article['text']); ?>", "article-generic", {
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
