(function () {
    'use strict';

    if (typeof BookingConfig === 'undefined') {
        return;
    }

    var state = {
        tz: '',
        viewYear: 0,
        viewMonth: 0,
        selectedDate: null,
        selectedTime: null,
        availableSet: {}
    };

    var els = {};

    function $(id) { return document.getElementById(id); }

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function fmtDate(y, m, d) {
        return y + '-' + pad(m + 1) + '-' + pad(d);
    }

    function monthName(m) {
        var names = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];
        return names[m];
    }

    function shortMonth(m) {
        return monthName(m).slice(0, 3);
    }

    function detectTimezone() {
        try {
            if (typeof Intl !== 'undefined' && Intl.DateTimeFormat) {
                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                if (tz) { return tz; }
            }
        } catch (e) { /* ignore */ }
        return BookingConfig.companyTimezone || 'UTC';
    }

    function showAlert(msg, type) {
        if (!els.alert) { return; }
        els.alert.className = 'alert alert-' + (type || 'danger');
        els.alert.textContent = msg;
    }

    function clearAlert() {
        if (els.alert) { els.alert.className = ''; els.alert.textContent = ''; }
    }

    function setStep(step) {
        var steps = document.querySelectorAll('.booking-step');
        steps.forEach(function (s) {
            var n = parseInt(s.getAttribute('data-step'), 10);
            s.classList.remove('active', 'done');
            if (n === step) { s.classList.add('active'); }
            else if (n < step) { s.classList.add('done'); }
        });
        var panes = { 1: 'schedule', 2: 'details', 3: 'success' };
        ['schedule', 'details', 'success'].forEach(function (p) {
            var pane = $('pane-' + p);
            if (pane) { pane.classList.toggle('active', panes[step] === p); }
        });
    }

    function fetchAvailable(from, to, cb) {
        var url = BookingConfig.getSlotsUrl + '?from=' + encodeURIComponent(from) +
            '&to=' + encodeURIComponent(to) + '&timezone=' + encodeURIComponent(state.tz) +
            '&duration=' + BookingConfig.defaultDuration;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var set = {};
                if (data && data.success && Array.isArray(data.available_dates)) {
                    data.available_dates.forEach(function (d) { set[d] = true; });
                }
                cb(set);
            })
            .catch(function () { cb({}); });
    }

    function renderCalendar() {
        var y = state.viewYear, m = state.viewMonth;
        els.calTitle.textContent = monthName(m) + ' ' + y;

        var first = new Date(y, m, 1);
        var startPad = first.getDay();
        var daysInMonth = new Date(y, m + 1, 0).getDate();

        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var minDate = new Date(today.getFullYear(), today.getMonth(), 1);

        els.calPrev.disabled = (y === minDate.getFullYear() && m === minDate.getMonth());

        var from = fmtDate(y, m, 1);
        var last = new Date(y, m + 1, 0);
        var to = fmtDate(y, m, last.getDate());

        els.calDays.innerHTML = '';
        for (var p = 0; p < startPad; p++) {
            var empty = document.createElement('div');
            empty.className = 'cal-day empty';
            els.calDays.appendChild(empty);
        }

        fetchAvailable(from, to, function (avail) {
            state.availableSet = avail;
            for (var d = 1; d <= daysInMonth; d++) {
                var cell = document.createElement('div');
                cell.className = 'cal-day';
                cell.textContent = d;
                var dateStr = fmtDate(y, m, d);
                var cellDate = new Date(y, m, d);
                cellDate.setHours(0, 0, 0, 0);

                if (cellDate < today) {
                    cell.classList.add('disabled');
                } else if (avail[dateStr]) {
                    cell.classList.add('available');
                    var dot = document.createElement('span');
                    dot.className = 'dot';
                    cell.appendChild(dot);
                } else {
                    cell.classList.add('disabled');
                }

                if (state.selectedDate === dateStr) {
                    cell.classList.add('selected');
                }

                if (cell.classList.contains('available')) {
                    (function (ds) {
                        cell.addEventListener('click', function () { selectDate(ds); });
                    })(dateStr);
                }

                els.calDays.appendChild(cell);
            }
        });
    }

    function selectDate(dateStr) {
        state.selectedDate = dateStr;
        state.selectedTime = null;
        if (els.toDetails) { els.toDetails.disabled = true; }
        renderCalendar();
        loadSlots(dateStr);
        var dt = new Date(dateStr + 'T00:00:00');
        els.slotsDateLabel.textContent = shortMonth(dt.getMonth()) + ' ' + dt.getDate() + ', ' + dt.getFullYear();
    }

    function loadSlots(dateStr) {
        els.slotsList.innerHTML = '<p class="text-muted slots-empty"><i class="fa fa-spinner fa-spin"></i> Loading...</p>';
        var url = BookingConfig.getSlotsUrl + '?date=' + encodeURIComponent(dateStr) +
            '&timezone=' + encodeURIComponent(state.tz) + '&duration=' + BookingConfig.defaultDuration;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                els.slotsList.innerHTML = '';
                var slots = (data && data.slots && data.slots[dateStr]) ? data.slots[dateStr] : [];
                if (!slots.length) {
                    els.slotsList.innerHTML = '<p class="text-muted slots-empty">' + BookingConfig.lang.noSlots + '</p>';
                    return;
                }
                slots.forEach(function (s) {
                    var pill = document.createElement('button');
                    pill.type = 'button';
                    pill.className = 'time-pill';
                    pill.textContent = s.time_display;
                    pill.addEventListener('click', function () {
                        var all = els.slotsList.querySelectorAll('.time-pill');
                        all.forEach(function (pp) { pp.classList.remove('selected'); });
                        pill.classList.add('selected');
                        state.selectedTime = s.time;
                        if (els.toDetails) { els.toDetails.disabled = false; }
                    });
                    els.slotsList.appendChild(pill);
                });
            })
            .catch(function () {
                els.slotsList.innerHTML = '<p class="text-muted slots-empty">Could not load times. Please try again.</p>';
            });
    }

    function goDetails() {
        if (!state.selectedDate || !state.selectedTime) { return; }
        clearAlert();
        $('f-date').value = state.selectedDate;
        $('f-time').value = state.selectedTime;
        $('f-timezone').value = state.tz;
        $('f-duration').value = BookingConfig.defaultDuration;
        var dt = new Date(state.selectedDate + 'T00:00:00');
        $('sum-date').textContent = monthName(dt.getMonth()) + ' ' + dt.getDate() + ', ' + dt.getFullYear();
        $('sum-time').textContent = state.selectedTime;
        setStep(2);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function submitBooking(e) {
        e.preventDefault();
        clearAlert();
        var form = $('booking-form');
        if (form.reportValidity && !form.reportValidity()) {
            return;
        }
        var btn = $('book-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Booking...';

        var payload = {
            appointment_date: $('f-date').value,
            appointment_time: $('f-time').value,
            customer_timezone: $('f-timezone').value,
            duration_minutes: $('f-duration').value,
            customer_name: $('f-name').value,
            customer_email: $('f-email').value,
            customer_phone: $('f-phone').value,
            company: $('f-company').value,
            country: $('f-country').value,
            consultation_type: $('f-type').value,
            notes: $('f-notes').value
        };

        var body = Object.keys(payload).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(payload[k]);
        }).join('&');

        fetch(BookingConfig.bookUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            btn.innerHTML = BookingConfig.lang.confirmBooking;
            if (data && data.success) {
                var link = $('success-link');
                if (link && data.confirm_url) { link.href = data.confirm_url; }
                setStep(3);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                showAlert((data && data.message) ? data.message : 'Something went wrong. Please try again.', 'danger');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = BookingConfig.lang.confirmBooking;
            showAlert('Network error. Please try again.', 'danger');
        });
    }

    function init() {
        els.alert = $('booking-alert');
        els.calTitle = $('cal-title');
        els.calDays = $('cal-days');
        els.calPrev = $('cal-prev');
        els.calNext = $('cal-next');
        els.slotsList = $('slots-list');
        els.slotsDateLabel = $('slots-date-label');
        els.toDetails = $('to-details');

        state.tz = detectTimezone();
        var tzLabel = $('tz-label');
        if (tzLabel) { tzLabel.textContent = state.tz; }

        var now = new Date();
        state.viewYear = now.getFullYear();
        state.viewMonth = now.getMonth();

        if (els.calPrev) {
            els.calPrev.addEventListener('click', function () {
                if (els.calPrev.disabled) { return; }
                state.viewMonth--;
                if (state.viewMonth < 0) { state.viewMonth = 11; state.viewYear--; }
                renderCalendar();
            });
        }
        if (els.calNext) {
            els.calNext.addEventListener('click', function () {
                state.viewMonth++;
                if (state.viewMonth > 11) { state.viewMonth = 0; state.viewYear++; }
                renderCalendar();
            });
        }

        if (els.toDetails) { els.toDetails.addEventListener('click', goDetails); }
        var back = $('back-schedule');
        if (back) {
            back.addEventListener('click', function () {
                setStep(1);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        var form = $('booking-form');
        if (form) { form.addEventListener('submit', submitBooking); }

        setStep(1);
        renderCalendar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
