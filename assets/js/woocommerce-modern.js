/* WooCommerce modern list toolbar — companion to woocommerce_modern.css.
   Wires the .list-toolbar controls to the DataTables API created by
   fire_datatable() (buttons array order: 0 print, 1 print-selected, 2 excel,
   3 csv, 4 pdf, 5 custom-bulk-button). */
var WOO = { status: '', store: '', table: null };

WOO.bind = function (api) {
    WOO.table = api;

    var btnMap = {
        'woo-print': 0,
        'woo-sel': 1,
        'woo-xls': 2,
        'woo-csv': 3,
        'woo-pdf': 4,
        'woo-del': 5
    };
    Object.keys(btnMap).forEach(function (cls) {
        $('.' + cls).off('click.woo').on('click.woo', function (e) {
            e.preventDefault();
            var node = api.button(btnMap[cls]).node();
            if (node) {
                node.click(); // native click: app.js bulk handler reads window.event
            }
        });
    });

    var timer;
    $('.woo-q').off('input.woo').on('input.woo', function () {
        var value = this.value;
        clearTimeout(timer);
        timer = setTimeout(function () { api.search(value).draw(); }, 300);
    }).off('keydown.woo').on('keydown.woo', function (e) {
        if (e.key === 'Escape') {
            this.value = '';
            api.search('').draw();
        }
    });

    $('.woo-dd').each(function () {
        var wrap = $(this);
        var key = wrap.data('key'); // status | store
        wrap.find('.filter-pill').off('click.woo').on('click.woo', function (e) {
            e.stopPropagation();
            var dd = wrap.find('.dd');
            $('.dd.open').not(dd).removeClass('open');
            dd.toggleClass('open');
        });
        wrap.find('.dd-item').off('click.woo').on('click.woo', function () {
            var item = $(this);
            wrap.find('.dd-item').removeClass('on').find('.ck').html('');
            item.addClass('on').find('.ck').html('&#10003;');
            wrap.find('.pill-label').text(item.data('label'));
            WOO[key] = item.data('v') || '';
            wrap.find('.dd').removeClass('open');
            api.ajax.reload();
        });
    });

    $(document).off('click.wooClose').on('click.wooClose', function () {
        $('.dd.open').removeClass('open');
    });
};
