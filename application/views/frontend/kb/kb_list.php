<?php
// Shared KB asset pipeline (kb_modern.css) — must come first.
$this->load->view('kb_shared/_kb_assets');

/* -------------------------------------------------------------------------
 * View-layer stats — PUBLIC side: every article query is additionally scoped
 * to for_all = 'No' (plus status = 1) so internal-only articles never surface.
 * ---------------------------------------------------------------------- */
$kb_total_articles = (int)$this->db->where('status', 1)->where('for_all', 'No')->count_all_results('tbl_knowledgebase');
$kb_views_row = $this->db->select_sum('total_view')->where('status', 1)->where('for_all', 'No')->get('tbl_knowledgebase')->row();
$kb_total_views = $kb_views_row ? (int)$kb_views_row->total_view : 0;
$kb_category_count = is_array($all_kb_category) ? count($all_kb_category) : 0;

// Deterministic icon + accent cycling keyed off each category's sort order
// (categories have no icon/color columns — same view-layer sugar as admin).
$kb_icons = ['🖥️', '🧩', '☁️', '🔐', '📚', '🛠️', '🚀', '🌐'];
$kb_accents = ['kb-tag-mint', 'kb-tag-cyan', 'kb-tag-amber', 'kb-tag-purple'];

/** Pick the icon + accent class pair for a given sort order. */
$kb_style_for = function ($sort) use ($kb_icons, $kb_accents) {
    $sort = abs((int)$sort);
    return [$kb_icons[$sort % count($kb_icons)], $kb_accents[$sort % count($kb_accents)]];
};

// Popular panel: top public articles by all-time views.
$kb_popular = $this->db
    ->select('tbl_knowledgebase.*, tbl_kb_category.category, tbl_kb_category.sort AS cat_sort')
    ->from('tbl_knowledgebase')
    ->join('tbl_kb_category', 'tbl_kb_category.kb_category_id = tbl_knowledgebase.kb_category_id')
    ->where('tbl_knowledgebase.status', 1)
    ->where('tbl_knowledgebase.for_all', 'No')
    ->order_by('tbl_knowledgebase.total_view', 'DESC')
    ->limit(5)
    ->get()
    ->result();

// Recently updated panel: newest public articles first (tables only store
// created_at, so the "updated" wording is displayed over the creation date).
$kb_recent = $this->db
    ->select('tbl_knowledgebase.*, tbl_kb_category.category, tbl_kb_category.sort AS cat_sort')
    ->from('tbl_knowledgebase')
    ->join('tbl_kb_category', 'tbl_kb_category.kb_category_id = tbl_knowledgebase.kb_category_id')
    ->where('tbl_knowledgebase.status', 1)
    ->where('tbl_knowledgebase.for_all', 'No')
    ->order_by('tbl_knowledgebase.created_at', 'DESC')
    ->limit(5)
    ->get()
    ->result();
?>
<div class="kb-root kb-overview kb-public">
    <div class="kb-crumb"><b><?= lang('knowledgebase') ?></b></div>

    <!-- ===== Hero ===== -->
    <div class="kb-hero">
        <h1><?= lang('kb_how_can_we_help') ?></h1>
        <p><?= lang('kb_browse_by_category') ?></p>
        <div class="kb-hero-search">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <?php $this->load->view('frontend/kb/search_box'); ?>
        </div>
        <div class="kb-hero-stats">
            <div class="kb-hero-stat"><b><?= $kb_category_count ?></b><span><?= lang('kb_all_categories') ?></span></div>
            <div class="kb-hero-stat"><b><?= $kb_total_articles ?></b><span><?= lang('kb_articles_count') ?></span></div>
            <div class="kb-hero-stat"><b><?= number_format($kb_total_views) ?></b><span><?= lang('kb_total_views') ?></span></div>
        </div>
    </div>

    <!-- ===== Category grid ===== -->
    <div class="kb-sec-head">
        <h2><?= lang('kb_browse_by_category') ?></h2>
    </div>
    <div class="kb-cat-grid">
        <?php if (!empty($all_kb_category)) {
            foreach ($all_kb_category as $kb_category) {
                $kb_rows = $this->db->where(array('kb_category_id' => $kb_category->kb_category_id, 'status' => 1, 'for_all' => 'No'))
                    ->order_by('created_at', 'DESC')
                    ->get('tbl_knowledgebase')
                    ->result();
                $total_kb = count($kb_rows);
                $kb_latest = $total_kb ? $kb_rows[0]->created_at : null;
                list($kb_icon, $kb_accent) = $kb_style_for($kb_category->sort);
                ?>
                <a class="kb-cat-card" href="<?= base_url('frontend/kb_details/kb_category/' . $kb_category->kb_category_id) ?>">
                    <div class="kb-cat-icon <?= $kb_accent ?>"><?= $kb_icon ?></div>
                    <h3><?= $kb_category->category ?></h3>
                    <p><?= $kb_category->description ?></p>
                    <div class="kb-cat-meta">
                        <span class="kb-pill"><?= !empty($total_kb) ? $total_kb : 0; echo ' ' . lang('kb_articles_count') ?></span>
                        <?php if ($kb_latest) { ?>
                            <span><?= lang('kb_updated_ago') . ' ' . time_ago($kb_latest) ?></span>
                        <?php } ?>
                    </div>
                    <div class="kb-cat-go">
                        <?= lang('kb_browse') ?>
                        <svg class="kb-icn" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </div>
                </a>
            <?php }
        } ?>
    </div>

    <!-- ===== Popular + Recently updated ===== -->
    <div class="kb-two-col" style="margin-top:var(--sp-6)">
        <div class="kb-panel">
            <div class="kb-panel-head">
                <h3>🔥 <?= lang('kb_popular_articles') ?></h3>
            </div>
            <?php if (empty($kb_popular)) { ?>
                <div class="kb-art-row"><div class="kb-art-main"><span><?= lang('kb_no_articles') ?></span></div></div>
            <?php } foreach ($kb_popular as $kb_article) {
                list($kb_icon, $kb_accent) = $kb_style_for($kb_article->cat_sort);
                ?>
                <a class="kb-art-row" href="<?= base_url('frontend/kb_details/articles/' . $kb_article->kb_id) ?>">
                    <div class="kb-art-badge <?= $kb_accent ?>"><?= $kb_icon ?></div>
                    <div class="kb-art-main">
                        <b><?= $kb_article->title ?></b>
                        <span><?= $kb_article->category ?></span>
                    </div>
                    <div class="kb-art-views">👁 <?= (int)$kb_article->total_view ?></div>
                </a>
            <?php } ?>
        </div>
        <div class="kb-panel">
            <div class="kb-panel-head">
                <h3>🕐 <?= lang('kb_recently_updated') ?></h3>
            </div>
            <?php if (empty($kb_recent)) { ?>
                <div class="kb-art-row"><div class="kb-art-main"><span><?= lang('kb_no_articles') ?></span></div></div>
            <?php } foreach ($kb_recent as $kb_article) { ?>
                <a class="kb-art-row" href="<?= base_url('frontend/kb_details/articles/' . $kb_article->kb_id) ?>">
                    <div class="kb-art-main">
                        <b><?= $kb_article->title ?></b>
                        <span><?= lang('kb_updated_ago') . ' ' . time_ago($kb_article->created_at) ?></span>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>
</div>
