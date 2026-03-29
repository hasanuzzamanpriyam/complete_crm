<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-custom">
            <header class="panel-heading">
                <div class="panel-title"><strong>Add/Edit Mapping</strong></div>
            </header>
            <div class="panel-body">
                <form action="<?= base_url('admin/attendance/save_biometric_mapping') ?>" method="post" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Device User ID</label>
                        <div class="col-sm-8">
                            <input type="text" name="device_user_id" class="form-control" required placeholder="e.g. 101">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Employee</label>
                        <div class="col-sm-8">
                            <select name="user_id" class="form-control select_box" style="width: 100%" required>
                                <option value="">Select Employee</option>
                                <?php foreach($all_employee as $dept_name => $employees): ?>
                                    <optgroup label="<?= $dept_name ?>">
                                        <?php foreach($employees as $employee): ?>
                                            <option value="<?= $employee->user_id ?>"><?= $employee->fullname ?> (<?= $employee->employment_id ?>)</option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary">Save Mapping</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="panel panel-custom">
            <header class="panel-heading">
                <div class="panel-title"><strong>Current Mappings</strong></div>
            </header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped DataTables">
                        <thead>
                            <tr>
                                <th>Device User ID</th>
                                <th>Employee Name</th>
                                <th>Employment ID</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($mappings as $mapping): ?>
                            <tr>
                                <td><?= $mapping->device_user_id ?></td>
                                <td><?= $mapping->fullname ?></td>
                                <td><?= $mapping->employment_id ?></td>
                                <td>
                                    <a href="<?= base_url('admin/attendance/delete_biometric_mapping/'.$mapping->id) ?>" 
                                       class="btn btn-danger btn-xs" 
                                       onclick="return confirm('Are you sure?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
