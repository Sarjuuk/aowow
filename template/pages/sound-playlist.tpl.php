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
?>

            <div class="text">
                <h1><?=$this->h1; ?></h1>

<?php $this->brick('markup', ['markup' => $this->article]); ?>

<div id="playlistcontrols" style="margin: 20px"></div><div id="playlisttracks"></div>
<script type="text/javascript">//<![CDATA[
g_audioplaylist.setAudioControls($WH.ge('playlistcontrols'));
(function(){
    var delline = function()
    {
        var li = this.parentNode;
        var siblings = li.parentNode.childNodes;

        for (var id = 0; id < siblings.length; id++)
            if (siblings[id] === li)
                break;

        g_audioplaylist.deleteSound(id);
        li.parentNode.removeChild(li);
    }

    var l = g_audioplaylist.getList();
    var ol = $WH.ce('ol');
    var s, li;
    for (var x in l)
    {
        li = $WH.ce('li');

        s = $WH.ce('span');
        s.className = 'icon-delete';
        s.style.cursor = 'pointer';
        $WH.Tooltip.simple(s, LANG.delete, 'q2');
        $WH.aE(s, 'click', delline);
        $WH.ae(li, s);

        s = $WH.ce('span');
        $WH.st(s, l[x]);
        $WH.ae(li, s);

        $WH.ae(ol, li);
    }
    $WH.ae($WH.ge('playlisttracks'),ol);
})();
//]]></script></div>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
