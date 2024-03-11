<h3><?=Lang::spell('reagents'); ?></h3>

<?php
if ($enhanced):
?>
<style type="text/css">
    .iconlist-col                                         { float: left; width: 31%; margin-right: 2%; }
    .iconlist                                             { border-collapse: collapse; margin-top: 4px; }
    .iconlist ul                                          { margin: 0!important; padding: 0!important; }
    .iconlist ul li                                       { list-style-position: inside; list-style-type: square; padding-left: 13px; }
    .iconlist th                                          { min-width: 18px; white-space: nowrap; }
    .iconlist td                                          { padding: 4px 0 6px 0; }
    .iconlist var                                         { font-size: 1px; }
    .iconlist .iconsmall                                  { margin-right: 4px; }
    .iconlist a.disclosure-on, .iconlist a.disclosure-off { font-weight: normal; text-decoration: underline; }
    .iconlist .iconlist ul li                             { padding-left: 10px; }
    .iconlist .iconlist th, .iconlist .iconlist td        { font-size: 11px; }
    .iconlist-col table th li                             { list-style-position: outside; padding: 0; margin-left: 20px; }
</style>
<script type="text/javascript">//<![CDATA[
function iconlist_showhide(spn) {
    var
        tr,
        table,
        trs,
        s;

    tr = spn;

    while ((tr.parentNode) && tr.tagName.toUpperCase() != 'TR') {
        tr = tr.parentNode;
    }

    table = tr;

    while ((table.parentNode) && table.tagName.toUpperCase() != 'TABLE') {
        table = table.parentNode;
    }
    trs = table.getElementsByTagName('tr');

    var opening = spn.className.indexOf('disclosure-off') >= 0;
    var isSpell = tr.id.substr(tr.id.lastIndexOf('.')+1, 1) == '6';
    var isItem  = tr.id.substr(tr.id.lastIndexOf('.')+1, 1) == '3';

    if (opening) {
        if (isSpell) { //find any other open spells on this branch and close them
            for (var x = 0; x < trs.length; x++)
                if (trs[x].id.indexOf(tr.id.substr(0,tr.id.lastIndexOf('-'))) == 0) { //sister spell
                    ns = trs[x].getElementsByTagName('div');
                    for (var y = 0; y < ns.length; y++)
                        if (ns[y].className == 'iconlist-tree disclosure-on')
                            iconlist_showhide(ns[y]);
                }
        }
        if (isItem) { //expanding item to spells, so cross out item
            tr.style.textDecoration = 'line-through';
        }

        for (var x = 0; x < trs.length; x++) {
            if ((trs[x].id.indexOf(tr.id) == 0) && (trs[x].id.substr(tr.id.length+1).indexOf('.') < 0)) {
                trs[x].style.display = '';
            }
        }
        spn.className = 'iconlist-tree disclosure-on';

        if (isItem) { //check to see if there is one spell for this item.. if so, expand it
            var spellCount = 0; var lastTr = 0;
            for (var x = 0; x < trs.length; x++) {
                if ((trs[x].id.indexOf(tr.id+'.6') == 0) && (trs[x].id.lastIndexOf('-') == tr.id.length + 2)) {
                    spellCount++;
                    lastTr = x;
                }
            }

            if (spellCount == 1) {
                ns = trs[lastTr].getElementsByTagName('div');
                for (var y = 0; y < ns.length; y++) {
                    if (ns[y].className == 'iconlist-tree disclosure-off') {
                        iconlist_showhide(ns[y]);
                    }
                }
            }
        }

    }
    else {
        for (var x = 0; x < trs.length; x++) {
            if ((trs[x].id.indexOf(tr.id) == 0) && (trs[x].id != tr.id)) {
                trs[x].style.display = 'none';
                trs[x].style.textDecoration = '';
                ns = trs[x].getElementsByTagName('div');
                for (var y = 0; y < ns.length; y++) {
                    if (ns[y].className == 'iconlist-tree disclosure-on') {
                        ns[y].className = 'iconlist-tree disclosure-off';
                    }
                }
            }
        }
        spn.className = 'iconlist-tree disclosure-off';
        tr.style.textDecoration = '';
    }
}

