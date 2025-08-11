/* Note: comment replies are called "comments" because part of this code was taken from another project of mine. */

function SetupReplies(post, comment)
{
    SetupAddEditComment(post, comment, false);
    SetupShowMoreComments(post, comment);

    post.find('.comment-reply-row').each(function () { SetupRepliesControls($(this), comment); });
    post.find('.comment-reply-row').hover(function () { $(this).find('span').attr('data-hover', 'true'); }, function () { $(this).find('span').attr('data-hover', 'false'); });
}

function SetupAddEditComment(post, comment, edit)
{
    /* Variables that will be set by Initialize() */
    var Form = null;
    var Body = null;
    var AddButton = null;
    var TextCounter = null;
    var AjaxLoader = null;
    var FormContainer = null;
    var DialogTableRowContainer = null;

    /* Constants */
    var MIN_LENGTH = 15;
    var MAX_LENGTH = 600;

    /* State keeping booleans */
    var Initialized = false;
    var Active = false;
    var Flashing = false;
    var Submitting = false;

    /* Shortcuts */
    var CommentsTable = post.find('.comment-replies > table');
    var AddCommentLink = post.find('.add-reply');
    var CommentsCount = comment.replies.length;

    if(edit)
        Open();
    else
        AddCommentLink.click(function () { Open(); });

    function Initialize()
    {
        if (Initialized)
            return;

        Initialized = true;

        var row = $('<tr/>');

        if(edit)
            row.addClass('comment-reply-row').addClass('reply-edit-row');

        row.html('<td style="width: 0"></td>' +
            '<td class="comment-form"><form><table>' +
                '<form>' +
                  '<table><tr>' +
                     '<td style="width: 600px">' +
                       '<textarea required="required" name="body" cols="68" rows="3"></textarea>' +
                     '</td>' +
                     '<td>' +
                       '<input type="submit" value="' + (edit ? LANG.save : LANG.addreply) + '" />' +
                       '<img src="' + g_staticUrl + '/images/icons/ajax.gif" class="ajax-loader" />' +
                     '</td>' +
                  '</tr>' +
                  '<tr><td colspan="2">' +
                    '<span class="text-counter">Text counter placeholder</span>' +
                  '</td></tr></table>' +
                '</form>' +
              '</td>');

        /* Set up the various variables for the controls we just created */
        Body = row.find('.comment-form textarea');
        AddButton = row.find('.comment-form input[type=submit]');
        TextCounter = row.find('.comment-form span.text-counter');
        Form = row.find('.comment-form form');
        AjaxLoader = row.find('.comment-form .ajax-loader');
        FormContainer = row.find('.comment-form');

        /* Intercept submits */
        Form.submit(function () { Submit(); return false; });

        UpdateTextCounter();

        /* This is kinda a mess.. Every browser seems to implement keyup, keydown and keypress differently.
         * - keyup: We need to use keyup to update the text counter for the simple reason we want to update it only when the user stops typing.
         * - keydown: We need to use keydown to detect the ESC key because it's the only one that works in all browsers for ESC
         * - keypress: We need to use keypress to detect Enter because it's the only one that 1) Works 2) Allows us to prevent a new line from being entered in the textarea
         * I find it very funny that in each scenario there is only one of the 3 that works, and that that one is always different from the others.
         */

        Body.keyup(function (e) { UpdateTextCounter(); });
        Body.keydown(function (e) { if (e.keyCode == 27) { Close(); return false; } }); // ESC
        Body.keypress(function (e) { if (e.keyCode == 13) { Submit(); return false; } }); // ENTER

        if(edit)
        {
            post.after(row);
            post.hide();
            Form.find('textarea').text(comment.replies[post.attr('data-idx')].body);
        }
        else
            CommentsTable.append(row);

        DialogTableRowContainer = row;
        Form.find('textarea').focus();
    }

    function Open()
    {
        if (!Initialized)
            Initialize();

        Active = true;

        if(!edit)
        {
            AddCommentLink.hide();
            post.find('.comment-replies').show();
            FormContainer.show();
            FormContainer.find('textarea').focus();
        }
    }

    function Close()
    {
        Active = false;

        if(edit)
        {
            if(DialogTableRowContainer)
                DialogTableRowContainer.remove();
            post.show();
            return;
        }

        AddCommentLink.show();
        FormContainer.hide();

        if (CommentsCount == 0)
            post.find('.comment-replies').hide();
    }

    function Submit()
    {
        if (!Active || Submitting)
            return;

        if (Body.val().length < MIN_LENGTH || Body.val().length > MAX_LENGTH)
        {
            /* Flash the char counter to attract the attention of the user. */
            if (!Flashing)
            {
                Flashing = true;
                TextCounter.animate({ opacity: '0.0' }, 150);
                TextCounter.animate({ opacity: '1.0' }, 150, null, function() { Flashing = false; });
            }

            return false;
        }

        SetSubmitState();
        $.ajax({
            type: 'POST',
            url: edit ? '?comment=edit-reply' : '?comment=add-reply',
            data: { commentId: comment.id, replyId: (edit ? post.attr('data-replyid') : 0), body: Body.val() },
            success: function (newReplies) { OnSubmitSuccess(newReplies); },
            dataType: 'json',
            error: function (jqXHR) { OnSubmitFailure(jqXHR.responseText); }
        });
        return true;
    }

    function SetSubmitState()
    {
        Submitting = true;
        AjaxLoader.show();
        AddButton.attr('disabled', 'disabled');
        FormContainer.find('.message-box').remove();
    }

    function ClearSubmitState()
    {
        Submitting = false;
        AjaxLoader.hide();
        AddButton.removeAttr('disabled');
    }

    function OnSubmitSuccess(newReplies)
    {
        comment.replies = newReplies;
        Listview.templates.comment.updateReplies(comment);
    }

    function OnSubmitFailure(error)
    {
        ClearSubmitState();
        MessageBox(FormContainer, error);
    }

    function UpdateTextCounter()
    {
        var text = '(error)';
        var cssClass = 'q0';
        var chars = Body.val().replace(/(\s+)/g, ' ').replace(/^\s*/, '').replace(/\s*$/, '').length;
        var charsLeft = MAX_LENGTH - chars;

        if (chars == 0)
            text = $WH.sprintf(LANG.replylength1_format, MIN_LENGTH);
        else if (chars < MIN_LENGTH)
            text = $WH.sprintf(LANG.replylength2_format, MIN_LENGTH - chars);
        else
        {
            text = $WH.sprintf(charsLeft == 1 ? LANG.replylength4_format : LANG.replylength3_format, charsLeft);

            if (charsLeft < 120)
                cssClass = 'q10';
            else if (charsLeft < 240)
                cssClass = 'q5';
            else if (charsLeft < 360)
                cssClass = 'q11';
        }

        TextCounter.html(text).attr('class', cssClass);
    }
}

