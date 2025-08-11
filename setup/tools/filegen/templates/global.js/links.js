var Links = new function()
{
    var dialog  = null;
    var oldHash = null;

 /* aowow - official armory is gone
  * var validArmoryTypes = {
  *     item: 1
  * };
  */

    var extraTypes = {
        29: 'icondb'
    };

    this.onShow = function()
    {
        if (location.hash && location.hash != '#links')
            oldHash = location.hash;

        location.replace('#links');
    }

    this.onHide = function()
    {
        if (oldHash && (oldHash.indexOf('screenshots:') == -1 || oldHash.indexOf('videos:') == -1))
            location.replace(oldHash);
        else
            location.replace('#.');
    }

    this.show = function(opt)
    {
        if (!opt || !opt.type || !opt.typeId)
            return;

        var type = g_types[opt.type];

        if (!dialog)
            this.init();

     /* aowow - the official wow armory ... good times
      * if (validArmoryTypes[type] && Dialog.templates.links.fields[1].id != 'armoryurl')
      * {
      *     Dialog.templates.links.fields.splice(1, 0, {
      *         id: 'armoryurl',
      *         type: 'text',
      *         label: 'Armory URL',
      *         size: 40
      *     });
      * }
      */

        var link = '';
        if (opt.linkColor && opt.linkId && opt.linkName)
        {
            link = g_getIngameLink(opt.linkColor, opt.linkId, opt.linkName);

            if (opt.sound)
                link = '/script PlaySoundFile("' + opt.sound + '", "master")';
             // link = '/script PlaySoundKitID(' + opt.sound + ')'; aowow: lua fn not available in 3.3.5

            if (Dialog.templates.links.fields[Dialog.templates.links.fields.length - 2].id != 'ingamelink')
            {
                Dialog.templates.links.fields.splice(Dialog.templates.links.fields.length - 1, 0, {
                    id: 'ingamelink',
                    type: 'text',
                    label: 'Ingame Link',
                    size: 40
                });
            }
        }

        var data = {
            'wowheadurl': g_host +'/?' + type + '=' + opt.typeId,
         // 'armoryurl': 'http://us.battle.net/wow/en/' + type + '/' + opt.typeId,
            'ingamelink': link,
            'markuptag': '[' + (extraTypes[opt.type] || type) + '=' + opt.typeId + ']'
        };

        dialog.show('links', {
            data: data,
            onShow: this.onShow,
            onHide: this.onHide,
            onSubmit: function() { return false; }
        });
    }

    this.checkPound = function()
    {
        if (location.hash && location.hash == '#links')
        {
            $('#open-links-button').click();
        }
    }

    this.init = function()
    {
        dialog = new Dialog();

        Dialog.templates.links = {
            title: LANG.pr_menu_links || 'Links',
            width: 425,
            buttons: [['cancel', LANG.close]],

            fields:
                [
                    {
                        id:    'wowheadurl',
                        type:  'text',
                        label: 'Aowow URL',
                        size:  40
                    },
                    {
                        id:    'markuptag',
                        type:  'text',
                        label: 'Markup Tag',
                        size:  40
                    }
                ],

            onInit: function(form)
            {

            },

            onShow: function(form)
            {
                setTimeout(function() {
                    $(form.ingamelink).select();
                }, 50);
                setTimeout(Lightbox.reveal, 100);
            }
        };
    };

    $(document).ready(this.checkPound);
};
