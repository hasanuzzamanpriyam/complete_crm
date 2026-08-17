<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="booking-card">
    <div class="booking-card-header text-center">
        <h2><?= lang('consultation_book_title') ?></h2>
        <p class="text-muted"><?= lang('consultation_book_subtitle') ?></p>
    </div>

    <div class="booking-steps">
        <div class="booking-step active" data-step="1">
            <span class="step-num">1</span><?= lang('consultation_step_schedule') ?>
        </div>
        <div class="booking-step" data-step="2">
            <span class="step-num">2</span><?= lang('consultation_step_details') ?>
        </div>
    </div>

    <div id="booking-alert"></div>

    <!-- Step 1: Calendar + Slots -->
    <div class="booking-pane active" id="pane-schedule">
        <div class="timezone-banner">
            <i class="fa fa-clock-o"></i> <?= lang('consultation_timezone_note') ?> <strong id="tz-label"></strong>
        </div>

        <div class="schedule-grid">
            <div class="calendar-panel">
                <div class="calendar-head">
                    <button type="button" class="cal-nav" id="cal-prev" aria-label="Previous month"><i class="fa fa-chevron-left"></i></button>
                    <span id="cal-title"></span>
                    <button type="button" class="cal-nav" id="cal-next" aria-label="Next month"><i class="fa fa-chevron-right"></i></button>
                </div>
                <div class="calendar-weekdays">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                </div>
                <div class="calendar-days" id="cal-days"></div>
            </div>

            <div class="slots-panel">
                <h4 class="slots-date-label" id="slots-date-label"><?= lang('consultation_select_day') ?></h4>
                <div class="time-list" id="slots-list">
                    <p class="text-muted slots-empty"><?= lang('consultation_pick_day_first') ?></p>
                </div>
            </div>
        </div>

        <div class="booking-actions">
            <a href="<?= base_url() ?>" class="btn btn-default"><?= lang('consultation_back_home') ?></a>
            <button type="button" class="btn btn-primary" id="to-details" disabled><?= lang('consultation_continue') ?></button>
        </div>
    </div>

    <!-- Step 2: Details -->
    <div class="booking-pane" id="pane-details">
        <div class="booking-summary">
            <h4><?= lang('consultation_selected') ?></h4>
            <div class="summary-row">
                <span><?= lang('consultation_date') ?></span>
                <strong id="sum-date"></strong>
            </div>
            <div class="summary-row">
                <span><?= lang('consultation_time') ?></span>
                <strong id="sum-time"></strong>
            </div>
            <div class="summary-row">
                <span><?= lang('consultation_consultant') ?></span>
                <strong><?= lang('consultation_specialist') ?></strong>
            </div>
        </div>

        <form id="booking-form" class="mt" novalidate>
            <input type="hidden" name="appointment_date" id="f-date">
            <input type="hidden" name="appointment_time" id="f-time">
            <input type="hidden" name="customer_timezone" id="f-timezone">
            <input type="hidden" name="duration_minutes" id="f-duration">

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_customer_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" id="f-name" class="form-control" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_customer_email') ?> <span class="text-danger">*</span></label>
                        <input type="email" name="customer_email" id="f-email" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_customer_phone') ?></label>
                        <input type="text" name="customer_phone" id="f-phone" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_company') ?></label>
                        <input type="text" name="company" id="f-company" class="form-control">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_country') ?></label>
                        <input type="text" name="country" id="f-country" class="form-control">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= lang('consultation_consultation_type') ?></label>
                        <select name="consultation_type" id="f-type" class="form-control">
                            <option value="consultation">General Consultation</option>
                            <option value="sales">Sales</option>
                            <option value="support">Support</option>
                            <option value="technical">Technical</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><?= lang('consultation_notes') ?></label>
                <textarea name="notes" id="f-notes" class="form-control" rows="3"></textarea>
            </div>

            <div class="booking-actions">
                <button type="button" class="btn btn-default" id="back-schedule"><?= lang('consultation_back') ?></button>
                <button type="submit" class="btn btn-primary" id="book-btn"><?= lang('consultation_confirm_booking') ?></button>
            </div>
        </form>
    </div>

    <!-- Success -->
    <div class="booking-pane booking-confirm text-center" id="pane-success">
        <div class="confirm-icon"><i class="fa fa-check-circle"></i></div>
        <h2><?= lang('consultation_booked_title') ?></h2>
        <p class="lead"><?= lang('consultation_booked_text') ?></p>
        <a href="#" id="success-link" class="btn btn-primary btn-lg"><?= lang('consultation_view_details') ?></a>
    </div>
</div>

<?php if (isset($booking_scripts)) echo $booking_scripts; ?>

<script>
    var BookingConfig = {
        getSlotsUrl: "<?= site_url('booking/get_slots') ?>",
        bookUrl: "<?= site_url('booking/book') ?>",
        defaultDuration: <?= (int)$this->consultation_model->get_setting('default_duration', 30) ?>,
        companyTimezone: "<?= consultation_company_timezone() ?>",
        lang: {
            noSlots: "<?= lang('consultation_no_slots') ?>",
            confirmBooking: "<?= lang('consultation_confirm_booking') ?>"
        }
    };
</script>
