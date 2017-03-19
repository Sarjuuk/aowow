<?php
if (!empty($this->article)):
?>
    <div id="article-generic" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        Markup.printHtml("<?=strtr($this->article['text'], ['scr\\"+\\"ipt' => 'scr"+"ipt']);?>", "article-generic", <?=Util::toJSON($this->article['params']);?>);
    //]]></script>
    <div class="pad2"></div>
<?php
endif;
?>
