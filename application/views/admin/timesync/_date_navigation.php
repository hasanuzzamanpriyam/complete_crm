<div class="date-navigation" style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:8px 0;">
  <div class="dn-left" style="display:flex;align-items:center;gap:6px;">
    <i class="fa fa-calendar" style="font-size:16px;color:#555;"></i>
    <span id="dn-period-label" style="font-weight:600;font-size:14px;min-width:120px;display:inline-block;"></span>
    <span id="dn-custom-inputs" style="display:none;align-items:center;gap:6px;">
      <input type="date" id="dn-custom-from" class="form-control input-sm" style="height:28px;font-size:12px;width:140px;display:inline-block;">
      <span style="color:#999;font-size:12px;">—</span>
      <input type="date" id="dn-custom-to" class="form-control input-sm" style="height:28px;font-size:12px;width:140px;display:inline-block;">
    </span>
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
    <button type="button" class="btn btn-xs dn-interval-tab" data-interval="custom" style="border-radius:4px;padding:2px 10px;font-size:11px;">Custom</button>
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

  var activeHighlightInterval = params.get('interval') || 'daily';

  if (window.__spaCurrentFilterState) {
    if (window.__spaCurrentFilterState.interval) {
      interval = window.__spaCurrentFilterState.interval;
      activeHighlightInterval = window.__spaCurrentFilterState.interval;
    }
    if (window.__spaCurrentFilterState.from) fromStr = window.__spaCurrentFilterState.from;
    if (window.__spaCurrentFilterState.to) toStr = window.__spaCurrentFilterState.to;
    window.__spaCurrentFilterState = null;
  }

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
      case 'custom':
        if (!from || !to) {
          f = new Date(d); t = new Date(d);
        } else {
          return { from: from, to: to };
        }
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
    var isCustom = interval === 'custom';

    if (isCustom) {
      label = fmtYmd(from) + ' \u2014 ' + fmtYmd(to);
      document.getElementById('dn-period-label').style.display = 'none';
      var cust = document.getElementById('dn-custom-inputs');
      cust.style.display = 'inline-flex';
      document.getElementById('dn-custom-from').value = fmtYmd(from);
      document.getElementById('dn-custom-to').value = fmtYmd(to);
      document.getElementById('dn-btn-prev').style.display = 'none';
      document.getElementById('dn-btn-next').style.display = 'none';
      document.getElementById('dn-btn-current').style.display = 'none';
    } else {
      document.getElementById('dn-custom-inputs').style.display = 'none';
      document.getElementById('dn-period-label').style.display = 'inline-block';
      document.getElementById('dn-btn-prev').style.display = '';
      document.getElementById('dn-btn-next').style.display = '';

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

      var td = today();
      var fn = new Date(from); fn.setHours(0,0,0,0);
      var tn = new Date(to); tn.setHours(0,0,0,0);
      var showCurrent = td < fn || td > tn;
      document.getElementById('dn-btn-current').style.display = showCurrent ? '' : 'none';
    }

    document.getElementById('dn-period-label').textContent = label;

    document.getElementById('dn-from-hidden').value = fmtYmd(from);
    document.getElementById('dn-to-hidden').value = fmtYmd(to);
    document.getElementById('dn-interval-hidden').value = interval;

    document.querySelectorAll('.dn-interval-tab').forEach(function(btn) {
      if (activeHighlightInterval && btn.dataset.interval === activeHighlightInterval) {
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
    var targetFrom = fmtYmd(newFrom);
    var targetTo = fmtYmd(newTo);
    var targetInterval = newInterval || interval;

    q.set('from', targetFrom);
    q.set('to', targetTo);
    q.set('interval', targetInterval);

    window.__spaCurrentFilterState = {
      from: targetFrom,
      to: targetTo,
      interval: targetInterval
    };

    var url = window.location.pathname + '?' + q.toString();
    if (window.__spaNavigate) {
      window.__spaNavigate(url);
    } else {
      window.location.href = url;
    }
  }

  document.getElementById('dn-btn-prev').addEventListener('click', function() {
    if (interval === 'custom') return;
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
    if (interval === 'custom') return;
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
      activeHighlightInterval = ni;

      document.querySelectorAll('.dn-interval-tab').forEach(function(b) {
        if (b.dataset.interval === ni) {
          b.style.background = '#337ab7';
          b.style.color = '#fff';
          b.style.borderColor = '#337ab7';
        } else {
          b.style.background = '';
          b.style.color = '';
          b.style.borderColor = '';
        }
      });

      if (ni === 'custom') {
        navigate(from, to, ni);
      } else {
        var r = getIntervalRange(ni, today());
        navigate(r.from, r.to, ni);
      }
    });
  });

  document.getElementById('dn-custom-from').addEventListener('change', function() {
    var newFrom = parseYmd(this.value);
    var newTo = parseYmd(document.getElementById('dn-custom-to').value);
    if (newFrom && newTo && !isNaN(newFrom.getTime()) && !isNaN(newTo.getTime())) {
      navigate(newFrom, newTo, 'custom');
    }
  });

  document.getElementById('dn-custom-to').addEventListener('change', function() {
    var newFrom = parseYmd(document.getElementById('dn-custom-from').value);
    var newTo = parseYmd(this.value);
    if (newFrom && newTo && !isNaN(newFrom.getTime()) && !isNaN(newTo.getTime())) {
      navigate(newFrom, newTo, 'custom');
    }
  });

  updateUI();
})();
</script>
