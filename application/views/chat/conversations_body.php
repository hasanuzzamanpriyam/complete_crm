<?php
// get all message by private chat id
// NOTE: the controller passes messages already reversed (chronological) here.
// Markup must stay identical to the .cc-msgs block in full_conversations.php.
$messages = array();
if (!empty($all_messages)) {
    foreach ($all_messages as $message) {
        array_push($messages, $message);
    }
    $cc_last_day = '';
    $cc_my_id = $this->session->userdata('user_id');
    foreach ($all_messages as $message) :
        $cc_day = date('Y-m-d', strtotime($message->message_time));
        if ($cc_day != $cc_last_day) {
            $cc_last_day = $cc_day;
            if ($cc_day == date('Y-m-d')) {
                $cc_day_label = 'Today';
            } elseif ($cc_day == date('Y-m-d', strtotime('-1 day'))) {
                $cc_day_label = 'Yesterday';
            } else {
                $cc_day_label = date('d M Y', strtotime($message->message_time));
            }
            ?>
            <div class="cc-day"><?= $cc_day_label ?></div>
        <?php } ?>
        <?php if ($message->user_id == $cc_my_id) { ?>
            <div class="cc-m me">
                <img data-toggle="tooltip" data-placement="top"
                     title="<?= fullname($message->user_id) ?>" class="cc-ava"
                     src="<?= base_url(staffImage($message->user_id)) ?>"
                     alt="message user image"/>
                <div class="cc-mbody">
                    <div class="cc-bubble">
                        <?php echo $message->message ?>
                    </div>
                    <div data-toggle="tooltip" data-placement="top"
                         title="<?php echo strftime(config_item('date_format'), strtotime($message->message_time)) . ' ' . lang('at') . ' ' . display_time($message->message_time); ?>"
                         class="cc-time"><?= time_ago($message->message_time); ?></div>
                </div>
            </div>
        <?php } else { ?>
            <div class="cc-m">
                <img data-toggle="tooltip" data-placement="top"
                     title="<?= fullname($message->user_id) ?>" class="cc-ava"
                     src="<?= base_url(staffImage($message->user_id)) ?>"
                     alt="message user image"/>
                <div class="cc-mbody">
                    <div class="cc-who"><?= fullname($message->user_id) ?></div>
                    <div class="cc-bubble">
                        <?php echo $message->message ?>
                    </div>
                    <div data-toggle="tooltip" data-placement="top"
                         title="<?php echo strftime(config_item('date_format'), strtotime($message->message_time)) . ' ' . lang('at') . ' ' . display_time($message->message_time); ?>"
                         class="cc-time"><?= time_ago($message->message_time); ?></div>
                </div>
            </div>
        <?php } ?>
    <?php endforeach;
}
?>
