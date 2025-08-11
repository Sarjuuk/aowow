var g_audiocontrols = {
    __windowloaded: false,
};
var g_audioplaylist = {};

// aowow - why is window.JSON here, wedged between the audio controls. It's only used for SearchBrowseButtons (and sourced by Listview)
if (!window.JSON) {
    window.JSON = {
        parse: function (sJSON) {
            return eval("(" + sJSON + ")");
        },

        stringify: function (obj) {
            if (obj instanceof Object)
            {
                var str = '';
                if (obj.constructor === Array)
                {
                    for (var i = 0; i < obj.length; str += this.stringify(obj[i]) + ',', i++) {}
                    return '[' + str.substr(0, str.length - 1) + ']';
                }
                if (obj.toString !== Object.prototype.toString)
                    return '"' + obj.toString().replace(/"/g, '\\$&') + '"';

                for (var e in obj)
                    str += '"' + e.replace(/"/g, '\\$&') + '":' + this.stringify(obj[e]) + ',';

                return '{' + str.substr(0, str.length - 1) + '}';
            }

            return typeof obj === 'string' ? '"' + obj.replace(/"/g, '\\$&') + '"' : String(obj);
        }
    }
}

AudioControls = function ()
{
    var fileIdx    = -1;
    var canPlay    = false;
    var looping    = false;
    var fullPlayer = false;
    var autoStart  = false;
    var controls   = {};
    var playlist   = [];
    var url        = '';

    function updatePlayer(_self, itr, doPlay)
    {
        var elAudio = $WH.ce('audio');
        elAudio.preload = 'none';
        elAudio.controls = 'true';
        $(elAudio).click(function (s) { s.stopPropagation() });
        elAudio.style.marginTop = '5px';

        controls.audio.parentNode.replaceChild(elAudio, controls.audio);
        controls.audio = elAudio;
        $WH.aE(controls.audio, 'ended', setNextTrack.bind(_self));

        if (doPlay)
        {
            elAudio.preload = 'auto';
            autoStart = true;
            $WH.aE(controls.audio, 'canplaythrough', autoplay.bind(this));
        }

        if (!canPlay)
            controls.table.style.visibility = 'visible';

        var file;
        do
        {
            fileIdx += itr;
            if (fileIdx > playlist.length - 1)
            {
                fileIdx = 0;
                if (!canPlay)
                {
                    var div = $WH.ce('div');
                 // div.className = 'minibox'; Aowow custom
                    div.className = 'minibox minibox-left';
                    $WH.st(div, $WH.sprintf(LANG.message_browsernoaudio, file.type));
                    controls.table.parentNode.replaceChild(div, controls.table);
                    return
                }
            }

            if (fileIdx < 0)
                fileIdx = playlist.length - 1;

            file = playlist[fileIdx];
        }
        while (controls.audio.canPlayType(file.type) == '');

        var elSource = $WH.ce('source');
        elSource.src = file.url;
        elSource.type = file.type;
        $WH.ae(controls.audio, elSource);

        if (controls.hasOwnProperty('title'))
        {
            if (url)
            {
                $WH.ee(controls.title);
                var a = $WH.ce('a');
                a.href = url;
                $WH.st(a, '"' + file.title + '"');
                $WH.ae(controls.title, a);
            }
            else
                $WH.st(controls.title, '"' + file.title + '"');
        }

        if (controls.hasOwnProperty('trackdisplay'))
            $WH.st(controls.trackdisplay, '' + (fileIdx + 1) + ' / ' + playlist.length);

        if (!canPlay)
        {
            canPlay = true;
            for (var i = fileIdx + 1; i <= playlist.length - 1; i++)
            {
                if (controls.audio.canPlayType(playlist[i].type))
                {
                    $(controls.controlsdiv).children('a').removeClass('button-red-disabled');
                    break;
                }
            }
        }

        if (controls.hasOwnProperty('addbutton'))
        {
            $(controls.addbutton).removeClass('button-red-disabled');
         // $WH.st(controls.addbutton, LANG.add);           Aowow: doesnt work with RedButtons
            RedButton.setText(controls.addbutton, LANG.add);
        }
    }

    function autoplay()
    {
        if (!autoStart)
            return;

        autoStart = false;
        controls.audio.play();
    }

    this.init = function (files, parent, opt)
    {
        if (!$WH.is_array(files))
            return;

        if (files.length == 0)
            return;

        if ((parent.id == '') || g_audiocontrols.hasOwnProperty(parent.id))
        {
            var i = 0;
            while (g_audiocontrols.hasOwnProperty('auto-audiocontrols-' + (++i))) {}
            parent.id = 'auto-audiocontrols-' + i;
        }

        g_audiocontrols[parent.id] = this;

        if (typeof opt == 'undefined')
            opt = {};

        looping = !!opt.loop;
        if (opt.hasOwnProperty('url'))
            url = opt.url;

        playlist = files;
        controls.div = parent;

        if (!opt.listview)
        {
            var tbl = $WH.ce('table', { className: 'audio-controls' });
            controls.table = tbl;
            controls.table.style.visibility = 'hidden';
            $WH.ae(controls.div, tbl);

            var tr = $WH.ce('tr');
            $WH.ae(tbl, tr);

            var td = $WH.ce('td');
            $WH.ae(tr, td);

            controls.audio = $WH.ce('div');
            $WH.ae(td, controls.audio);

            controls.title = $WH.ce('div', { className: 'audio-controls-title' });
            $WH.ae(td, controls.title);

            controls.controlsdiv = $WH.ce('div', { className: 'audio-controls-pagination' });
            $WH.ae(td, controls.controlsdiv);

            var prevBtn = createButton(LANG.previous, true);
            $WH.ae(controls.controlsdiv, prevBtn);
            $WH.aE(prevBtn, 'click', this.btnPrevTrack.bind(this));

            controls.trackdisplay = $WH.ce('div', { className: 'audio-controls-pagination-track' });
            $WH.ae(controls.controlsdiv, controls.trackdisplay);

            var nextBtn = createButton(LANG.next, true);
            $WH.ae(controls.controlsdiv, nextBtn);
            $WH.aE(nextBtn, 'click', this.btnNextTrack.bind(this))
        }
        else
        {
            fullPlayer = true;
            var div = $WH.ce('div');
            controls.table = div;
            $WH.ae(controls.div, div);

            controls.audio = $WH.ce('div');
            $WH.ae(div, controls.audio);

            controls.trackdisplay = opt.trackdisplay;
            controls.controlsdiv = $WH.ce('span');
            $WH.ae(div, controls.controlsdiv);
        }

        if (g_audioplaylist.isEnabled() && !opt.fromplaylist)
        {
            var addBtn = createButton(LANG.add);
            $WH.ae(controls.controlsdiv, addBtn);
            $WH.aE(addBtn, 'click', this.btnAddToPlaylist.bind(this, addBtn));
            controls.addbutton = addBtn;

            if (fullPlayer)
                addBtn.style.verticalAlign = '50%';
        }

        if (g_audiocontrols.__windowloaded)
            this.btnNextTrack();
    };

    function setNextTrack()
    {
        updatePlayer(this, 1, (looping || (fileIdx < (playlist.length - 1))));
    }

    this.btnNextTrack = function ()
    {
        updatePlayer(this, 1, (canPlay && (controls.audio.readyState > 1) && (!controls.audio.paused)));
    };

    this.btnPrevTrack = function ()
    {
        updatePlayer(this, -1, (canPlay && (controls.audio.readyState > 1) && (!controls.audio.paused)));
    };

    this.btnAddToPlaylist = function (_self)
    {
        if (fullPlayer)
        {
            for (var i = 0; i < playlist.length; i++)
                g_audioplaylist.addSound(playlist[i]);
        }
        else
            g_audioplaylist.addSound(playlist[fileIdx]);

        _self.className += ' button-red-disabled';
     // $WH.st(_self, LANG.added);                          // Aowow doesn't work with RedButtons
        RedButton.setText(_self, LANG.added);
    };

    this.isPlaying = function ()
    {
        return !controls.audio.paused;
    };

    this.removeSelf = function ()
    {
        controls.table.parentNode.removeChild(controls.table);
        delete g_audiocontrols[controls.div];
    };

    function createButton(text, disabled)
    {
        return $WH.g_createButton(text, null, {
            disabled: disabled,
         // 'float': false,                                 Aowow - adapted style
         // style: 'margin:0 12px; display:inline-block'
            style: 'margin:0 12px; display:inline-block; float:inherit; '
        });
    }
};

$WH.aE(window, 'load', function ()
{
    g_audiocontrols.__windowloaded = true;
    for (var i in g_audiocontrols)
        if (i.substr(0, 2) != '__')
            g_audiocontrols[i].btnNextTrack();
});

AudioPlaylist = function ()
{
    var enabled  = false;
    var playlist = [];
    var player, container;

    this.init = function ()
    {
        if (!$WH.localStorage.isSupported())
            return;

        enabled = true;

        var tracks;
        if (tracks = $WH.localStorage.get('AudioPlaylist'))
            playlist = JSON.parse(tracks);
    };

    this.savePlaylist = function ()
    {
        if (!enabled)
            return false;

        $WH.localStorage.set('AudioPlaylist', JSON.stringify(playlist));
    };

    this.isEnabled = function ()
    {
        return enabled;
    };

    this.addSound = function (track)
    {
        if (!enabled)
            return false;

        this.init();
        playlist.push(track);
        this.savePlaylist();
    };

    this.deleteSound = function (idx)
    {
        if (idx < 0)
            playlist = [];
        else
            playlist.splice(idx, 1);

        this.savePlaylist();

        if (!player.isPlaying())
        {
            player.removeSelf();
            this.setAudioControls(container);
        }

        if (playlist.length == 0)
            $WH.Tooltip.hide();
    };

    this.getList = function ()
    {
        var buf = [];
        for (var i = 0; i < playlist.length; i++)
            buf.push(playlist[i].title);

        return buf;
    };

    this.setAudioControls = function (parent)
    {
        if (!enabled)
            return false;

        container = parent;
        player = new AudioControls();
        player.init(playlist, container, { loop: true, fromplaylist: true });
    };
};

g_audioplaylist = (new AudioPlaylist);
g_audioplaylist.init();
