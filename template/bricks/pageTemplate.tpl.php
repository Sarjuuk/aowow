<?php
    namespace Aowow\Template;

    /** @var PageTemplate $this */
?>

            <script type="text/javascript">//<![CDATA[

<?php
if ($this->contribute & CONTRIBUTE_CO):
    echo '                var lv_comments = '.$this->community['co'].';'.PHP_EOL;
endif;
if ($this->contribute & CONTRIBUTE_SS):
    echo '                var lv_screenshots = '.$this->community['ss'].';'.PHP_EOL;
endif;
if ($this->contribute & CONTRIBUTE_VI):
    echo '                var lv_videos = '.$this->community['vi'].';'.PHP_EOL;
endif;

if ($this->gPageInfo):
    echo '                var g_pageInfo = '.$this->json('gPageInfo', varRef: true).';'.PHP_EOL;

    // set by ItemBaseEndpoint
    if ($this->user::isLoggedIn() && !empty($this->redButtons[BUTTON_EQUIP])):
        echo "                \$(document).ready(function() { pr_addEquipButton('equip-pinned-button', ".$this->typeId."); });".PHP_EOL;
    endif;
endif;

if ($this->pageTemplate):
    if ($this->locale->value && $this->pageTemplate['pageName'] != 'home'):
        echo '                Locale.set('.$this->locale->value.');'.PHP_EOL;
    endif;
    echo '                PageTemplate.set('.$this->json('pageTemplate', varRef: true).');'.PHP_EOL;
endif;
    echo '                PageTemplate.init();'.PHP_EOL;

if (isset($fiQuery) && count($fiMenuItem) > 1 && array_slice($fiMenuItem, 0, 2) == [1, 5]):
    echo "                \$(document).ready(function(){ Menu.modifyUrl(Menu.findItem(mn_path, ".$this->json($fiMenuItem)."), { filter: '".$this->escJS($fiQuery)."'}, { onAppendCollision: fi_mergeFilterParams }) });".PHP_EOL;
elseif (isset($fiQuery)):
    echo "                Menu.modifyUrl(Menu.findItem(mn_database, ".$this->json($fiMenuItem)."), { filter: '+=".$this->escJS($fiQuery)."' }, { onAppendCollision: fi_mergeFilterParams, onAppendEmpty: fi_setFilterParams, menuUrl: Menu.getItemUrl(Menu.findItem(mn_database, ".$this->json($fiMenuItem).")) });".PHP_EOL;
endif;
?>

            //]]></script>
