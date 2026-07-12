<div class="timesync-filter-bar" style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;">
  <input type="hidden" name="from" id="filter-from" value="<?= $from ?? date('Y-m-d', strtotime('-7 days')) ?>">
  <input type="hidden" name="to" id="filter-to" value="<?= $to ?? date('Y-m-d') ?>">

  <button type="button" class="btn btn-xs btn-default filter-preset" data-days="0" style="border-radius:14px;padding:3px 12px;">Today</button>
  <button type="button" class="btn btn-xs btn-default filter-preset" data-days="6" style="border-radius:14px;padding:3px 12px;">Last 7 Days</button>
  <button type="button" class="btn btn-xs btn-default filter-preset" data-days="29" style="border-radius:14px;padding:3px 12px;">Last 30 Days</button>
  <button type="button" class="btn btn-xs btn-default filter-preset" data-days="89" style="border-radius:14px;padding:3px 12px;">Last 90 Days</button>

  <div class="form-group" style="margin:0 4px;">
    <input type="date" class="form-control input-sm filter-custom-from" value="<?= $from ?? date('Y-m-d', strtotime('-7 days')) ?>" style="height:26px;font-size:11px;width:130px;display:inline-block;">
  </div>
  <span style="color:#999;font-size:11px;">—</span>
  <div class="form-group" style="margin:0 4px;">
    <input type="date" class="form-control input-sm filter-custom-to" value="<?= $to ?? date('Y-m-d') ?>" style="height:26px;font-size:11px;width:130px;display:inline-block;">
  </div>

  <button type="submit" class="btn btn-sm btn-primary" style="border-radius:14px;padding:3px 14px;font-size:12px;">
    <i class="fa fa-filter"></i> Filter
  </button>
</div>

<script>
$(function() {
  function setFilterDates(from, to) {
    $('#filter-from').val(from);
    $('#filter-to').val(to);
    $('.filter-custom-from').val(from);
    $('.filter-custom-to').val(to);
  }

  $('.filter-preset').on('click', function() {
    var days = parseInt($(this).data('days'), 10);
    var to = new Date();
    var from = new Date();
    from.setDate(from.getDate() - days);
    var fmt = function(d) {
      var y = d.getFullYear();
      var m = ('0' + (d.getMonth() + 1)).slice(-2);
      var day = ('0' + d.getDate()).slice(-2);
      return y + '-' + m + '-' + day;
    };
    setFilterDates(fmt(from), fmt(to));
    $(this).closest('form').submit();
  });

  $('.filter-custom-from, .filter-custom-to').on('change', function() {
    var from = $('.filter-custom-from').val();
    var to = $('.filter-custom-to').val();
    $('#filter-from').val(from);
    $('#filter-to').val(to);
  });
});
</script>
