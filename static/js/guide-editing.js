$(document).ready(function () {
    var uploadBar = {};
    var a = new qq.FileUploader({
        element: $("#image-upload")[0],
        action: "?edit=image",
        params: { guide: 1 },
        allowedExtensions: ['jpg', 'jpeg', 'png'],
        sizeLimit: 10 * 1024 * 1024,
        template: '<div class="qq-uploader"><div class="qq-upload-drop-area"><span>Drop files here to upload</span></div><div class="qq-upload-button">Upload an image</div><ul class="qq-upload-list"></ul></div>',
        onSubmit: function (id, fileName) {
            uploadBar[id] = new ProgressBar({text: "0%", hoverText: "0%"});
            var c = $("#upload-progress");
            // c.empty();
            c.append(uploadBar[id].getContainer())
        },
        onProgress: function (id, fileName, loaded, total) {
            var pct = Math.round(loaded / total * 100);
            if (uploadBar[id]) {
                uploadBar[id].setText(pct + "%");
                uploadBar[id].setHoverText(pct + "%");
                uploadBar[id].setProgress(pct);
            }
        },
        onComplete: function (id, fileName, rspJSON) {
            uploadBar[id] = null;
            if (!rspJSON.success) {
                $("#upload-result").append($('<span>').attr('id', id).addClass('q10').text('Upload failed (' + rspJSON.error + ')'));
            }
            else {
                var result = $('<span>').attr('id', id).addClass('q2').text('Upload of ');
                result.append($('<b>').text(rspJSON.name)).append(' complete: ').append($('<input>').attr({id: id, type: 'text'}));
                $("#upload-result").append(result);
                $("#upload-result").find("input#"+id).val("[img src=" + g_staticUrl + "/uploads/guide/images/" + rspJSON.id + "." + (rspJSON.type == 3 ? "png" : "jpg") + "]").focus(function () { this.select() })
            }
        }
    })
});
