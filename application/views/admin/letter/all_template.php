<?= message_box('success'); ?>
<?= message_box('error'); ?>

<div class="panel panel-custom">
    <header class="panel-heading ">
        <div class="panel-title">
            <strong>Letter Templates</strong>
        </div>
    </header>

    <div class="panel-body">
        <div class="row">
            <div class="col-sm-12">
                <div class="btn-group pull-right m-b-15">
                    <a href="<?= base_url('admin/letter/create') ?>"
                       class="btn btn-success" data-toggle="modal" data-target="#myModal">
                        <i class="fa fa-plus"></i> Add Template
                    </a>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped DataTables" id="DataTables" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Title</th>
                    <th class="col-options no-sort">Action</th>
                </tr>
                </thead>
                <tbody>
                <script type="text/javascript">
                    $(document).ready(function () {
                        list = base_url + "admin/letter/templateList";
                    });
                </script>
                </tbody>
            </table>
        </div>
    </div>
</div>
