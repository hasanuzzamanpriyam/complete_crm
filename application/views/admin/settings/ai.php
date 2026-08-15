<?php echo message_box('success'); ?>
<?php
$all_categories = array('General', 'HR', 'Sales', 'Marketing', 'Support', 'Development');
if (!empty($categories)) {
    foreach ($categories as $category) {
        if (!in_array($category, $all_categories)) {
            $all_categories[] = $category;
        }
    }
}
?>

<!-- ============================ Global Enable ============================ -->
<form role="form"
      action="<?php echo base_url(); ?>admin/ai/save_settings"
      method="post"
      class="form-horizontal">
    <section class="panel panel-custom">
        <header class="panel-heading">
            <?= lang('ai_assistant_status') ?>
            <div class="pull-right">
                <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-save"></i> <?= lang('save') ?></button>
            </div>
        </header>
        <div class="panel-body">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('ai_assistant_enabled') ?></label>
                <div class="col-lg-9">
                    <label class="checkbox-inline c-checkbox">
                        <input type="checkbox" name="ai_enabled" value="1" <?= (!empty($ai_enabled) ? 'checked' : '') ?>>
                        <span class="fa fa-check"></span> <?= lang('ai_enable_hint') ?>
                    </label>
                </div>
            </div>
        </div>
    </section>
</form>

