<div class="panel panel-custom">
    <div class="panel-heading">
        <div class="panel-title"><?= lang('chat') ?></div>
    </div>
    <div class="panel-body">
        <div class="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #eee; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <div id="chat-messages">
                <!-- Messages will be loaded here via AJAX -->
            </div>
        </div>
        <form id="chat-form" method="post" action="<?= base_url() ?>admin/tasks/save_chat">
            <input type="hidden" name="task_id" value="<?= $task_details->task_id ?>">
            <div class="input-group">
                <input type="text" name="message" id="chat-message-input" class="form-control" placeholder="<?= lang('type_your_message_here') ?>" required>
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit" id="send-chat-btn"><?= lang('send') ?></button>
                </span>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        function loadChatMessages() {
            var task_id = '<?= $task_details->task_id ?>';
            $.ajax({
                type: 'GET',
                url: '<?= base_url() ?>admin/tasks/get_chat_messages/' + task_id,
                dataType: 'json',
                success: function (data) {
                    $('#chat-messages').html(data.html);
                    var container = $('.chat-container');
                    container.scrollTop(container[0].scrollHeight);
                }
            });
        }

        loadChatMessages();
        // Poll for new messages every 5 seconds
        setInterval(loadChatMessages, 5000);

        $('#chat-form').submit(function (e) {
            e.preventDefault();
            var form = $(this);
            var btn = $('#send-chat-btn');
            btn.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status == 'success') {
                        $('#chat-message-input').val('');
                        loadChatMessages();
                    } else {
                        alert(data.message);
                    }
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>

<style>
    .chat-message {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 10px;
        max-width: 80%;
    }
    .chat-message.me {
        background-color: #e3f2fd;
        margin-left: auto;
        text-align: right;
    }
    .chat-message.others {
        background-color: #f5f5f5;
        margin-right: auto;
    }
    .chat-user {
        font-weight: bold;
        font-size: 0.8em;
        margin-bottom: 3px;
        display: block;
    }
    .chat-time {
        font-size: 0.7em;
        color: #999;
        display: block;
        margin-top: 3px;
    }
</style>
