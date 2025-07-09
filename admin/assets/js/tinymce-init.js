document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
        selector: 'textarea.tinymce',
        plugins: 'image code table media autosave fullscreen wordcount preview link lists advlist charmap hr anchor',
        toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote hr | link image media table charmap | subscript superscript | code fullscreen preview',
        height: 500,
        menubar: false,
        statusbar: true, // pastikan statusbar aktif
        document_base_url: 'http://localhost:8000/',
        images_upload_url: 'api/upload_image.php',
        images_upload_credentials: true,
        file_picker_callback: function (cb, value, meta) {
            var input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.onchange = function () {
                var file = this.files[0];
                var reader = new FileReader();
                reader.onload = function () {
                    var id = 'blobid' + (new Date()).getTime();
                    var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    var base64 = reader.result.split(',')[1];
                    var blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
        media_url_resolver: function (data, resolve/*, reject*/) {
            if (data.url.indexOf('youtube') !== -1) {
                var id = data.url.match(/(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})/);
                if (id && id[1]) {
                    resolve({ html: '<iframe src="https://www.youtube.com/embed/' + id[1] + '" width="560" height="315" frameborder="0" allowfullscreen></iframe>' });
                } else {
                    resolve({ html: '' });
                }
            } else if (data.url.indexOf('vimeo') !== -1) {
                var id = data.url.match(/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:video\/|)(\d+)/);
                if (id && id[1]) {
                    resolve({ html: '<iframe src="https://player.vimeo.com/video/' + id[1] + '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>' });
                } else {
                    resolve({ html: '' });
                }
            } else {
                resolve({ html: '' });
            }
        },
        autosave_ask_before_unload: false,
        autosave_interval: '30s',
        autosave_prefix: 'tinymce-autosave-' + pageContentType + '-' + pageContentId + '-',
        autosave_restore_when_empty: true,
        autosave_retention: '30m',
        autosave_save_callback: function (editor, callback) {
            var content = editor.getContent();
            var editorId = editor.id;
            var entityType = editor.settings.pageContentType;
            var entityId = editor.settings.pageContentId;
            if (entityId === null || entityId === 'null') {
                callback();
                return;
            }
            var formData = new FormData();
            formData.append('entity_type', entityType);
            formData.append('entity_id', entityId);
            formData.append('content', content);
            fetch('api/save_draft.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Draft saved successfully!');
                } else {
                    console.error('Failed to save draft:', data.message);
                }
                callback();
            })
            .catch(error => {
                console.error('Error saving draft:', error);
                callback();
            });
        },
        autosave_restore_callback: function (editor, data) {
            if (data && data.content) {
                editor.setContent(data.content);
                return true;
            }
            return false;
        },
        setup: function (editor) {
            // Live preview ke panel preview-panel, support gambar base64 dan url
            editor.on('keyup change', function () {
                var html = editor.getContent();
                var preview = document.getElementById('preview-panel');
                if (preview) {
                    // Fallback: jika ada <img> dengan src blob, tampilkan base64
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    var imgs = tempDiv.querySelectorAll('img');
                    imgs.forEach(function(img) {
                        var srcAttr = img.getAttribute('src');
                        console.log('Preview IMG before:', srcAttr);
                        if (img.src.startsWith('blob:')) {
                            // Cari gambar di blobCache TinyMCE
                            var blobInfo = editor.editorUpload.blobCache.getByUri(img.src);
                            if (blobInfo) {
                                img.src = blobInfo.base64();
                                img.setAttribute('src', blobInfo.base64());
                                console.log('Preview IMG set as base64:', blobInfo.base64());
                            }
                        } else if (srcAttr) {
                            // Jika src mengandung uploads/ di awal (dengan atau tanpa slash)
                            var uploadsMatch = srcAttr.match(/^\/?uploads\//);
                            if (uploadsMatch) {
                                var cleanSrc = srcAttr.replace(/^\/?uploads\//, '');
                                var newSrc = '/uploads/' + cleanSrc;
                                img.src = newSrc;
                                img.setAttribute('src', newSrc);
                                console.log('Preview IMG fixed to:', newSrc);
                            }
                        }
                    });
                    preview.innerHTML = tempDiv.innerHTML;
                }
                // Hitung word count dan read time
                var text = editor.getContent({ format: 'text' });
                var words = text.trim().split(/\s+/).filter(Boolean);
                var wordCount = words.length;
                var readTime = Math.max(1, Math.round(wordCount / 200)); // 200 kata/menit
                var wordCountElem = document.getElementById('word-count');
                var readTimeElem = document.getElementById('read-time');
                if (wordCountElem) wordCountElem.textContent = wordCount;
                if (readTimeElem) readTimeElem.textContent = readTime + ' min';
            });
        }
    });
});