<!-- ============================ Providers ============================ -->
<section class="panel panel-custom">
    <header class="panel-heading">
        <?= lang('ai_providers') ?>
        <span class="text-muted pull-right"><?= lang('ai_providers_hint') ?></span>
    </header>
    <div class="panel-body">

        <?php if (!empty($providers)) {
            foreach ($providers as $provider) { ?>
                <form role="form"
                      action="<?php echo base_url(); ?>admin/ai/save_provider/<?= $provider->id ?>"
                      method="post"
                      class="form-horizontal ai-provider-form"
                      data-provider-code="<?= $provider->provider_code ?>">
                    <div class="panel panel-default ai-provider-card">
                        <div class="panel-heading">
                            <strong><i class="fa fa-plug"></i> <?= $provider->provider_name ?></strong>
                            <div class="pull-right">
                                <span class="label <?= (!empty($provider->is_default) ? 'label-success' : 'label-default') ?>"><?= lang('ai_default') ?></span>
                                <span class="label <?= (!empty($provider->is_active) ? 'label-primary' : 'label-warning') ?>"><?= (!empty($provider->is_active) ? lang('active') : lang('ai_inactive')) ?></span>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_api_key') ?></label>
                                <div class="col-lg-7">
                                    <div class="input-group">
                                        <input type="password" name="api_key" value="" autocomplete="new-password"
                                               placeholder="<?= (!empty($provider->api_key) ? lang('ai_key_set_placeholder') : lang('ai_enter_api_key')) ?>"
                                               class="form-control">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-info ai-test-conn" data-provider-code="<?= $provider->provider_code ?>">
                                                <i class="fa fa-plug"></i> <?= lang('ai_test_connection') ?>
                                            </button>
                                        </span>
                                    </div>
                                    <?php if (!empty($provider->api_key)) { ?>
                                        <span class="help-block"><i class="fa fa-lock"></i> <?= lang('ai_key_stored') ?></span>
                                    <?php } ?>
                                    <div class="ai-test-result hidden"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_default_model') ?></label>
                                <div class="col-lg-7">
                                    <input type="text" name="default_model" value="<?= $provider->default_model ?>"
                                           class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_available_models') ?></label>
                                <div class="col-lg-7">
                                    <textarea name="available_models" rows="4" class="form-control"
                                              placeholder="model-one&#10;model-two"><?= htmlentities(implode("\n", (array) json_decode($provider->available_models, true)), ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_api_endpoint') ?></label>
                                <div class="col-lg-7">
                                    <input type="text" name="api_endpoint" value="<?= $provider->api_endpoint ?>"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_max_tokens') ?></label>
                                <div class="col-lg-3">
                                    <input type="number" name="max_tokens" min="16" step="16" value="<?= (int) $provider->max_tokens ?>"
                                           class="form-control">
                                </div>
                                <label class="col-lg-3 control-label"><?= lang('ai_temperature') ?></label>
                                <div class="col-lg-3">
                                    <input type="number" name="temperature" min="0" max="2" step="0.1" value="<?= (float) $provider->temperature ?>"
                                           class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label"><?= lang('ai_status') ?></label>
                                <div class="col-lg-9">
                                    <label class="checkbox-inline c-checkbox">
                                        <input type="checkbox" name="is_active" value="1" <?= (!empty($provider->is_active) ? 'checked' : '') ?>>
                                        <span class="fa fa-check"></span> <?= lang('active') ?>
                                    </label>
                                    <label class="radio-inline c-radio">
                                        <input type="radio" name="is_default" value="1" <?= (!empty($provider->is_default) ? 'checked' : '') ?>>
                                        <span class="fa fa-circle-o"></span> <?= lang('ai_set_default') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-3 col-lg-9">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= lang('save') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php }
        } ?>
    </div>
</section>

<!-- ============================ Prompt Templates ============================ -->
<section class="panel panel-custom">
    <header class="panel-heading"><?= lang('ai_prompt_templates') ?></header>
    <div class="panel-body">
        <div class="alert alert-info"><?= lang('ai_prompts_hint') ?></div>

        <form role="form" class="form-horizontal ai-prompt-form">
            <input type="hidden" name="prompt_id" value="">
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('ai_category') ?></label>
                <div class="col-lg-4">
                    <select name="category" class="form-control">
                        <?php foreach ($all_categories as $category) { ?>
                            <option value="<?= $category ?>"><?= $category ?></option>
                        <?php } ?>
                    </select>
                </div>
                <label class="col-lg-2 control-label"><?= lang('ai_icon') ?></label>
                <div class="col-lg-3">
                    <input type="text" name="icon" value="fa fa-magic" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('ai_title') ?></label>
                <div class="col-lg-9">
                    <input type="text" name="title" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label"><?= lang('ai_prompt_template') ?></label>
                <div class="col-lg-9">
                    <textarea name="prompt_template" rows="3" class="form-control" required></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <button type="submit" class="btn btn-success ai-prompt-save"><i class="fa fa-save"></i> <?= lang('save') ?></button>
                    <button type="button" class="btn btn-default hidden ai-prompt-cancel"><?= lang('ai_cancel_edit') ?></button>
                </div>
            </div>
        </form>

        <?php if (!empty($prompts)) {
            $grouped = array();
            foreach ($prompts as $prompt) {
                $grouped[$prompt->category][] = $prompt;
            }
            foreach ($grouped as $category => $items) { ?>
                <h5 class="text-uppercase"><i class="fa fa-folder-open-o"></i> <?= $category ?></h5>
                <table class="table table-striped table-hover">
                    <tbody>
                    <?php foreach ($items as $prompt) { ?>
                        <tr data-prompt-id="<?= $prompt->prompt_id ?>"
                            data-title="<?= htmlentities($prompt->title, ENT_QUOTES, 'UTF-8') ?>"
                            data-template="<?= htmlentities($prompt->prompt_template, ENT_QUOTES, 'UTF-8') ?>"
                            data-category="<?= $prompt->category ?>"
                            data-icon="<?= $prompt->icon ?>">
                            <td><i class="<?= $prompt->icon ?>"></i> <strong><?= $prompt->title ?></strong></td>
                            <td class="text-muted"><?= mb_substr($prompt->prompt_template, 0, 90) ?>...</td>
                            <td class="text-right" width="120">
                                <button type="button" class="btn btn-xs btn-info ai-prompt-edit" title="<?= lang('edit') ?>">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-xs btn-danger ai-prompt-delete" title="<?= lang('delete') ?>">
                                    <i class="fa fa-trash-o"></i>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php }
        } ?>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function () {
        var base = '<?= base_url() ?>';

        $('.ai-test-conn').on('click', function () {
            var btn = $(this);
            var form = btn.closest('.ai-provider-form');
            var box = form.find('.ai-test-result');
            btn.prop('disabled', true).find('i').attr('class', 'fa fa-spinner fa-spin');
            box.removeClass('hidden alert-success alert-danger').addClass('alert').text('<?= lang('please_wait') ?>...');
            $.ajax({
                url: base + 'admin/ai/test_connection',
                type: 'POST',
                dataType: 'json',
                data: {
                    provider_code: form.data('provider-code'),
                    api_key: form.find('input[name=api_key]').val(),
                    model: form.find('input[name=default_model]').val()
                },
                success: function (res) {
                    btn.prop('disabled', false).find('i').attr('class', 'fa fa-plug');
                    box.removeClass('hidden');
                    if (res.success) {
                        box.removeClass('alert-danger').addClass('alert-success').text(res.message);
                    } else {
                        box.removeClass('alert-success').addClass('alert-danger').text(res.message);
                    }
                },
                error: function () {
                    btn.prop('disabled', false).find('i').attr('class', 'fa fa-plug');
                    box.removeClass('hidden').addClass('alert-danger').text('<?= lang('ai_error_request_failed') ?>');
                }
            });
        });

        $('.ai-prompt-edit').on('click', function () {
            var row = $(this).closest('tr');
            var form = $('.ai-prompt-form');
            form.find('input[name=prompt_id]').val(row.data('prompt-id'));
            form.find('select[name=category]').val(row.data('category'));
            form.find('input[name=icon]').val(row.data('icon'));
            form.find('input[name=title]').val(row.data('title'));
            form.find('textarea[name=prompt_template]').val(row.data('template'));
            $('.ai-prompt-cancel').removeClass('hidden');
            window.scrollTo(0, form.offset().top - 80);
        });

        $('.ai-prompt-cancel').on('click', function () {
            var form = $('.ai-prompt-form');
            form.find('input[name=prompt_id]').val('');
            form.find('input[name=title]').val('');
            form.find('textarea[name=prompt_template]').val('');
            form.find('select[name=category]').val(form.find('select[name=category] option:first').val());
            form.find('input[name=icon]').val('fa fa-magic');
            $(this).addClass('hidden');
        });

        $('.ai-prompt-form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: base + 'admin/ai/save_prompt',
                type: 'POST',
                dataType: 'json',
                data: form.serialize(),
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(function () {
                            location.reload();
                        }, 800);
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function () {
                    toastr.error('<?= lang('ai_error_request_failed') ?>');
                }
            });
        });

        $('.ai-prompt-delete').on('click', function () {
            var row = $(this).closest('tr');
            var id = row.data('prompt-id');
            if (!confirm('<?= lang('ai_confirm_delete') ?>')) {
                return;
            }
            $.ajax({
                url: base + 'admin/ai/delete_prompt/' + id,
                type: 'POST',
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        row.fadeOut();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function () {
                    toastr.error('<?= lang('ai_error_request_failed') ?>');
                }
            });
        });
    });
</script>
