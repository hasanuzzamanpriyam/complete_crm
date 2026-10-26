<div class="cl-card card">
    <div class="cl-sec-head card-head">
        <h3>Map Location</h3>
        <div class="cl-spacer spacer"></div>
    </div>

    <div class="cl-form-card form-card">
        <?php echo form_open(base_url('admin/client/update_latitude/' . $client_details->client_id), array('class' => 'cl-form-body form-body', 'enctype' => 'multipart/form-data', 'data-parsley-validate' => '', 'role' => 'form')); ?>
        <div class="cl-frow frow">
            <label class="cl-label label">
                <a href="#" onclick="fetch_lat_long_from_google_cprofile(); return false;" class="cl-accent accent">
                    <i id="gmaps-search-icon" class="fa fa-map-marker"></i> <?= lang('fetch_from_google') ?>
                </a>
                <?= lang('latitude') . ' ( ' . lang('google_map') . ' )' ?>
            </label>
            <input type="text" class="cl-input fin" value="<?php
            if (!empty($client_details->latitude)) {
                echo html_escape($client_details->latitude);
            }
            ?>" placeholder="Auto-filled from address" name="latitude">
        </div>

        <div class="cl-frow frow">
            <label class="cl-label label"><?= lang('longitude') . ' ( ' . lang('google_map') . ' )' ?></label>
            <input type="text" class="cl-input fin" value="<?php
            if (!empty($client_details->longitude)) {
                echo html_escape($client_details->longitude);
            }
            ?>" placeholder="Auto-filled from address" name="longitude">
        </div>

        <div class="cl-frow frow" style="grid-template-columns:1fr;gap:var(--sp-2)">
            <label style="text-align:left"><?= lang('address') ?></label>
            <textarea class="cl-input fin" rows="2" name="address"><?php
                if (!empty($client_details->address)) {
                    echo html_escape($client_details->address);
                }
            ?></textarea>
        </div>

        <input type="hidden" name="city" value="<?php
        if (!empty($client_details->city)) {
            echo html_escape($client_details->city);
        }
        ?>">
        <input type="hidden" name="country" value="<?php
        if (!empty($client_details->country)) {
            echo html_escape($client_details->country);
        }
        ?>">

        <div class="cl-form-foot form-foot">
            <button type="submit" class="btn btn-primary btn-sm cl-btn cl-btn-primary cl-btn-sm"><?= lang('update') ?> coordinates</button>
        </div>
        <?php echo form_close(); ?>
    </div>

    <div class="embed-frame cl-embed-frame" style="margin-top:var(--sp-5)">
        <?php
        $google_api_key = config_item('google_api_key');
        if (!empty($google_api_key) && !empty($client_details->latitude) && !empty($client_details->longitude) && !is_nan($client_details->latitude) && !is_nan($client_details->longitude)) { ?>
            <div id="map" class="client_map" style="height:350px;width:100%;border-radius:var(--r-lg);overflow:hidden;"></div>
            <script>
                var latitude = '<?= $client_details->latitude ?>';
                var longitude = '<?= $client_details->longitude ?>';
                var marker = '<?= addslashes($client_details->name) ?>';
            </script>
            <script src="<?= base_url() ?>assets/plugins/map/map.js"></script>
            <script async defer
                    src="https://maps.googleapis.com/maps/api/js?key=<?= $google_api_key ?>&callback=initMap">
            </script>
        <?php } else { ?>
            <div id="map" class="client_map" style="height:350px;background:var(--c-surface-2);border:2px dashed var(--c-border-strong);border-radius:var(--r-lg);display:grid;place-items:center;color:var(--c-text-3)">
                <div style="text-align:center">
                    <div style="font-size:48px;margin-bottom:var(--sp-3)">🗺</div>
                    <strong>Google Maps embed</strong>
                    <p style="margin-top:var(--sp-2);font-size:var(--fs-sm)">
                        <?= empty($google_api_key) ? 'Requires Google Maps API key' : lang('map_notice') ?>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    function fetch_lat_long_from_google_cprofile() {
        var data = {};
        data.address = $('textarea[name="address"]').val();
        data.city = $('input[name="city"]').val();
        data.country = $('input[name="country"]').val();
        var $icon = $('#gmaps-search-icon');
        $icon.removeClass('fa-map-marker fa-google').addClass('fa-spinner fa-spin');
        $.post('<?= base_url() ?>admin/common/fetch_address_info_gmaps', data).done(function (res) {
            $icon.removeClass('fa-spinner fa-spin').addClass('fa-map-marker');
            try {
                if (typeof res === 'string') { res = JSON.parse(res); }
                if (res.response && res.response.status == 'OK') {
                    $('input[name="latitude"]').val(res.lat);
                    $('input[name="longitude"]').val(res.lng);
                } else if (res.response && res.response.status == 'ZERO_RESULTS') {
                    toastr.warning("<?php echo lang('g_search_address_not_found'); ?>");
                } else {
                    toastr.warning(res.response ? res.response.status : 'Error fetching coordinates');
                }
            } catch (e) {
                toastr.error('Failed to parse geocoding response');
            }
        }).fail(function() {
            $icon.removeClass('fa-spinner fa-spin').addClass('fa-map-marker');
            toastr.error('Network error contacting server');
        });
    }
</script>
