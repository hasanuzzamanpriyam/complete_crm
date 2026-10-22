<?php echo message_box('success'); ?>
<?php echo message_box('error');
$all_customer_group = $this->db->where('type', 'client')->order_by('customer_group_id', 'DESC')->get('tbl_customer_group')->result();
?>

<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-form">
    <div class="cl-form-card">
        <div class="cl-form-body">
            <div class="cl-subtabs">
                <a href="#general_compnay" class="active" data-toggle="tab"><?= lang('general') ?></a>
                <a href="#contact_compnay" data-toggle="tab"><?= lang('client_contact') . ' ' . lang('details') ?></a>
                <a href="#web_compnay" data-toggle="tab"><?= lang('web') ?></a>
                <a href="#hosting_compnay" data-toggle="tab"><?= lang('hosting') ?></a>
            </div>
            
            <form enctype="multipart/form-data" id="update_client" data-parsley-validate="" novalidate=""
                  action="<?php echo base_url('admin/client/update_client') ?>" method="post" class="cl-form">
                <div class="tab-content">
                    <!-- General -->
                    <div class="tab-pane active" id="general_compnay">
                        <div class="cl-frow">
                            <label><?= lang('company_name') ?> <span class="cl-req">*</span></label>
                            <input type="text" class="cl-input" required value="<?php
                            if (!empty($client_info->name)) {
                                echo html_escape($client_info->name);
                            }
                            ?>" name="name">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_email') ?> <span class="cl-req">*</span></label>
                            <input type="email" class="cl-input" required value="<?php
                            if (!empty($client_info->email)) {
                                echo html_escape($client_info->email);
                            }
                            ?>" name="email">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_vat') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->vat)) {
                                echo html_escape($client_info->vat);
                            }
                            ?>" name="vat">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('customer_group') ?></label>
                            <div class="cl-input-group">
                                <select name="customer_group_id" class="cl-input" style="width: 100%">
                                    <?php
                                    if (!empty($all_customer_group)) {
                                        foreach ($all_customer_group as $customer_group) : ?>
                                            <option value="<?= $customer_group->customer_group_id ?>" <?php
                                            if (!empty($client_info->customer_group_id) && $client_info->customer_group_id == $customer_group->customer_group_id) {
                                                echo 'selected';
                                            } ?>> <?= $customer_group->customer_group; ?></option>
                                        <?php endforeach;
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('language') ?></label>
                            <select name="language" class="cl-input" style="width: 100%">
                                <?php foreach ($languages as $lang) : ?>
                                    <option value="<?= $lang->name ?>" <?php
                                    if (!empty($client_info->language) && $client_info->language == $lang->name) {
                                        echo 'selected';
                                    } elseif (empty($client_info->language) && $this->config->item('language') == $lang->name) {
                                        echo 'selected';
                                    } ?>><?= ucfirst($lang->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('currency') ?></label>
                            <select name="currency" class="cl-input" style="width: 100%">
                                <?php if (!empty($currencies)) : foreach ($currencies as $currency) : ?>
                                    <option value="<?= $currency->code ?>" <?php
                                    if (!empty($client_info->currency) && $client_info->currency == $currency->code) {
                                        echo 'selected';
                                    } elseif (empty($client_info->currency) && $this->config->item('default_currency') == $currency->code) {
                                        echo 'selected';
                                    } ?>><?= $currency->name ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('short_note') ?></label>
                            <textarea class="cl-input" name="short_note" rows="3"><?php
                            if (!empty($client_info->short_note)) {
                                echo html_escape($client_info->short_note);
                            }
                            ?></textarea>
                        </div>
                        
                        <?php
                        if (!empty($client_info)) {
                            $client_id = $client_info->client_id;
                        } else {
                            $client_id = null;
                        }
                        ?>
                        <?= custom_form_Fields(12, $client_id); ?>
                    </div>
                    
                    <!-- Contact -->
                    <div class="tab-pane" id="contact_compnay">
                        <div class="cl-frow">
                            <label><?= lang('company_phone') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->phone)) {
                                echo html_escape($client_info->phone);
                            }
                            ?>" name="phone">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_mobile') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->mobile)) {
                                echo html_escape($client_info->mobile);
                            }
                            ?>" name="mobile">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('zipcode') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->zipcode)) {
                                echo html_escape($client_info->zipcode);
                            }
                            ?>" name="zipcode">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_city') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->city)) {
                                echo html_escape($client_info->city);
                            }
                            ?>" name="city">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_country') ?></label>
                            <select name="country" class="cl-input" style="width: 100%">
                                <optgroup label="Default Country">
                                    <option value="<?= $this->config->item('company_country') ?>">
                                        <?= $this->config->item('company_country') ?></option>
                                </optgroup>
                                <optgroup label="<?= lang('other_countries') ?>">
                                    <?php if (!empty($countries)) : foreach ($countries as $country) : ?>
                                        <option value="<?= $country->value ?>"
                                            <?= (!empty($client_info->country) && $client_info->country == $country->value ? 'selected' : NULL) ?>>
                                            <?= $country->value ?></option>
                                        <?php endforeach; endif; ?>
                                </optgroup>
                            </select>
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_fax') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->fax)) {
                                echo html_escape($client_info->fax);
                            }
                            ?>" name="fax">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('company_address') ?></label>
                            <textarea class="cl-input" name="address" rows="3"><?php
                            if (!empty($client_info->address)) {
                                echo html_escape($client_info->address);
                            }
                            ?></textarea>
                        </div>
                        
                        <div class="cl-frow">
                            <label>
                                <a href="#" onclick="fetch_lat_long_from_google_cprofile(); return false;"
                                   data-toggle="tooltip"
                                   data-title="<?= lang('fetch_from_google') . ' - ' . lang('customer_fetch_lat_lng_usage'); ?>">
                                    <i id="gmaps-search-icon" class="fa fa-google" aria-hidden="true"></i>
                                </a>
                                <?= lang('latitude') . '( ' . lang('google_map') . ' )' ?>
                            </label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->latitude)) {
                                echo $client_info->latitude;
                            }
                            ?>" name="latitude">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('longitude') . '( ' . lang('google_map') . ' )' ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->longitude)) {
                                echo $client_info->longitude;
                            }
                            ?>" name="longitude">
                        </div>
                    </div>
                    
                    <!-- Web -->
                    <div class="tab-pane" id="web_compnay">
                        <div class="cl-frow">
                            <label><?= lang('company_domain') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->website)) {
                                echo html_escape($client_info->website);
                            }
                            ?>" name="website">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('skype_id') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->skype_id)) {
                                echo html_escape($client_info->skype_id);
                            }
                            ?>" name="skype_id">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('facebook_profile_link') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->facebook)) {
                                echo html_escape($client_info->facebook);
                            }
                            ?>" name="facebook">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('twitter_profile_link') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->twitter)) {
                                echo html_escape($client_info->twitter);
                            }
                            ?>" name="twitter">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('linkedin_profile_link') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->linkedin)) {
                                echo html_escape($client_info->linkedin);
                            }
                            ?>" name="linkedin">
                        </div>
                    </div>
                    
                    <!-- Hosting -->
                    <div class="tab-pane" id="hosting_compnay">
                        <div class="cl-frow">
                            <label><?= lang('hosting_company') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->hosting_company)) {
                                echo html_escape($client_info->hosting_company);
                            }
                            ?>" name="hosting_company">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('hostname') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->hostname)) {
                                echo html_escape($client_info->hostname);
                            }
                            ?>" name="hostname">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('username') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->username)) {
                                echo html_escape($client_info->username);
                            }
                            ?>" name="username">
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('password') ?></label>
                            <div class="cl-input-group">
                                <?php
                                if (!empty($client_info->password)) {
                                    $password = strlen(decrypt($client_info->password));
                                }
                                ?>
                                <input type="password" name="password" value="" placeholder="<?php
                                if (!empty($password)) {
                                    for ($p = 1; $p <= $password; $p++) {
                                        echo '*';
                                    }
                                }
                                ?>" class="cl-input">
                                <strong id="show_password" class="required"></strong>
                            </div>
                            <?php if (!empty($client_info->password)) { ?>
                                <a data-toggle="modal" data-target="#myModal"
                                   href="<?= base_url('admin/client/see_password/c_' . $client_info->client_id) ?>"
                                   id="see_password"><?= lang('see_password') ?></a>
                                <strong id="hosting_password" class="required"></strong>
                            <?php } ?>
                        </div>
                        
                        <div class="cl-frow">
                            <label><?= lang('port') ?></label>
                            <input type="text" class="cl-input" value="<?php
                            if (!empty($client_info->port)) {
                                echo html_escape($client_info->port);
                            }
                            ?>" name="port">
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="cl-form-foot">
                <button type="submit" form="update_client" class="cl-btn cl-btn-primary"><?= lang('save') ?></button>
            </div>
        </div>
    </div>

