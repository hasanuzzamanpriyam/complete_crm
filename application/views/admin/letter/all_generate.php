<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong>Generated Letters</strong>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group pull-right m-b-15">
                    <a href="<?= base_url('admin/letter/add_generate') ?>"
                       class="btn btn-success">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                    <a href="<?= base_url('admin/letter/variables') ?>" class="btn btn-info">
                        <i class="fa fa-list"></i> Manage Variables
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped DataTables" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Employee</th>
                    <th>Letter Type</th>
                    <th>Created</th>
                    <th class="col-options no-sort">Action</th>
                </tr>
                </thead>
                <tbody>
                <script type="text/javascript">
                    $(document).ready(function () {
                        list = base_url + "admin/letter/getGeneratedList";
                    });
                </script>
                </tbody>
            </table>
        </div>
    </div>
</div>
