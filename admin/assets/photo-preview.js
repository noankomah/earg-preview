/**
 * Photo upload preview for the admin forms.
 *
 * When a file is chosen it shows a small preview card (dashed gold border)
 * with the file name and size, and hides the "current photo" card when one
 * is present. Clearing the file input hides the preview and restores the
 * current photo card.
 *
 * The preview is only a visual aid: the forms still work without JavaScript.
 */
(function () {
    "use strict";

    function formatSize(bytes) {
        if (bytes < 1024) {
            return bytes + " B";
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + " KB";
        }

        return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    }

    document.querySelectorAll("[data-photo-input]").forEach(function (input) {
        var preview = document.querySelector("[data-photo-preview]");
        var image = document.querySelector("[data-photo-preview-img]");
        var name = document.querySelector("[data-photo-preview-name]");
        var size = document.querySelector("[data-photo-preview-size]");
        var current = document.querySelector("[data-current-photo]");
        var previewUrl = null;

        if (!preview || !image) {
            return;
        }

        input.addEventListener("change", function () {
            var file = input.files && input.files[0];

            if (!file) {
                preview.hidden = true;

                if (current) {
                    current.hidden = false;
                }

                return;
            }

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }

            previewUrl = URL.createObjectURL(file);
            image.src = previewUrl;
            image.alt = file.name;

            if (name) {
                name.textContent = file.name;
            }

            if (size) {
                size.textContent = formatSize(file.size);
            }

            preview.hidden = false;

            if (current) {
                current.hidden = true;
            }
        });
    });
})();
