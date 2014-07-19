<?php
if (!empty($this->headIcons)):
    foreach ($this->headIcons as $k => $v):
        echo '<div id="h1-icon-'.$k."\" class=\"h1-icon\"></div>\n";
    endforeach;
?>
<script type="text/javascript">//<![CDATA[
<?php
    foreach ($this->headIcons as $k => $v):
        echo "\$WH.ge('h1-icon-".$k."').appendChild(Icon.create('".Util::jsEscape($v)."', 1));\n";
    endforeach;
?>
//]]></script>
<?php endif; ?>
