<h4><?= lang('remote_mails') ?></h4>
<?php foreach ($gmailBoxes as $k => $f) { ?>
<li class="<?php echo ($menu_active == $k) ? 'active' : ''; ?>">
    <?php if ($k == '#') {
        echo $f['main'];
    } else { ?>
        <a href="<?= base_url() ?>admin/mailbox/index/<?= $k ?>"> <i class="fa  text-yellow"></i>
            <?= $f['main'] ?>
            <span class="label label-primary pull-right"><?php
                if (isset($mail_count[$k])) {
                    echo $mail_count[$k];
                } else {
                    echo '0';
                }
                ?></span>
        </a>
    <?php } ?>
    <?php if (isset($f['sub'])) { ?>
        <ul class="nav nav-pills nav-stacked pl-lg">
            <?php foreach ($f['sub'] as $k2 => $v2) {
                ?>
                <li class="<?php echo ($menu_active == $k2) ? 'active' : ''; ?>">
                    <a href="<?= base_url() ?>admin/mailbox/index/<?= $k2 ?>"> <i class="fa  text-yellow"></i>
                        <?= $v2 ?>
                        <span class="label label-primary pull-right"><?php
                            if (!empty($mail_count[$k2])) {
                                echo $mail_count[$k2];
                            } else {
                                echo '0';
                            }
                            ?></span>
                    </a>
                </li>
            
            <?php } ?>
        </ul>
        </li>
    <?php }
} ?>
