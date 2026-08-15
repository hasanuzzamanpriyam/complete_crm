/* AI Assistant - integrated into the native CRM chat system */
(function ($) {
    'use strict';

    var CFG = window.AIChatConfig || {};
    var baseUrl = CFG.baseUrl || '/';
    var state = { sessionId: null, sending: false, open: false, providerLoaded: false };

    function cfg() {
        return window.AIChatConfig || {};
    }

    function esc(s) {
        s = String(s == null ? '' : s);
        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function decodeEntities(s) {
        s = String(s == null ? '' : s);
        return s.replace(/&#(\d+);/g, function (m, n) { return String.fromCharCode(parseInt(n, 10)); })
                .replace(/&#x([0-9a-fA-F]+);/g, function (m, n) { return String.fromCharCode(parseInt(n, 16)); })
                .replace(/&quot;/g, '"')
                .replace(/&apos;/g, "'")
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&amp;/g, '&');
    }

    function nl2br(s) {
        return esc(s).replace(/\n/g, '<br>');
    }

    function fmtTime(iso) {
        var d;
        if (iso) {
            d = new Date(String(iso).replace(' ', 'T'));
        } else {
            d = new Date();
        }
        if (isNaN(d.getTime())) { d = new Date(); }
        var h = d.getHours(), m = d.getMinutes();
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    function timeAgo(iso) {
        var d = new Date(String(iso).replace(' ', 'T'));
        if (isNaN(d.getTime())) { return ''; }
        var s = Math.floor((Date.now() - d.getTime()) / 1000);
        if (s < 60) { return 'just now'; }
        var m = Math.floor(s / 60);
        if (m < 60) { return m + 'm'; }
        var h = Math.floor(m / 60);
        if (h < 24) { return h + 'h'; }
        var days = Math.floor(h / 24);
        if (days < 7) { return days + 'd'; }
        return d.toLocaleDateString();
    }

    function scrollBottom() {
        var $body = $('#ai_chat_body');
        if ($body.length) {
            $body.scrollTop($body[0].scrollHeight);
        }
    }

    function renderMessage(role, text, time) {
        var li = $('<li>');
        text = nl2br(decodeEntities(text));
        if (role === 'assistant') {
            li.html('<div class="message chat-message">'
                + '<div class="avatar"><span class="ai-chat-avatar ai-chat-avatar-sm custom-bg"><i class="fa fa-magic"></i></span></div>'
                + '<div class="text text-l"><p>' + text + '</p>'
                + (time ? '<p><small>' + esc(time) + '</small></p>' : '')
                + '</div></div>');
        } else {
            li.html('<div class="message-right chat-message">'
                + '<div class="text text-r"><p>' + text + '</p>'
                + (time ? '<p><small>' + esc(time) + '</small></p>' : '')
                + '</div>'
                + '<div class="avatar" style="padding:0px 0px 0px 10px !important">'
                + '<img class="img-circle" style="width:100%;" src="' + esc((cfg().userAvatar) || '') + '">'
                + '</div></div>');
        }
        $('#ai_chat_messages').append(li);
        scrollBottom();
    }

    function renderError(message) {
        var li = $('<li>').html('<div class="message chat-message">'
            + '<div class="avatar"><span class="ai-chat-avatar ai-chat-avatar-sm custom-bg"><i class="fa fa-magic"></i></span></div>'
            + '<div class="text text-l"><p class="ai-chat-error">' + esc(decodeEntities(message)) + '</p></div></div>');
        $('#ai_chat_messages').append(li);
        scrollBottom();
    }

    function showTyping() {
        if ($('#ai_typing').length) { return; }
        var li = $('<li id="ai_typing">').html('<div class="message chat-message">'
            + '<div class="avatar"><span class="ai-chat-avatar ai-chat-avatar-sm custom-bg"><i class="fa fa-magic"></i></span></div>'
            + '<div class="text text-l"><p class="ai-chat-typing">' + esc((cfg().strings && cfg().strings.thinking) || 'Thinking...') + '</p></div></div>');
        $('#ai_chat_messages').append(li);
        scrollBottom();
    }

    function hideTyping() {
        $('#ai_typing').remove();
    }

    function showWelcome() {
        renderMessage('assistant', decodeEntities((cfg().strings && cfg().strings.emptyChat) || 'No messages yet.'), '');
    }

    function updateProviderLabel(name, model) {
        var label = name || 'AI';
        if (model) { label += ' · ' + model; }
        $('#ai_provider_label').text(label);
        $('#ai_provider_menu_item').show();
    }

    function loadConfig() {
        state.providerLoaded = true;
        $.ajax({
            url: baseUrl + 'admin/ai/config',
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (!res || !res.success) { return; }
                if (res.default) {
                    updateProviderLabel(res.default.provider_name, res.default.model);
                } else if (res.providers && res.providers.length) {
                    updateProviderLabel(res.providers[0].provider_name, res.providers[0].model);
                } else {
                    $('#ai_provider_label').text('No active provider');
                }
            }
        });
    }

    function open() {
        state.open = true;
        $('#ai_chat_panel').show();
        $('.chat_frame').addClass('ai-open');
        $('#chat_list').fadeOut(5);
        $('#open_chat_list').show();
        $('#chat_box').css('margin-right', '520px');
        if (!state.providerLoaded) { loadConfig(); }
        if (!$('#ai_chat_messages').children().length) {
            showWelcome();
        }
        setTimeout(function () { $('#ai_chat_input').trigger('focus'); }, 150);
    }

    function close() {
        state.open = false;
        $('#ai_chat_panel').hide();
        $('.chat_frame').removeClass('ai-open');
        hideHistory();
        if (!$('#chat_list').is(':visible')) {
            $('#chat_box').css('margin-right', '70px');
        }
    }

    function toggleMaximize() {
        var $box = $('#chat_box');
        var isMax = $box.hasClass('ai-maximized');
        if (!isMax) {
            $box.addClass('ai-maximized');
            $box.css('width', '80vw').css('height', '85vh').css('max-width', 'none');
            $('.chat_frame').addClass('ai-chat-max').css('right', '50%').css('left', '50%').css('transform', 'translate(-50%, 0)');
        } else {
            $box.removeClass('ai-maximized');
            $box.css('width', '').css('height', '').css('max-width', '');
            $('.chat_frame').removeClass('ai-chat-max').css('right', '5px').css('left', '').css('transform', '');
            $box.css('margin-right', '520px');
        }
    }

    function newChat() {
        hideHistory();
        $('#ai_chat_messages').empty();
        state.sessionId = null;
        $.ajax({
            url: baseUrl + 'admin/ai/new_session',
            type: 'POST',
            data: { module_context: cfg().contextModule || 'chat' },
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    state.sessionId = res.session_id;
                }
            }
        });
        showWelcome();
        $('#ai_chat_input').trigger('focus');
    }

    function send() {
        var $input = $('#ai_chat_input');
        var text = $.trim($input.val());
        if (!text || state.sending) { return; }
        state.sending = true;
        renderMessage('user', nl2br(text), fmtTime());
        $input.val('');
        showTyping();

        $.ajax({
            url: baseUrl + 'admin/ai/process',
            type: 'POST',
            dataType: 'json',
            data: {
                prompt: text,
                session_id: state.sessionId || '',
                module_context: cfg().contextModule || 'chat',
                context_url: cfg().contextUrl || ''
            },
            success: function (res) {
                hideTyping();
                state.sending = false;
                if (res && res.success) {
                    state.sessionId = res.session_id || state.sessionId;
                    renderMessage('assistant', nl2br(res.content || ''), fmtTime());
                    if (res.provider) {
                        updateProviderLabel(res.provider, res.model);
                    }
                } else {
                    renderError((res && res.message) ? res.message : 'Request failed. Please try again.');
                }
                $('#ai_chat_input').trigger('focus');
            },
            error: function () {
                hideTyping();
                state.sending = false;
                renderError('Request failed. Please try again.');
            }
        });
    }

    function toggleHistory() {
        if ($('#ai_chat_history').is(':visible')) {
            hideHistory();
            return;
        }
        loadHistory();
    }

    function hideHistory() {
        $('#ai_chat_history').hide();
    }

    function loadHistory() {
        $('#ai_chat_history').show();
        var $body = $('#ai_chat_history_body');
        $body.html('<div class="ai-chat-history-empty">Loading...</div>');
        $.ajax({
            url: baseUrl + 'admin/ai/sessions',
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (!res || !res.success || !res.sessions || !res.sessions.length) {
                    $body.html('<div class="ai-chat-history-empty">No conversations yet</div>');
                    return;
                }
                $body.empty();
                res.sessions.forEach(function (s) {
                    var item = $('<div class="ai-chat-history-item">')
                        .attr('data-id', s.session_id)
                        .addClass(state.sessionId && state.sessionId === s.session_id ? 'active' : '')
                        .append('<span class="ai-chat-history-title">' + esc(decodeEntities(s.title)) + '</span>')
                        .append('<span class="ai-chat-history-time">' + timeAgo(s.updated_at) + '</span>')
                        .append('<i class="fa fa-trash ai-chat-history-del" title="Delete conversation" data-id="' + esc(s.session_id) + '"></i>');
                    $body.append(item);
                });
            },
            error: function () {
                $body.html('<div class="ai-chat-history-empty">Failed to load history</div>');
            }
        });
    }

    function loadSession(id) {
        hideHistory();
        $.ajax({
            url: baseUrl + 'admin/ai/load_session/' + encodeURIComponent(id),
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (!res || !res.success) { return; }
                state.sessionId = res.session_id;
                $('#ai_chat_messages').empty();
                var msgs = res.messages || [];
                msgs.forEach(function (m) {
                    renderMessage(m.role === 'user' ? 'user' : 'assistant', nl2br(m.content || ''), fmtTime(m.created_at));
                });
                if (!msgs.length) { showWelcome(); }
            }
        });
    }

    function loadSession(id) {
        hideHistory();
        $.ajax({
            url: baseUrl + 'admin/ai/load_session/' + encodeURIComponent(id),
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (!res || !res.success) { return; }
                state.sessionId = res.session_id;
                $('#ai_chat_messages').empty();
                var msgs = res.messages || [];
                msgs.forEach(function (m) {
                    renderMessage(m.role === 'user' ? 'user' : 'assistant', nl2br(m.content || ''), fmtTime(m.created_at));
                });
                if (!msgs.length) { showWelcome(); }
            }
        });
    }

    function deleteSession(id) {
        var ok = window.confirm((cfg().strings && cfg().strings.confirmDelete) || 'Delete this conversation and all its messages? This cannot be undone.');
        if (!ok) { return; }
        $.ajax({
            url: baseUrl + 'admin/ai/delete_session/' + encodeURIComponent(id),
            type: 'POST',
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    if (state.sessionId === id) {
                        state.sessionId = null;
                        $('#ai_chat_messages').empty();
                        showWelcome();
                    }
                    loadHistory();
                }
            },
            error: function () {
                renderError('Failed to delete session. Please try again.');
            }
        });
    }

    $(function () {
        $(document).on('click', '#ai_start_chat', function (e) {
            e.preventDefault();
            open();
        });

        $(document).on('click', '[data-ai-action]', function (e) {
            e.preventDefault();
            var act = $(this).data('ai-action');
            if (act === 'new') { newChat(); }
            else if (act === 'history') { toggleHistory(); }
            else if (act === 'close') { close(); }
            else if (act === 'maximize') { toggleMaximize(); }
        });

        $(document).on('click', '#ai_history_close', function (e) {
            e.preventDefault();
            hideHistory();
        });

        $(document).on('click', '#open_chat_list', function () {
            if (state.open) { close(); }
        });

        $(document).on('keydown', '#ai_chat_input', function (e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                e.preventDefault();
                send();
            }
        });

        $(document).on('click', '.ai-chat-history-item', function (e) {
            if ($(e.target).closest('.ai-chat-history-del').length) { return; }
            loadSession($(this).data('id'));
        });

        $(document).on('click', '.ai-chat-history-del', function (e) {
            e.stopPropagation();
            deleteSession($(this).data('id'));
        });
    });

})(jQuery);
