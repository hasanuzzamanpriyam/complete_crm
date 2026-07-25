<style>
.tl-container{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;font-size:12px;position:relative;color:#333}
.tl-day{margin-bottom:20px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:box-shadow .2s}
.tl-day:hover{box-shadow:0 2px 8px rgba(0,0,0,.1)}
.tl-day-header{padding:12px 16px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-bottom:1px solid #e2e8f0;font-weight:600;font-size:13px;color:#1e293b;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;transition:background .2s}
.tl-day-header:hover{background:linear-gradient(135deg,#f1f5f9,#e2e8f0)}
.tl-day-header-left{display:flex;align-items:center;gap:10px}
.tl-day-chevron{font-size:11px;color:#94a3b8;transition:transform .3s ease;display:inline-flex}
.tl-day-chevron.collapsed{transform:rotate(-90deg)}
.tl-day-hours-badge{font-size:11px;font-weight:500;background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:12px}
.tl-day-body{overflow:hidden;transition:max-height .4s cubic-bezier(.4,0,.2,1),opacity .3s ease}
.tl-body{padding:0 16px 12px;overflow-x:auto;overflow-y:hidden;position:relative}

.tl-badge{font-size:11px;font-weight:600;padding:3px 10px;border-radius:12px;white-space:nowrap;display:inline-flex;align-items:center;gap:5px;line-height:1}
.tl-badge-incomplete{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
.tl-badge-complete{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
.tl-badge i{font-size:9px}

.tl-scale{display:flex;position:relative;height:26px;border-bottom:2px solid #e2e8f0;margin-bottom:0;background:#fff}
.tl-scale-hour{flex:1;text-align:center;font-size:10px;color:#475569;line-height:26px;border-left:1px solid #f1f5f9;min-width:0;white-space:nowrap;font-weight:600}
.tl-scale-hour:first-child{border-left:none}
.tl-scale-hour.tl-scale-noon{color:#0f172a;font-weight:700;border-left:2px solid #94a3b8}

.tl-tracks{position:relative}
.tl-track{display:flex;align-items:center;height:32px;position:relative;margin-bottom:3px}
.tl-track.tl-track-activity{height:40px}
.tl-track-label{width:96px;min-width:96px;font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.6px;padding-right:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;display:flex;align-items:center;gap:4px}
.tl-track-label .tl-label-icon{font-size:11px}
.tl-track-bar{flex:1;position:relative;height:100%;border-radius:6px;overflow:visible;border:1px solid #f1f5f9;background:linear-gradient(to right,transparent calc(50% - .5px),rgba(148,163,184,.4) calc(50% - .5px),rgba(148,163,184,.4) calc(50% + .5px),transparent calc(50% + .5px)),repeating-linear-gradient(to right,transparent 0px,transparent calc(100% / 24),#e2e8f0 calc(100% / 24),#e2e8f0 calc(100% / 24 + 1px)),#f8fafc}

.tl-block{position:absolute;top:4px;height:24px;border-radius:4px;min-width:3px;z-index:1;cursor:default;transition:filter .15s,transform .1s}
.tl-block:hover{filter:brightness(1.1) saturate(1.1);transform:scaleY(1.08);z-index:5}
.tl-block-logged{background:linear-gradient(135deg,#3b82f6,#2563eb);box-shadow:0 1px 2px rgba(37,99,235,.2)}
.tl-block-logged.tl-block-running{background:repeating-linear-gradient(135deg,#3b82f6,#3b82f6 6px,#60a5fa 6px,#60a5fa 12px);box-shadow:0 1px 4px rgba(37,99,235,.3);animation:tlPulse 2s ease-in-out infinite}
@keyframes tlPulse{0%,100%{opacity:1}50%{opacity:.8}}
.tl-block-productive{background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 1px 2px rgba(22,163,74,.15)}
.tl-block-neutral{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 1px 2px rgba(217,119,6,.15)}
.tl-block-distracting{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 1px 2px rgba(220,38,38,.15)}

.tl-ss-cluster{position:absolute;top:50%;transform:translateY(-50%);z-index:3;cursor:pointer}
.tl-ss-badge{width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#8b5cf6,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;border:2px solid #fff;box-shadow:0 2px 6px rgba(124,58,237,.35);transition:transform .2s,box-shadow .2s}
.tl-ss-badge:hover{transform:scale(1.25);box-shadow:0 4px 12px rgba(124,58,237,.5);z-index:10}
.tl-ss-badge i{font-size:10px}
.tl-ss-count{position:absolute;top:-4px;right:-6px;background:#ef4444;color:#fff;font-size:8px;font-weight:700;min-width:14px;height:14px;border-radius:7px;display:flex;align-items:center;justify-content:center;border:1px solid #fff;padding:0 3px}

.tl-act-bar{position:absolute;bottom:0;width:3px;border-radius:2px 2px 0 0;min-height:1px;z-index:1;transition:opacity .1s}
.tl-act-bar:hover{opacity:.85;filter:brightness(1.1)}
.tl-act-point{position:absolute;top:0;width:7px;height:7px;border-radius:50%;background:#06b6d4;border:1.5px solid #fff;box-shadow:0 1px 3px rgba(6,182,212,.4);z-index:2;cursor:pointer;transition:transform .15s}
.tl-act-point:hover{transform:scale(1.5);z-index:5}

.tl-crosshair{position:absolute;top:0;width:1px;background:rgba(15,23,42,.15);z-index:20;pointer-events:none;display:none}
.tl-crosshair::before{content:attr(data-time);position:absolute;top:-20px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:2px 8px;border-radius:4px;font-size:10px;white-space:nowrap;font-weight:500;box-shadow:0 2px 6px rgba(0,0,0,.15)}

.tl-tooltip{position:fixed;z-index:1000;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;box-shadow:0 8px 30px rgba(0,0,0,.12),0 2px 8px rgba(0,0,0,.06);pointer-events:none;display:none;max-width:340px;min-width:200px;font-size:12px;backdrop-filter:blur(8px)}
.tl-tooltip-title{font-weight:700;font-size:13px;margin-bottom:8px;color:#0f172a;display:flex;align-items:center;gap:6px}
.tl-tooltip-row{display:flex;align-items:center;gap:6px;margin-bottom:5px;color:#475569;line-height:1.4}
.tl-tooltip-row strong{color:#334155;min-width:65px;font-weight:600}
.tl-tooltip-dot{width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0}
.tl-tooltip-img{margin-top:8px;border-radius:8px;max-width:300px;max-height:170px;object-fit:contain;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,.08)}

.tl-ss-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:2000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);animation:tlFadeIn .2s ease}
@keyframes tlFadeIn{from{opacity:0}to{opacity:1}}
.tl-ss-modal{background:#fff;border-radius:16px;box-shadow:0 25px 60px rgba(0,0,0,.3);max-width:760px;width:92%;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;animation:tlModalIn .25s ease}
@keyframes tlModalIn{from{opacity:0;transform:scale(.95) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
.tl-ss-modal-header{padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between}
.tl-ss-modal-header span{font-weight:700;font-size:15px;color:#0f172a;display:flex;align-items:center;gap:8px}
.tl-ss-modal-close{background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;padding:4px 8px;border-radius:6px;line-height:1}
.tl-ss-modal-close:hover{background:#f1f5f9;color:#334155}
.tl-ss-modal-body{padding:16px 20px;overflow-y:auto;flex:1}
.tl-ss-modal-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.tl-ss-card{border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:box-shadow .15s,transform .15s}
.tl-ss-card:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-1px)}
.tl-ss-card img{width:100%;height:80px;object-fit:cover;display:block;background:#f1f5f9;border-bottom:1px solid #e2e8f0}
.tl-ss-card-info{padding:6px 8px}
.tl-ss-card-time{font-size:10px;font-weight:600;color:#334155}
.tl-ss-card-activity{display:inline-block;font-size:9px;font-weight:700;padding:1px 6px;border-radius:8px;margin-top:2px}
.tl-ss-card-activity.tl-act-high{background:#dcfce7;color:#15803d}
.tl-ss-card-activity.tl-act-mid{background:#fef3c7;color:#92400e}
.tl-ss-card-activity.tl-act-low{background:#f1f5f9;color:#64748b}

.tl-app-icon{display:inline-block;vertical-align:middle;margin-right:6px;border-radius:3px;object-fit:contain}
.tl-app-icon-fa{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:4px;margin-right:6px;font-size:14px;vertical-align:middle;background:rgba(0,0,0,.06)}

.tl-empty{padding:60px 40px;text-align:center;color:#94a3b8;font-size:14px}
.tl-empty i{font-size:36px;display:block;margin-bottom:12px;color:#cbd5e1}
.tl-legend{display:flex;gap:18px;margin-bottom:16px;flex-wrap:wrap;padding:10px 16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0}
.tl-legend-item{display:flex;align-items:center;gap:6px;font-size:11px;color:#64748b;font-weight:500}
.tl-legend-swatch{width:14px;height:14px;border-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,.1)}
</style>

<div class="tl-container" id="tl-root" data-user-id="<?= $user_id ?? '' ?>" data-from="<?= $from ?? '' ?>" data-to="<?= $to ?? '' ?>"></div>
<div class="tl-tooltip" id="tl-tooltip"></div>

<script>
(function() {
  var embeddedData = <?= json_encode($timeline_days ?? []) ?>;
  var root = document.getElementById('tl-root');
  var tooltip = document.getElementById('tl-tooltip');
  var HOURS = 24;
  var HOUR_WIDTH = 50;
  var MINUTE_WIDTH = HOUR_WIDTH / 60;
  var CLUSTER_WINDOW_MIN = 2;
  var DAILY_GOAL = 8;
  var TRACK_W = HOURS * HOUR_WIDTH;

  window._tlScreenshots = {};

  var todayStr = (function() {
    var n = new Date();
    return n.getFullYear() + '-' + String(n.getMonth()+1).padStart(2,'0') + '-' + String(n.getDate()).padStart(2,'0');
  })();

  function parseToLocal(isoStr) {
    if (!isoStr) return null;
    if (isoStr.indexOf('Z') === -1 && isoStr.indexOf('+') === -1 && isoStr.indexOf('T') !== -1) {
      isoStr = isoStr + 'Z';
    }
    return new Date(isoStr);
  }

  function fmtHM12(date) {
    if (!date) return '??:??';
    var h = date.getHours(), m = date.getMinutes();
    var ap = h >= 12 ? 'PM' : 'AM';
    var h12 = h % 12; if (h12 === 0) h12 = 12;
    return h12 + ':' + (m < 10 ? '0' : '') + m + ' ' + ap;
  }

  function minutesToTimeAMPM(m) {
    var h = Math.floor(m / 60);
    var mn = m % 60;
    var ap = h >= 12 ? 'PM' : 'AM';
    var h12 = h % 12; if (h12 === 0) h12 = 12;
    return h12 + ':' + (mn < 10 ? '0' : '') + mn + ' ' + ap;
  }

  function fmtHourLabel(h) {
    if (h === 0) return '12 AM';
    if (h < 12) return h + ' AM';
    if (h === 12) return '12 PM';
    return (h - 12) + ' PM';
  }

  function timeToMinutes(h, m) { return h * 60 + m; }
  function posFromMinutes(min) { return min * MINUTE_WIDTH; }
  function minutesFromPos(px) { return Math.round(px / MINUTE_WIDTH); }

  function getBarWidthPx(startMin, endMin) {
    return Math.max((endMin - startMin) * MINUTE_WIDTH, 3);
  }

  function formatSeconds(sec) {
    var h = Math.floor(sec / 3600);
    var m = Math.floor((sec % 3600) / 60);
    if (h > 0) return h + 'h ' + m + 'm';
    return m + 'm';
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function extractDomain(url) {
    if (!url) return null;
    try {
      var a = document.createElement('a');
      a.href = url;
      var host = a.hostname;
      if (!host || host === 'localhost' || host === '127.0.0.1') return null;
      return host.replace(/^www\./, '');
    } catch(e) { return null; }
  }

  function getAppIconHtml(appName, appUrl) {
    var lower = (appName || '').toLowerCase();

    if (appUrl) {
      var domain = extractDomain(appUrl);
      if (domain) {
        return '<img class="tl-app-icon" src="https://www.google.com/s2/favicons?domain=' + encodeURIComponent(domain) + '&sz=32" width="18" height="18" onerror="this.style.display=\'none\'" />';
      }
    }

    var faMap = [
      [/chrome/i, 'fa-chrome', '#4285f4'],
      [/firefox/i, 'fa-firefox', '#ff7139'],
      [/opera/i, 'fa-opera', '#ff1b2d'],
      [/safari/i, 'fa-safari', '#006CFF'],
      [/\bcode\b|visual studio|phpstorm|webstorm|intellij|pycharm|cursor|windsurf|rider|goland|clion|datagrip|rubymine|appcode/i, 'fa-code', '#007acc'],
      [/sublime|atom|notepad|brackets|lighttable/i, 'fa-code', '#66595C'],
      [/terminal|cmd|powershell|windows terminal|git bash|wsl|mintty|hyper|iterm|alacritty|wezterm|kitty|tilix/i, 'fa-terminal', '#4d4d4d'],
      [/slack/i, 'fa-slack', '#4a154b'],
      [/skype/i, 'fa-skype', '#00aff0'],
      [/discord/i, 'fa-comment', '#5865F2'],
      [/teams/i, 'fa-comment', '#6264A7'],
      [/whatsapp/i, 'fa-comment', '#25D366'],
      [/telegram/i, 'fa-send', '#0088cc'],
      [/zoom/i, 'fa-video-camera', '#2D8CFF'],
      [/excel/i, 'fa-file-excel-o', '#217346'],
      [/word/i, 'fa-file-word-o', '#2b579a'],
      [/powerpoint/i, 'fa-file-powerpoint-o', '#b7472a'],
      [/onenote/i, 'fa-sticky-note', '#7719aa'],
      [/outlook/i, 'fa-envelope', '#0078d4'],
      [/\bgit\b|github|sourcetree|gitkraken|tortoisegit/i, 'fa-code-fork', '#f05032'],
      [/docker/i, 'fa-cube', '#2496ed'],
      [/kubernetes|kubectl|k8s/i, 'fa-cube', '#326CE5'],
      [/jenkins/i, 'fa-cog', '#D33833'],
      [/terraform/i, 'fa-cog', '#7B42BC'],
      [/ansible/i, 'fa-cog', '#EE0000'],
      [/heidisql|tableplus|dbeaver|mysql|navicat|pgadmin|redis|workbench/i, 'fa-database', '#336791'],
      [/figma/i, 'fa-pencil-square-o', '#a259ff'],
      [/sketch/i, 'fa-pencil-square-o', '#F7B500'],
      [/photoshop|psd/i, 'fa-pencil-square-o', '#31a8ff'],
      [/illustrator|ai\b/i, 'fa-pencil-square-o', '#ff9a00'],
      [/premiere|after effects|premiere pro|ae\b/i, 'fa-film', '#9999ff'],
      [/indesign/i, 'fa-pencil-square-o', '#FF3366'],
      [/blender/i, 'fa-cube', '#E87D0D'],
      [/spotify/i, 'fa-spotify', '#1db954'],
      [/vlc/i, 'fa-play-circle', '#ff8800'],
      [/itunes|apple music/i, 'fa-music', '#fc3c44'],
      [/youtube/i, 'fa-play', '#FF0000'],
      [/netflix/i, 'fa-play', '#E50914'],
      [/twitch/i, 'fa-play', '#9146FF'],
      [/explorer|file explorer|finder|nautilus|dolphin/i, 'fa-folder-open-o', '#f5ba42'],
      [/calculator/i, 'fa-calculator', '#1a73e8'],
      [/settings|preferences|preferences\.app/i, 'fa-cogs', '#666'],
      [/task manager|activity monitor|system monitor/i, 'fa-bar-chart', '#1a73e8'],
      [/notion/i, 'fa-sticky-note', '#000000'],
      [/obsidian/i, 'fa-sticky-note', '#7C3AED'],
      [/evernote/i, 'fa-sticky-note', '#00A82D'],
      [/trello/i, 'fa-th-large', '#0052CC'],
      [/asana/i, 'fa-th-large', '#F06A6A'],
      [/jira/i, 'fa-th-large', '#0052CC'],
      [/linear/i, 'fa-th-large', '#5E6AD2'],
      [/postman/i, 'fa-th-large', '#FF6C37'],
      [/insomnia/i, 'fa-th-large', '#4000BF'],
      [/soapui/i, 'fa-th-large', '#0D73F6'],
    ];

    for (var i = 0; i < faMap.length; i++) {
      if (faMap[i][0].test(lower)) {
        return '<i class="fa ' + faMap[i][1] + ' tl-app-icon-fa" style="color:' + faMap[i][2] + '"></i>';
      }
    }

    if (appUrl) return '<i class="fa fa-globe tl-app-icon-fa" style="color:#4285f4"></i>';

    return '<i class="fa fa-th-large tl-app-icon-fa" style="color:#94a3b8"></i>';
  }

  function actBadgeClass(pct) {
    if (pct > 60) return 'tl-act-high';
    if (pct > 30) return 'tl-act-mid';
    return 'tl-act-low';
  }

  function actColor(pct) {
    if (pct > 60) return '#06b6d4';
    if (pct > 30) return '#fbbf24';
    return '#cbd5e1';
  }

  function cubicInterp(p0, p1, p2, p3, t) {
    var t2 = t * t, t3 = t2 * t;
    return 0.5 * ((2*p1) + (-p0+p2)*t + (2*p0-5*p1+4*p2-p3)*t2 + (-p0+3*p1-3*p2+p3)*t3);
  }

  window.renderTimeline = function(timelineData) {
    root = document.getElementById('tl-root');
    tooltip = document.getElementById('tl-tooltip');
    if (!root) return;

    window._tlScreenshots = {};

    if (!timelineData || timelineData.length === 0) {
      root.innerHTML = '<div class="tl-empty"><i class="fa fa-clock-o"></i>No timeline data available for this date range.</div>';
      return;
    }

    var html = '<div class="tl-legend">' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"></span>Logged Time</span>' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(135deg,#22c55e,#16a34a)"></span>Productive</span>' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(135deg,#f59e0b,#d97706)"></span>Neutral</span>' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(135deg,#ef4444,#dc2626)"></span>Distracting</span>' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);border-radius:50%"></span>Screenshot</span>' +
      '<span class="tl-legend-item"><span class="tl-legend-swatch" style="background:linear-gradient(90deg,#cbd5e1,#fbbf24,#06b6d4);width:48px;border-radius:3px"></span>Activity <span style="font-size:9px;color:#94a3b8">(0-100%)</span></span>' +
      '</div>';

    var runningSessions = [];

    for (var d = 0; d < timelineData.length; d++) {
      var day = timelineData[d];
      var dayTotalSec = 0;
      for (var ei = 0; ei < day.time_entries.length; ei++) {
        dayTotalSec += day.time_entries[ei].total_seconds;
      }
      var hoursStr = formatSeconds(dayTotalSec);
      var goalMet = dayTotalSec >= (DAILY_GOAL * 3600);
      var goalBadge = goalMet
        ? '<span class="tl-badge tl-badge-complete"><i class="fa fa-check-circle"></i>' + hoursStr + ' / ' + DAILY_GOAL + 'h Target</span>'
        : '<span class="tl-badge tl-badge-incomplete"><i class="fa fa-hourglass-half"></i>' + hoursStr + ' / ' + DAILY_GOAL + 'h Target</span>';

      var isExpanded = (day.date === todayStr);
      if (!isExpanded && d === timelineData.length - 1) {
        var hasToday = false;
        for (var dd = 0; dd < timelineData.length; dd++) {
          if (timelineData[dd].date === todayStr) { hasToday = true; break; }
        }
        if (!hasToday) isExpanded = true;
      }

      html += '<div class="tl-day" data-day="' + day.date + '">';
      html += '<div class="tl-day-header" onclick="toggleDay(this)">';
      html += '<div class="tl-day-header-left">';
      html += '<span class="tl-day-chevron ' + (isExpanded ? '' : 'collapsed') + '"><i class="fa fa-chevron-down"></i></span>';
      html += '<span>' + escapeHtml(day.day_label) + '</span>';
      html += goalBadge;
      html += '</div>';
      html += '<span style="font-size:11px;color:#94a3b8">' + day.time_entries.length + ' entries &middot; ' + day.screenshots.length + ' screenshots</span>';
      html += '</div>';
      html += '<div class="tl-day-body" style="' + (isExpanded ? '' : 'max-height:0;opacity:0') + '">';

      html += '<div class="tl-body" data-day="' + day.date + '">';
      html += '<div class="tl-scale">';
      for (var h = 0; h < HOURS; h++) {
        var noonCls = (h === 12) ? ' tl-scale-noon' : '';
        html += '<div class="tl-scale-hour' + noonCls + '">' + fmtHourLabel(h) + '</div>';
      }
      html += '</div>';
      html += '<div class="tl-crosshair" id="ch-' + day.date + '"></div>';
      html += '<div class="tl-tracks">';

      /* Track 1: Logged Time */
      html += '<div class="tl-track"><div class="tl-track-label"><span class="tl-label-icon"><i class="fa fa-clock-o"></i></span>Logged</div><div class="tl-track-bar" data-track="logged" data-day="' + day.date + '">';
      for (var ei2 = 0; ei2 < day.time_entries.length; ei2++) {
        var te = day.time_entries[ei2];
        var teStart = parseToLocal(te.start_ts);
        if (!teStart) continue;
        var sMin = timeToMinutes(teStart.getHours(), teStart.getMinutes());
        var eMin;
        var isRunning = te.is_running;
        if (isRunning) {
          var now = new Date();
          eMin = timeToMinutes(now.getHours(), now.getMinutes());
          if (eMin <= sMin) eMin = sMin + 1;
        } else {
          var teEnd = parseToLocal(te.end_ts);
          eMin = teEnd ? timeToMinutes(teEnd.getHours(), teEnd.getMinutes()) : sMin + 1;
          if (eMin <= sMin) eMin = sMin + 1;
        }
        var runningCls = isRunning ? ' tl-block-running' : '';
        var endLabel = isRunning ? 'Now' : fmtHM12(parseToLocal(te.end_ts));
        var blockId = 'te-block-' + day.date + '-' + ei2;
        html += '<div class="tl-block tl-block-logged' + runningCls + '" id="' + blockId + '" style="left:' + posFromMinutes(sMin) + 'px;width:' + getBarWidthPx(sMin, eMin) + 'px" data-type="entry" data-start="' + fmtHM12(teStart) + '" data-end="' + endLabel + '" data-task="' + escapeHtml(te.task_name) + '" data-seconds="' + te.total_seconds + '" data-smin="' + sMin + '"></div>';
        if (isRunning) {
          runningSessions.push({ id: blockId, sMin: sMin, startTs: te.start_ts, startLocal: teStart });
        }
      }
      html += '</div></div>';

      /* Track 2: Productivity */
      html += '<div class="tl-track"><div class="tl-track-label"><span class="tl-label-icon"><i class="fa fa-pie-chart"></i></span>Prod.</div><div class="tl-track-bar" data-track="productivity" data-day="' + day.date + '">';
      for (var ai = 0; ai < day.app_usage.length; ai++) {
        var au = day.app_usage[ai];
        var auStart = parseToLocal(au.start_ts);
        var auEnd = parseToLocal(au.end_ts);
        if (!auStart || !auEnd) continue;
        var asMin = timeToMinutes(auStart.getHours(), auStart.getMinutes());
        var aeMin = timeToMinutes(auEnd.getHours(), auEnd.getMinutes());
        if (aeMin <= asMin) aeMin = asMin + 1;
        var cls2 = 'tl-block-' + au.category;
        html += '<div class="tl-block ' + cls2 + '" style="left:' + posFromMinutes(asMin) + 'px;width:' + getBarWidthPx(asMin, aeMin) + 'px" data-type="app" data-app="' + escapeHtml(au.app_name) + '" data-title="' + escapeHtml(au.window_title) + '" data-url="' + escapeHtml(au.url) + '" data-category="' + au.category + '" data-start="' + fmtHM12(auStart) + '" data-end="' + fmtHM12(auEnd) + '" data-seconds="' + au.total_seconds + '"></div>';
      }
      html += '</div></div>';

      /* Track 3: Apps (colored by app) */
      html += '<div class="tl-track"><div class="tl-track-label"><span class="tl-label-icon"><i class="fa fa-th-large"></i></span>Apps</div><div class="tl-track-bar" data-track="apps" data-day="' + day.date + '">';
      var appColors = {};
      var colorPalette = ['#3b82f6','#8b5cf6','#ec4899','#f97316','#14b8a6','#06b6d4','#84cc16','#e11d48','#7c3aed','#0ea5e9','#f59e0b','#10b981'];
      var ci = 0;
      for (var ai2 = 0; ai2 < day.app_usage.length; ai2++) {
        var au2 = day.app_usage[ai2];
        var auStart2 = parseToLocal(au2.start_ts);
        var auEnd2 = parseToLocal(au2.end_ts);
        if (!auStart2 || !auEnd2) continue;
        var asMin2 = timeToMinutes(auStart2.getHours(), auStart2.getMinutes());
        var aeMin2 = timeToMinutes(auEnd2.getHours(), auEnd2.getMinutes());
        if (aeMin2 <= asMin2) aeMin2 = asMin2 + 1;
        var akey = au2.app_name;
        if (!appColors[akey]) { appColors[akey] = colorPalette[ci % colorPalette.length]; ci++; }
        html += '<div class="tl-block" style="left:' + posFromMinutes(asMin2) + 'px;width:' + getBarWidthPx(asMin2, aeMin2) + 'px;background:' + appColors[akey] + ';opacity:0.88" data-type="app" data-app="' + escapeHtml(au2.app_name) + '" data-title="' + escapeHtml(au2.window_title) + '" data-url="' + escapeHtml(au2.url) + '" data-category="' + au2.category + '" data-start="' + fmtHM12(auStart2) + '" data-end="' + fmtHM12(auEnd2) + '" data-seconds="' + au2.total_seconds + '" title="' + escapeHtml(au2.app_name) + '"></div>';
      }
      html += '</div></div>';

      /* Track 4: Screenshots (clustered) */
      html += '<div class="tl-track"><div class="tl-track-label"><span class="tl-label-icon"><i class="fa fa-camera"></i></span>Shots</div><div class="tl-track-bar" data-track="screenshots" data-day="' + day.date + '">';
      var ssSorted = day.screenshots.slice().sort(function(a, b) {
        return a.captured_at.localeCompare(b.captured_at);
      });
      var clusters = [];
      var curCluster = null;
      for (var si = 0; si < ssSorted.length; si++) {
        var ss = ssSorted[si];
        var ssDate = parseToLocal(ss.captured_at);
        if (!ssDate) continue;
        var ssMin = timeToMinutes(ssDate.getHours(), ssDate.getMinutes());
        if (!curCluster || (ssMin - curCluster.endMin) > CLUSTER_WINDOW_MIN) {
          curCluster = { startMin: ssMin, endMin: ssMin, items: [] };
          clusters.push(curCluster);
        }
        curCluster.endMin = ssMin;
        curCluster.items.push({ ss: ss, date: ssDate, min: ssMin });
      }
      var globalClusterIdx = 0;
      for (var ci2 = 0; ci2 < clusters.length; ci2++) {
        var cl = clusters[ci2];
        var clMidMin = Math.round((cl.startMin + cl.endMin) / 2);
        var clLeft = posFromMinutes(clMidMin) - 13;
        if (cl.items.length === 1) {
          var ssi = cl.items[0];
          html += '<div class="tl-ss-cluster" style="left:' + clLeft + 'px">' +
            '<div class="tl-ss-badge" data-type="screenshot" data-time="' + fmtHM12(ssi.date) + '" data-id="' + ssi.ss.id + '" data-keystrokes="' + ssi.ss.keystroke_count + '" data-mouse="' + ssi.ss.mouse_click_count + '" data-activity="' + ssi.ss.activity_percentage + '" data-img="' + escapeHtml(ssi.ss.image_url) + '">' +
            '<i class="fa fa-camera"></i></div></div>';
        } else {
          var key = 'c_' + globalClusterIdx;
          window._tlScreenshots[key] = [];
          for (var sj = 0; sj < cl.items.length; sj++) {
            var sjItem = cl.items[sj];
            window._tlScreenshots[key].push({
              id: sjItem.ss.id,
              time: fmtHM12(sjItem.date),
              activity: sjItem.ss.activity_percentage,
              keystrokes: sjItem.ss.keystroke_count,
              mouse: sjItem.ss.mouse_click_count,
              imageUrl: sjItem.ss.image_url
            });
          }
          html += '<div class="tl-ss-cluster" style="left:' + clLeft + 'px">' +
            '<div class="tl-ss-badge" data-type="cluster" data-cluster-id="' + key + '" data-count="' + cl.items.length + '">' +
            '<i class="fa fa-camera"></i>' +
            '<span class="tl-ss-count">' + cl.items.length + '</span></div></div>';
          globalClusterIdx++;
        }
      }
      html += '</div></div>';

      /* Track 5: Activity Heatmap (continuous) */
      html += '<div class="tl-track tl-track-activity"><div class="tl-track-label"><span class="tl-label-icon"><i class="fa fa-area-chart"></i></span>Activity</div><div class="tl-track-bar" data-track="activity" data-day="' + day.date + '">';
      var actPoints = [];
      for (var si2 = 0; si2 < ssSorted.length; si2++) {
        var ss2 = ssSorted[si2];
        var ssDate2 = parseToLocal(ss2.captured_at);
        if (!ssDate2) continue;
        actPoints.push({ min: timeToMinutes(ssDate2.getHours(), ssDate2.getMinutes()), pct: ss2.activity_percentage, date: ssDate2, ss: ss2 });
      }
      if (actPoints.length > 0) {
        var prevMin = Math.max(0, actPoints[0].min - 5);
        var prevPct = 0;
        for (var ap = 0; ap < actPoints.length; ap++) {
          var pt = actPoints[ap];
          if (ap > 0) {
            var segStart = prevMin;
            var segEnd = pt.min;
            var steps = Math.max(1, Math.round((segEnd - segStart) / 3));
            var pp0 = (ap >= 2) ? actPoints[ap-2].pct : prevPct;
            var pp1 = prevPct;
            var pp2 = pt.pct;
            var pp3 = (ap + 1 < actPoints.length) ? actPoints[ap+1].pct : pt.pct;
            for (var s = 0; s < steps; s++) {
              var frac = (s + 1) / (steps + 1);
              var interpPct = Math.max(0, Math.min(100, cubicInterp(pp0, pp1, pp2, pp3, frac)));
              var barMin = Math.round(segStart + (segEnd - segStart) * ((s + 1) / (steps + 1)));
              var barH = Math.max(1, (interpPct / 100) * 34);
              html += '<div class="tl-act-bar" style="left:' + (posFromMinutes(barMin) - 1.5) + 'px;height:' + barH + 'px;background:' + actColor(interpPct) + ';opacity:' + (0.4 + 0.6 * interpPct / 100) + '" data-type="activity" data-pct="' + Math.round(interpPct) + '" data-time="' + minutesToTimeAMPM(barMin) + '"></div>';
            }
          }
          var barH2 = Math.max(1, (pt.pct / 100) * 34);
          html += '<div class="tl-act-point" style="left:' + (posFromMinutes(pt.min) - 3.5) + 'px;bottom:' + (Math.max(2, (pt.pct / 100) * 34) - 3) + 'px" data-type="activity" data-pct="' + pt.pct + '" data-time="' + fmtHM12(pt.date) + '" data-keystrokes="' + pt.ss.keystroke_count + '" data-mouse="' + pt.ss.mouse_click_count + '" data-img="' + escapeHtml(pt.ss.image_url) + '"></div>';
          html += '<div class="tl-act-bar" style="left:' + (posFromMinutes(pt.min) - 1.5) + 'px;height:' + barH2 + 'px;background:' + actColor(pt.pct) + ';opacity:' + (0.4 + 0.6 * pt.pct / 100) + '"></div>';
          prevMin = pt.min;
          prevPct = pt.pct;
        }
        var lastMin = prevMin + 5;
        if (lastMin <= 1439) {
          var fadeEnd = Math.min(lastMin + 15, 1439);
          var fadeSteps = Math.max(1, Math.round((fadeEnd - lastMin) / 3));
          for (var fs = 0; fs < fadeSteps; fs++) {
            var fadeFrac = 1 - ((fs + 1) / (fadeSteps + 1));
            var fadePct = prevPct * fadeFrac;
            var fadeBarMin = Math.round(lastMin + (fadeEnd - lastMin) * ((fs + 1) / (fadeSteps + 1)));
            var fadeBarH = Math.max(1, (fadePct / 100) * 34);
            html += '<div class="tl-act-bar" style="left:' + (posFromMinutes(fadeBarMin) - 1.5) + 'px;height:' + fadeBarH + 'px;background:' + actColor(fadePct) + ';opacity:' + (0.4 + 0.6 * fadeFrac) + '"></div>';
          }
        }
      }
      html += '</div></div>';

      html += '</div></div></div></div>';
    }

    root.innerHTML = html;

    window.toggleDay = function(header) {
      var dayEl = header.closest('.tl-day');
      var body = dayEl.querySelector('.tl-day-body');
      var chevron = header.querySelector('.tl-day-chevron');
      if (body.style.maxHeight && body.style.maxHeight !== '0px') {
        body.style.maxHeight = '0px';
        body.style.opacity = '0';
        chevron.classList.add('collapsed');
      } else {
        body.style.maxHeight = body.scrollHeight + 500 + 'px';
        body.style.opacity = '1';
        chevron.classList.remove('collapsed');
        setTimeout(function() { body.style.maxHeight = 'none'; }, 400);
      }
    };

    var expandedBodies = root.querySelectorAll('.tl-day-body');
    for (var eb = 0; eb < expandedBodies.length; eb++) {
      if (expandedBodies[eb].style.maxHeight !== '0px') {
        expandedBodies[eb].style.maxHeight = expandedBodies[eb].scrollHeight + 500 + 'px';
      }
    }

    function showTooltip(e, tipHtml) {
      tooltip.innerHTML = tipHtml;
      tooltip.style.display = 'block';
      var rect = tooltip.getBoundingClientRect();
      var x = e.clientX + 16;
      var y = e.clientY - 12;
      if (x + rect.width > window.innerWidth - 12) x = e.clientX - rect.width - 16;
      if (y + rect.height > window.innerHeight - 12) y = window.innerHeight - rect.height - 12;
      if (y < 8) y = 8;
      tooltip.style.left = x + 'px';
      tooltip.style.top = y + 'px';
    }

    function hideTooltip() { tooltip.style.display = 'none'; }

    function openScreenshotModal(clusterId) {
      var items = window._tlScreenshots[clusterId];
      if (!items || items.length === 0) return;
      var overlay = document.createElement('div');
      overlay.className = 'tl-ss-modal-overlay';
      overlay.setAttribute('data-modal', 'ss-gallery');
      var gridHtml = '';
      for (var i = 0; i < items.length; i++) {
        var it = items[i];
        gridHtml += '<div class="tl-ss-card">' +
          '<img src="' + escapeHtml(it.imageUrl) + '" onerror="this.style.background=\'#f1f5f9\';this.alt=\'No preview\'" loading="lazy" />' +
          '<div class="tl-ss-card-info">' +
          '<div class="tl-ss-card-time">' + it.time + '</div>' +
          '<span class="tl-ss-card-activity ' + actBadgeClass(it.activity) + '">' + it.activity + '%</span>' +
          '</div></div>';
      }
      overlay.innerHTML =
        '<div class="tl-ss-modal">' +
        '<div class="tl-ss-modal-header"><span><i class="fa fa-camera" style="color:#8b5cf6"></i> ' + items.length + ' Screenshots</span>' +
        '<button class="tl-ss-modal-close" data-close-modal="ss-gallery">&times;</button></div>' +
        '<div class="tl-ss-modal-body"><div class="tl-ss-modal-grid">' + gridHtml + '</div></div></div>';
      document.body.appendChild(overlay);
      overlay.addEventListener('click', function(e) {
        if (e.target === overlay || e.target.getAttribute('data-close-modal')) {
          overlay.remove();
        }
      });
      document.addEventListener('keydown', function onEsc(e) {
        if (e.key === 'Escape') { overlay.remove(); document.removeEventListener('keydown', onEsc); }
      });
    }

    function showCrosshair(e, dayDate) {
      var ch = document.getElementById('ch-' + dayDate);
      if (!ch) return;
      var trackContainer = ch.parentElement.querySelector('.tl-tracks');
      if (!trackContainer) return;
      var firstBar = trackContainer.querySelector('.tl-track-bar');
      if (!firstBar) return;
      var barRect = firstBar.getBoundingClientRect();
      var x = e.clientX - barRect.left;
      if (x < 0 || x > barRect.width) { ch.style.display = 'none'; return; }
      ch.style.display = 'block';
      ch.style.left = (96 + x) + 'px';
      ch.style.height = trackContainer.scrollHeight + 'px';
      var totalMin = minutesFromPos(x);
      ch.setAttribute('data-time', minutesToTimeAMPM(totalMin));
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
        tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-clock-o" style="color:#3b82f6"></i>Time Entry</div>' +
          '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-start') + ' &mdash; ' + el.getAttribute('data-end') + '</div>' +
          '<div class="tl-tooltip-row"><strong>Duration:</strong> ' + formatSeconds(parseInt(el.getAttribute('data-seconds'))) + '</div>' +
          '<div class="tl-tooltip-row"><strong>Task:</strong> ' + escapeHtml(el.getAttribute('data-task')) + '</div>';
      } else if (type === 'app') {
        var cat = el.getAttribute('data-category');
        var catColor = { productive: '#22c55e', neutral: '#f59e0b', distracting: '#ef4444' }[cat] || '#94a3b8';
        tipHtml = '<div class="tl-tooltip-title">' + getAppIconHtml(el.getAttribute('data-app'), el.getAttribute('data-url')) + escapeHtml(el.getAttribute('data-app')) + '</div>' +
          '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-start') + ' &mdash; ' + el.getAttribute('data-end') + '</div>' +
          '<div class="tl-tooltip-row"><strong>Duration:</strong> ' + formatSeconds(parseInt(el.getAttribute('data-seconds'))) + '</div>' +
          '<div class="tl-tooltip-row"><span class="tl-tooltip-dot" style="background:' + catColor + '"></span><strong>Status:</strong> ' + cat.charAt(0).toUpperCase() + cat.slice(1) + '</div>';
        var ttl = el.getAttribute('data-title');
        if (ttl) tipHtml += '<div class="tl-tooltip-row"><strong>Window:</strong> ' + escapeHtml(ttl).substring(0, 50) + '</div>';
        var url = el.getAttribute('data-url');
        if (url) tipHtml += '<div class="tl-tooltip-row"><strong>URL:</strong> ' + escapeHtml(url).substring(0, 50) + '</div>';
      } else if (type === 'screenshot') {
        tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-camera" style="color:#8b5cf6"></i>Screenshot</div>' +
          '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-time') + '</div>' +
          '<div class="tl-tooltip-row"><strong>Activity:</strong> ' + el.getAttribute('data-activity') + '%</div>' +
          '<div class="tl-tooltip-row"><strong>Keystrokes:</strong> ' + el.getAttribute('data-keystrokes') + '</div>' +
          '<div class="tl-tooltip-row"><strong>Mouse:</strong> ' + el.getAttribute('data-mouse') + ' clicks</div>';
        var imgSrc = el.getAttribute('data-img');
        if (imgSrc) tipHtml += '<img class="tl-tooltip-img" src="' + imgSrc + '" onerror="this.style.display=\'none\'" />';
      } else if (type === 'activity') {
        tipHtml = '<div class="tl-tooltip-title"><i class="fa fa-area-chart" style="color:#06b6d4"></i>Activity Level</div>' +
          '<div class="tl-tooltip-row"><strong>Time:</strong> ' + el.getAttribute('data-time') + '</div>' +
          '<div class="tl-tooltip-row"><strong>Level:</strong> ' + el.getAttribute('data-pct') + '%</div>';
        var ks = el.getAttribute('data-keystrokes');
        if (ks) tipHtml += '<div class="tl-tooltip-row"><strong>Keys:</strong> ' + ks + '</div>';
        var mc = el.getAttribute('data-mouse');
        if (mc) tipHtml += '<div class="tl-tooltip-row"><strong>Mouse:</strong> ' + mc + ' clicks</div>';
        var actImg = el.getAttribute('data-img');
        if (actImg) tipHtml += '<img class="tl-tooltip-img" src="' + actImg + '" onerror="this.style.display=\'none\'" />';
      }
      if (tipHtml) showTooltip(e, tipHtml);
    }

    var blocks = root.querySelectorAll('.tl-block, .tl-ss-badge, .tl-act-point');
    for (var i = 0; i < blocks.length; i++) {
      blocks[i].addEventListener('mouseenter', handleBlockHover);
      blocks[i].addEventListener('mousemove', handleBlockHover);
      blocks[i].addEventListener('mouseleave', hideTooltip);
    }

    var clusterBadges = root.querySelectorAll('.tl-ss-badge[data-type="cluster"]');
    for (var ci3 = 0; ci3 < clusterBadges.length; ci3++) {
      (function(badge) {
        badge.addEventListener('click', function(e) {
          e.stopPropagation();
          var cid = badge.getAttribute('data-cluster-id');
          if (cid) openScreenshotModal(cid);
        });
      })(clusterBadges[ci3]);
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

    /* Dynamic running session updates */
    if (window._tlRunningInterval) clearInterval(window._tlRunningInterval);
    if (runningSessions.length > 0) {
      window._tlRunningInterval = setInterval(function() {
        var now = new Date();
        var nowMin = timeToMinutes(now.getHours(), now.getMinutes());
        for (var ri = 0; ri < runningSessions.length; ri++) {
          var rs = runningSessions[ri];
          var el = document.getElementById(rs.id);
          if (!el) continue;
          var eMin = nowMin;
          if (eMin <= rs.sMin) eMin = rs.sMin + 1;
          el.style.width = getBarWidthPx(rs.sMin, eMin) + 'px';
          el.setAttribute('data-end', 'Now');
          var elapsed = (now.getTime() - rs.startLocal.getTime()) / 1000;
          el.setAttribute('data-seconds', Math.max(0, Math.round(elapsed)));
        }
      }, 30000);
    }
  };

  window.loadTimeline = function(userId, from, to) {
    root = document.getElementById('tl-root');
    if (!root) return;
    root.innerHTML = '<div class="tl-empty"><i class="fa fa-spinner fa-spin"></i>Loading timeline...</div>';

    var baseUrl = (document.querySelector('base') || {}).href || window.location.origin;
    var url = baseUrl + '/admin/timesync/timeline_json/' + userId + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);

    fetch(url)
      .then(function(r) { return r.json(); })
      .then(function(data) { renderTimeline(data); })
      .catch(function() {
        root.innerHTML = '<div class="tl-empty"><i class="fa fa-exclamation-triangle"></i>Failed to load timeline data.</div>';
      });
  };

  window.initTimesyncTimeline = function(forceReload) {
    root = document.getElementById('tl-root');
    if (!root) return;

    if (!forceReload && root.querySelector('.tl-day')) return;

    var uid = root.getAttribute('data-user-id');
    var fromVal = root.getAttribute('data-from');
    var toVal = root.getAttribute('data-to');

    if (embeddedData && embeddedData.length > 0) {
      requestAnimationFrame(function() { renderTimeline(embeddedData); });
    } else if (uid && fromVal && toVal) {
      requestAnimationFrame(function() { loadTimeline(uid, fromVal, toVal); });
    }
  };

  requestAnimationFrame(function() { window.initTimesyncTimeline(); });
})();
</script>
