$(document).ready(function()
{
    $('form.vote-comment input').click(function() {
        var vote = $(this).attr('data-vote');
        var block = $(this).parent();
        var ajax = block.find('.ajax');
        var inputs = block.find('input');
        var post = block.attr('data-post');
        var type = block.attr('data-type');

        inputs.attr('disabled', 'disabled');
        ajax.show();

        $.post('?vote', { post: post, type: type, vote: vote }, function(data) {
            if (data != 'ok')
            {
                inputs.removeAttr('disabled');
                ajax.hide();
                alert(LANG.voteerror_tip);
                return;
            }

            block.remove();
        });

        return false;
    });
});
