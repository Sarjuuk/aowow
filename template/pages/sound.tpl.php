<?php $this->brick('header'); ?>

    <div class="main" id="main">
        <div class="main-precontents" id="main-precontents"></div>
        <div class="main-contents" id="main-contents">

<?php
    $this->brick('announcement');

    $this->brick('pageTemplate');
?>

            <div class="text">
<?php
    $this->brick('redButtons');
?>

                <h1><?=$this->name; ?></h1>

<?php
    $this->brick('article');

    if ($this->special):
?>
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

<?php
    else:

    if (!empty($this->map)):
        $this->brick('mapper');
    endif;
?>
                <ol id="soundfilelist"></ol>
                <div id="mainsound"></div>
                <script type="text/javascript">//<![CDATA[
                    var soundpaths = g_sounds[<?=$this->typeId; ?>].files;
                    soundpaths.sort(function(a, b) { return $WH.stringCompare(a.title, b.title) || $WH.stringCompare(a.id, b.id); });
                    var sounddialog = new Dialog();

                    Dialog.templates.sound = {
                        title: LANG.types[19][0],
                        buttons: [['cancel', LANG.close]],

                        fields:
                            [
                                {
                                    id: 'ingamelink',
                                    type: 'text',
                                    label: 'Ingame Link',
                                    size: 40
                                }
                            ],

                        onInit: function(form)
                        {

                        },

                        onShow: function(form) {
                            setTimeout(function() { $WH.safeSelect(form.ingamelink); }, 50);
                            setTimeout(Lightbox.reveal, 100);
                        }
                    };

                    function showSoundLink(idx)
                    {
                        // var data = { 'ingamelink': '/script PlaySoundKitID(<?=$this->typeId; ?>)' }; // aowow - PlaySoundKitID() not available in 3.3.5
                        var data = { 'ingamelink': '/script PlaySoundFile("' + soundpaths[idx].path + '", "master")' };
                        sounddialog.show('sound', { data: data, onSubmit: $WH.rf });
                    }

                    (new AudioControls()).init(soundpaths,$WH.ge('mainsound'));

                    (function(){
                        var ol = $WH.ge('soundfilelist');
                        for (var x = 0; x < soundpaths.length; x++)
                        {
                            var li = $WH.ce('li');
                            var a = $WH.ce('a');
                            a.href = 'javascript:;';
                            $WH.aE(a, 'click', (function(xy) { return function() { showSoundLink(xy); } })(x));
                            $WH.st(a, soundpaths[x].title);
                            $WH.ae(li, a);
                            $WH.ae(ol, li);
                        }
                    })();

                //]]></script>
                <h2 class="clear"><?=Lang::main('related'); ?></h2>

            </div>

<?php
        $this->brick('lvTabs', ['relTabs' => true]);

        $this->brick('contribute');
    endif;
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
