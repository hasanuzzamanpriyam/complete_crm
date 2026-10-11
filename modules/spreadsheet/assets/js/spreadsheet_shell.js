/**
 * Spreadsheet Design-A shell (presentation-side only).
 * All state derives from the server-rendered data-tt-* rows: zero network
 * behavior. Kebab dispatches a synthetic contextmenu so context_menu.js keeps
 * owning the menu (7/2 actions, position, dispatch) untouched.
 */
(function ($) {
    "use strict";

    var $panel = $('#ss-panel');
    var $table = $('#spreadsheet-advanced');
    if (!$panel.length || !$table.length || typeof window.ss_lang === 'undefined') {
        return;
    }

    var $crumb = $('#ss-crumb');
    var rootLabel = $crumb.attr('data-root-label') || 'Folder';
    var activeRoot = $panel.attr('data-active') || 'my';
    var currentId = '0';
    var nodes = {};
    var childIndex = {};
    var drillTimer = null;

    function esc(s) {
        return $('<div>').text(s == null ? '' : String(s)).html();
    }

    function collect() {
        nodes = {};
        childIndex = {};
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var id = String($tr.data('tt-id'));
            var parent = $tr.attr('data-tt-parent-id');
            parent = (parent === undefined || parent === null || parent === '') ? '0' : String(parent);
            nodes[id] = {
                id: id,
                parent: parent,
                type: String($tr.data('tt-type') || ''),
                name: $tr.attr('data-tt-name') || '',
                role: $tr.attr('data-tt-role') || '',
                $row: $tr
            };
            (childIndex[parent] = childIndex[parent] || []).push(id);
        });
    }

    function descendantCount(id) {
        var kids = childIndex[id] || [];
        var total = kids.length;
        for (var i = 0; i < kids.length; i++) {
            total += descendantCount(kids[i]);
        }
        return total;
    }

    function pathOf(id) {
        var path = [];
        var cur = id;
        while (cur && cur !== '0' && nodes[cur]) {
            path.unshift(cur);
            cur = nodes[cur].parent;
        }
        return path;
    }

    function applyFilter() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var parent = $tr.attr('data-tt-parent-id');
            parent = (parent === undefined || parent === null || parent === '') ? '0' : String(parent);
            if (parent === currentId) {
                $tr.removeClass('ss-hidden').css('display', '');
            } else {
                $tr.addClass('ss-hidden').css('display', 'none');
            }
        });
        window.ssCurrentFolder = currentId;
        renderCrumb();
        renderCounts();
        renderEmpty();
        var $sub = $panel.find('.ss-subtree[data-subtree="' + activeRoot + '"]');
        $sub.find('.ss-tf').removeClass('on');
        if (currentId !== '0') {
            $sub.find('[data-drill="' + currentId + '"]').addClass('on');
        }
    }
    window.ssApplyFilter = applyFilter;

    function drill(id) {
        if (id !== '0' && !nodes[id]) {
            return;
        }
        currentId = id;
        applyFilter();
    }

    function renderCrumb() {
        var html = '<b>' + esc(rootLabel) + '</b>';
        if (currentId !== '0' && nodes[currentId]) {
            var path = pathOf(currentId);
            for (var i = 0; i < path.length; i++) {
                var n = nodes[path[i]];
                var last = (i === path.length - 1);
                html += ' <span class="sep">/</span> ';
                html += last
                    ? '<span class="ss-cur">' + esc(n.name) + '</span>'
                    : '<a data-drill="' + n.id + '">' + esc(n.name) + '</a>';
            }
        }
        $crumb.html(html);
    }

    function renderCounts() {
        var total = 0, files = 0, folders = 0, shared = 0;
        for (var id in nodes) {
            if (!Object.prototype.hasOwnProperty.call(nodes, id)) { continue; }
            var n = nodes[id];
            total++;
            if (n.type === 'folder') { folders++; } else { files++; }
            if (n.role !== '' || n.$row.find('.share-status').length) { shared++; }
        }
        $panel.find('[data-ss-total]').text(total);
        $panel.find('.f-item.on [data-ss-count]').text(total);
        $panel.find('[data-ss-files]').text(files);
        $panel.find('[data-ss-folders]').text(folders);
        $panel.find('[data-ss-shared]').text(shared);
        var $chip = $('[data-ss-chip]');
        if ($chip.length) {
            $chip.text(total + ' ' + ss_lang.items);
        }
    }

    function renderEmpty() {
        var visible = 0;
        $table.find('tbody tr.right-menu-position').each(function () {
            if (!$(this).hasClass('ss-hidden')) { visible++; }
        });
        var $state = $table.next('.ss-empty');
        if (visible === 0) {
            if (!$state.length) {
                $state = $('<div class="empty ss-empty">' +
                    '<span class="big fa fa-folder-open-o"></span>' +
                    '<b>' + esc(ss_lang.empty_title) + '</b>' +
                    esc(ss_lang.empty_hint) + '</div>');
                $table.after($state);
            }
            $table.addClass('ss-hidden-table').hide();
            $state.show();
        } else {
            $state.hide();
            $table.show();
        }
    }

    function subtreeHtml(ids) {
        var html = '<ul>';
        for (var i = 0; i < ids.length; i++) {
            var n = nodes[ids[i]];
            var kids = (childIndex[n.id] || []).filter(function (k) {
                return nodes[k] && nodes[k].type === 'folder';
            });
            var count = descendantCount(n.id);
            html += '<li>' +
                '<div class="ss-tf" data-drill="' + n.id + '">' +
                (kids.length
                    ? '<span class="ss-caret fa fa-caret-right" data-caret="' + n.id + '"></span>'
                    : '<span class="ss-caret"></span>') +
                '<span>' + esc(n.name) + '</span>' +
                (count ? '<span class="cnt">' + count + '</span>' : '') +
                '</div>' +
                (kids.length
                    ? '<div class="ss-kids ss-collapsed" data-kids="' + n.id + '">' + subtreeHtml(kids) + '</div>'
                    : '') +
                '</li>';
        }
        return html + '</ul>';
    }

    function renderSubtree() {
        var $sub = $panel.find('.ss-subtree[data-subtree="' + activeRoot + '"]');
        $sub.empty();
        var roots = childIndex['0'] || [];
        var folders = [];
        for (var i = 0; i < roots.length; i++) {
            if (nodes[roots[i]] && nodes[roots[i]].type === 'folder') {
                folders.push(roots[i]);
            }
        }
        if (folders.length) {
            $sub.html(subtreeHtml(folders));
        }
    }

    function appendSublines() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $tr = $(this);
            var n = nodes[String($tr.data('tt-id'))];
            if (!n) { return; }
            var text = '';
            if (n.type === 'folder') {
                text = ss_lang.folder + ' · ' + descendantCount(n.id) + ' ' + ss_lang.items;
            } else if (n.role !== '') {
                text = (String(n.role) === '2') ? ss_lang.edit : ss_lang.view;
            }
            if (text) {
                var $span = $tr.find('.tr-pointer').first();
                if ($span.length && !$span.find('.ss-sub').length) {
                    $span.append('<span class="ss-sub">' + esc(text) + '</span>');
                }
            }
        });
    }

    function appendKebab() {
        $table.find('tbody tr.right-menu-position').each(function () {
            var $td = $(this).children('td').first();
            if ($td.length && !$td.find('.ss-kebab').length) {
                $td.append('<button type="button" class="kebab ss-kebab" title="Menu"><i class="fa fa-ellipsis-v"></i></button>');
            }
        });
    }

    /* kebab -> synthetic contextmenu (context_menu.js owns the menu; its
       document click listener runs first — registered earlier — closing any
       stale menu before this opens a fresh one at the pointer). */
    $(document).on('click', '.ss-kebab', function (e) {
        e.preventDefault();
        this.dispatchEvent(new MouseEvent('contextmenu', {
            bubbles: true, cancelable: true, view: window,
            clientX: e.clientX, clientY: e.clientY, button: 2
        }));
    });

    /* folder name click = drill, debounced so double-click (rename) wins */
    $(document).on('click', '#spreadsheet-advanced .tr-pointer.folder', function () {
        var id = String($(this).closest('tr').data('tt-id'));
        window.clearTimeout(drillTimer);
        drillTimer = window.setTimeout(function () { drill(id); }, 260);
    });
    $(document).on('dblclick', '#spreadsheet-advanced .tr-pointer', function () {
        window.clearTimeout(drillTimer);
    });

    /* crumb segments + panel subtree navigate client-side */
    $(document).on('click', '[data-drill]', function (e) {
        if ($(e.target).closest('.ss-caret').length) { return; }
        e.preventDefault();
        window.clearTimeout(drillTimer);
        drill(String($(this).attr('data-drill')));
    });

    /* panel carets expand/collapse the subtree (replaces caption buttons) */
    $(document).on('click', '.ss-caret[data-caret]', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var id = $(this).attr('data-caret');
        $('[data-kids="' + id + '"]').toggleClass('ss-collapsed');
        $(this).toggleClass('fa-caret-right fa-caret-down');
    });

    $(function () {
        collect();
        renderSubtree();
        appendSublines();
        appendKebab();
        applyFilter();
    });
})(jQuery);
