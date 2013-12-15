<h3>{$lang.reagents}</h3>

{if $enhanced}
<style type="text/css">
    .iconlist-col                                         {ldelim} float: left; width: 31%; margin-right: 2%; {rdelim}
    .iconlist                                             {ldelim} border-collapse: collapse; margin-top: 4px; {rdelim}
    .iconlist ul                                          {ldelim} margin: 0!important; padding: 0!important; {rdelim}
    .iconlist ul li                                       {ldelim} list-style-position: inside; list-style-type: square; padding-left: 13px; {rdelim}
    .iconlist th                                          {ldelim} min-width: 18px; white-space: nowrap; {rdelim}
    .iconlist td                                          {ldelim} padding: 4px 0 6px 0; {rdelim}
    .iconlist var                                         {ldelim} font-size: 1px; {rdelim}
    .iconlist .iconsmall                                  {ldelim} margin-right: 4px; {rdelim}
    .iconlist a.disclosure-on, .iconlist a.disclosure-off {ldelim} font-weight: normal; text-decoration: underline; {rdelim}
    .iconlist .iconlist ul li                             {ldelim} padding-left: 10px; {rdelim}
    .iconlist .iconlist th, .iconlist .iconlist td        {ldelim} font-size: 11px; {rdelim}
    .iconlist-col table th li                             {ldelim} list-style-position: outside; padding: 0; margin-left: 20px; {rdelim}
</style>
<script type="text/javascript">//<![CDATA[
function iconlist_showhide(spn) {ldelim}
    var
        tr,
        table,
        trs,
        s;

    tr = spn;

    while ((tr.parentNode) && tr.tagName.toUpperCase() != 'TR') {ldelim}
        tr = tr.parentNode;
    {rdelim}

    table = tr;

    while ((table.parentNode) && table.tagName.toUpperCase() != 'TABLE') {ldelim}
        table = table.parentNode;
    {rdelim}
    trs = table.getElementsByTagName('tr');

    var opening = spn.className.indexOf('disclosure-off') >= 0;
    var isSpell = tr.id.substr(tr.id.lastIndexOf('.')+1, 1) == '6';
    var isItem  = tr.id.substr(tr.id.lastIndexOf('.')+1, 1) == '3';

    if (opening) {ldelim}
        if (isSpell) {ldelim} //find any other open spells on this branch and close them
            for (var x = 0; x < trs.length; x++)
                if (trs[x].id.indexOf(tr.id.substr(0,tr.id.lastIndexOf('-'))) == 0) {ldelim} //sister spell
                    ns = trs[x].getElementsByTagName('div');
                    for (var y = 0; y < ns.length; y++)
                        if (ns[y].className == 'iconlist-tree disclosure-on')
                            iconlist_showhide(ns[y]);
                {rdelim}
        {rdelim}
        if (isItem) {ldelim} //expanding item to spells, so cross out item
            tr.style.textDecoration = 'line-through';
        {rdelim}

        for (var x = 0; x < trs.length; x++) {ldelim}
            if ((trs[x].id.indexOf(tr.id) == 0) && (trs[x].id.substr(tr.id.length+1).indexOf('.') < 0)) {ldelim}
                trs[x].style.display = '';
            {rdelim}
        {rdelim}
        spn.className = 'iconlist-tree disclosure-on';

        if (isItem) {ldelim} //check to see if there is one spell for this item.. if so, expand it
            var spellCount = 0; var lastTr = 0;
            for (var x = 0; x < trs.length; x++) {ldelim}
                if ((trs[x].id.indexOf(tr.id+'.6') == 0) && (trs[x].id.lastIndexOf('-') == tr.id.length + 2)) {ldelim}
                    spellCount++;
                    lastTr = x;
                {rdelim}
            {rdelim}

            if (spellCount == 1) {ldelim}
                ns = trs[lastTr].getElementsByTagName('div');
                for (var y = 0; y < ns.length; y++) {ldelim}
                    if (ns[y].className == 'iconlist-tree disclosure-off') {ldelim}
                        iconlist_showhide(ns[y]);
                    {rdelim}
                {rdelim}
            {rdelim}
        {rdelim}

    {rdelim}
    else {ldelim}
        for (var x = 0; x < trs.length; x++) {ldelim}
            if ((trs[x].id.indexOf(tr.id) == 0) && (trs[x].id != tr.id)) {ldelim}
                trs[x].style.display = 'none';
                trs[x].style.textDecoration = '';
                ns = trs[x].getElementsByTagName('div');
                for (var y = 0; y < ns.length; y++) {ldelim}
                    if (ns[y].className == 'iconlist-tree disclosure-on') {ldelim}
                        ns[y].className = 'iconlist-tree disclosure-off';
                    {rdelim}
                {rdelim}
            {rdelim}
        {rdelim}
        spn.className = 'iconlist-tree disclosure-off';
        tr.style.textDecoration = '';
    {rdelim}
{rdelim}

