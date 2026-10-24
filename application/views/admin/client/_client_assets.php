<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php // Bump the ?v= below whenever assets/css/client_modern.css changes. ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/client_modern.css?v=10">
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
</script>
