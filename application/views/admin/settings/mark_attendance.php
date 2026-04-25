<div class="row">
    <div class="col-sm-12" data-spy="scroll" data-offset="0">
        <div class="panel panel-custom">
            <div class="panel-heading">
                <div class="panel-title">
                    <strong><?= lang('mark_attendance'); ?> (Live Biometric Dashboard)</strong>
                    <div class="pull-right">
                        <span id="sync_status" class="label label-success">Live <i class="fa fa-circle-o-notch fa-spin"></i></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <table class="table table-striped" id="Transation_DataTables">
                        <thead>
                        <tr>
                            <th><?= lang('emp_id') ?></th>
                            <th><?= lang('name') ?></th>
                            <th><?= lang('clocking_hours') ?></th>
                        </tr>
                        </thead>
                        <tbody id="attendance_table_body">
                        <?php
                        if (!empty($users)) {
                            foreach ($users as $user) {
                                ?>
                                <tr id="user_row_<?= $user->user_id ?>">
                                    <td><?= $user->employment_id; ?></td>
                                    <td><?= $user->fullname; ?></td>
                                    <td class="clocking_cell">
                                        <span class="total_time text-muted"></span><br/>
                                        <span class="status_container">
                                            <!-- Labels and Live Timers will be injected here via AJAX -->
                                            <i class="fa fa-spinner fa-spin text-muted"></i> Loading...
                                        </span>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#Transation_DataTables').dataTable({
            paging: false,
            "bSort": false
        });

        const currentDate = '<?= !empty($date) ? $date : date('Y-m-d') ?>';
        const isToday = (currentDate === '<?= date('Y-m-d') ?>');
        let activeTimers = {}; // Store intervals by user_id

        function padZero(num) {
            return num < 10 ? '0' + num : num;
        }

        function updateLiveTimer(userId, clockInTimestamp, totalHh, totalMm) {
            const now = Date.now();
            let diffMs = now - clockInTimestamp;
            
            // Add previously accumulated time for the day
            diffMs += (totalHh * 3600000) + (totalMm * 60000);

            const hours = Math.floor(diffMs / 3600000);
            const minutes = Math.floor((diffMs % 3600000) / 60000);
            const seconds = Math.floor((diffMs % 60000) / 1000);

            $(`#user_row_${userId} .status_container`).html(
                '<span style="padding:5px 15px; font-size: 14px;" class="label label-purple std_p">' +
                '<i class="fa fa-clock-o fa-spin"></i> ' +
                padZero(hours) + ':' + padZero(minutes) + ':' + padZero(seconds) +
                ' (Currently Clocked In)</span>'
            );
        }

        function fetchLiveStatus() {
            $.ajax({
                url: '<?= base_url() ?>admin/dashboard/get_live_attendance_status',
                type: 'POST',
                data: { date: currentDate },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        response.data.forEach(function(user) {
                            const row = $(`#user_row_${user.user_id}`);
                            
                            // Display accumulated time if any
                            if (user.total_time_formatted) {
                                row.find('.total_time').html('Total Completed: ' + user.total_time_formatted);
                            } else {
                                row.find('.total_time').html('');
                            }

                            if (user.is_clocked_in && isToday) {
                                // If they are clocked in, ensure the timer interval is running
                                if (!activeTimers[user.user_id]) {
                                    activeTimers[user.user_id] = setInterval(function() {
                                        updateLiveTimer(user.user_id, user.clock_in_timestamp, user.total_hh, user.total_mm);
                                    }, 1000);
                                    // Run immediately once
                                    updateLiveTimer(user.user_id, user.clock_in_timestamp, user.total_hh, user.total_mm);
                                }
                            } else {
                                // If they are clocked out, clear any running timer
                                if (activeTimers[user.user_id]) {
                                    clearInterval(activeTimers[user.user_id]);
                                    delete activeTimers[user.user_id];
                                }
                                
                                // Display static status label
                                row.find('.status_container').html(user.status_label);
                            }
                        });
                    }
                },
                error: function() {
                    console.error("Failed to fetch live attendance status.");
                }
            });
        }

        // Initial fetch
        fetchLiveStatus();

        // Poll every 5 seconds only if viewing today's date
        if (isToday) {
            setInterval(fetchLiveStatus, 5000);
        }
    });
</script>
