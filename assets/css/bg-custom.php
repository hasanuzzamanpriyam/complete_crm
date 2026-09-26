<?php
$uri = $this->uri->segment(3);
if ($uri == 'theme') {
    $im = null;
} else {
    $im = '!important';
}

/*
 * Emit every configurable colour as a custom property ONCE, in :root, instead of
 * repeating the value in a dozen separate rules. Rules then consume var(--...).
 * This is what makes live theming work: one assignment restyles the whole app.
 */
$vars = [
    '--c-bg'                  => 'body_background',
    '--th-navbar-logo-bg'     => 'navbar_logo_background',
    '--c-topbar-bg'           => 'top_bar_background',
    '--c-topbar-fg'           => 'top_bar_color',
    '--c-sidebar-bg'          => 'sidebar_background',
    '--c-sidebar-text'        => 'sidebar_color',
    '--c-sidebar-active'      => 'sidebar_active_background',
    '--th-sidebar-active-txt' => 'sidebar_active_color',
    '--th-submenu-open-bg'    => 'submenu_open_background',
    '--th-active-bg'          => 'active_background',
    '--th-active-fg'          => 'active_color',
];
$css = ":root {\n";
foreach ($vars as $name => $key) {
    $css .= "        $name: " . config_item($key) . ";\n";
}
$css .= "    }\n";
?>
<style type="text/css">
    <?= $css ?>
    /* ========================================================================
       Component: layout
     ========================================================================== */
    body,
    .wrapper > section {
        background-color: var(--c-bg)<?= $im ?>;
    }

    #loader-wrapper {
        background-color: var(--c-bg)<?= $im ?>;
    }

    .wrapper > .aside {
        background-color: #1e1e2d;
    }

    /* ========================================================================
       Component: top-navbar
     ========================================================================== */
    .topnavbar {
        background-color: #fff;
    }

    .topnavbar .navbar-header {
        background: var(--th-navbar-logo-bg)<?= $im ?>;
    }

    @media only screen and (min-width: 768px) {
        .topnavbar .navbar-header {
            background-image: none;
        }
    }

    .topnavbar .navbar-nav > li > a,
    .topnavbar .navbar-nav > .open > a {
        color: var(--c-topbar-fg)<?= $im ?>;
    }

    .topnavbar .navbar-nav > li > a:hover,
    .topnavbar .navbar-nav > .open > a:hover,
    .topnavbar .navbar-nav > li > a:focus,
    .topnavbar .navbar-nav > .open > a:focus {
        color: var(--c-topbar-fg)<?= $im ?>;
    }

    .topnavbar .navbar-nav > .active > a,
    .topnavbar .navbar-nav > .open > a,
    .topnavbar .navbar-nav > .active > a:hover,
    .topnavbar .navbar-nav > .open > a:hover,
    .topnavbar .navbar-nav > .active > a:focus,
    .topnavbar .navbar-nav > .open > a:focus {
        background-color: transparent;
    }

    .topnavbar .navbar-nav > li > [data-toggle='navbar-search'] {
        color: #ffffff;
    }

    .topnavbar .nav-wrapper {
        background: var(--c-topbar-bg)<?= $im ?>;
    }

    @media only screen and (min-width: 768px) {
        .topnavbar {
            background: var(--c-topbar-bg)<?= $im ?>;
        }

        .topnavbar .navbar-nav > .open > a,
        .topnavbar .navbar-nav > .open > a:hover,
        .topnavbar .navbar-nav > .open > a:focus {
            box-shadow: 0 -3px 0 rgba(255, 255, 255, 0.5) inset;
        }

        .topnavbar .navbar-nav > li > a,
        .topnavbar .navbar-nav > .open > a {
            color: var(--c-topbar-fg)<?= $im ?>;
        }

        .topnavbar .navbar-nav > li > a:hover,
        .topnavbar .navbar-nav > .open > a:hover,
        .topnavbar .navbar-nav > li > a:focus,
        .topnavbar .navbar-nav > .open > a:focus {
            color: var(--c-topbar-fg)<?= $im ?>;
        }
    }

    /* ========================================================================
       Component: sidebar
     ========================================================================== */
    .sidebar {
        background: var(--c-sidebar-bg)<?= $im ?>;
    }

    .sidebar .nav-heading {
        color: var(--c-sidebar-text) !important;
    }

    .sidebar .nav > li > a,
    .sidebar .nav > li > .nav-item, .user-block .user-block-info .user-block-name, .user-block .user-block-info .user-block-role {
        color: var(--c-sidebar-text)<?= $im ?>;
    }

    .sidebar .nav > li > a:focus,
    .sidebar .nav > li > .nav-item:focus,
    .sidebar .nav > li > a:hover,
    .sidebar .nav > li > .nav-item:hover {
        color: var(--c-sidebar-active)<?= $im ?>;
    }

    .sidebar .nav > li > a > em,
    .sidebar .nav > li > .nav-item > em {
        color: inherit;
    }

    .sidebar .nav > li.active,
    .sidebar .nav > li.open,
    .sidebar .nav > li.active > a,
    .sidebar .nav > li.open > a,
    .sidebar .nav > li.active .nav,
    .sidebar .nav > li.open .nav {
        background-color: var(--c-sidebar-active)<?= $im ?>;
        color: var(--th-sidebar-active-txt)<?= $im ?>;
    }

    .sidebar .nav > li.active > a > em,
    .sidebar .nav > li.open > a > em {
        color: var(--th-sidebar-active-txt)<?= $im ?>;
    }

    .sidebar .nav > li.active {
        border-left-color: var(--th-sidebar-active-txt)<?= $im ?>;
    }

    .sidebar-subnav {
        background-color: var(--th-submenu-open-bg)<?= $im ?>;
    }

    .sidebar-subnav > .sidebar-subnav-header {
        color: #888ea8;
    }

    .sidebar-subnav > li > a,
    .sidebar-subnav > li > .nav-item {
        color: #888ea8;
    }

    .sidebar-subnav > li > a:focus,
    .sidebar-subnav > li > .nav-item:focus,
    .sidebar-subnav > li > a:hover,
    .sidebar-subnav > li > .nav-item:hover {
        color: var(--th-sidebar-active-txt)<?= $im ?>;
    }

    .sidebar-subnav > li.active > a,
    .sidebar-subnav > li.active > .nav-item {
        color: var(--th-sidebar-active-txt)<?= $im ?>;
    }

    .sidebar-subnav > li.active > a:after,
    .sidebar-subnav > li.active > .nav-item:after {
        border-color: var(--th-sidebar-active-txt)<?= $im ?>;
        /*background-color: #2b957a;*/
    }

    /* ========================================================================
       Component: offsidebar
     ========================================================================== */
    .offsidebar {
        border-left: 1px solid #cccccc;
        background-color: #ffffff;
        color: #515253;
    }

    .nav-pills > li.active > a,
    .nav-pills > li.active > a:hover,
    .nav-pills > li.active > a:focus, li.user-header {
        background-color: var(--th-active-bg)<?= $im ?>;
        color: var(--th-active-fg)<?= $im ?>;
    }

    .pinned li.nav-heading .badge {
        font-size: 11px;
        padding: 3px 6px;
        margin-top: 2px;
        border-radius: 10px;
        background-color: var(--th-active-bg)<?= $im ?>;
    }

    .panel-custom .panel-heading {
        border-bottom: 2px solid var(--c-topbar-bg);
    }

    .custom-bg, .fc-state-default {
        background: var(--c-topbar-bg);
        color: var(--c-topbar-fg)<?= $im ?>;
    }

    .nav-tabs > li.active > a {
        border-top: 2px solid var(--c-topbar-bg);
    }

    .timeline-2, .timeline-2 .time-item:after, .time-item, .time-item:after {
        border-color: var(--c-topbar-bg);
    }

    .sub-active {
        border-left-color: var(--c-topbar-bg);
    }

    <?php
    if (!empty(config_item('sidebar_font_size'))){
        ?>
    :root {
        --th-sidebar-font-size: <?= config_item('sidebar_font_size') ?>px;
    }

    .sidebar .nav > li > a,
    .sidebar .nav > li > .nav-item {
        font-size: var(--th-sidebar-font-size) !important;
    }

    <?php
}
?>
</style>
