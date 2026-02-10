<style>
    .c-signature {
        border: 1px solid #dde6e9;
    }

    #signatureInput1 {
        width: 1px !important;
        height: 1px !important;
        border: 0px !important;
    }
</style>
<?php
echo message_box('success');
echo message_box('error');
$created = can_action_by_label('128', 'created');
$edited = can_action_by_label('128', 'edited');
?>
<div class="panel panel-custom">
    <header class="panel-heading ">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                    class="sr-only">Close</span></button>
        <?= lang('signature_and_identity') ?>
    </header>
    <form method="post" role="form" id="lead_sources"
          action="<?= base_url() ?>contracts/contract/sign_contract/<?= $id ?>" class="form-horizontal"
          data-parsley-validate="" novalidate="">
        <div class="form-group">
            <label class="col-sm-3 control-label"><?= lang('first_name') ?></label>
            <div class="col-sm-7">
                <input type="text" name="first_name" class="form-control" placeholder="<?= lang('first_name') ?>"
                       required>
            </div>
        </div>
        
        <div class="form-group">
            <label class="col-sm-3 control-label"><?= lang('last_name') ?></label>
            <div class="col-sm-7">
                <input type="text" name="last_name" class="form-control" placeholder="<?= lang('last_name') ?>">
            </div>
        </div>
        
        
        <div class="form-group">
            <label class="col-sm-3 control-label"><?= lang('email') ?></label>
            <div class="col-sm-7">
                <input type="text" name="email" class="form-control" placeholder="<?= lang('email') ?>" required>
            </div>
        </div>
        
        
        <div class="form-group row">
            
            <label class="col-form-label col-sm-3"><strong><?= lang('Customer') . ' ' . lang('signature') ?>
                    
                    :</strong></label>
            
            <div class="col-sm-7" id="customer_signature">
                
                <div class="signature-pad--body c-signature">
                    
                    <canvas class="signature" height="100"></canvas>
                
                </div>
                
                <input type="text" tabindex="-1" name="signature"
                       id="signatureInput1">
                
                <div class="dispay-block">
                    
                    <button type="button" data-action="clear" class="btn btn-default btn-xs clear" tabindex="-1"
                            id="clear"><?php echo lang('clear'); ?></button>
                    
                    <button type="button" data-action="undo" class="btn btn-default btn-xs" tabindex="-1"
                            id="undo"><?php echo lang('undo'); ?></button>
                    
                    <button type="submit" class="btn btn-default btn-xs update"
                            data-id="customer_signature"><?php echo lang('update'); ?></button>
                
                </div>
            
            
            </div>
        
        </div>
        
        
        <div class="form-group mt">
            <label class="col-lg-3"></label>
            <div class="col-lg-3">
                <button type="submit" class="btn btn-sm btn-primary"><?= lang('save') ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= lang('close') ?></button>
            </div>
        </div>
    </form>

</div>

<script src="<?= module_dirURL(CONTRACTS_MODULE, 'assets/plugins/signature-pad/signature_pad.min.js') ?>"></script>

<script>
    (function ($) {
        "use strict";

        SignaturePad.prototype.toDataURLAndRemoveBlanks = function () {

            var canvas = this._ctx.canvas;

            // First duplicate the canvas to not alter the original

            var croppedCanvas = document.createElement('canvas'),

                croppedCtx = croppedCanvas.getContext('2d');


            croppedCanvas.width = canvas.width;

            croppedCanvas.height = canvas.height;

            croppedCtx.drawImage(canvas, 0, 0);


            // Next do the actual cropping

            var w = croppedCanvas.width,

                h = croppedCanvas.height,

                pix = {

                    x: [],

                    y: []

                },

                imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),

                x, y, index;


            for (y = 0; y < h; y++) {

                for (x = 0; x < w; x++) {

                    index = (y * w + x) * 4;

                    if (imageData.data[index + 3] > 0) {

                        pix.x.push(x);

                        pix.y.push(y);


                    }

                }

            }

            pix.x.sort(function (a, b) {

                return a - b

            });

            pix.y.sort(function (a, b) {

                return a - b

            });

            var n = pix.x.length - 1;


            w = pix.x[n] - pix.x[0];

            h = pix.y[n] - pix.y[0];

            var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);


            croppedCanvas.width = w;

            croppedCanvas.height = h;

            croppedCtx.putImageData(cut, 0, 0);


            return croppedCanvas.toDataURL();

        };


        function signaturePad1Changed() {


            var input = document.getElementById('signatureInput1');

            var $signatureLabel1 = $('#signatureLabel1');

            $signatureLabel1.removeClass('text-danger');


            if (signaturePad1.isEmpty()) {

                $signatureLabel1.addClass('text-danger');

                input.value = '';

                return false;

            }


            $('#signatureInput-error1').remove();

            var partBase64 = signaturePad1.toDataURLAndRemoveBlanks();

            partBase64 = partBase64.split(',')[1];

            input.value = partBase64;

        }


        var wrapper1 = document.getElementById("customer_signature"),

            canvas1 = wrapper1.querySelector("canvas"),

            signaturePad1;

        var clearButton1 = wrapper1.querySelector("[data-action=clear]");

        var undoButton1 = wrapper1.querySelector("[data-action=undo]");


        if (canvas1) {

            signaturePad1 = new SignaturePad(canvas1, {

                maxWidth: 2,

                onEnd: function () {

                    signaturePad1Changed();

                }

            });

            clearButton1.addEventListener("click", function (event) {

                signaturePad1.clear();

                signaturePad1Changed();

            });

            undoButton1.addEventListener("click", function (event) {

                var data = signaturePad1.toData();

                if (data) {

                    data.pop(); // remove the last dot or line

                    signaturePad1.fromData(data);

                    signaturePad1Changed();

                }

            });


        }

    })(jQuery);
</script>


<script type="text/javascript">
    $(document).on("submit", "form", function (event) {
        event.preventDefault();
        var form = $(event.target);
        var id = form.attr('id');
        $.ajax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        }).done(function (res) {
            res = JSON.parse(res);
            if (res.status == 'success') {
                toastr[res.status](res.message);
                window.location.reload(true);
            }
            toastr[res.status](res.message);
            $('#myModal').modal('hide');
        }).fail(function () {
            alert('There was a problem with AJAX');
        });
    });
</script>