/*
 * Admin survey dashboard behaviour.
 *
 * Referenced by admin/survey/index.php (responses list) and
 * admin/survey/view.php (a single response).
 *
 *   1. Confirms before any delete form/button (those carrying a data-confirm
 *      message) is submitted.
 *   2. On the responses list, turns the table into a jQuery DataTable
 *      (search, sorting, pagination) and keeps the bulk-selection checkboxes
 *      in sync with a hidden "ids[]" list inside the bulk form.
 */
(function () {
    'use strict';

    // ---- 1. Delete confirmation -------------------------------------
    document.addEventListener('submit', function (event) {
        var trigger = event.submitter || event.target;
        var message =
            (trigger && trigger.getAttribute && trigger.getAttribute('data-confirm')) ||
            (event.target && event.target.getAttribute && event.target.getAttribute('data-confirm'));

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    }, true);

    // ---- 2. Responses DataTable -------------------------------------
    // The rest of this file only applies to the responses list page, which
    // loads jQuery + DataTables.
    if (typeof window.jQuery !== 'function') {
        return;
    }

    var tableElement = document.getElementById('responsesTable');
    if (!tableElement) {
        return;
    }

    window.jQuery(function ($) {
        var table = $(tableElement).DataTable({
            order: [],                 // keep the server order (newest first)
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            columnDefs: [
                { orderable: false, targets: 0 },  // checkbox column
                { orderable: false, targets: 3 },  // actions column
            ],
            language: { emptyTable: 'No responses yet.' },
        });

        // Selected ids live here and are written into the bulk form as hidden
        // "ids[]" inputs, so selection works across DataTables pages.
        var selected = new Set();

        function syncHiddenInputs() {
            var $box = $('#bulkIds').empty();
            selected.forEach(function (id) {
                $('<input>', { type: 'hidden', name: 'ids[]', value: id }).appendTo($box);
            });
        }

        function refreshCheckboxes() {
            table.rows().every(function () {
                var id = String($(this.node()).find('input[data-row-check]').data('id'));
                $(this.node()).find('input[data-row-check]').prop('checked', selected.has(id));
            });
            $('#selectAll').prop('checked', selected.size > 0 && selected.size === table.rows().count());
        }

        // Select / deselect one row.
        $(tableElement).on('change', 'input[data-row-check]', function () {
            var id = String($(this).data('id'));

            if (this.checked) {
                selected.add(id);
            } else {
                selected.delete(id);
            }

            syncHiddenInputs();
            $('#selectAll').prop('checked', selected.size > 0 && selected.size === table.rows().count());
        });

        // "Select all" picks every row across all pages.
        $('#selectAll').on('change', function () {
            if (this.checked) {
                table.rows().every(function () {
                    selected.add(String($(this.node()).find('input[data-row-check]').data('id')));
                });
            } else {
                selected.clear();
            }

            syncHiddenInputs();
            refreshCheckboxes();
        });

        // Re-apply checkbox ticks whenever the table redraws (new page/search).
        $(tableElement).on('draw', refreshCheckboxes);
    });
})();