<script type="text/javascript">
$(document).on("submit", "form", function(event) {
    var form = $(event.target);
    var id = form.attr('id');
    if (form.attr('action') == '<?= base_url('admin/client/update_client') ?>') {
        event.preventDefault();
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        }).done(function(response) {
            response = JSON.parse(response);
            if (response.status == 'success') {
                if (typeof(response.id) != 'undefined') {
                    var client_id = $('select[name="client_id"]');
                    var paid_by = $('select[name="paid_by"]');
                    if (client_id.length != 0) {
                        var groups = client_id;
                    } else if (paid_by.length != 0) {
                        var groups = paid_by;
                    }
                    groups.prepend('<option selected value="' + response.id + '">' + response.name + '</option>');
                    var select2Instance = groups.data('select2');
                    var resetOptions = select2Instance.options.options;
                    groups.select2('destroy').select2(resetOptions)
                }
            }
            toastr[response.status](response.message);
            $('#myModal').modal('hide');
        }).fail(function() {
            console.log('There was a problem with AJAX')
        });
    }
});

function fetch_lat_long_from_google_cprofile() {
    var data = {};
    data.address = $('textarea[name="address"]').val();
    data.city = $('input[name="city"]').val();
    data.country = $('select[name="country"] option:selected').text();
    $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
    $.post('<?= base_url() ?>admin/common/fetch_address_info_gmaps', data).done(function(data) {
        data = JSON.parse(data);
        $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
        if (data.response.status == 'OK') {
            $('input[name="latitude"]').val(data.lat);
            $('input[name="longitude"]').val(data.lng);
        } else {
            if (data.response.status == 'ZERO_RESULTS') {
                toastr.warning("<?php echo lang('g_search_address_not_found'); ?>");
            } else {
                toastr.warning(data.response.status);
            }
        }
    });
}
</script>
</div>