<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');

    $this->brick('infobox');
?>

            <div class="text">
<?php $this->brick('redButtons'); ?>

                <h1><?php echo $this->name; ?></h1>

<?php
$this->brick('article');

if ($this->positions):
?>
                <div>{#This_Object_can_be_found_in#}
                <span id="locations">
                    {foreach from=$object.position item=zone name=zone}
                        <a href="javascript:;" onclick="
                            myMapper.update(
                                {
                                {if $zone.atid}
                                    zone:{$zone.atid}
                                    {if $zone.points}
                                        ,
                                    {/if}
                                {else}
                                    show:false
                                {/if}
                                {if $zone.points}
                                    coords:[
                                        {foreach from=$zone.points item=point name=point}
                                                [{$point.x},{$point.y},
                                                {
                                                    label:'$<br>
                                                    <div class=q0>
                                                        <small>{#Respawn#}:
                                                            {if isset($point.r.h)} {$point.r.h}{#hr#}{/if}
                                                            {if isset($point.r.m)} {$point.r.m}{#min#}{/if}
                                                            {if isset($point.r.s)} {$point.r.s}{#sec#}{/if}
                                                            {if isset($point.events)}<br>{$point.events|escape:"quotes"}{/if}
                                                        </small>
                                                    </div>',type:'{$point.type}'
                                                }]
                                                {if !$smarty.foreach.point.last},{/if}
                                        {/foreach}
                                    ]
                                {/if}
                                });
                            g_setSelectedLink(this, 'mapper'); return false" onmousedown="return false">
                            {$zone.name}</a>{if $zone.population > 1}&nbsp;({$zone.population}){/if}{if $smarty.foreach.zone.last}.{else}, {/if}
                    {/foreach}
                </span></div>

                <div id="mapper-generic"></div>
                <div class="clear"></div>

                <script type="text/javascript">
                    var myMapper = new Mapper({parent: 'mapper-generic', zone: '{$position[0].atid}'});
                    $WH.gE($WH.ge('locations'), 'a')[0].onclick();
                </script>
<?php
else:
    echo Lang::$gameObject['unkPosition'];
endif;

$this->brick('book');
?>

                <h2 class="clear"><?php echo Lang::$main['related']; ?></h2>
            </div>

<?php
$this->brick('lvTabs', ['relTabs' => true]);

$this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
