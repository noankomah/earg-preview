// Live preview of images chosen in the publication "Images" file input.
// Renders a small 3-across grid so the admin can see what was picked before
// saving. Used by admin/publications/create.php and edit.php.
(function () {
    var input = document.querySelector('input[name="gallery_images[]"]');
    var preview = document.getElementById('new-image-preview');

    if (!input || !preview) {
        return;
    }

    var previousUrls = [];

    input.addEventListener('change', function () {
        // Free the object URLs created for the previous selection.
        previousUrls.forEach(function (url) {
            URL.revokeObjectURL(url);
        });
        previousUrls = [];

        preview.innerHTML = '';
        var added = 0;

        var files = Array.prototype.slice.call(input.files || []);

        files.forEach(function (file, index) {
            if (file.type.indexOf('image/') !== 0) {
                return;
            }

            added++;

            var tile = document.createElement('div');
            tile.className = 'admin-image-preview-tile';

            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Selected image ' + (index + 1);

            var name = document.createElement('span');
            name.textContent = file.name;

            tile.appendChild(img);
            tile.appendChild(name);
            preview.appendChild(tile);

            previousUrls.push(img.src);
        });

        preview.hidden = added === 0;
    });
})();