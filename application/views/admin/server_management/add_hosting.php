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
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Provider Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control" required>
                                        <option value="">Select Provider</option>
                                        <option value="Akamai">Akamai</option>
                                        <option value="Vercel">Vercel</option>
                                        <option value="HostGator">HostGator</option>
                                        <option value="AWS">AWS</option>
                                        <option value="DigitalOcean">DigitalOcean</option>
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
                                <label>Server Type <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="Shared">Shared</option>
                                    <option value="VPS">VPS</option>
                                    <option value="Cloud">Cloud</option>
                                    <option value="Dedicated">Dedicated</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Server Location</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>IP Address</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>CPanel URL</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row">
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
                                <label>Price</label>
                                <input type="number" class="form-control">
                            </div>
                        </div>
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="form-control" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Suspended">Suspended</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Row 5 -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>FTP Username</label>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>FTP Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SSL Settings -->
                    <div class="mt-4 mb-3">
                        <h5>SSL Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="ssl_certificate">
                                    <label class="custom-control-label" for="ssl_certificate">SSL Certificate</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>SSL Expiry Date</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>SSL Type</label>
                                <select class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="Free">Free</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Wildcard">Wildcard</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>SSL Certificate Information</label>
                                <textarea class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="mt-4 mb-3">
                        <h5>Notification Settings</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-2">
                                    <input type="checkbox" class="custom-control-input" id="expiry_notification">
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
                    <div class="mt-4 mb-3">
                        <h5>Description</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <textarea class="form-control" rows="4" placeholder="Enter description..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>