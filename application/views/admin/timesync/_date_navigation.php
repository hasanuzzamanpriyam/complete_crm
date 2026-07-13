<div class="date-navigation" style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:8px 0;">
  <div class="dn-left" style="display:flex;align-items:center;gap:6px;">
    <i class="fa fa-calendar" style="font-size:16px;color:#555;"></i>
    <span id="dn-period-label" style="font-weight:600;font-size:14px;min-width:120px;display:inline-block;"></span>
    <a href="#" id="dn-date-picker-trigger" style="color:#999;text-decoration:none;font-size:12px;position:relative;" title="Pick a date">
      <i class="fa fa-caret-down"></i>
      <input type="text" id="dn-date-picker" class="datepicker" style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer;border:none;padding:0;margin:0;font-size:1px;" autocomplete="off" readonly>
    </a>
  </div>

  <div class="dn-middle" style="display:flex;align-items:center;gap:4px;margin-left:12px;">
    <button type="button" id="dn-btn-current" class="btn btn-xs btn-default" style="border-radius:14px;padding:2px 10px;font-size:11px;display:none;">
      <i class="fa fa-undo"></i> Current
    </button>
    <button type="button" id="dn-btn-prev" class="btn btn-xs btn-default" style="border-radius:14px;padding:2px 10px;font-size:11px;">
      <i class="fa fa-chevron-left"></i> Prev
    </button>
    <button type="button" id="dn-btn-next" class="btn btn-xs btn-default" style="border-radius:14px;padding:2px 10px;font-size:11px;">
      Next <i class="fa fa-chevron-right"></i>
    </button>
  </div>

  <div class="dn-intervals" style="display:flex;align-items:center;gap:2px;margin-left:12px;">
    <button type="button" class="btn btn-xs dn-interval-tab" data-interval="daily" style="border-radius:4px;padding:2px 10px;font-size:11px;">Daily</button>
    <button type="button" class="btn btn-xs dn-interval-tab" data-interval="weekly" style="border-radius:4px;padding:2px 10px;font-size:11px;">Weekly</button>
    <button type="button" class="btn btn-xs dn-interval-tab" data-interval="fortnightly" style="border-radius:4px;padding:2px 10px;font-size:11px;">Fortnightly</button>
    <button type="button" class="btn btn-xs dn-interval-tab" data-interval="monthly" style="border-radius:4px;padding:2px 10px;font-size:11px;">Monthly</button>
  </div>

  <input type="hidden" name="from" id="dn-from-hidden" value="">
  <input type="hidden" name="to" id="dn-to-hidden" value="">
  <input type="hidden" name="interval" id="dn-interval-hidden" value="">
</div>

