$(document).ready(function () {
    Menu.addButtons($('#home-menu'), mn_path);

    var form = $('#home-search form');
    form.submit(g_preventEmptyFormSubmission);

    var inp = $('input', form);
    LiveSearch.attach(inp);
    inp.focus();

    var btn = $('<a></a>').attr('href', 'javascript:;');
    btn.click(function () {
        $(this).parent('form').submit().children('input').focus()
    }).appendTo(form);

    $('.home-featuredbox-links a').hover(
        function () { $(this).next('var').addClass('active') },
        function () { $(this).next('var').removeClass('active') }
    ).click(function () { $WH.Track.interactiveEvent({category: 'Featured Box', action: 'Click', label: this.title}) }
    ).each( function () { $WH.Track.nonInteractiveEvent({category: 'Featured Box', action: 'Show', label: this.title}) }
    );
});
