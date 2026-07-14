(function () {
    'use strict';

    var SPA = {
        contentSelector: '.col-lg-12',
        timesyncPath: 'admin/timesync',
    };

    window.__spaNavigate = function (url) {
        SPA.navigate(url);
    };

    function isTimesyncUrl(url) {
        return url.indexOf('admin/timesync') !== -1;
    }

    function buildAjaxUrl(url) {
        var sep = url.indexOf('?') !== -1 ? '&' : '?';
        return url + sep + 'ajax=1';
    }

    SPA.load = function (url, pushState) {
        if (pushState === undefined) pushState = true;

        var $target = $(SPA.contentSelector);
        if (!$target.length) {
            window.location.href = url;
            return;
        }

        $target.addClass('spa-loading');

        var ajaxUrl = buildAjaxUrl(url);

        fetch(ajaxUrl)
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (pushState && url !== window.location.href) {
                    history.pushState({ url: url, title: data.title || '' }, '', url);
                }
                if (data.title) {
                    document.title = data.title;
                }
                cleanupWidgets();
                replaceContent($target, data.html || '');
                updateActiveSidebar(url);
                $target.removeClass('spa-loading');
            })
            .catch(function () {
                window.location.href = url;
            });
    };

    SPA.navigate = function (url) {
        this.load(url, true);
    };

    function cleanupWidgets() {
        if (window.$.fn.DataTable) {
            $('.DataTables').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
            });
        }
        if (window.$.fn.select2) {
            $('[class*="select"]').each(function () {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
        }
        if (window.$.fn.datepicker) {
            $('.datepicker').each(function () {
                if ($(this).data('datepicker')) {
                    $(this).datepicker('destroy');
                }
            });
        }
    }

    function replaceContent($target, html) {
        var scripts = [];
        var cleaned = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, function (match) {
            scripts.push(match);
            return '';
        });
        $target.empty().append(cleaned);
        executeScripts(scripts);
    }

    function executeScripts(scripts) {
        scripts.forEach(function (scriptHtml) {
            var srcMatch = scriptHtml.match(/src\s*=\s*["']([^"']+)["']/);
            if (srcMatch) {
                var s = document.createElement('script');
                s.src = srcMatch[1];
                s.async = false;
                document.body.appendChild(s);
                return;
            }
            var contentMatch = scriptHtml.match(/<script[^>]*>([\s\S]*?)<\/script>/i);
            if (contentMatch && contentMatch[1].trim()) {
                try {
                    $.globalEval(contentMatch[1]);
                } catch (e) { }
            }
        });
    }

    function updateActiveSidebar(url) {
        $('ul#timesync li').removeClass('active');
        var $best = null;
        var bestLen = 0;
        $('ul#timesync a').each(function () {
            var href = $(this).attr('href');
            if (!href || href.indexOf('#') !== -1) return;
            if (url.indexOf(href) !== -1 && href.length > bestLen) {
                bestLen = href.length;
                $best = $(this);
            }
        });
        if ($best) {
            $best.closest('li').addClass('active');
        }
    }

    function interceptSidebarLinks() {
        $('ul#timesync').on('click', 'a', function (e) {
            var href = $(this).attr('href');
            if (!href || href === '#' || href.indexOf('#') !== -1) return;
            if (href.indexOf(SPA.timesyncPath) === -1) return;
            e.preventDefault();
            SPA.navigate(href.indexOf('http') === 0 ? href : window.location.origin + '/' + href.replace(/^\//, ''));
        });
    }

    function interceptContentLinks() {
        $(SPA.contentSelector).on('click', 'a', function (e) {
            if (e.isDefaultPrevented()) return;
            var href = $(this).attr('href');
            if (!href || href === '#' || href.indexOf('#') !== -1) return;
            if ($(this).attr('target') === '_blank') return;
            if ($(this).attr('data-toggle') === 'modal') return;
            if (href.indexOf(SPA.timesyncPath) === -1) return;
            if (href.indexOf('logout') !== -1 || href.indexOf('download') !== -1) return;
            e.preventDefault();
            SPA.navigate(href.indexOf('http') === 0 ? href : window.location.origin + '/' + href.replace(/^\//, ''));
        });
    }

    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.url) {
            SPA.load(e.state.url, false);
        }
    });

    $(function () {
        if (!isTimesyncUrl(window.location.href)) return;
        history.replaceState({ url: window.location.href, title: document.title }, '');
        interceptSidebarLinks();
        interceptContentLinks();
    });

})();