function iconlist_expandall(tableid,doexpand) {ldelim}
    var table = document.getElementById(tableid);
    var trs = table.getElementsByTagName('tr');

    if (doexpand) {ldelim}
        iconlist_expandall(tableid,false);
        var firstSpells = new Array();
        for (var x = 0; x < trs.length; x++) {ldelim}
            if (!trs[x].id) {ldelim}
                continue;
            {rdelim}
            if (trs[x].style.display == 'none') {ldelim}
                continue;
            {rdelim}

            if (trs[x].id.substr(trs[x].id.lastIndexOf('.')+1,1) == '6') {ldelim} //is spell
                var skipOut  = false;
                var thisItem = trs[x].id.substr(0,trs[x].id.lastIndexOf('.'));

                for (var y = 0; y < firstSpells.length; y++) {ldelim}
                    if (firstSpells[y] == thisItem) {ldelim}
                        skipOut = true;
                        break;
                    {rdelim}
                {rdelim}

                if (skipOut) {ldelim}
                    continue;
                {rdelim}

                firstSpells.push(thisItem);
            {rdelim}

            var spn = document.getElementById('spn.'+trs[x].id);
            if (spn && spn.className.indexOf('disclosure-off') >= 0) {ldelim}
                iconlist_showhide(spn);
            {rdelim}
        {rdelim}
    {rdelim}
    else {ldelim}
        for (var x = 0; x < trs.length; x++) {ldelim}
            if (!trs[x].id) {ldelim}
                continue;
            {rdelim}
            if (trs[x].id.indexOf('.') != trs[x].id.lastIndexOf('.')) {ldelim}
                continue;
            {rdelim}

            var spn = document.getElementById('spn.'+trs[x].id);
            if (spn && spn.className.indexOf('disclosure-on') >= 0) {ldelim}
                iconlist_showhide(spn);
            {rdelim}
        {rdelim}
    {rdelim}
{rdelim}
//]]></script>
{/if}

<table class="iconlist" id="reagent-list-generic">
{if $enhanced}
    <tr>
        <th></th>
        <th align="left">
            <input type="button" style="font-size: 11px; margin-right: 0.5em" onclick="iconlist_expandall('reagent-list-generic',true);" value="{$lang._expandAll}">
            <input type="button" style="font-size: 11px; margin-right: 0.5em" onclick="iconlist_expandall('reagent-list-generic',false);" value="{$lang._collapseAll}">
        </th>
    </tr>
{/if}
{foreach from=$reagents key='k' item='itr'}
    {strip}<tr id="reagent-list-generic.{$itr.path}"{if $itr.level} style="display: none"{/if}>
        <th align="right" id="iconlist-icon{$k}"></th>
        <td{if $itr.level} style="padding-left: {$itr.level}em"{/if}>
{if !empty($itr.final) && $enhanced}
            <div class="iconlist-tree" style="width: 15px; float: left">&nbsp;</div>
{elseif $enhanced}
            <div class="iconlist-tree disclosure-off" onclick="iconlist_showhide(this);" style="padding-left: 0; cursor: pointer; width: 15px; float: left" id="spn.reagent-list-generic.{$itr.path}">&nbsp;</div>
{/if}
            <span class="q{if $itr.type == $smarty.const.TYPE_ITEM}{$itr.quality}{/if}"><a href="?{$itr.typeStr}={$itr.typeId}">{$itr.name}</a></span>{if $itr.qty > 1}&nbsp;({$itr.qty}){/if}
        </td>
    </tr>{/strip}
{/foreach}
</table>

<script type="text/javascript">//<![CDATA[
{foreach from=$reagents key='k' item='itr'}
    $WH.ge('iconlist-icon{$k}').appendChild(g_{$itr.typeStr}s.createIcon({$itr.typeId}, 0, {$itr.qty}));
{/foreach}
//]]></script>

<div class="clear"></div>