<script>
(function() {
  var params = new URLSearchParams(window.location.search);
  var fromStr = params.get('from') || '';
  var toStr = params.get('to') || '';
  var interval = params.get('interval') || 'daily';

  function parseYmd(str) {
    if (!str) return null;
    var parts = str.split('-');
    if (parts.length !== 3) return null;
    return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
  }

  function fmtYmd(d) {
    return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
  }

  function today() {
    var d = new Date();
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
  }

  function getIntervalRange(intv, refDate) {
    var d = new Date(refDate);
    var f, t;
    switch (intv) {
      case 'daily':
        f = new Date(d); t = new Date(d);
        break;
      case 'weekly':
        var day = d.getDay();
        var diff = day === 0 ? -6 : 1 - day;
        f = new Date(d);
        f.setDate(d.getDate() + diff);
        t = new Date(f);
        t.setDate(f.getDate() + 6);
        break;
      case 'fortnightly':
        f = new Date(d);
        t = new Date(d);
        t.setDate(d.getDate() + 13);
        break;
      case 'monthly':
        f = new Date(d.getFullYear(), d.getMonth(), 1);
        t = new Date(d.getFullYear(), d.getMonth() + 1, 0);
        break;
      default:
        f = new Date(d); t = new Date(d);
    }
    return { from: f, to: t };
  }

  var from = parseYmd(fromStr);
  var to = parseYmd(toStr);

  if (!from || !to || isNaN(from.getTime()) || isNaN(to.getTime())) {
    var def = getIntervalRange(interval, today());
    from = def.from; to = def.to;
  }

  function updateUI() {
    var label = '';
    var isDaily = interval === 'daily';
    var isMonthly = interval === 'monthly';

    if (isMonthly) {
      label = from.toLocaleString('en', { month: 'long', year: 'numeric' });
    } else if (isDaily) {
      label = from.toLocaleString('en', { month: 'short', day: 'numeric', year: 'numeric' });
    } else {
      var sameMonth = from.getMonth() === to.getMonth() && from.getFullYear() === to.getFullYear();
      if (sameMonth) {
        label = from.toLocaleString('en', { month: 'short' }) + ' ' + from.getDate() + ' \u2013 ' + to.getDate();
      } else {
        label = from.toLocaleString('en', { month: 'short', day: 'numeric' }) + ' \u2013 ' + to.toLocaleString('en', { month: 'short', day: 'numeric' });
      }
    }

    document.getElementById('dn-period-label').textContent = label;

    document.getElementById('dn-from-hidden').value = fmtYmd(from);
    document.getElementById('dn-to-hidden').value = fmtYmd(to);
    document.getElementById('dn-interval-hidden').value = interval;

    var td = today();
    var fn = new Date(from); fn.setHours(0,0,0,0);
    var tn = new Date(to); tn.setHours(0,0,0,0);
    var showCurrent = td < fn || td > tn;
    document.getElementById('dn-btn-current').style.display = showCurrent ? '' : 'none';

    document.querySelectorAll('.dn-interval-tab').forEach(function(btn) {
      if (btn.dataset.interval === interval) {
        btn.style.background = '#337ab7';
        btn.style.color = '#fff';
        btn.style.borderColor = '#337ab7';
      } else {
        btn.style.background = '';
        btn.style.color = '';
        btn.style.borderColor = '';
      }
    });
  }

  function navigate(newFrom, newTo, newInterval) {
    var q = new URLSearchParams(window.location.search);
    q.set('from', fmtYmd(newFrom));
    q.set('to', fmtYmd(newTo));
    q.set('interval', newInterval || interval);
    window.location.href = window.location.pathname + '?' + q.toString();
  }

  document.getElementById('dn-btn-prev').addEventListener('click', function() {
    var nf = new Date(from), nt = new Date(to);
    switch (interval) {
      case 'daily': nf.setDate(from.getDate() - 1); nt.setDate(to.getDate() - 1); break;
      case 'weekly': nf.setDate(from.getDate() - 7); nt.setDate(to.getDate() - 7); break;
      case 'fortnightly': nf.setDate(from.getDate() - 14); nt.setDate(to.getDate() - 14); break;
      case 'monthly': nf.setMonth(from.getMonth() - 1); nt.setMonth(to.getMonth() - 1); break;
    }
    navigate(nf, nt, interval);
  });

  document.getElementById('dn-btn-next').addEventListener('click', function() {
    var nf = new Date(from), nt = new Date(to);
    switch (interval) {
      case 'daily': nf.setDate(from.getDate() + 1); nt.setDate(to.getDate() + 1); break;
      case 'weekly': nf.setDate(from.getDate() + 7); nt.setDate(to.getDate() + 7); break;
      case 'fortnightly': nf.setDate(from.getDate() + 14); nt.setDate(to.getDate() + 14); break;
      case 'monthly': nf.setMonth(from.getMonth() + 1); nt.setMonth(to.getMonth() + 1); break;
    }
    navigate(nf, nt, interval);
  });

  document.getElementById('dn-btn-current').addEventListener('click', function() {
    var r = getIntervalRange(interval, today());
    navigate(r.from, r.to, interval);
  });

  document.querySelectorAll('.dn-interval-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var ni = this.dataset.interval;
      var r = getIntervalRange(ni, today());
      navigate(r.from, r.to, ni);
    });
  });

  $(function() {
    $('#dn-date-picker').datepicker({
      autoclose: true,
      format: 'yyyy-mm-dd',
      todayBtn: "linked"
    }).on('changeDate', function(e) {
      var picked = e.date;
      if (!picked) return;
      var r = getIntervalRange(interval, picked);
      navigate(r.from, r.to, interval);
    });
  });

  document.getElementById('dn-date-picker-trigger').addEventListener('click', function(e) {
    e.preventDefault();
    $('#dn-date-picker').datepicker('show');
  });

  updateUI();
})();
</script>
