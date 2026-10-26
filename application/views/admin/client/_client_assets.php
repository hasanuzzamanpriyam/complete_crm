<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php // Bump the ?v= below whenever assets/css/client_modern.css changes. ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/client_modern.css?v=14">
<script>
/* cl-subtabs highlight bridge.
 * Bootstrap 3's tab plugin moves .active by activating $(this).parent('li')
 * inside closest('ul'). Our sub-tabs are div.cl-subtabs > a[data-toggle=tab]
 * (demo markup), so the pane switched but the link highlight never moved and
 * General stayed active no matter which tab was clicked. Move the class
 * ourselves; Bootstrap still owns the pane visibility. Delegated on document
 * so tabs injected later (AJAX quick-add modal) are covered too. */
document.addEventListener('click', function (e) {
    var el = e.target;
    if (!el || typeof el.closest !== 'function') return;
    var a = el.closest('.cl-subtabs > a[data-toggle="tab"]');
    if (!a) return;
    var links = a.parentElement.querySelectorAll(':scope > a[data-toggle="tab"]');
    for (var i = 0; i < links.length; i++) {
        if (links[i] === a) { links[i].classList.add('active'); }
        else { links[i].classList.remove('active'); }
    }
});
/* Import client dropzone — clicking anywhere opens the file picker. */
document.addEventListener('click', function (e) {
    var dz = e.target.closest('.cl-dropzone');
    if (!dz) return;
    var input = dz.querySelector('input[type="file"]');
    if (input && !e.target.closest('input')) { input.click(); }
});
/* Client details tables: live search input filter */
document.addEventListener('input', function (e) {
    var input = e.target;
    if (!input || !input.closest('.search-in')) return;
    var card = input.closest('.cl-card');
    if (!card) return;
    var table = card.querySelector('table');
    if (!table) return;
    if (window.jQuery && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable(table)) {
        jQuery(table).DataTable().search(input.value).draw();
        return;
    }
    var q = input.value.toLowerCase().trim();
    var rows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < rows.length; i++) {
        var text = rows[i].textContent.toLowerCase();
        rows[i].style.display = text.indexOf(q) !== -1 ? '' : 'none';
    }
});
</script>
