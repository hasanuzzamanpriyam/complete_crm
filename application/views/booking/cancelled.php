<div class="booking-card">
    <div class="booking-confirm text-center">
        <div class="confirm-icon"><i class="fa fa-times-circle"></i></div>
        <h2>Consultation cancelled</h2>
        <p class="lead">
            Your free consultation with
            <strong><?= html_escape($appointment->consultant_name) ?></strong> on
            <strong><?= consultation_format_date($appointment->appointment_date) ?> at
            <?= consultation_format_time($appointment->appointment_time) ?></strong>
            has been cancelled.
        </p>
        <p class="text-muted">
            A cancellation notice has been sent to <?= html_escape($appointment->customer_email) ?>.
            If this was a mistake, please contact us to rebook.
        </p>
        <div class="mt-lg">
            <a href="<?= site_url('booking') ?>" class="btn btn-success btn-lg">
                <i class="fa fa-calendar-plus-o"></i> Book a New Consultation
            </a>
        </div>
    </div>
</div>
