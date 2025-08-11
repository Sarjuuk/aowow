function MessageBox(parent, text) {
    parent.find(".message-box").remove();
    var box = $("<div></div>");
    box.hide();
    box.addClass("message-box");
    box.html('<p class="message">' + text + '</p><p class="close">(Click on this box to close it)</p>');
    box.click(function () { $(this).fadeOut(); });

    box.click(function (e) {
        $WH.sp(e);                                          // aowow - custom: without this, the comment-header would also register the click
        $(this).fadeOut();
    });

    parent.append(box[0]);
    box.fadeIn();
}
