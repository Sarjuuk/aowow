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

                <h1><?php echo $this->name.($this->subname ? ' &lt;'.$this->subname.'&gt;' : null); ?></h1>

<?php
    $this->brick('article');

if (is_array($this->position)):
    echo '                <div>'.Lang::$npc['difficultyPH'].' <a href="?npc='.$this->position[0].'">'.$this->position[1]."</a>.</div>\n";
?>
                <div class="pad"></div>
<?php
elseif (!empty($this->position)):
?>
                <div>{#This_NPC_can_be_found_in#}<span id="locations">
                    {foreach from=$position item=zone name=zone}
                        <a href="javascript:;" onclick="
                        {if $zone.atid}
                            myMapper.update(
                                {
                                    zone:{$zone.atid}
                                    {if $zone.points}
                                        ,
                                    {/if}
                                {if $zone.points}
                                    coords:[
                                        {foreach from=$zone.points item=point name=point}
                                                [{$point.x},{$point.y},
                                                {
                                                    label:'$<br>
                                                    <div class=q0>
                                                        <small>
                                                            {if isset($point.r)}
                                                                {#Respawn#}:
                                                                {if isset($point.r.h)} {$point.r.h}{#hr#}{/if}
                                                                {if isset($point.r.m)} {$point.r.m}{#min#}{/if}
                                                                {if isset($point.r.s)} {$point.r.s}{#sec#}{/if}
                                                            {else}
                                                                {#Waypoint#}
                                                            {/if}
                                                            {if isset($point.events)}<br>{$point.events|escape:"quotes"}{/if}
                                                        </small>
                                                    </div>',type:'{$point.type}'
                                                }]
                                                {if !$smarty.foreach.point.last},{/if}
                                        {/foreach}
                                    ]
                                {/if}
                                });
                            $WH.ge('mapper-generic').style.display='block';
                        {else}
                            $WH.ge('mapper-generic').style.display='none';
                        {/if}
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
    echo '                '.Lang::$npc['unkPosition']."\n";
endif;

if ($this->quotes[0]):
?>
                <h3><a class="disclosure-off" onclick="return g_disclose($WH.ge('quotes-generic'), this)"><?php echo Lang::$npc['quotes'].' ('.$this->quotes[1]; ?>)</a></h3>
                <div id="quotes-generic" style="display: none"><ul>
<?php
    foreach ($this->quotes[0] as $group):
        if (count($group) > 1 && count($this->quotes[0]) > 1):
            echo "<ul>\n";
        endif;

        echo '<li>';

        $last = end($group);
        foreach ($group as $itr):
            echo '<div><span class="s'.$itr['type'].'">'.($itr['type'] != 4 ? $this->name.' '.Lang::$npc['textTypes'][$itr['type']].Lang::$main['colon'].($itr['lang'] ? '['.$itr['lang'].']' : null) : null).$itr['text'].'</span></div>';
            echo ($itr == $last) ? null : "</li>\n<li>";
        endforeach;

        echo "</li>\n";

        if (count($group) > 1 && count($this->quotes[0]) > 1):
            echo "</ul>\n";
        endif;

    endforeach;
?>
                </ul></div>
<?php
endif;

if ($this->reputation):
?>
                <h3><?php echo Lang::$main['gains']; ?></h3>
<?php
    echo Lang::$npc['gainsDesc'].Lang::$main['colon'];

    foreach ($this->reputation as $set):
        if (count($this->reputation) > 1):
            echo '<ul><li><span class="rep-difficulty">'.$set[0].'</span></li>';
        endif;

        echo '<ul>';

        foreach ($set[1] as $itr):
            echo '<li><div'.($itr['qty'] < 0 ? ' class="reputation-negative-amount"' : null).'><span>'.$itr['qty'].'</span> '.Lang::$npc['repWith'] .
                ' <a href="?faction='.$itr['id'].'">'.$itr['name'].'</a>'.($itr['cap'] && $itr['qty'] > 0 ? ' ('.sprintf(Lang::$npc['stopsAt'], $itr['cap']).')' : null).'</div></li>';
        endforeach;

        echo '</ul>';

        if (count($this->reputation) > 1):
            echo '</ul>';
        endif;
    endforeach;
endif;
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
