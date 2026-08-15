<?php
$frontend = $this->uri->segment(1);
$mid = my_id();
if (!empty($mid) && $frontend != 'frontend') { ?>
    <div class="chat_frame">
        <?php include_once 'assets/plugins/chat/chat.php'; ?>
        <button type="button" class="btn btn-round custom-bg" id="open_chat_list"><span
                class="fa fa-comments"></span></button>
        <div class="panel b0" id="chat_list">
            <div class="panel-heading custom-bg">
                <div class="">
                    <?= lang('users') . ' ' . lang('list') ?>
                    <div class="pull-right chat-icon">
                        <i data-toggle="tooltip" data-placement="top" title="<?= lang('close') ?>" id="close_chat_list"
                           class="fa fa-times"
                           aria-hidden="true"></i>
                    </div>
                </div>
            </div>
            <ul class="nav b bt0">
                <?php
                $ai_user_id = (int) $this->session->userdata('user_id');
                $ai_user_type = (int) $this->session->userdata('user_type');
                $ai_visible = !empty($ai_user_id) && !empty($mid)
                    && $ai_user_type != 2
                    && config_item('ai_assistant_enabled') == '1';
                if ($ai_visible) { ?>
                    <li>
                        <a href="#" id="ai_start_chat" class="media-box p pb-sm pt-sm bb mt0">
                            <span class="pull-right"><span class="circle circle-success circle-lg"></span></span>
                            <span class="pull-left">
                                <span class="ai-chat-avatar custom-bg"><i class="fa fa-magic"></i></span>
                            </span>
                            <span class="media-box-body">
                                <span class="media-box-heading">
                                    <strong class="text-sm"><?= lang('ai_fab_title') ?></strong>
                                    <br>
                                    <small class="text-muted"><span class="pull-left"><?= lang('ai_online') ?></span></small>
                                </span>
                            </span>
                        </a>
                    </li>
                <?php } ?>
                <li>
                    <?php
                    $users = $this->admin_model->get_online_users();
                    if (!empty($users)) {
                        foreach ($users as $key => $v_users) {
                            if (!empty($v_users)) {
                                foreach ($v_users as $v_user) {
                                    ?>
                                    <!-- START User status-->
                                    <a href="#" data-user_id="<?= $v_user->user_id ?>"
                                       class="media-box p pb-sm pt-sm bb mt0 start_chat">
                                        <?php
                                        if ($key == 'online') {
                                            ?>
                                            <span class="pull-right">
                                 <span class="circle circle-success circle-lg"></span>
                              </span>
                                        <?php } else {
                                            ?>
                                            <span class="pull-right">
                                 <span class="circle circle-warning circle-lg"></span>
                              </span>
                                        <?php } ?>
                                        <span class="pull-left">
                                 <!-- Contact avatar-->
                                 <img
                                     src="<?= base_url($v_user->avatar) ?>"
                                     alt="Image" class="media-box-object img-circle thumb48">
                              </span>
                                        <!-- Contact info-->
                              <span class="media-box-body">
                                 <span class="media-box-heading">
                                    <strong class="text-sm"><?= $v_user->fullname ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <span class="pull-left">
                                        <?= $v_user->designations ?></span>
                                        <span class="pull-right"><?php
                                            if(!empty($v_user->online_time)){
                                                echo time_ago($v_user->online_time);
                                            }else{
                                                echo lang('never');
                                            }?></span>
                                    </small>
                                 </span>
                              </span>
                                    </a>
                                    <?php
                                }
                            }
                        }
                    } ?>
                </li>
            </ul>
        </div>
        <div id="chat_box"></div>

        <?php if ($ai_visible) :
            $ai_seg1 = $this->uri->segment(1);
            $ai_seg2 = $this->uri->segment(2);
            $ai_context_url = $this->uri->uri_string();
            $ai_module = trim($ai_seg1 . ' > ' . $ai_seg2, ' > ');
            if (empty($ai_module)) {
                $ai_module = 'dashboard';
            }
            $ai_avatar = staffImage($mid);
            ?>
            <link rel="stylesheet" href="<?= base_url('assets/css/ai_chat.css') ?>">

            <div class="panel b0 mb0 ai-chat-panel" id="ai_chat_panel">
                <div class="panel-heading custom-bg pt-sm">
                    <div>
                        <span class="chat_title"><i class="fa fa-magic"></i> <?= lang('ai_fab_title') ?></span>
                        <div class="pull-right chat-icon">
                            <div class="dropdown ai-chat-dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                                   title="<?= lang('settings') ?>" href="#">
                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                </a>
                                <ul class="dropdown-menu chat-setting-dropdown animated zoomIn ai-chat-menu">
                                    <li class="ai-provider-menu-item" id="ai_provider_menu_item" style="display:none;">
                                        <i class="fa fa-plug"></i> <span id="ai_provider_label"><?= lang('ai_no_provider_active') ?></span>
                                    </li>
                                    <li><a href="#" data-ai-action="new"><i class="fa fa-plus"></i> <?= lang('ai_new_chat') ?></a></li>
                                    <li><a href="#" data-ai-action="history"><i class="fa fa-clock-o"></i> <?= lang('ai_history') ?></a></li>
                                    <li><a href="#" data-ai-action="close"><i class="fa fa-times"></i> <?= lang('ai_close') ?></a></li>
                                </ul>
                            </div>
                            <i data-toggle="tooltip" data-placement="top" data-ai-action="close"
                               title="<?= lang('ai_close') ?>" class="fa fa-times" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <div class="ai-chat-history" id="ai_chat_history">
                    <div class="ai-chat-history-head">
                        <span><i class="fa fa-clock-o"></i> <?= lang('ai_history') ?></span>
                        <span class="pull-right"><a href="#" id="ai_history_close"><i class="fa fa-times"></i></a></span>
                    </div>
                    <div class="ai-chat-history-body" id="ai_chat_history_body"></div>
                </div>

                <div class="chat-body br bl" id="ai_chat_body">
                    <ul id="ai_chat_messages"></ul>
                </div>
                <div class="panel-footer b0 chat-input-box">
                    <input class="form-control" id="ai_chat_input" placeholder="<?= lang('ai_type_message') ?>">
                </div>
            </div>

            <script type="text/javascript">
                window.AIChatConfig = {
                    baseUrl: <?= json_encode(base_url()) ?>,
                    userId: <?= (int) $ai_user_id ?>,
                    userAvatar: <?= json_encode(base_url($ai_avatar)) ?>,
                    contextUrl: <?= json_encode($ai_context_url) ?>,
                    contextModule: <?= json_encode($ai_module) ?>,
                    strings: <?= json_encode(array(
                        'thinking'      => lang('ai_thinking'),
                        'confirmDelete' => lang('ai_confirm_delete_session'),
                        'emptyChat'     => lang('ai_chat_empty'),
                    )) ?>
                };
            </script>
            <script src="<?= base_url('assets/js/ai_chat.js') ?>"></script>
        <?php endif; ?>
        <audio id="chat-tune" controls="">
            <source src="<?= base_url() ?>assets/plugins/chat/chat_tune.mp3" type="audio/mpeg">
        </audio>
    </div><!--End live_chat_section-->
<?php } ?>
