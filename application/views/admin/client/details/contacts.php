<?php
$add_contact = $this->uri->segment(6);
if (!empty($add_contact)) :
    $user_id = $this->uri->segment(7);
    $languages = $this->db->where('active', 1)->order_by('name', 'ASC')->get('tbl_languages')->result();
    $locales = $this->db->order_by('name', 'ASC')->get('tbl_locales')->result();
    ?>
    <?php $this->load->view('admin/client/_client_assets'); ?>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php
    $edited = can_action('4', 'edited');
    if (!empty($edited)) {
        $account_details = $this->client_model->check_by(array('user_id' => $user_id), 'tbl_account_details');    
        $user_info = $this->client_model->check_by(array('user_id' => $user_id), 'tbl_users');
        ?>
        <form role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
              action="<?php echo base_url(); ?>admin/client/save_contact/<?php
              if (!empty($account_details)) {
                  echo $account_details->user_id;
              }
              ?>" method="post" class="cl-form cl-form-card">
        
        <div class="cl-card">
            <!-- Header -->
            <div class="cl-sec-head">
                <h1><?= lang('add_contact') ?></h1>
                <div class="cl-spacer"></div>
                <a href="<?= base_url() ?>admin/client/details/<?= $client_details->client_id ?>/contacts"
                   class="cl-btn cl-btn-ghost"><?= lang('return_to_details') ?></a>
            </div>
            
            <div class="cl-form-body">
                <input type="hidden" name="r_url"
                       value="<?= base_url() ?>admin/client/details/<?= $client_details->client_id ?>/contacts">
                <input type="hidden" name="company" value="<?= $client_details->client_id ?>">
                <input type="hidden" name="role_id" value="2">
                <input type="hidden" id="user_id" value="<?php
                if (!empty($account_details)) {
                    echo $account_details->user_id;
                }
                ?>">
                
                <!-- Left column: fields -->
                <div class="cl-frow">
                    <label><?= lang('full_name') ?> <span class="cl-req">*</span></label>
                    <input type="text" class="cl-input" value="<?php
                    if (!empty($account_details)) {
                        echo html_escape($account_details->fullname);
                    }
                    ?>" placeholder="E.g John Doe" name="fullname" required>
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('email') ?><span class="cl-req">*</span></label>
                    <input class="cl-input" id="check_email_addrees" type="email"
                           value="<?php
                           if (!empty($user_info)) {
                               echo html_escape($user_info->email);
                           }
                           ?>" placeholder="me@domin.com" name="email" required>
                    <span id="email_addrees_error" class="required"></span>
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('phone') ?></label>
                    <input type="text" class="cl-input" value="<?php
                    if (!empty($account_details)) {
                        echo html_escape($account_details->phone);
                    }
                    ?>" name="phone" placeholder="+52 782 983 434">
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('mobile') ?> <span class="cl-req">*</span></label>
                    <input type="text" class="cl-input" value="<?php
                    if (!empty($account_details)) {
                        echo html_escape($account_details->mobile);
                    }
                    ?>" name="mobile" placeholder="+8801723611125">
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('skype_id') ?></label>
                    <input type="text" class="cl-input" value="<?php
                    if (!empty($account_details)) {
                        echo html_escape($account_details->skype);
                    }
                    ?>" name="skype" placeholder="john">
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('language') ?></label>
                    <select name="language" class="cl-input">
                        <?php foreach ($languages as $lang) : ?>
                            <option value="<?= $lang->name ?>" <?php
                            if (!empty($account_details->language) && $account_details->language == $lang->name) {
                                echo 'selected="selected"';
                            } else {
                                echo($this->config->item('language') == $lang->name ? ' selected="selected"' : '');
                            }
                            ?>><?= ucfirst($lang->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="cl-frow">
                    <label><?= lang('locale') ?></label>
                    <select class="cl-input" name="locale">
                        <?php foreach ($locales as $loc) : ?>
                            <option lang="<?= $loc->code ?>"
                                    value="<?= $loc->locale ?>" <?= ($this->config->item('locale') == $loc->locale ? ' selected="selected"' : '') ?>>
                                <?= $loc->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php
                if (!empty($account_details->direction)) {
                    $direction = $account_details->direction;
                } else {
                    $RTL = config_item('RTL');
                    if (!empty($RTL)) {
                        $direction = 'rtl';
                    }
                }
                ?>
                <div class="cl-frow">
                    <label for="direction"><?= lang('direction') ?></label>
                    <select name="direction" class="cl-input">
                        <option <?php
                        if (!empty($direction)) {
                            echo $direction == 'ltr' ? 'selected' : '';
                        }
                        ?> value="ltr"><?= lang('ltr') ?></option>
                        <option <?php
                        if (!empty($direction)) {
                            echo $direction == 'rtl' ? 'selected' : '';
                        }
                        ?> value="rtl"><?= lang('rtl') ?></option>
                    </select>
                </div>
                
                <?php if (empty($account_details)) : ?>
                <div class="cl-frow">
                    <label><?= lang('username') ?> <span class="cl-req">*</span></label>
                    <input class="cl-input" id="check_username" type="text"
                           value="<?= set_value('username') ?>" placeholder="johndoe" name="username"
                           required>
                    <div class="required" id="check_username_error"></div>
                </div>
                <div class="cl-frow">
                    <label><?= lang('password') ?> <span class="cl-req">*</span></label>
                    <input type="password" class="cl-input" id="new_password"
                           value="<?= set_value('password') ?>" name="password" required>
                </div>
                <div class="cl-frow">
                    <label><?= lang('confirm_password') ?>
                        <span class="cl-req">*</span></label>
                    <input type="password" class="cl-input" data-parsley-equalto="#new_password"
                           value="<?= set_value('confirm_password') ?>" name="confirm_password"
                           required>
                </div>
                <div class="cl-frow">
                    <label><?= lang('send_email') . ' ' . lang('password') ?></label>
                    <div class="cl-input-group">
                        <div class="checkbox c-checkbox">
                            <label class="needsclick">
                                <input type="checkbox" name="send_email_password">
                                <span class="fa fa-check"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right column: permissions -->
            <div class="cl-side-panel">
                <h4><?= lang('permission') ?></h4>
                <?php
                $all_client_menu = $this->db->where('parent', 0)->order_by('sort')->get('tbl_client_menu')->result();
                if (!empty($user_info)) {
                    $user_menu = $this->db->where('user_id', $user_info->user_id)->get('tbl_client_role')->result();
                }
                foreach ($all_client_menu as $key => $v_menu) {
                    ?>
                    <div class="cl-frow" style="grid-template-columns: 1fr; gap: var(--sp-2);">
                        <label class="cl-switch" style="justify-content: flex-start;">
                            <span class="cl-track"></span>
                            <span class="cl-txt"><?= lang($v_menu->label) ?></span>
                            <input data-id="" name="<?= $v_menu->label ?>"
                                   value="<?= $v_menu->menu_id ?>" <?php
                            $client_menu = unserialize(config_item('client_default_menu'));
                            if (!empty($user_menu)) {
                                foreach ($user_menu as $v_u_menu) {
                                    if ($v_u_menu->menu_id == $v_menu->menu_id) {
                                        echo 'checked';
                                    }
                                }
                            } else {
                                if (!empty($client_menu['client_default_menu'])) {
                                    foreach ($client_menu['client_default_menu'] as $default_menu) {
                                        echo $v_menu->menu_id == $default_menu ? 'checked' : '';
                                    }
                                }
                            }
                            ?> type="checkbox" style="position: absolute; opacity: 0; pointer-events: none;">
                        </label>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <div class="cl-form-foot">
                <button type="submit" id="new_uses_btn"
                        class="cl-btn cl-btn-primary"><?= lang('save') . ' ' . lang('client_contact') ?></button>
            </div>
        </div>
        </form>
        <?php
    } ?>
<?php else : ?>
    <div class="cl-card cl-contacts">
        <div class="cl-sec-head">
            <h1><?= lang('contacts') ?></h1>
            <div class="cl-spacer"></div>
            <?php
            $edited = can_action('4', 'edited');
            if (!empty($edited)) {
                ?>
                <a href="<?= base_url() ?>admin/client/details/<?= $client_details->client_id ?>/contacts/add_contacts"
                   class="cl-btn cl-btn-primary"><?= lang('add_contact') ?></a>
            <?php } ?>
        </div>
        
        <table class="cl-tbl cl-mini" id="datatable_action" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('full_name') ?></th>
                <th><?= lang('email') ?></th>
                <th><?= lang('phone') ?></th>
                <th><?= lang('mobile') ?></th>
                <th><?= lang('skype_id') ?></th>
                <th class="cl-num"><?= lang('last_login') ?></th>
                <th class="cl-acts"><?= lang('action') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $client_contacts = $this->client_model->get_client_contacts($client_details->client_id);
            if (!empty($client_contacts)) {
                foreach ($client_contacts as $key => $contact) {
                    ?>
                    <tr>
                        <td>
                            <div class="cl-cell-name">
                                <div class="cl-avatar cl-av-mint">
                                    <?php
                                    $words = preg_split('/\s+/', trim($contact->fullname));
                                    $initials = '';
                                    foreach ($words as $w) {
                                        if ($w !== '') $initials .= mb_substr($w, 0, 1);
                                    }
                                    echo strtoupper(mb_substr($initials, 0, 2)) ?: 'CL';
                                    ?>
                                </div>
                                <div>
                                    <div class="cl-nm"><?= html_escape($contact->fullname) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="cl-em"><?= html_escape($contact->email) ?></td>
                        <td><a href="tel:<?= html_escape($contact->phone) ?>"><?= html_escape($contact->phone) ?></a></td>
                        <td><a href="tel:<?= html_escape($contact->mobile) ?>"><?= html_escape($contact->mobile) ?></a></td>
                        <td>
                            <a href="skype:<?= html_escape($contact->skype) ?>?call"><?= html_escape($contact->skype) ?></a>
                        </td>
                        <?php
                        if ($contact->last_login == '0000-00-00 00:00:00' || empty($contact->last_login)) {
                            $login_time = "-";
                        } else {
                            $login_time = strftime(config_item('date_format'), strtotime($contact->last_login)) . ' ' . display_time($contact->last_login);
                        } ?>
                        <td class="cl-num"><?= $login_time ?></td>
                        <td class="cl-acts">
                            <a href="<?= base_url() ?>admin/client/make_primary/<?= $contact->user_id ?>/<?= $client_details->client_id ?>"
                               class="cl-btn cl-btn-icon <?php
                            if ($client_details->primary_contact == $contact->user_id) {
                                echo "accent";
                            }
                            ?>" title="<?= lang('primary_contact') ?>">
                                <i class="fa fa-chain"></i>
                            </a>
                            <a href="<?= base_url() ?>admin/client/details/<?= $client_details->client_id . '/contacts/add_contacts/' . $contact->user_id ?>"
                               class="cl-btn cl-btn-icon" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="<?= base_url() ?>admin/client/delete_contacts/<?= $client_details->client_id . '/' . $contact->user_id ?>"
                               class="cl-btn cl-btn-icon danger" title="<?= lang('delete') ?>">
                                <i class="fa fa-trash-o"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
<?php endif ?>