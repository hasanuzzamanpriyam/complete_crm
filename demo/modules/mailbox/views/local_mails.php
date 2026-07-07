<div class="row">
    <div class="col-md-12">
        <form method="post" action="<?php echo base_url() ?>admin/mailbox/delete_mail/<?= $action; ?>">
            <!-- Main content -->
            <div class="panel panel-custom">
                <div class="panel-heading">
                    <div class="mailbox-controls">

                        <!-- Check all button -->
                        <div class="mail_checkbox mr-sm">
                            <input type="checkbox" id="parent_present">
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-default btn-xs mr-sm"><i class="fa fa-trash-o"></i></button>
                        </div><!-- /.btn-group -->
                        <a href="<?php echo base_url() ?>admin/mailbox/index/compose"
                           class="btn btn-danger btn-xs mr-sm">Compose +</a>
                    </div>
                </div>
                <div class="table-responsive mailbox-messages">
                    <table class="table table-striped DataTables mb-mails" id="DataTables" width="100%">
                        <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th><?= lang('host') ?></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <script type="text/javascript">
                            list = base_url + "admin/mailbox/inboxList/<?= $action; ?>";
                        </script>
                    </table>
                </div>
            </div><!-- /.box-body -->
    </div><!-- /. box -->
    </form>
</div><!-- /.content-wrapper -->
