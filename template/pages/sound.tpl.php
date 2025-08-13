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
<?php
    $this->brick('redButtons');
?>

                <h1><?=$this->h1; ?></h1>

<?php
    $this->brick('markup', ['markup' => $this->article]);

    $this->brickIf($this->map, 'mapper');
?>
                <ol id="soundfilelist"></ol>
                <div id="mainsound"></div>
                <script type="text/javascript">//<![CDATA[
                    var soundpaths = g_sounds[<?=$this->typeId; ?>].files;
                    soundpaths.sort(function(a, b) { return $WH.strcmp(a.title, b.title) || $WH.strcmp(a.id, b.id); });
                    // aowow - see $WH.strcmp - soundpaths.sort(function(a, b) { return $WH.stringCompare(a.title, b.title) || $WH.stringCompare(a.id, b.id); });
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

                    (new AudioControls()).init(soundpaths, $WH.ge('mainsound'));

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
        $this->brick('lvTabs');

        $this->brick('contribute');
?>

            <div class="clear"></div>
        </div><!-- main-contents -->
    </div><!-- main -->

<?php $this->brick('footer'); ?>
