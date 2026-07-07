(function () {
    "use strict";
    $('#file-link').on('click', function (e) {
        e.preventDefault();
        $('#Luckyexcel-demo-file').trigger('click');
    });
})(jQuery);
