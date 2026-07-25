<style>
.tl-container { font-family: inherit; font-size: 12px; position: relative; }
.tl-day { margin-bottom: 24px; border: 1px solid #e8e8e8; border-radius: 6px; background: #fff; overflow: hidden; }
.tl-day-header { padding: 10px 14px; background: #f7f9fc; border-bottom: 1px solid #e8e8e8; font-weight: 600; font-size: 13px; color: #333; display: flex; align-items: center; justify-content: space-between; }
.tl-day-hours { font-size: 12px; font-weight: 400; color: #666; }
.tl-body { padding: 0 14px 10px; overflow-x: auto; position: relative; }
.tl-scale { display: flex; position: relative; height: 20px; border-bottom: 1px solid #eee; margin-bottom: 2px; }
.tl-scale-hour { flex: 1; text-align: center; font-size: 10px; color: #999; line-height: 20px; border-left: 1px solid #f0f0f0; min-width: 0; white-space: nowrap; overflow: hidden; }
.tl-scale-hour:first-child { border-left: none; }
.tl-tracks { position: relative; }
.tl-track { display: flex; align-items: center; height: 28px; position: relative; margin-bottom: 2px; }
.tl-track-label { width: 90px; min-width: 90px; font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tl-track-bar { flex: 1; position: relative; height: 100%; background: #fafafa; border-radius: 3px; overflow: hidden; }
.tl-block { position: absolute; top: 3px; height: 22px; border-radius: 3px; min-width: 2px; z-index: 1; cursor: default; transition: opacity 0.15s; }
.tl-block:hover { opacity: 0.85; filter: brightness(1.08); }
.tl-block-logged { background: #3b82f6; }
.tl-block-productive { background: #22c55e; }
.tl-block-neutral { background: #f59e0b; }
.tl-block-distracting { background: #ef4444; }
.tl-ss-marker { position: absolute; top: 2px; width: 18px; height: 18px; border-radius: 50%; background: #8b5cf6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 9px; z-index: 2; cursor: pointer; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); transition: transform 0.15s; }
.tl-ss-marker:hover { transform: scale(1.3); z-index: 10; }
.tl-activity-bar { position: absolute; bottom: 0; width: 4px; background: #06b6d4; border-radius: 2px 2px 0 0; min-height: 1px; z-index: 1; }
.tl-crosshair { position: absolute; top: 0; width: 1px; background: rgba(0,0,0,0.3); z-index: 20; pointer-events: none; display: none; }
.tl-crosshair::before { content: attr(data-time); position: absolute; top: -18px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 1px 6px; border-radius: 3px; font-size: 10px; white-space: nowrap; }
.tl-tooltip { position: fixed; z-index: 1000; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); pointer-events: none; display: none; max-width: 320px; min-width: 200px; font-size: 12px; }
.tl-tooltip-title { font-weight: 600; font-size: 13px; margin-bottom: 6px; color: #222; }
.tl-tooltip-row { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; color: #555; }
.tl-tooltip-row strong { color: #333; min-width: 60px; }
.tl-tooltip-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
.tl-tooltip-img { margin-top: 6px; border-radius: 4px; max-width: 280px; max-height: 160px; object-fit: contain; border: 1px solid #eee; }
.tl-empty { padding: 40px; text-align: center; color: #999; font-size: 13px; }
.tl-legend { display: flex; gap: 16px; margin-bottom: 14px; flex-wrap: wrap; padding: 8px 14px; background: #f7f9fc; border-radius: 6px; border: 1px solid #e8e8e8; }
.tl-legend-item { display: flex; align-items: center; gap: 5px; font-size: 11px; color: #666; }
.tl-legend-swatch { width: 12px; height: 12px; border-radius: 3px; }
.tl-day-header .tl-collapse { cursor: pointer; color: #999; font-size: 11px; border: none; background: none; padding: 2px 6px; }
.tl-day-header .tl-collapse:hover { color: #333; }
.tl-day-body { overflow: hidden; transition: max-height 0.3s ease; }
</style>

<div class="tl-container" id="tl-root"></div>
<div class="tl-tooltip" id="tl-tooltip"></div>

<script>
(function() {
  var timelineData = <?= json_encode($timeline_days ?? []) ?>;
  var root = document.getElementById('tl-root');
  var tooltip = document.getElementById('tl-tooltip');
  var HOURS = 24;
  var HOUR_WIDTH = 50;
  var MINUTE_WIDTH = HOUR_WIDTH / 60;

  var categoryColors = {
    productive: '#22c55e',
    neutral: '#f59e0b',
    distracting: '#ef4444'
  };

  function timeToMinutes(t) {
    var parts = t.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
  }

  function minutesToTime(m) {
    var h = Math.floor(m / 60);
    var mn = m % 60;
    return (h < 10 ? '0' : '') + h + ':' + (mn < 10 ? '0' : '') + mn;
  }

  function posFromTime(t) {
    return timeToMinutes(t) * MINUTE_WIDTH;
  }

  function timeFromPos(px) {
    return minutesToTime(Math.round(px / MINUTE_WIDTH));
  }

  function getBarWidth(start, end) {
    var s = posFromTime(start);
    var e = posFromTime(end);
    return Math.max(e - s, 2);
  }

  function formatSeconds(sec) {
    var h = Math.floor(sec / 3600);
    var m = Math.floor((sec % 3600) / 60);
    if (h > 0) return h + 'h ' + m + 'm';
    return m + 'm';
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  if (!timelineData || timelineData.length === 0) {
    root.innerHTML = '<div class="tl-empty"><i class="fa fa-clock-o"></i> No timeline data available for this date range.</div>';
    return;
  }

  var html = '<div class="tl-legend">' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#3b82f6"></span> Logged Time</span>' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#22c55e"></span> Productive</span>' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#f59e0b"></span> Neutral</span>' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#ef4444"></span> Distracting</span>' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#8b5cf6; border-radius:50%"></span> Screenshot</span>' +
    '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:#06b6d4"></span> Activity</span>' +
    '</div>';

  for (var d = 0; d < timelineData.length; d++) {
    var day = timelineData[d];
    var dayTotalSec = 0;
    for (var ei = 0; ei < day.time_entries.length; ei++) {
      dayTotalSec += day.time_entries[ei].total_seconds;
    }
    var hoursStr = formatSeconds(dayTotalSec);

    html += '<div class="tl-day" data-day="' + day.date + '">';
    html += '<div class="tl-day-header"><span>' + escapeHtml(day.day_label) + ' <span class="tl-day-hours">(' + hoursStr + ')</span></span>';
    html += '<button class="tl-collapse" onclick="this.closest(\'.tl-day\').querySelector(\'.tl-day-body\').style.display=this.closest(\'.tl-day\').querySelector(\'.tl-day-body\').style.display===\'none\'?\'block\':\'none\'"><i class="fa fa-chevron-down"></i></button></div>';
    html += '<div class="tl-day-body">';

    html += '<div class="tl-body" data-day="' + day.date + '">';
    html += '<div class="tl-scale">';
    for (var h = 0; h < HOURS; h++) {
      html += '<div class="tl-scale-hour">' + (h < 10 ? '0' : '') + h + ':00</div>';
    }
    html += '</div>';
    html += '<div class="tl-crosshair" id="ch-' + day.date + '"></div>';
    html += '<div class="tl-tracks">';

    html += '<div class="tl-track"><div class="tl-track-label">Logged</div><div class="tl-track-bar" data-track="logged" data-day="' + day.date + '">';
    for (var ei = 0; ei < day.time_entries.length; ei++) {
      var te = day.time_entries[ei];
      var left = posFromTime(te.start);
      var width = getBarWidth(te.start, te.end);
      html += '<div class="tl-block tl-block-logged" style="left:' + left + 'px;width:' + width + 'px" data-type="entry" data-start="' + te.start + '" data-end="' + te.end + '" data-task="' + escapeHtml(te.task_name) + '" data-seconds="' + te.total_seconds + '" data-day="' + day.date + '"></div>';
    }
    html += '</div></div>';

    html += '<div class="tl-track"><div class="tl-track-label">Productivity</div><div class="tl-track-bar" data-track="productivity" data-day="' + day.date + '">';
    for (var ai = 0; ai < day.app_usage.length; ai++) {
      var au = day.app_usage[ai];
      var left2 = posFromTime(au.start);
      var width2 = getBarWidth(au.start, au.end);
      var cls2 = 'tl-block-' + au.category;
      html += '<div class="tl-block ' + cls2 + '" style="left:' + left2 + 'px;width:' + width2 + 'px" data-type="app" data-app="' + escapeHtml(au.app_name) + '" data-title="' + escapeHtml(au.window_title) + '" data-url="' + escapeHtml(au.url) + '" data-category="' + au.category + '" data-start="' + au.start + '" data-end="' + au.end + '" data-seconds="' + au.total_seconds + '" data-day="' + day.date + '"></div>';
    }
    html += '</div></div>';

    html += '<div class="tl-track"><div class="tl-track-label">Apps</div><div class="tl-track-bar" data-track="apps" data-day="' + day.date + '">';
    var appColors = {};
    var colorPalette = ['#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#14b8a6', '#06b6d4', '#84cc16', '#e11d48', '#7c3aed', '#0ea5e9'];
    var ci = 0;
    for (var ai2 = 0; ai2 < day.app_usage.length; ai2++) {
      var au2 = day.app_usage[ai2];
      var akey = au2.app_name;
      if (!appColors[akey]) {
        appColors[akey] = colorPalette[ci % colorPalette.length];
        ci++;
      }
      var left3 = posFromTime(au2.start);
      var width3 = getBarWidth(au2.start, au2.end);
      html += '<div class="tl-block" style="left:' + left3 + 'px;width:' + width3 + 'px;background:' + appColors[akey] + ';opacity:0.85" data-type="app" data-app="' + escapeHtml(au2.app_name) + '" data-title="' + escapeHtml(au2.window_title) + '" data-url="' + escapeHtml(au2.url) + '" data-category="' + au2.category + '" data-start="' + au2.start + '" data-end="' + au2.end + '" data-seconds="' + au2.total_seconds + '" data-day="' + day.date + '" title="' + escapeHtml(au2.app_name) + '"></div>';
    }
    html += '</div></div>';

    html += '<div class="tl-track"><div class="tl-track-label">Screenshots</div><div class="tl-track-bar" data-track="screenshots" data-day="' + day.date + '">';
    for (var si = 0; si < day.screenshots.length; si++) {
      var ss = day.screenshots[si];
      var left4 = posFromTime(ss.time);
      html += '<div class="tl-ss-marker" style="left:' + (left4 - 9) + 'px" data-type="screenshot" data-time="' + ss.time + '" data-id="' + ss.id + '" data-keystrokes="' + ss.keystroke_count + '" data-mouse="' + ss.mouse_click_count + '" data-activity="' + ss.activity_percentage + '" data-img="' + escapeHtml(ss.image_url) + '" data-day="' + day.date + '" title="Screenshot at ' + ss.time + '"><i class="fa fa-camera" style="font-size:8px"></i></div>';
    }
    html += '</div></div>';

    html += '<div class="tl-track"><div class="tl-track-label">Activity</div><div class="tl-track-bar" data-track="activity" data-day="' + day.date + '">';
    for (var si2 = 0; si2 < day.screenshots.length; si2++) {
      var ss2 = day.screenshots[si2];
      var left5 = posFromTime(ss2.time);
      var actH = Math.max(2, (ss2.activity_percentage / 100) * 24);
      html += '<div class="tl-activity-bar" style="left:' + (left5 - 2) + 'px;height:' + actH + 'px" data-type="screenshot" data-time="' + ss2.time + '" data-keystrokes="' + ss2.keystroke_count + '" data-mouse="' + ss2.mouse_click_count + '" data-activity="' + ss2.activity_percentage + '" data-img="' + escapeHtml(ss2.image_url) + '" data-day="' + day.date + '"></div>';
    }
    html += '</div></div>';

    html += '</div></div></div></div>';
  }

  root.innerHTML = html;

  function showTooltip(e, html) {
    tooltip.innerHTML = html;
    tooltip.style.display = 'block';
    var rect = tooltip.getBoundingClientRect();
    var x = e.clientX + 14;
    var y = e.clientY - 10;
    if (x + rect.width > window.innerWidth - 10) x = e.clientX - rect.width - 14;
    if (y + rect.height > window.innerHeight - 10) y = window.innerHeight - rect.height - 10;
    if (y < 5) y = 5;
    tooltip.style.left = x + 'px';
    tooltip.style.top = y + 'px';
  }

  function hideTooltip() { tooltip.style.display = 'none'; }

  function showCrosshair(e, dayDate) {
    var ch = document.getElementById('ch-' + dayDate);
    if (!ch) return;
    var bar = ch.parentElement.querySelector('.tl-track-bar');
    if (!bar) return;
    var barRect = bar.getBoundingClientRect();
    var x = e.clientX - barRect.left;
    if (x < 0 || x > barRect.width) { ch.style.display = 'none'; return; }
    ch.style.display = 'block';
    ch.style.left = (90 + x) + 'px';
    ch.style.height = (barRect.height + 100) + 'px';
    ch.setAttribute('data-time', timeFromPos(x));
  }

  function hideCrosshair() {
    var els = document.querySelectorAll('.tl-crosshair');
    for (var i = 0; i < els.length; i++) els[i].style.display = 'none';
  }

  function handleBlockHover(e) {
    var el = e.currentTarget;
    var type = el.getAttribute('data-type');
    var tipHtml = '';
    if (type === 'entry') {
      tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-clock-o"></i> Time Entry</div>' +
        '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-start') + ' - ' + el.getAttribute('data-end') + '</div>' +
        '<div class="tl-tooltip-row"><strong>Duration:</strong> ' + formatSeconds(parseInt(el.getAttribute('data-seconds'))) + '</div>' +
        '<div class="tl-tooltip-row"><strong>Task:</strong> ' + escapeHtml(el.getAttribute('data-task')) + '</div>';
    } else if (type === 'app') {
      var cat = el.getAttribute('data-category');
      var catColor = categoryColors[cat] || '#999';
      tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-windows"></i> ' + escapeHtml(el.getAttribute('data-app')) + '</div>' +
        '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-start') + ' - ' + el.getAttribute('data-end') + '</div>' +
        '<div class="tl-tooltip-row"><strong>Duration:</strong> ' + formatSeconds(parseInt(el.getAttribute('data-seconds'))) + '</div>' +
        '<div class="tl-tooltip-row"><span class="tl-tooltip-dot" style="background:' + catColor + '"></span> <strong>Status:</strong> ' + cat.charAt(0).toUpperCase() + cat.slice(1) + '</div>';
      var ttl = el.getAttribute('data-title');
      if (ttl) tipHtml += '<div class="tl-tooltip-row"><strong>Window:</strong> ' + escapeHtml(ttl) + '</div>';
      var url = el.getAttribute('data-url');
      if (url) tipHtml += '<div class="tl-tooltip-row"><strong>URL:</strong> ' + escapeHtml(url).substring(0, 60) + '</div>';
    } else if (type === 'screenshot') {
      tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-camera"></i> Screenshot</div>' +
        '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-time') + '</div>' +
        '<div class="tl-tooltip-row"><strong>Activity:</strong> ' + el.getAttribute('data-activity') + '%</div>' +
        '<div class="tl-tooltip-row"><strong>Keystrokes:</strong> ' + el.getAttribute('data-keystrokes') + '</div>' +
        '<div class="tl-tooltip-row"><strong>Mouse:</strong> ' + el.getAttribute('data-mouse') + ' clicks</div>';
      var imgSrc = el.getAttribute('data-img');
      if (imgSrc) tipHtml += '<img class="tl-tooltip-img" src="' + imgSrc + '" onerror="this.style.display=\'none\'" />';
    }
    showTooltip(e, tipHtml);
  }

  var blocks = root.querySelectorAll('.tl-block, .tl-ss-marker, .tl-activity-bar');
  for (var i = 0; i < blocks.length; i++) {
    blocks[i].addEventListener('mouseenter', handleBlockHover);
    blocks[i].addEventListener('mousemove', handleBlockHover);
    blocks[i].addEventListener('mouseleave', hideTooltip);
  }

  var trackBars = root.querySelectorAll('.tl-track-bar');
  for (var j = 0; j < trackBars.length; j++) {
    (function(bar) {
      bar.addEventListener('mousemove', function(e) {
        showCrosshair(e, bar.getAttribute('data-day'));
      });
      bar.addEventListener('mouseleave', hideCrosshair);
    })(trackBars[j]);
  }
})();
</script>
