<?php
    namespace Aowow\Template;

    use \Aowow\Lang;

    $this->brick('header');
?>
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

                <h1 class="h1-icon"><?=$this->h1; ?></h1>

                <div class="clear"></div>

<?php $this->brick('markup', ['markup' => $this->article]); ?>


                <h3><?=Lang::enchantment('details'); ?></h3>

                <table class="grid" id="spelldetails">
                    <colgroup>
                        <col width="8%" />
                        <col width="42%" />
                        <col width="50%" />
                    </colgroup>
<?php
if ($this->activation):
?>
                    <tr>
                        <th><?=Lang::enchantment('activation'); ?></th>
                        <td colspan="2"><?=$this->activation; ?></td>
                    </tr>
<?php
endif;

foreach ($this->effects as $i => $e):
?>
                    <tr>
                        <th><?=Lang::spell('_effect').' #'.$i; ?></th>
                        <td colspan="3" style="line-height: 17px">
<?php
    echo '                            '.$e['name'].($e['tip'] ? Lang::main('colon').'(<span '.($e['tip'][0] ? 'class="tip" ' : '').'id="efftip-'.$i.'"></span>)' : '').'<small>';

    if ($e['value']):
        echo '<br />'.Lang::spell('_value').Lang::main('colon').$e['value'];
    endif;

    if ($e['proc']):
        echo '<br />';

        if ($e['proc'] < 0):
            echo Lang::spell('ppm', [-$e['proc']]);
        elseif ($e['proc'] < 100.0):
            echo Lang::spell('procChance', [$e['proc']]);
        endif;
    endif;

    echo "</small>\n";

    if ($e['tip']):
?>
                            <script type="text/javascript">
<?php
        echo "                                \$WH.ae(\$WH.ge('efftip-".$i."'), \$WH.ct(LANG.traits['".$e['tip'][1]."'][0]));\n";
        if ($e['tip'][0]):
            echo "                                g_addTooltip(\$WH.ge('efftip-".$i."'), 'Object: ".$e['tip'][0]."', 'q');\n";
        endif;
?>
                            </script>
<?php
    endif;


    if ($e['icon']):
?>
                            <table class="icontab">
                                <tr>
                                    <?=$e['icon']->renderContainer(0, $i); ?>
                                    <th></th><td></td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                <?=$e['icon']->renderJS(); ?>
                            </script>
<?php
    endif;
?>
                        </td>
                    </tr>
<?php
endforeach;
?>
                </table>

                <h2 class="clear"><?=Lang::main('related'); ?></h2>
            </div>

<?php
$this->brick('lvTabs');

$this->brick('contribute');
?>
            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
