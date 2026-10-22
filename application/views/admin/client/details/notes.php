<?php $this->load->view('admin/client/_client_assets'); ?>
<div class="cl-root cl-tbl">
    <div class="cl-card">
        <div class="cl-sec-head">
            <h1><?= lang('notes') ?></h1>
            <div class="cl-spacer"></div>
            <button id="new_notes" class="cl-btn cl-btn-ghost cl-btn-sm"><?= lang('new') . ' ' . lang('notes') ?></button>
        </div>
        
        <?php
        $client_notes = get_result('tbl_notes', array('user_id' => $client_details->client_id, 'is_client' => 'Yes'));
        $url = $this->uri->segment(5);
        if ($url == 'notes') {
            $notes_id = $this->uri->segment(6);
            $notes_info = $this->db->where('notes_id', $notes_id)->get('tbl_notes')->row();
        } else {
            $notes_id = null;
        }
        ?>
        
        <div class="new_notes mb-lg" style="display: <?= !empty($notes_info) ? 'block' : 'none' ?>">
            <form action="<?php echo base_url() ?>admin/client/new_notes/<?= $notes_id ?>" method="post"
                  class="cl-form cl-form-card">
                <div class="cl-form-body">
                    <div class="cl-frow" style="grid-template-columns: 1fr;">
                        <textarea name="notes" class="cl-input" rows="4"><?php if (!empty($notes_info)) {
                            echo $notes_info->notes;
                        } ?></textarea>
                    </div>
                    <input type="hidden" name="client_id" value="<?= $client_details->client_id ?>">
                    <div class="cl-form-foot">
                        <button class="cl-btn cl-btn-primary" type="submit"><?= lang('save') ?></button>
                    </div>
                </div>
            </form>
        </div>
        
        <script>
            $(document).ready(function () {
                $('#new_notes').click(function () {
                    $('.new_notes').toggle('slow');
                });
            });
        </script>
        
        <table class="cl-tbl cl-mini" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th><?= lang('description') ?></th>
                <th><?= lang('added_by') ?></th>
                <th class="cl-num"><?= lang('date') ?></th>
                <th class="cl-acts"><?= lang('action') ?></th>
            </tr>
            </thead>
            
            <tbody>
            <?php
            if (!empty($client_notes)) {
                foreach ($client_notes as $v_notes) {
                    $n_user = $this->db->where('user_id', $v_notes->added_by)->get('tbl_users')->row();
                    if (empty($n_user)) {
                        $n_user->fullname = '-';
                        $n_url = '#';
                    } else {
                        $n_url = base_url() . 'admin/user/user_details/' . $n_user->user_id;
                    }
                    ?>
                    <tr>
                        <td>
                            <a class="cl-nm"
                               href="<?= base_url() ?>admin/client/details/<?= $client_details->client_id . '/notes/' . $v_notes->notes_id ?>">
                                <?= html_escape($v_notes->notes) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?= $n_url ?>"> <?= html_escape($n_user->username) ?></a>
                        </td>
                        <td class="cl-num"><?= strftime(config_item('date_format'), strtotime($v_notes->added_date)) . ' ' . display_time($v_notes->added_date); ?></td>
                        <td class="cl-acts">
                            <?= btn_edit('admin/client/details/' . $client_details->client_id . '/notes/' . $v_notes->notes_id) ?>
                            <?php echo btn_delete('admin/client/delete_notes/' . $v_notes->notes_id . '/' . $client_details->client_id); ?>
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