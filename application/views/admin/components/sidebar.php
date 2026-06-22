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
            <!-- START sidebar nav-->
            <div class="inner-addon left-addon" style="width: 95%">
                <i class="fa fa-search"></i>
                <input type="search" id="s-menu" class="form-control menu-border-transparent" placeholder="<?= lang('search_menu') ?>"/>
            </div>
            <br/>

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
