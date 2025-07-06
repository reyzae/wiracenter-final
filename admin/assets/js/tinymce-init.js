document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '.tinymce',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 400,
        menubar: false,
        branding: false,
        relative_urls: false,
        remove_script_host: false,
        images_upload_url: 'api/upload_image.php',
        automatic_uploads: true,
        file_picker_types: 'image',
        file_picker_callback: function(callback, value, meta) {
            if (meta.filetype === 'image') {
                tinymce.activeEditor.windowManager.openUrl({
                    url: 'files.php?mode=tinymce',
                    title: 'Media Library',
                    width: 800,
                    height: 600,
                    buttons: [
                        {
                            type: 'cancel',
                            text: 'Close'
                        }
                    ],
                    onMessage: function (api, message) {
                        if (message.data.mceAction === 'fileSelected') {
                            callback(message.data.url, { title: message.data.title });
                            api.close();
                        }
                    }
                });
            }
        },
        setup: function (editor) {
            editor.on('init change', function () {
                if (typeof updateWordCountAndReadTime === 'function') updateWordCountAndReadTime();
                if (typeof updatePreview === 'function') updatePreview();
            });
            editor.on('change', function () {
                if (typeof autoSaveDraft === 'function') {
                    autoSaveDraft(window.pageContentType, window.pageContentId);
                }
            });
            editor.on('keyup', function () {
                if (typeof autoCapitalize === 'function') autoCapitalize(editor);
            });
        }
    });
});