function SetupShowMoreComments(post, comment)
{
    var ShowMoreCommentsLink = post.find('.show-more-replies');
    var CommentCell = post.find('.comment-replies');

    ShowMoreCommentsLink.click(function () { ShowMoreComments(); });

    function ShowMoreComments()
    {
        /* Replace link with ajax loader */
        ShowMoreCommentsLink.hide();
        CommentCell.append(CreateAjaxLoader());

        $.ajax({
            type: 'GET',
            url: '?comment=show-replies',
            data: { id: comment.id },
            success: function (replies) { comment.replies = replies; Listview.templates.comment.updateReplies(comment); },
            dataType: 'json',
            error: function () { OnFetchFail(); }
        });
    }

    function OnFetchFail()
    {
        ShowMoreCommentsLink.show();
        CommentCell.find('.ajax-loader').remove();

        MessageBox(CommentCell, "There was an error fetching the comments. Try refreshing the page.");
    }
}

function SetupRepliesControls(post, comment)
{
    var CommentId = post.attr('data-replyid');
    var VoteUpControl = post.find('.reply-upvote');
    var VoteDownControl = post.find('.reply-downvote');
    var FlagControl = post.find('.reply-report');
    var CommentScoreText = post.find('.reply-rating');
    var CommentActions = post.find('.reply-controls');
    var DeleteButton = post.find('.reply-delete');
    var EditButton = post.find('.reply-edit');
    var Voting = false;
    var Deleting = false;
    // aowow - detach functionality is custom
    var Detaching = false;
    var DetachButton = post.find('.reply-detach');
    var Container = comment.repliesCell;

    EditButton.click(function() {
        SetupAddEditComment(post, comment, true);
    });

    FlagControl.click(function ()
    {
        if (Voting || !confirm(LANG.replyreportwarning_tip))
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=flag-reply',
            data: { id: CommentId },
            success: function () { OnFlagSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    VoteUpControl.click(function ()
    {
        if (VoteUpControl.attr('data-hasvoted') == 'true' || VoteUpControl.attr('data-canvote') != 'true' || Voting)
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=upvote-reply',
            data: { id: CommentId },
            success: function () { OnVoteSuccessful(1); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    VoteDownControl.click(function ()
    {
        if (VoteDownControl.attr('data-hasvoted') == 'true' || VoteDownControl.attr('data-canvote') != 'true' || Voting)
            return;

        Voting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=downvote-reply',
            data: { id: CommentId },
            success: function () { OnVoteSuccessful(-1); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    DetachButton.click(function ()
    {
        if (Detaching) {
            MessageBox(CommentActions, LANG.message_cantdetachcomment);
            return;
        }

        if (!confirm(LANG.confirm_detachcomment)) {
            return;
        }

        Detaching = true;
        $.ajax({
            type: 'POST',
            url: '?comment=detach-reply',
            data: { id: CommentId },
            success: function () { OnDetachSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    DeleteButton.click(function ()
    {
        if (Deleting)
            return;

        if (!confirm(LANG.deletereplyconfirmation_tip))
            return;

        Deleting = true;
        $.ajax({
            type: 'POST',
            url: '?comment=delete-reply',
            data: { id: CommentId },
            success: function () { OnDeleteSuccessful(); },
            error: function (jqXHR) { OnError(jqXHR.responseText); }
        });
    });

    function OnVoteSuccessful(ratingChange)
    {
        var rating = parseInt(CommentScoreText.text());

        rating += ratingChange;

        CommentScoreText.text(rating);

        if(ratingChange > 0)
            VoteUpControl.attr('data-hasvoted', 'true');
        else
            VoteDownControl.attr('data-hasvoted', 'true');

        VoteUpControl.attr('data-canvote', 'false');
        VoteDownControl.attr('data-canvote', 'false');

        if(ratingChange > 0)
            FlagControl.remove();
        Voting = false;
    }

    function OnFlagSuccessful()
    {
        Voting = false;
        FlagControl.remove();
    }

    function OnDetachSuccessful()
    {
        post.remove();
        MessageBox(Container, LANG.message_commentdetached);
        Detaching = false;
    }

    function OnDeleteSuccessful()
    {
        post.remove();
        Deleting = false;
    }

    function OnError(text)
    {
        Voting = false;
        Deleting = false;
        Detaching = false;

        if (!text)
            text = LANG.genericerror;

        MessageBox(CommentActions, text);
    }
}

/*
Global comment-related functions
*/

function co_addYourComment()
{
    tabsContribute.focus(0);
    var ta = $WH.gE(document.forms['addcomment'], 'textarea')[0];
    ta.focus();
}

function co_validateForm(f)
{
    var ta = $WH.gE(f, 'textarea')[0];

    // prevent locale comments on guide pages
    var locale = Locale.getId();
    // aowow - disabled
    // if(locale != LOCALE_ENUS && $(f).attr('action') && ($(f).attr('action').replace(/^.*type=([0-9]*).*$/i, '$1')) == 100)
    if (false)
    {
        alert(LANG.message_cantpostlcomment_tip);
        return false;
    }

    if (g_user.permissions & 1) {
        return true;
    }

    if (Listview.funcBox.coValidate(ta)) {
        return true;
    }

    return false;
}

// Display a warning if a user attempts to leave the page and he has started writing a message
$(document).ready(function()
{
    g_setupChangeWarning($("form[name=addcomment]"), [$("textarea[name=commentbody]")], LANG.message_startedpost);
});
