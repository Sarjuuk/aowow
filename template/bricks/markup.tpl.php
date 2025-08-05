
<?php if ($markup): ?>
    <div id="<?=$markup->getParent(); ?>" class="left"></div>
    <script type="text/javascript">//<![CDATA[
        <?=$markup;?>
    //]]></script>
    <div class="pad2"></div>
<?php endif; ?>
