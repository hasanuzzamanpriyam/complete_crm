(function ($) {
    'use strict';

    var cfg = window.BookingConfig || {};
    var state = {
        step: 1,
        consultant: null,
        timezone: null,
        slotsByDate: {},
        selectedDate: null,
        selectedTime: null,
        selectedSlot: null
    };

    function detectTimezone() {
        try {
            state.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
        } catch (e) {
            state.timezone = 'UTC';
        }
    }

    function goToStep(step) {
        if (step < 1) step = 1;
        if (step > 3) step = 3;
        state.step = step;
        $('.booking-step').each(function () {
            var s = parseInt($(this).data('step'), 10);
            $(this).toggleClass('active', s === step);
            $(this).toggleClass('done', s < step);
        });
        $('.booking-pane').each(function () {
            $(this).toggleClass('active', parseInt($(this).data('pane'), 10) === step);
        });
    }

    function avatarHtml(consultant) {
        if (consultant.avatar) {
            return '<div class="consultant-avatar"><img src="' + consultant.avatar + '" alt=""></div>';
        }
        var initials = (consultant.name || '?').split(/\s+/).map(function (w) {
            return w.charAt(0);
        }).join('').substring(0, 2).toUpperCase();
        return '<div class="consultant-avatar">' + initials + '</div>';
    }

    function loadConsultants() {
        $('#consultant-list').html('<div class="text-center p-lg"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $.getJSON(cfg.getConsultantsUrl, function (res) {
            if (!res.success) {
                $('#consultant-list').html('<div class="text-center p-lg text-muted">' + (res.message || 'No consultants available.') + '</div>');
                return;
            }
            if (!res.consultants.length) {
                $('#consultant-list').html('<div class="text-center p-lg text-muted">No consultants are available right now. Please check back soon.</div>');
                return;
            }
            var html = '';
            $.each(res.consultants, function (i, c) {
                html += '<div class="consultant-card" data-id="' + c.consultant_id + '" data-json="' +
                    encodeURIComponent(JSON.stringify(c)) + '">'
                    + avatarHtml(c)
                    + '<h4>' + $('<div>').text(c.name).html() + '</h4>'
                    + '<div class="consultant-meta">'
                    + (c.department ? $('<div>').text(c.department).html() + ' · ' : '')
                    + 'Timezone: ' + $('<div>').text(c.timezone).html()
                    + '</div>'
                    + (c.bio ? '<div class="consultant-bio">' + $('<div>').text(c.bio).html() + '</div>' : '')
                    + '<button type="button" class="btn btn-success btn-sm choose-btn"><i class="fa fa-arrow-right"></i> Choose</button>'
                    + '</div>';
            });
            $('#consultant-list').html(html);
        }).fail(function () {
            $('#consultant-list').html('<div class="text-center p-lg text-muted">Could not load consultants. Please refresh the page.</div>');
        });
    }

    function loadSlots() {
        $('#date-strip').html('<div class="text-center p-lg text-muted"><i class="fa fa-spinner fa-spin"></i> Loading available days...</div>');
        $('#time-list').html('<div class="text-center p-lg text-muted">Select a day above to see available times.</div>');
        state.selectedDate = null;
        state.selectedTime = null;
        state.selectedSlot = null;

        $.getJSON(cfg.getSlotsUrl, {
            consultant_id: state.consultant.consultant_id,
            timezone: state.timezone,
            duration: cfg.defaultDuration,
            days: cfg.slotDays || 14
        }, function (res) {
            if (!res.success) {
                $('#date-strip').html('<div class="text-center p-lg text-muted">' + (res.message || 'No availability found.') + '</div>');
                return;
            }
            state.slotsByDate = res.slots || {};
            var dates = Object.keys(state.slotsByDate).sort();
            if (!dates.length) {
                $('#date-strip').html('<div class="text-center p-lg text-muted">No available slots in the next ' + (cfg.slotDays || 14) + ' days.</div>');
                return;
            }
            var weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            var html = '';
            $.each(dates, function (i, d) {
                var parts = d.split('-');
                var dt = new Date(parts[0], parts[1] - 1, parts[2]);
                html += '<div class="date-cell" data-date="' + d + '">'
                    + '<div class="date-weekday">' + weekdays[dt.getDay()] + '</div>'
                    + '<div class="date-day">' + parts[2] + '</div>'
                    + '<div class="date-month">' + months[dt.getMonth()] + '</div>'
                    + '</div>';
            });
            $('#date-strip').html(html);
        }).fail(function () {
            $('#date-strip').html('<div class="text-center p-lg text-muted">Could not load availability. Please refresh.</div>');
        });
    }

    function renderTimes(date) {
        var slots = state.slotsByDate[date] || [];
        if (!slots.length) {
            $('#time-list').html('<div class="text-center p-lg text-muted">No available times on this day.</div>');
            return;
        }
        var html = '<div class="p-lg">';
        $.each(slots, function (i, s) {
            html += '<span class="time-pill" data-time="' + s.time + '" data-datetime="' + s.datetime + '">'
                + s.time_display + '</span>';
        });
        html += '</div>';
        $('#time-list').html(html);
    }

    function updateSummary() {
        if (state.consultant) {
            $('#summary-consultant').text(state.consultant.name);
        }
        if (state.selectedDate && state.selectedTime) {
            var dt = new Date(state.selectedDate + 'T' + state.selectedTime + ':00');
            var opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' };
            $('#summary-datetime').text(dt.toLocaleString(undefined, opts));
        }
        $('#summary-duration').text((cfg.defaultDuration || 30) + ' minutes');
        $('#summary-timezone').text(state.timezone || '—');
    }

    function fillStep3() {
        $('#f-consultant-id').val(state.consultant.consultant_id);
        $('#f-date').val(state.selectedDate);
        $('#f-time').val(state.selectedTime);
        $('#f-duration').val(cfg.defaultDuration || 30);
        $('#f-timezone').val(state.timezone);
        $('#summary-consultant').text(state.consultant.name);
        updateSummary();
    }

    function submitBooking() {
        var $btn = $('#book-submit');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Confirming...');

        var data = $('#booking-form').serialize();
        $.ajax({
            type: 'POST',
            url: cfg.bookUrl,
            data: data,
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                window.location.href = res.confirm_url;
            } else {
                $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Confirm Booking');
                toastr.error(res.message || 'Could not complete your booking. Please try again.');
            }
        }).fail(function (xhr) {
            $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> Confirm Booking');
            var msg = 'Could not complete your booking. Please try again.';
            try {
                var body = JSON.parse(xhr.responseText);
                if (body.message) msg = body.message;
            } catch (e) {}
            toastr.error(msg);
        });
    }

    $(function () {
        detectTimezone();
        $('#tz-label').text(state.timezone);

        // Step 1: choose consultant
        $(document).on('click', '.consultant-card', function () {
            $('.consultant-card').removeClass('selected');
            $(this).addClass('selected');
            var raw = decodeURIComponent($(this).data('json'));
            state.consultant = JSON.parse(raw);
            state.selectedDate = null;
            state.selectedTime = null;
            loadSlots();
            goToStep(2);
        });

        // Step 2: choose date
        $(document).on('click', '.date-cell:not(.disabled)', function () {
            $('.date-cell').removeClass('selected');
            $(this).addClass('selected');
            state.selectedDate = $(this).data('date');
            state.selectedTime = null;
            state.selectedSlot = null;
            renderTimes(state.selectedDate);
        });

        // Step 2: choose time
        $(document).on('click', '.time-pill', function () {
            $('.time-pill').removeClass('selected');
            $(this).addClass('selected');
            state.selectedTime = $(this).data('time');
            state.selectedSlot = $(this).data('datetime');
            fillStep3();
            goToStep(3);
        });

        // Step 3: submit
        $('#booking-form').on('submit', function (e) {
            e.preventDefault();
            var ok = true;
            ['#f-customer-name', '#f-customer-email', '#f-timezone'].forEach(function (sel) {
                var $f = $(sel);
                if (!$f.val() || !$.trim($f.val())) {
                    $f.closest('.form-group').addClass('has-error');
                    ok = false;
                } else {
                    $f.closest('.form-group').removeClass('has-error');
                }
            });
            if (!ok) {
                toastr.error('Please fill in all required fields.');
                return;
            }
            submitBooking();
        });

        loadConsultants();
    });
})(jQuery);
