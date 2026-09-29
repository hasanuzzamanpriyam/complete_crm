<style>

    .menu-border-transparent {
        border-color: transparent !important;
        height: 40px;
        color: #a9a3a3;
        background-color: rgba(255, 255, 255, .1);
        /*width: 100%;*/
    }

    input[type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: searchfield-cancel-button;
    }
    .inner-addon {
        position: relative;
    }
    .left-addon .fa {
        left: 0px;
    }
    .inner-addon .fa {
        position: absolute;
        pointer-events: none;
        padding: 13px;
    }
    .left-addon input {
        padding-left: 30px;
    }


</style>
<aside class="aside">
    <!-- START Sidebar (left)-->
    <div class="aside-inner">
        <nav data-sidebar-anyclick-close="" class="sidebar <?= config_item('show-scrollbar') ?>">
            <!-- START sidebar brand: tile + site name, as the reference shell -->
            <div class="side-brand">
                <?php
                $brand_name = trim((string) config_item('website_name'));
                if ($brand_name === '') {
                    $brand_name = trim((string) config_item('company_name'));
                }
                $brand_initial = $brand_name !== '' ? strtoupper(substr($brand_name, 0, 1)) : 'A';
                $brand_logo_rel = trim((string) config_item('company_logo'));
                $brand_logo_url = '';
                if ($brand_logo_rel !== '' && file_exists(ROOTPATH . '/' . $brand_logo_rel)) {
                    $brand_logo_url = base_url($brand_logo_rel);
                }
                ?>
                <span class="tile"><?= htmlspecialchars($brand_initial) ?></span>
                <a href="<?= base_url('admin/dashboard') ?>" class="name">
                    <?php if ($brand_logo_url !== '') { ?>
                        <img src="<?= $brand_logo_url ?>" alt="<?= htmlspecialchars($brand_name !== '' ? $brand_name : 'App') ?>">
                    <?php } else { ?>
                        <?= htmlspecialchars($brand_name !== '' ? $brand_name : 'App') ?>
                    <?php } ?>
                </a>
            </div>
            <!-- END sidebar brand -->
            <!-- START sidebar nav-->
            <div class="inner-addon left-addon">
                <i class="fa fa-search"></i>
                <input type="search" id="s-menu" class="form-control menu-border-transparent" placeholder="<?= lang('search_menu') ?>"/>
            </div>

            <ul class="nav pinned" id="nav_pinned_cont">
            </ul>
            <ul class="nav pinned" id="nav_pinned_cont_2">
            </ul>

            <?php
            echo $this->menu->dynamicMenu();
            if ($this->session->userdata('user_type') == 1) { ?>
                <ul class="nav">
                    <li>
                        <a title="Expense Schedules" href="<?= base_url('admin/expenses') ?>">
                            <em class="fa fa-list-ul"></em>
                            <span>Expense Schedules</span>
                        </a>
                    </li>

                    <li>
                        <a title="API Routes" href="<?= base_url('admin/api-routes') ?>">
                            <em class="fa fa-list-alt"></em>
                            <span>API Routes</span>
                        </a>
                    </li>
                    <li>
                        <a title="API Documentation" href="<?= base_url('admin/api-doc') ?>">
                            <em class="fa fa-book"></em>
                            <span>API Documentation</span>
                        </a>
                    </li>
                </ul>
            <?php } ?>
            <script>
                $(document).ready(function () {  ins_data(base_url+'admin/dashboard/pinned_menu_items')   });
            </script>
            <!-- END sidebar nav-->
        </nav>
    </div>
    <!-- END Sidebar (left)-->
</aside>
