<div class="booking-card">
    <div class="booking-card-header text-center">
        <h2>Book a Free Consultation</h2>
        <p class="text-muted">Pick a consultant, choose a time that works for you, and we'll take care of the rest.</p>
    </div>

    <div class="booking-steps">
        <div class="booking-step active" data-step="1">
            <span class="step-num">1</span> Consultant
        </div>
        <div class="booking-step" data-step="2">
            <span class="step-num">2</span> Date &amp; Time
        </div>
        <div class="booking-step" data-step="3">
            <span class="step-num">3</span> Your Details
        </div>
    </div>

    <!-- Step 1: Consultant selection -->
    <div class="booking-pane active" data-pane="1">
        <div id="consultant-list" class="consultant-grid">
            <div class="text-center p-lg"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
        </div>
    </div>

    <!-- Step 2: Date & time -->
    <div class="booking-pane" data-pane="2">
        <div id="timezone-banner" class="timezone-banner">
            <i class="fa fa-globe"></i>
            Showing times in your timezone: <strong id="tz-label">detecting...</strong>
        </div>
        <div id="date-strip" class="date-strip">
            <div class="text-center p-lg text-muted"><i class="fa fa-spinner fa-spin"></i> Loading available days...</div>
        </div>
        <div id="time-list" class="time-list">
            <div class="text-center p-lg text-muted">Select a day above to see available times.</div>
        </div>
    </div>

    <!-- Step 3: Customer details -->
    <div class="booking-pane" data-pane="3">
        <div class="row">
            <div class="col-md-7">
                <form id="booking-form" novalidate>
                    <input type="hidden" name="consultant_id" id="f-consultant-id">
                    <input type="hidden" name="appointment_date" id="f-date">
                    <input type="hidden" name="appointment_time" id="f-time">
                    <input type="hidden" name="duration_minutes" id="f-duration">

                    <div class="form-group">
                        <label for="f-customer-name">Your Name *</label>
                        <input type="text" class="form-control" id="f-customer-name" name="customer_name"
                               placeholder="John Smith" required>
                    </div>
                    <div class="form-group">
                        <label for="f-customer-email">Email *</label>
                        <input type="email" class="form-control" id="f-customer-email" name="customer_email"
                               placeholder="john@example.com" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="f-customer-phone">Phone</label>
                                <input type="text" class="form-control" id="f-customer-phone" name="customer_phone"
                                       placeholder="+1 555 000 1234">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="f-company">Company</label>
                                <input type="text" class="form-control" id="f-company" name="company">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="f-timezone">Your Timezone *</label>
                        <select class="form-control" id="f-timezone" name="customer_timezone" required>
                            <option value="">Select your timezone...</option>
                            <?php foreach ($timezones as $tz) : ?>
                                <option value="<?= $tz ?>"><?= $tz ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f-consultation-type">Consultation Type</label>
                        <select class="form-control" id="f-consultation-type" name="consultation_type">
                            <option value="consultation">General Consultation</option>
                            <option value="product_demo">Product Demo</option>
                            <option value="sales_call">Sales Call</option>
                            <option value="support">Support</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="f-notes">Notes</label>
                        <textarea class="form-control" id="f-notes" name="notes" rows="3"
                                  placeholder="Anything you'd like us to know before the call?"></textarea>
                    </div>
                    <button type="submit" id="book-submit" class="btn btn-success btn-lg btn-block">
                        <i class="fa fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
            <div class="col-md-5">
                <div class="booking-summary">
                    <h4>Your Appointment</h4>
                    <div class="summary-row">
                        <span>Consultant</span>
                        <strong id="summary-consultant">—</strong>
                    </div>
                    <div class="summary-row">
                        <span>Date &amp; time</span>
                        <strong id="summary-datetime">—</strong>
                    </div>
                    <div class="summary-row">
                        <span>Duration</span>
                        <strong id="summary-duration">—</strong>
                    </div>
                    <div class="summary-row">
                        <span>Timezone</span>
                        <strong id="summary-timezone">—</strong>
                    </div>
                    <p class="text-muted mt-sm">You'll receive a confirmation email with your video meeting link.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.BookingConfig = {
        baseUrl: '<?= base_url() ?>',
        getConsultantsUrl: '<?= site_url('booking/get_consultants') ?>',
        getSlotsUrl: '<?= site_url('booking/get_slots') ?>',
        bookUrl: '<?= site_url('booking/book') ?>',
        defaultDuration: <?= (int)config_item('consultation_default_duration') ?: 30 ?>,
        slotDays: 14
    };
</script>
