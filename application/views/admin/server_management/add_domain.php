<?php echo message_box('success'); ?>
<?php echo message_box('error'); ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form>
                    <!-- Row 1 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Domain Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control" required>
                                        <option value="">Select Provider</option>
                                        <option value="Google Workspace">Google Workspace</option>
                                        <option value="GoDaddy">GoDaddy</option>
                                        <option value="Namecheap">Namecheap</option>
                                        <option value="Cloudflare">Cloudflare</option>
                                        <option value="AWS">AWS</option>
                                        <option value="MongoDB Atlas">MongoDB Atlas</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button">Add</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider URL</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Type <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="COM">COM</option>
                                    <option value="NET">NET</option>
                                    <option value="ORG">ORG</option>
                                    <option value="IO">IO</option>
                                    <option value="DEV">DEV</option>
                                    <option value="TECH">TECH</option>
                                    <option value="CO">CO</option>
                                    <option value="APP">APP</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hosting</label>
                                <select class="form-control">
                                    <option value="">Select Hosting</option>
                                    <option value="API Server">API Server</option>
                                    <option value="Production Web Server">Production Web Server</option>
                                    <option value="Development Server">Development Server</option>
                                    <option value="Testing Server">Testing Server</option>
                                    <option value="Backup Server">Backup Server</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Transferring">Transferring</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Plan <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select Plan</option>
                                    <option value="Basic">Basic</option>
                                    <option value="Standard">Standard</option>
                                    <option value="Professional">Professional</option>
                                    <option value="Enterprise">Enterprise</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar URL</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Username</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registrar Status</label>
                                <select class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Expired">Expired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Project</label>
                                <select class="form-control">
                                    <option value="">Select Project</option>
                                    <option value="Project A">Project A</option>
                                    <option value="Project B">Project B</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Client</label>
                                <div class="input-group">
                                    <select class="form-control">
                                        <option value="">Select Client</option>
                                        <option value="Client A">Client A</option>
                                        <option value="Client B">Client B</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button">Add</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Toggles -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="auto_renewal" checked>
                                    <label class="custom-control-label" for="auto_renewal">Auto Renewal</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="whois_protection" checked>
                                    <label class="custom-control-label" for="whois_protection">WHOIS Protection</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="expiry_notification" checked>
                                    <label class="custom-control-label" for="expiry_notification">Expiry Notification</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Notification Days Before</label>
                                <input type="number" class="form-control" value="7">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Notification Time Unit</label>
                                <select class="form-control">
                                    <option value="Days">Days</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Months">Months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" rows="4" placeholder="Enter description..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer text-right">
                        <a href="<?= base_url('admin/server_management/domain') ?>" class="btn btn-link">Cancel</a>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>