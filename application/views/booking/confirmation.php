<?php
$title = lang('consultation_booking_confirmed');
$details = 'Your free consultation is confirmed.' . "\n"
    . 'Meeting: ' . $appointment->meeting_url;
$google_calendar = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
    . '&text=' . urlencode($title)
    . '&dates=' . urlencode($appointment->start_utc . '/' . $appointment->end_utc)
    . '&details=' . urlencode($details)
    . '&location=' . urlencode($appointment->meeting_url);
?>
<div class="booking-card">
    <div class="booking-confirm text-center">
        <div class="confirm-icon"><i class="fa fa-check-circle"></i></div>
        <h2>You're booked!</h2>
        <p class="lead">
            Your free consultation is confirmed. We've emailed the details to
            <strong><?= html_escape($appointment->customer_email) ?></strong>.
        </p>

        <div class="appointment-summary-box">
            <div class="summary-row">
            <span><?= lang('consultation_consultant') ?></span>
            <strong><?= lang('consultation_specialist') ?></strong>
            </div>
            <div class="summary-row">
                <span>Date</span>
                <strong><?= consultation_format_date($appointment->appointment_date) ?></strong>
            </div>
            <div class="summary-row">
                <span>Time</span>
                <strong><?= consultation_format_time($appointment->appointment_time) ?>
                    (<?= html_escape($appointment->customer_timezone) ?>)</strong>
            </div>
            <div class="summary-row">
                <span>Duration</span>
                <strong><?= (int)$appointment->duration_minutes ?> minutes</strong>
            </div>
        </div>

        <div class="mt-lg">
            <a href="<?= $appointment->meeting_url ?>" target="_blank" class="btn btn-primary btn-lg">
                <i class="fa fa-video-camera"></i> Join the Meeting
            </a>
        </div>
        <div class="mt-sm">
            <a href="<?= $google_calendar ?>" target="_blank" class="btn btn-default">
                <i class="fa fa-calendar-plus-o"></i> Add to Google Calendar
            </a>
        </div>

        <p class="text-muted mt-lg">Save this page, or watch your inbox for the confirmation email with your meeting link.</p>
    </div>
</div>
