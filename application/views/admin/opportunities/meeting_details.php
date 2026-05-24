<div class="panel panel-custom">
    <div class="panel-heading">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><?= lang('meeting_details') ?></h4>
    </div>
    <div class="modal-body wrap-modal wrap">
        <div class="panel-body form-horizontal">
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('subject') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php if (!empty($details->meeting_subject)) echo $details->meeting_subject; ?></p>
                </div>
            </div>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('start_date') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php if (!empty($details->start_date)) echo display_datetime($details->start_date, true); ?></p>
                </div>
            </div>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('end_date') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php if (!empty($details->end_date)) echo display_datetime($details->end_date, true); ?></p>
                </div>
            </div>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('attendess') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php
                        if (!empty($details->attendees)) {
                            $user_id = unserialize($details->attendees);
                            foreach ($user_id['attendees'] as $assding_id) {
                                echo fullname($assding_id) . '<br/>';
                            }
                        }
                        ?></p>
                </div>
            </div>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('responsible') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php
                        if (!empty($details->user_id)) {
                            echo fullname($details->user_id);
                        }
                        ?></p>
                </div>
            </div>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('location') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php
                        if (!empty($details->location)) {
                            echo $details->location;
                        }
                        ?></p>
                </div>
            </div>
            <?php
            $m_url = '';
            if (!empty($details->meeting_url)) {
                $m_url = $details->meeting_url;
            } elseif (!empty($details->platform) && $details->platform == 'jitsi') {
                $m_url = base_url('admin/jitsi/join/' . url_encode($details->mettings_id));
            }
            if (!empty($m_url)):
            ?>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('meeting_url') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <div class="form-control-static">
                        <a href="<?= $m_url ?>" target="_blank" class="btn btn-xs btn-info" style="margin-right: 5px;">
                            <i class="fa fa-video-camera"></i> Join Meeting
                        </a>
                        <button class="btn btn-xs btn-default copy-url" data-url="<?= $m_url ?>"
                                onclick="copyMeetingLink(this, '<?= $m_url ?>')" title="Copy" style="margin-right: 5px;">
                            <i class="fa fa-copy"></i> Copy Link
                        </button>
                        <div style="margin-top: 5px; word-break: break-all; white-space: normal;">
                            <a href="<?= $m_url ?>" target="_blank" style="word-break: break-all; color: #555; font-size: 12px;"><?= $m_url ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-12 notice-details-margin">
                <div class="col-sm-4 text-right">
                    <label class="control-label"><strong><?= lang('description') ?> :</strong></label>
                </div>
                <div class="col-sm-8">
                    <p class="form-control-static"><?php
                        if (!empty($details->description)) {
                            echo $details->description;
                        }
                        ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
            </div>
<script>
function copyMeetingLink(btn, url) {
    navigator.clipboard.writeText(url).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> Copied';
        setTimeout(function() {
            btn.innerHTML = original;
        }, 2000);
    });
}
</script>
        </div>
    </div>
</div>
