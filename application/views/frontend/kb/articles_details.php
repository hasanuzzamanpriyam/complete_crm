<?php
// Shared KB asset pipeline (kb_modern.css) — must come first.
$this->load->view('kb_shared/_kb_assets');

/* -------------------------------------------------------------------------
 * Resolve the active category exactly as the old view did: the article's
 * category when reading an article, otherwise the category id from the URL
 * (public route: frontend/kb_details/{kb_category|articles}/{id}).
 * ---------------------------------------------------------------------- */
$category_id = !empty($articles_info->kb_category_id) ? $articles_info->kb_category_id : $this->uri->segment(4);

// Deterministic icon + accent cycling keyed off the category's sort order
// (categories have no icon/color columns — same view-layer sugar as admin).
$kb_icons = ['🖥️', '🧩', '☁️', '🔐', '📚', '🛠️', '🚀', '🌐'];
$kb_accents = ['kb-tag-mint', 'kb-tag-cyan', 'kb-tag-amber', 'kb-tag-purple'];

// The current category's sort drives its icon/accent, so this view stays
// visually consistent with the overview grid ($all_kb_category = controller list).
$kb_cat_sort = 0;
if (!empty($all_kb_category)) {
    foreach ($all_kb_category as $kb_nav_category) {
        if ($kb_nav_category->kb_category_id == $category_id) {
            $kb_cat_sort = abs((int)$kb_nav_category->sort);
            break;
        }
    }
}
$kb_icon = $kb_icons[$kb_cat_sort % count($kb_icons)];
$kb_accent = $kb_accents[$kb_cat_sort % count($kb_accents)];
?>
<div class="kb-root kb-details kb-public">
    <?php
    $category = $this->uri->segment(3);
    if ($category == 'kb_category') {
        $category_info = get_row('tbl_kb_category', array('kb_category_id' => $category_id, 'status' => 1));

        /* ------------------------------------------------------------------
         * Category sort — VIEW LAYER ONLY. The controller and model are
         * frozen, so ?sort= re-orders the already-fetched
         * $articles_by_category array with usort() before the rows render.
         * Membership is never touched, so the public for_all = 'No' (+ status
         * = 1) scoping applied by the model stays exactly as-is.
         * No ?sort= param (or an unknown one) = the original controller order.
         * ---------------------------------------------------------------- */
        $kb_sort_options = array(
            'default' => lang('kb_sort_default'),
            'views' => lang('kb_sort_most_viewed'),
            'newest' => lang('kb_sort_newest'),
            'title' => lang('kb_sort_title'),
        );
        $kb_sort = $this->input->get('sort', true);
        $kb_sort = is_string($kb_sort) && array_key_exists($kb_sort, $kb_sort_options) ? $kb_sort : 'default';
        if ($kb_sort !== 'default' && is_array($articles_by_category) && count($articles_by_category) > 1) {
            // created_at may be a timestamp or a datetime string — normalise both.
            $kb_sort_time = function ($value) {
                return is_numeric($value) ? (float) $value : (int) strtotime((string) $value);
            };
            usort($articles_by_category, function ($a, $b) use ($kb_sort, $kb_sort_time) {
                if ($kb_sort === 'views') {
                    return (int) $b->total_view <=> (int) $a->total_view;
                }
                if ($kb_sort === 'newest') {
                    return $kb_sort_time($b->created_at) <=> $kb_sort_time($a->created_at);
                }
                return strcasecmp((string) $a->title, (string) $b->title); // A-Z
            });
        }
        ?>
        <!-- ===== Category view: header + in-category search + article rows ===== -->
        <div class="kb-crumb">
            <a href="<?= base_url('frontend/knowledgebase') ?>"><?= lang('knowledgebase') ?></a>
            <span class="kb-sep">/</span>
            <b><?= $category_info->category ?></b>
        </div>
        <div class="kb-cat-hero">
            <div class="kb-cat-icon <?= $kb_accent ?>"><?= $kb_icon ?></div>
            <div>
                <h2><?= $category_info->category ?></h2>
                <p><?= $category_info->description ?></p>
            </div>
            <div class="kb-count">
                <b><?= count($articles_by_category) ?></b>
                <span><?= lang('kb_articles_count') ?></span>
            </div>
        </div>
        <style>
            /* Scoped to this category view's sort control — token-driven, no raw hex. */
            .kb-sort { display: flex; align-items: center; gap: var(--sp-2); }
            .kb-sort .kb-sort-label { font-size: var(--fs-sm); font-weight: 600; color: var(--c-text-3); white-space: nowrap; }
            .kb-sort .kb-select-wrap { display: inline-block; }
            .kb-sort .kb-select-wrap select.kb-fin { width: auto; min-width: 158px; }
            .kb-sort .kb-sort-go { padding: 9px 11px; }
        </style>
        <div class="kb-toolbar">
            <div class="kb-search-in">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <?php $this->load->view("frontend/kb/search_box"); ?>
            </div>
            <div class="kb-grow"></div>
            <!-- Sort: GET param re-sorts the already-scoped array in this view (see above). -->
            <form class="kb-sort" method="get" action="<?= current_url() ?>">
                <label class="kb-sort-label" for="kb-sort-select-public"><?= lang('kb_sort') ?></label>
                <span class="kb-select-wrap">
                    <select class="kb-fin" id="kb-sort-select-public" name="sort" onchange="this.form.submit()">
                        <?php foreach ($kb_sort_options as $kb_sort_key => $kb_sort_label) { ?>
                            <option value="<?= $kb_sort_key ?>"<?= $kb_sort === $kb_sort_key ? ' selected="selected"' : '' ?>><?= $kb_sort_label ?></option>
                        <?php } ?>
                    </select>
                </span>
                <button type="submit" class="kb-btn kb-btn-ghost kb-sort-go" title="<?= lang('kb_sort') ?>" aria-label="<?= lang('kb_sort') ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h11M4 12h7M4 17h4"/><path d="M18 5v14m0 0-3-3m3 3 3-3"/></svg>
                </button>
            </form>
        </div>
        <div class="kb-panel">
            <?php
            if (!empty($articles_by_category)) {
                // Controller already scopes for_all = 'No' + status = 1 on the
                // public side; Popular = most viewed in this category, Updated =
                // newest row (tables only store created_at).
                $kb_max_view = 0;
                $kb_newest = '';
                foreach ($articles_by_category as $by_category) {
                    $kb_max_view = max($kb_max_view, (int)$by_category->total_view);
                    $kb_newest = max($kb_newest, $by_category->created_at);
                }
                foreach ($articles_by_category as $by_category) {
                    $kb_row_files = json_decode($by_category->attachments);
                    ?>
                    <a class="kb-art-row" href="<?= base_url('frontend/kb_details/articles/' . $by_category->kb_id) ?>">
                        <div class="kb-art-badge <?= $kb_accent ?>"><?= $kb_icon ?></div>
                        <div class="kb-art-main">
                            <b><?= $by_category->title ?></b>
                            <span><?= lang('kb_updated') . ' ' . time_ago($by_category->created_at) ?></span>
                        </div>
                        <?php if ($by_category->created_at == $kb_newest) { ?>
                            <span class="kb-tag kb-tag-amber"><?= lang('kb_updated') ?></span>
                        <?php } elseif ((int)$by_category->total_view == $kb_max_view && $kb_max_view > 0) { ?>
                            <span class="kb-tag kb-tag-mint"><?= lang('kb_popular') ?></span>
                        <?php } ?>
                        <?php if (!empty($kb_row_files)) { ?>
                            <span class="kb-tag kb-tag-slate"><i class="fa fa-paperclip"></i></span>
                        <?php } ?>
                        <div class="kb-art-views">👁 <?= (int)$by_category->total_view ?></div>
                    </a>
                <?php }
            } else { ?>
                <div class="kb-art-row"><div class="kb-art-main"><span><?= lang('kb_no_articles') ?></span></div></div>
            <?php } ?>
        </div>
    <?php } else {
        if (!empty($articles_info)) {
            // ===== Article view: breadcrumbs, article card + sticky sidebar =====
            $kb_category_row = get_row('tbl_kb_category', array('kb_category_id' => $category_id, 'status' => 1));
            $kb_category_name = !empty($kb_category_row->category) ? $kb_category_row->category : '';

            // Byline: author (created_by user), "Updated" date, read time, views.
            $kb_author = get_staff_details($articles_info->created_by);
            $kb_author_name = !empty($kb_author->fullname) ? $kb_author->fullname : lang('admin');
            $kb_initials = '';
            foreach (preg_split('/\s+/', trim($kb_author_name)) as $kb_word) {
                if ($kb_word !== '') {
                    $kb_initials .= mb_strtoupper(mb_substr($kb_word, 0, 1));
                    if (mb_strlen($kb_initials) == 2) {
                        break;
                    }
                }
            }
            $kb_read_min = max(1, (int)ceil(str_word_count(html_entity_decode(strip_tags($articles_info->description))) / 200));

            // Related: other PUBLIC articles in the same category — the public
            // side must never surface internal-only (for_all = 'Yes') rows.
            $kb_related = $this->db
                ->select('tbl_knowledgebase.kb_id, tbl_knowledgebase.title')
                ->from('tbl_knowledgebase')
                ->where('tbl_knowledgebase.kb_category_id', $articles_info->kb_category_id)
                ->where('tbl_knowledgebase.status', 1)
                ->where('tbl_knowledgebase.for_all', 'No')
                ->where('tbl_knowledgebase.kb_id !=', $articles_info->kb_id)
                ->order_by('tbl_knowledgebase.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->result();
            ?>
            <div class="kb-crumb">
                <a href="<?= base_url('frontend/knowledgebase') ?>"><?= lang('knowledgebase') ?></a>
                <span class="kb-sep">/</span>
                <?php if (!empty($kb_category_name)) { ?>
                    <a href="<?= base_url('frontend/kb_details/kb_category/' . $category_id) ?>"><?= $kb_category_name ?></a>
                    <span class="kb-sep">/</span>
                <?php } ?>
                <b><?= $articles_info->title ?></b>
            </div>
            <div class="kb-read">
                <article class="kb-article">
                    <div class="kb-a-tags">
                        <span class="kb-tag kb-tag-slate"><?= $kb_icon ?> <?= $kb_category_name ?></span>
                    </div>
                    <h1><?= $articles_info->title ?></h1>
                    <div class="kb-byline">
                        <div class="kb-avatar"><?= $kb_initials ?></div>
                        <div>
                            <b><?= $kb_author_name ?></b><br>
                            <?= lang('kb_updated') . ' ' . time_ago($articles_info->created_at) ?>
                            &middot; ⏱ <?= $kb_read_min . lang('minutes') ?>
                            &middot; 👁 <?= (int)$articles_info->total_view ?>
                        </div>
                    </div>
                    <div class="kb-content">
                        <?= html_entity_decode($articles_info->description) ?>
                    </div>
                    <?php
                    $uploaded_file = json_decode($articles_info->attachments);
                    if (!empty($uploaded_file)):
                        ?>
                        <div class="kb-panel" style="margin-top:var(--sp-5)">
                            <div class="kb-panel-head">
                                <h3><i class="fa fa-paperclip"></i> <?= lang('kb_attachments') ?></h3>
                            </div>
                            <div class="kb-file-chips" style="padding:0 var(--sp-5) var(--sp-4)">
                                <?php
                                foreach ($uploaded_file as $fKey => $v_files):

                                    if (!empty($v_files)):

                                        ?>
                                        <a class="kb-file-chip"
                                           href="<?= base_url('frontend/kb_download/' . $articles_info->kb_id . '/' . $fKey) ?>">
                                            <?php if ($v_files->is_image == 1) : ?>
                                                <img class="kb-fi" src="<?= base_url() . $v_files->path ?>" alt=""
                                                     style="width:22px;height:22px;object-fit:cover;border-radius:4px">
                                            <?php else : ?>
                                                <i class="fa fa-file-pdf-o"></i>
                                            <?php endif; ?>
                                            <?= $v_files->fileName ?>
                                            <span style="color:var(--c-text-3)"><?= $v_files->size ?> <?= lang('kb') ?></span>
                                        </a>
                                    <?php
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </article>
                <aside class="kb-toc">
                    <div id="kb-toc-page">
                        <h3><?= lang('kb_on_this_page') ?></h3>
                        <div id="kb-toc-links"></div>
                    </div>
                    <div style="margin-top:var(--sp-5);padding-top:var(--sp-4);border-top:1px solid var(--c-border)">
                        <h3><?= lang('kb_related') ?></h3>
                        <?php if (!empty($kb_related)) {
                            foreach ($kb_related as $kb_related_article) { ?>
                                <a href="<?= base_url('frontend/kb_details/articles/' . $kb_related_article->kb_id) ?>"
                                   style="border:none;padding-left:0"><?= $kb_related_article->title ?></a>
                            <?php }
                        } else { ?>
                            <span style="font-size:var(--fs-sm);color:var(--c-text-3)"><?= lang('kb_no_articles') ?></span>
                        <?php } ?>
                    </div>
                    <div class="kb-feedback">
                        <p><?= lang('kb_was_this_helpful') ?></p>
                        <div class="kb-fb-btns">
                            <button type="button" class="kb-fb-btn">👍 <?= lang('kb_yes') ?></button>
                            <button type="button" class="kb-fb-btn">👎 <?= lang('kb_no') ?></button>
                        </div>
                    </div>
                </aside>
            </div>
            <script>
                (function () {
                    var KB_FEEDBACK_THANKS = <?= json_encode(lang('kb_thanks_feedback')) ?>;

                    /* ----- Client-side heading TOC ----- */
                    var content = document.querySelector('.kb-details .kb-content');
                    var tocPage = document.getElementById('kb-toc-page');
                    var tocLinks = document.getElementById('kb-toc-links');
                    if (content && tocPage && tocLinks) {
                        var heads = content.querySelectorAll('h1, h2, h3, h4');
                        if (!heads.length) {
                            tocPage.style.display = 'none';
                        } else {
                            var links = [];
                            heads.forEach(function (h, i) {
                                if (!h.id) {
                                    h.id = 'kb-h-' + i;
                                }
                                var a = document.createElement('a');
                                a.href = '#' + h.id;
                                a.textContent = (h.textContent || '').trim();
                                if (i === 0) {
                                    a.className = 'on';
                                }
                                tocLinks.appendChild(a);
                                links.push(a);
                            });
                            tocLinks.addEventListener('click', function (e) {
                                var a = e.target.closest('a');
                                if (!a) {
                                    return;
                                }
                                var target = document.getElementById(a.getAttribute('href').slice(1));
                                if (target) {
                                    e.preventDefault();
                                    window.scrollTo({
                                        top: target.getBoundingClientRect().top + window.pageYOffset - 90,
                                        behavior: 'smooth'
                                    });
                                }
                            });
                            window.addEventListener('scroll', function () {
                                var probe = window.pageYOffset + 120;
                                var current = 0;
                                heads.forEach(function (h, i) {
                                    if (h.getBoundingClientRect().top + window.pageYOffset <= probe) {
                                        current = i;
                                    }
                                });
                                links.forEach(function (l, i) {
                                    l.classList.toggle('on', i === current);
                                });
                            }, {passive: true});
                        }
                    }

                    /* ----- "Was this helpful?" — inert, client-side only ----- */
                    var fbBtns = document.querySelector('.kb-details .kb-fb-btns');
                    if (fbBtns) {
                        fbBtns.addEventListener('click', function (e) {
                            if (e.target.closest('.kb-fb-btn')) {
                                fbBtns.textContent = KB_FEEDBACK_THANKS;
                            }
                        });
                    }
                })();
            </script>
        <?php }
    } ?>
</div>
