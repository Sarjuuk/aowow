<?php
    namespace Aowow\Template;

    /** @var PageTemplate $this */
?>

<?php
if ($this->headIcons):
    foreach ($this->headIcons as $k => $v):
        echo '<div id="h1-icon-'.$k.'" class="h1-icon"></div>'.PHP_EOL;
    endforeach;
?>

<script type="text/javascript">//<![CDATA[

<?php
    foreach ($this->headIcons as $k => $v):
        echo "\$WH.ge('h1-icon-".$k."').appendChild(Icon.create('".$this->escJS($v)."', 1));".PHP_EOL;
    endforeach;
?>

//]]></script>

<?php endif; ?>
