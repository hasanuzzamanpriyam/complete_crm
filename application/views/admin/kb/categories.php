<?php echo message_box('success'); ?>
<?php echo message_box('error');
$created = can_action('142', 'created');
$edited = can_action('142', 'edited');
$deleted = can_action('142', 'deleted');
// Shared KB asset pipeline (kb_modern.css).
$this->load->view('kb_shared/_kb_assets');
?>
<style>
    /* =======================================================================
       Scoped to the KB manage tables ONLY (rooted in .kb-tbl) — never global.
       Hides the stock DataTables export-button cluster + default search box
       (search is relocated into the kb toolbar) and restyles the remaining
       DataTables controls with design tokens.
       ======================================================================= */
    .kb-tbl .dataTables_wrapper .dt-buttons {
        display: none;
    }

    /* The kb-toggle pill's width/height only apply to flex items (it is a
       span) — blockify it via its existing wrapper, scoped to these tables. */
    .kb-tbl .change_kb_category,
    .kb-tbl .change_kb {
        display: flex;
        align-items: center;
    }

    .kb-tbl .dataTables_wrapper .dataTables_filter {
        display: none;
    }

    .kb-tbl .dataTables_wrapper .row {
        margin: 0;
    }

    .kb-tbl .dataTables_wrapper .dataTables_length {
        padding: var(--sp-3) var(--sp-5) 0;
        font-size: var(--fs-sm);
        color: var(--c-text-3);
    }

    .kb-tbl .dataTables_wrapper .dataTables_length select {
        border: 1px solid var(--c-border);
        border-radius: var(--r-md);
        padding: 4px var(--sp-2);
        background: var(--c-surface);
        color: var(--c-text);
        margin: 0 var(--sp-1);
    }

    .kb-tbl .dataTables_wrapper .dataTables_filter input {
        border: none;
        outline: none;
        background: transparent;
        flex: 1;
        padding: 9px 0;
        font-size: var(--fs-base);
        color: var(--c-text);
    }

    .kb-tbl .dataTables_wrapper .dataTables_info {
        padding: var(--sp-3) var(--sp-5);
        font-size: var(--fs-sm);
        color: var(--c-text-3);
    }

    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid var(--c-border);
        background: var(--c-surface);
        border-radius: var(--r-md);
        margin-left: var(--sp-1);
        color: var(--c-text-2) !important;
        box-shadow: none !important;
    }

    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--c-surface-2);
        color: var(--c-text) !important;
    }

    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.current:focus,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.active,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.active:hover,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.active:focus,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.active > a,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.active > a:hover {
        background: var(--c-accent) !important;
        color: var(--c-on-accent) !important;
        border-color: var(--c-accent) !important;
    }

    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .kb-tbl .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        color: var(--c-slate) !important;
        background: transparent;
    }
</style>
<div class="kb-root kb-tbl">
    <div class="kb-crumb">
        <a href="<?= base_url('admin/knowledgebase') ?>"><?= lang('knowledgebase') ?></a>
        <span class="kb-sep">/</span>
        <b><?= lang('categories') ?></b>
    </div>
    <div class="kb-sec-head" style="margin-top:0">
        <h2><?= lang('categories') ?></h2>
        <?php if (!empty($created)) { ?>
            <a href="<?= base_url() ?>admin/knowledgebase/new_categories" class="kb-btn kb-btn-primary"
               data-toggle="modal" data-target="#myModal">
                <i class="fa fa-plus"></i> <?= lang('kb_new_category') ?>
            </a>
        <?php } ?>
    </div>
    <div class="kb-toolbar">
        <div class="kb-search-in" id="kb-manage-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        </div>
        <div class="kb-grow"></div>
        <button type="button" class="kb-btn kb-btn-ghost" id="kb-manage-export">
            <i class="fa fa-download"></i> <?= lang('download') ?>
        </button>
    </div>
    <table class="table DataTables" id="DataTables" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th><?= lang('categories') ?></th>
                <th><?= lang('description') ?></th>
                <th><?= lang('kb_articles_count') ?></th>
                <th><?= lang('active') ?></th>
                <th><?= lang('order') ?></th>
                <th><?= lang('action') ?></th>
            </tr>
        </thead>
        <tbody>
        <script type="text/javascript">
            list = base_url + "admin/knowledgebase/categoriesList";
            /* Route DataTables options through the pipeline's opts channel:
               the Articles-count and Action columns are not DB-sortable. */
            var woo_opts = {config: {columnDefs: [{orderable: false, targets: [2, 5]}]}};
        </script>
        </tbody>
    </table>
</div>
<script>
    $(function () {
        /* Relocate the DataTables search input into the kb toolbar (the plugin
           node is moved, so its keyup binding keeps working). Runs after
           fire_datatable()'s own ready handler via the timeout. */
        setTimeout(function () {
            var $filter = $('.kb-tbl .dataTables_filter');
            if ($filter.length) {
                $filter.find('label').contents().filter(function () {
                    return this.nodeType === 3;
                }).remove();
                $filter.appendTo('#kb-manage-search');
            }
        }, 0);

        /* Single toolbar Export button — triggers the (visually hidden) stock
           DataTables Excel export so export behavior stays in the plugin. */
        $('#kb-manage-export').on('click', function () {
            if (typeof table !== 'undefined' && table) {
                table.api().button('.buttons-excel').trigger();
            }
        });
    });

    /* Active status toggle — same wrapper class, endpoint and payload contract
       as before; the stock bootstrap-toggle is replaced by the kb-toggle pill. */
    $(document).on("click", '.change_kb_category', function () {
        var $wrap = $(this);
        var $input = $wrap.find("input[type='checkbox']").first();
        var kb_category_id = $input.data("id");
        var status = $input.is(":checked") ? 2 : 1;
        $input.prop("checked", status === 1);
        $wrap.find(".kb-toggle").toggleClass("on", status === 1);
        $.ajax({
            type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
            url: '<?= base_url() ?>admin/knowledgebase/change_status/' + status + '/' +
            kb_category_id, // the url where we want to POST
            dataType: 'json', // what type of data do we expect back from the server
            encode: true,
            success: function (res) {
                if (res) {
                    toastr[res.status](res.message);
                } else {
                    alert('There was a problem with AJAX');
                }
            }
        })
    });
</script>