function iconlist_expandall(tableid,doexpand) {
    var table = document.getElementById(tableid);
    var trs = table.getElementsByTagName('tr');

    if (doexpand) {
        iconlist_expandall(tableid,false);
        var firstSpells = new Array();
        for (var x = 0; x < trs.length; x++) {
            if (!trs[x].id) {
                continue;
            }
            if (trs[x].style.display == 'none') {
                continue;
            }

            if (trs[x].id.substr(trs[x].id.lastIndexOf('.')+1,1) == '6') { //is spell
                var skipOut  = false;
                var thisItem = trs[x].id.substr(0,trs[x].id.lastIndexOf('.'));

                for (var y = 0; y < firstSpells.length; y++) {
                    if (firstSpells[y] == thisItem) {
                        skipOut = true;
                        break;
                    }
                }

                if (skipOut) {
                    continue;
                }

                firstSpells.push(thisItem);
            }

            var spn = document.getElementById('spn.'+trs[x].id);
            if (spn && spn.className.indexOf('disclosure-off') >= 0) {
                iconlist_showhide(spn);
            }
        }
    }
    else {
        for (var x = 0; x < trs.length; x++) {
            if (!trs[x].id) {
                continue;
            }
            if (trs[x].id.indexOf('.') != trs[x].id.lastIndexOf('.')) {
                continue;
            }

            var spn = document.getElementById('spn.'+trs[x].id);
            if (spn && spn.className.indexOf('disclosure-on') >= 0) {
                iconlist_showhide(spn);
            }
        }
    }
}
//]]></script>
<?php
endif;
?>

<table class="iconlist" id="reagent-list-generic">
<?php
if ($enhanced):
?>
    <tr>
        <th></th>
        <th align="left">
            <input type="button" style="font-size: 11px; margin-right: 0.5em" onclick="iconlist_expandall('reagent-list-generic',true);" value="<?=Lang::spell('_expandAll'); ?>">
            <input type="button" style="font-size: 11px; margin-right: 0.5em" onclick="iconlist_expandall('reagent-list-generic',false);" value="<?=Lang::spell('_collapseAll'); ?>">
        </th>
    </tr>
<?php
endif;

foreach ($reagents as $k => $itr):
    echo '<tr id="reagent-list-generic.'.$itr['path'].'"'.($itr['level'] ? ' style="display: none"' : null).'><th align="right" id="iconlist-icon'.$k.'"></th>' .
         '<td'.($itr['level'] ? ' style="padding-left: '.$itr['level'].'em"' : null).'>';

    if (!empty($itr['final']) && $enhanced):
        echo '<div class="iconlist-tree" style="width: 15px; float: left">&nbsp;</div>';
    elseif ($enhanced):
        echo '<div class="iconlist-tree disclosure-off" onclick="iconlist_showhide(this);" style="padding-left: 0; cursor: pointer; width: 15px; float: left" id="spn.reagent-list-generic.'.$itr['path'].'">&nbsp;</div>';
    endif;

    echo '<span class="q'.($itr['type'] == Type::ITEM ? $itr['quality'] : null).'"><a href="?'.$itr['typeStr'].'='.$itr['typeId'].'">'.$itr['name'].'</a></span>'.($itr['qty'] > 1 ? '&nbsp;('.$itr['qty'].')' : null)."</td></tr>\n";
endforeach;
?>
</table>

<script type="text/javascript">//<![CDATA[
<?php
foreach ($reagents as $k => $itr):
    echo "\$WH.ge('iconlist-icon".$k."').appendChild(g_".$itr['typeStr']."s.createIcon(".$itr['typeId'].", 0, ".$itr['qty']."));\n";
endforeach;
?>
//]]></script>

<div class="clear"></div>
