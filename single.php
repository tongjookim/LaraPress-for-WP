<?php
/**
 * LaraPress 기사 상세 페이지 (single.php)
 *
 * - amber-journal : 전용 레이아웃 (스티키 바 + 공유·프린트·글자크기 + 댓글)
 * - 그 외 스킨    : Fresh(SWN) 스타일 차용 (동일 기능 포함)
 */

extract( lp_skin_vars() );

// amber-journal 이외 스킨 레이아웃 변수
if ( $current_theme_style === 'newyorktimes-style' ) {
    $lp_main_class    = 'py-6 bg-white';
    $lp_sidebar_class = 'nyt-sidebar lg:border-l lg:border-gray-200 lg:pl-6';
} elseif ( $current_theme_style === 'basic' ) {
    $lp_main_class    = 'py-8';
    $lp_sidebar_class = 'basic-sidebar-area';
} elseif ( $current_theme_style !== 'amber-journal' ) {
    $lp_main_class    = 'py-10 bg-slate-50';
    $lp_sidebar_class = '';
}

get_header();

/* ════════════════════════════════════════════════════
   엠버 저널 전용 단독 처리
   ════════════════════════════════════════════════════ */
if ( $current_theme_style === 'amber-journal' ) :
    if ( have_posts() ) : the_post();

    $aj_post_url   = esc_url( get_permalink() );
    $aj_post_title = esc_js( get_the_title() );
    $aj_post_id    = get_the_ID();
?>

<!-- ① 스티키 기사 정보 바 + 열독률 진행바 -->
<div id="lp-sticky-bar" aria-hidden="true">
    <div class="lp-sb-inner <?php echo $container_class; ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lp-sb-logo hidden sm:block">
            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </a>
        <span class="lp-sb-sep hidden sm:block"></span>
        <div class="flex-1 min-w-0">
            <p class="lp-sb-title"><?php the_title(); ?></p>
            <p class="lp-sb-author"><?php
                $aj_sb_authors = lp_get_post_authors( get_the_ID() );
                $aj_sb_parts   = [];
                foreach ( $aj_sb_authors as $_a ) {
                    $aj_sb_parts[] = $_a['url']
                        ? '<a href="' . esc_url( $_a['url'] ) . '" class="aj-author-link">' . esc_html( $_a['name'] ) . '</a>'
                        : esc_html( $_a['name'] );
                }
                echo implode( ', ', $aj_sb_parts );
            ?> 기자 · <?php echo get_the_date( 'Y.m.d' ); ?></p>
        </div>
        <!-- 글자 크기 (sm 이상) -->
        <?php // AJ sticky bar author ?>
        <div class="aj-fz-ctrl hidden sm:flex" title="글자 크기 조절">
            <button class="aj-fz-btn" onclick="ajFontSize(-1)" id="aj-sb-fz-sm"  title="글자 작게">가<sup style="font-size:.55em;vertical-align:super">−</sup></button>
            <button class="aj-fz-btn" onclick="ajFontSize(0)"  id="aj-sb-fz-md"  title="기본 크기">가</button>
            <button class="aj-fz-btn" onclick="ajFontSize(1)"  id="aj-sb-fz-lg"  title="글자 크게">가<sup style="font-size:.55em;vertical-align:super">+</sup></button>
        </div>
        <span class="lp-sb-share-sep hidden sm:block"></span>
        <!-- 공유 -->
        <div class="flex items-center gap-1">
            <button onclick="ajCopyUrl(this)" class="aj-util-btn" title="URL 복사">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </button>
            <button onclick="ajShare('facebook')" style="background:#1877F2;border:none;color:#fff;" class="aj-util-btn" title="페이스북">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </button>
            <button onclick="ajShare('x')" style="background:#000;border:none;color:#fff;" class="aj-util-btn" title="X (Twitter)">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.638 5.903-5.638zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </button>
            <button onclick="ajShare('kakao')" style="background:#FEE500;border:none;color:#000;" class="aj-util-btn" title="카카오톡">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3C6.477 3 2 6.477 2 10.778c0 2.753 1.81 5.15 4.534 6.516-.178.617-.574 2.23-.657 2.576-.1.428.157.422.33.308.136-.09 2.157-1.454 3.027-2.04.25.035.504.053.766.053 5.52 0 10-3.477 10-7.778C20 6.477 17.52 3 12 3z"/></svg>
            </button>
        </div>
    </div>
    <div id="lp-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
</div>

<main class="aj-main flex-grow">
    <div class="<?php echo $container_class; ?>">
        <div class="aj-single-layout">

            <!-- ═══ 기사 본문 ═══ -->
            <article class="aj-single-article" id="aj-article" itemscope itemtype="https://schema.org/NewsArticle">

                <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'large', [ 'class' => 'aj-single-hero', 'itemprop' => 'image' ] ); ?>
                <?php endif; ?>

                <div class="aj-single-body">

                    <!-- 카테고리 태그 -->
                    <div class="aj-single-kicker">
                        <?php foreach ( get_the_category() as $aj_sc ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $aj_sc->term_id ) ); ?>" class="aj-cat-tag">
                            <?php echo esc_html( $aj_sc->name ); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- 제목 -->
                    <h1 class="aj-single-title" itemprop="headline"><?php the_title(); ?></h1>

                    <!-- 메타 + 유틸 툴바 -->
                    <div class="aj-single-meta">
                        <span><strong>기자</strong>
                        <?php
                        $aj_byline_authors = lp_get_post_authors( get_the_ID() );
                        $aj_byline_parts   = [];
                        foreach ( $aj_byline_authors as $_a ) {
                            $aj_byline_parts[] = $_a['url']
                                ? '<a href="' . esc_url( $_a['url'] ) . '" class="aj-author-link">' . esc_html( $_a['name'] ) . '</a>'
                                : esc_html( $_a['name'] );
                        }
                        echo implode( ', ', $aj_byline_parts );
                        ?></span>
                        <span aria-hidden="true">·</span>
                        <span itemprop="datePublished" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                            <?php echo get_the_date( 'Y년 n월 j일 H:i' ); ?>
                        </span>
                        <?php if ( get_the_modified_date( 'U' ) !== get_the_date( 'U' ) ) : ?>
                        <span aria-hidden="true">·</span>
                        <span style="color:var(--aj-amber);" itemprop="dateModified" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
                            <?php echo get_the_modified_date( 'Y.m.d H:i' ); ?> 수정
                        </span>
                        <?php endif; ?>

                        <!-- 유틸 툴바: 글자 크기 / 프린트 / 공유 -->
                        <div class="aj-util-toolbar" style="margin-left:auto;">
                            <!-- 글자 크기 -->
                            <div class="aj-fz-ctrl" title="글자 크기 조절">
                                <button class="aj-fz-btn" onclick="ajFontSize(-1)" id="aj-hdr-fz-sm"  title="글자 작게">가<sup style="font-size:.55em;vertical-align:super">−</sup></button>
                                <button class="aj-fz-btn" onclick="ajFontSize(0)"  id="aj-hdr-fz-md"  title="기본 크기">가</button>
                                <button class="aj-fz-btn" onclick="ajFontSize(1)"  id="aj-hdr-fz-lg"  title="글자 크게">가<sup style="font-size:.55em;vertical-align:super">+</sup></button>
                            </div>
                            <span class="aj-util-sep"></span>
                            <!-- 프린트 -->
                            <button onclick="window.print()" class="aj-util-btn" title="프린트">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            </button>
                            <!-- URL 복사 -->
                            <button onclick="ajCopyUrl(this)" class="aj-util-btn" title="URL 복사">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </button>
                            <span class="aj-util-sep"></span>
                            <!-- SNS 공유 -->
                            <button onclick="ajShare('facebook')" style="background:#1877F2;border:none;color:#fff;" class="aj-util-btn" title="페이스북">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </button>
                            <button onclick="ajShare('x')" style="background:#000;border:none;color:#fff;" class="aj-util-btn" title="X (Twitter)">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.638 5.903-5.638zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </button>
                            <button onclick="ajShare('kakao')" style="background:#FEE500;border:none;color:#000;" class="aj-util-btn" title="카카오톡">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3C6.477 3 2 6.477 2 10.778c0 2.753 1.81 5.15 4.534 6.516-.178.617-.574 2.23-.657 2.576-.1.428.157.422.33.308.136-.09 2.157-1.454 3.027-2.04.25.035.504.053.766.053 5.52 0 10-3.477 10-7.778C20 6.477 17.52 3 12 3z"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- 본문 -->
                    <div class="aj-article-content" id="aj-article-body" itemprop="articleBody">
                        <?php the_content(); ?>
                    </div>

                    <!-- 태그 -->
                    <?php $aj_tags = get_the_tags(); ?>
                    <?php if ( $aj_tags ) : ?>
                    <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--aj-border);display:flex;flex-wrap:wrap;gap:0.4rem;">
                        <?php foreach ( $aj_tags as $aj_tag ) : ?>
                        <a href="<?php echo esc_url( get_tag_link( $aj_tag->term_id ) ); ?>"
                           style="font-size:0.75rem;padding:0.25rem 0.7rem;border:1px solid var(--aj-border);border-radius:9999px;color:var(--aj-muted);text-decoration:none;transition:border-color 0.15s,color 0.15s;"
                           onmouseover="this.style.borderColor='var(--aj-amber)';this.style.color='var(--aj-amber)';"
                           onmouseout="this.style.borderColor='var(--aj-border)';this.style.color='var(--aj-muted)';">
                            #<?php echo esc_html( $aj_tag->name ); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- 기자 프로필 서명 (복수 저자 지원) -->
                    <?php
                    $aj_bio_authors = lp_get_post_authors( get_the_ID(), 64, 'aj-author-avatar' );
                    if ( $aj_bio_authors ) :
                        foreach ( $aj_bio_authors as $_ba ) :
                            if ( ! $_ba['name'] ) continue;
                    ?>
                    <div class="aj-author-bio">
                        <?php if ( $_ba['url'] ) : ?>
                        <a href="<?php echo esc_url( $_ba['url'] ); ?>" class="aj-author-bio-link" aria-label="<?php echo esc_attr( $_ba['name'] ); ?> 기자 페이지">
                            <?php echo $_ba['avatar_html']; ?>
                        </a>
                        <?php else : ?>
                        <span class="aj-author-bio-link"><?php echo $_ba['avatar_html']; ?></span>
                        <?php endif; ?>
                        <div class="aj-author-bio-body">
                            <p class="aj-author-bio-label">기자 프로필</p>
                            <?php if ( $_ba['url'] ) : ?>
                            <a href="<?php echo esc_url( $_ba['url'] ); ?>" class="aj-author-bio-name"><?php echo esc_html( $_ba['name'] ); ?></a>
                            <?php else : ?>
                            <span class="aj-author-bio-name"><?php echo esc_html( $_ba['name'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $_ba['description'] ) : ?>
                            <p class="aj-author-bio-desc"><?php echo esc_html( $_ba['description'] ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>

                    <!-- 이전 / 다음 기사 네비 -->
                    <?php
                    $aj_prev = get_previous_post();
                    $aj_next = get_next_post();
                    if ( $aj_prev || $aj_next ) :
                    ?>
                    <nav class="aj-single-nav" aria-label="이전 다음 기사">
                        <?php if ( $aj_prev ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $aj_prev ) ); ?>" class="aj-nav-btn aj-nav-prev">
                            <span class="aj-nav-label">← 이전 기사</span>
                            <span class="aj-nav-title"><?php echo esc_html( get_the_title( $aj_prev ) ); ?></span>
                        </a>
                        <?php else : ?><span></span><?php endif; ?>
                        <?php if ( $aj_next ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $aj_next ) ); ?>" class="aj-nav-btn aj-nav-next">
                            <span class="aj-nav-label">다음 기사 →</span>
                            <span class="aj-nav-title"><?php echo esc_html( get_the_title( $aj_next ) ); ?></span>
                        </a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>

                    <!-- ── 댓글 영역 ── -->
                    <?php if ( comments_open() || get_comments_number() ) : ?>
                    <section class="aj-comments" id="comments">
                        <h2 class="aj-comments-title">
                            <span class="aj-widget-mark"></span>
                            댓글
                            <span style="color:var(--aj-amber);font-size:0.9em;"><?php echo get_comments_number( '0', '1', '%' ); ?></span>
                        </h2>

                        <?php if ( have_comments() ) : ?>
                        <div style="margin-bottom:1.25rem;">
                            <?php
                            wp_list_comments( [
                                'style'       => 'ol',
                                'type'        => 'comment',
                                'short_ping'  => true,
                                'avatar_size' => 32,
                                'callback'    => 'lp_aj_comment_cb',
                            ] );
                            ?>
                        </div>
                        <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
                        <nav style="margin-bottom:1rem;font-size:0.8rem;display:flex;gap:0.5rem;">
                            <?php previous_comments_link( '← 이전 댓글' ); ?>
                            <?php next_comments_link( '다음 댓글 →' ); ?>
                        </nav>
                        <?php endif; ?>
                        <?php endif; ?>

                        <div class="aj-comment-form-wrap">
                        <?php
                        comment_form( [
                            'id_form'              => 'aj-comment-form',
                            'class_form'           => 'aj-comment-form-inner',
                            'title_reply'          => '댓글 작성',
                            'title_reply_to'       => '%s에게 댓글 작성',
                            'cancel_reply_link'    => '취소',
                            'label_submit'         => '댓글 등록',
                            'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="aj-comment-submit" value="%4$s">',
                            'comment_field'        => '<p class="comment-form-comment"><label for="comment">댓글 <span class="required">*</span></label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
                            'fields'               => [
                                'author' => '<div id="comment-form-meta"><p class="comment-form-author"><label for="author">이름 <span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr( isset( $_COOKIE['comment_author_' . COOKIEHASH] ) ? $_COOKIE['comment_author_' . COOKIEHASH] : '' ) . '" size="30" maxlength="245" required></p>',
                                'email'  => '<p class="comment-form-email"><label for="email">이메일 <span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr( isset( $_COOKIE['comment_author_email_' . COOKIEHASH] ) ? $_COOKIE['comment_author_email_' . COOKIEHASH] : '' ) . '" size="30" maxlength="100" required></p></div>',
                                'url'    => '',
                            ],
                            'comment_notes_before' => '',
                            'comment_notes_after'  => '',
                        ] );
                        ?>
                        </div><!-- /.aj-comment-form-wrap -->
                    </section>
                    <?php endif; ?>

                </div><!-- /.aj-single-body -->
            </article>

            <!-- ═══ 사이드바 ═══ -->
            <aside class="aj-sidebar">

                <!-- 많이 본 기사 위젯 -->
                <div class="aj-widget">
                    <div class="aj-widget-head">
                        <span class="aj-widget-mark"></span>
                        <h2 class="aj-widget-title">많이 본 기사</h2>
                        <span style="margin-left:auto;font-size:0.68rem;color:var(--aj-amber);font-weight:700;">HOT</span>
                    </div>
                    <?php echo lp_aj_hot_news_html(); ?>
                </div>

                <!-- 추천 기사 위젯 -->
                <?php
                $aj_s_picks_custom = get_theme_mod( 'lp_aj_picks_title', '' );
                $aj_s_picks_cat    = get_theme_mod( 'lp_aj_picks_cat', '' );
                $aj_s_cat_obj      = $aj_s_picks_cat ? get_category_by_slug( $aj_s_picks_cat ) : null;
                $aj_s_title        = $aj_s_picks_custom
                    ? esc_html( $aj_s_picks_custom )
                    : ( $aj_s_cat_obj ? esc_html( $aj_s_cat_obj->name ) . ' 추천' : '추천 기사' );
                ?>
                <div class="aj-widget">
                    <div class="aj-widget-head">
                        <span class="aj-widget-mark"></span>
                        <h2 class="aj-widget-title"><?php echo $aj_s_title; ?></h2>
                    </div>
                    <?php echo lp_aj_picks_html(); ?>
                </div>

                <!-- 사이드 배너 -->
                <?php $aj_s_banner = get_theme_mod( 'lp_banner_side', '' ); ?>
                <?php if ( $aj_s_banner ) : ?>
                <div style="overflow:hidden;"><?php echo $aj_s_banner; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                <?php endif; ?>

            </aside>

        </div><!-- /.aj-single-layout -->
    </div><!-- /.container -->
</main>

<script>
(function () {
    var progressBar = document.getElementById('lp-progress-bar');
    var stickyBar   = document.getElementById('lp-sticky-bar');
    var article     = document.getElementById('aj-article');
    var articleBody = document.getElementById('aj-article-body');
    var articleHdr  = article ? article.querySelector('.aj-single-title') : null;
    var gnav        = document.getElementById('aj-gnav');

    if (!stickyBar || !article) return;

    function getAdminBarH() {
        var ab = document.getElementById('wpadminbar');
        return ab ? ab.getBoundingClientRect().height : 0;
    }
    function getNavH() {
        return gnav ? getAdminBarH() + gnav.offsetHeight : getAdminBarH();
    }
    function positionStickyBar() {
        stickyBar.style.top = stickyBar.classList.contains('is-visible')
            ? getAdminBarH() + 'px' : getNavH() + 'px';
    }
    positionStickyBar();
    window.addEventListener('resize', positionStickyBar, { passive: true });

    function onScroll() {
        var scrollY = window.scrollY || document.documentElement.scrollTop;
        var winH    = window.innerHeight;

        if (progressBar && articleBody) {
            var bodyTop = articleBody.getBoundingClientRect().top + scrollY;
            var bodyEnd = bodyTop + articleBody.offsetHeight - winH;
            var pct = bodyEnd > bodyTop
                ? Math.min(100, Math.max(0, (scrollY - bodyTop) / (bodyEnd - bodyTop) * 100)) : 0;
            progressBar.style.width = pct + '%';
            progressBar.setAttribute('aria-valuenow', Math.round(pct));
        }

        if (articleHdr) {
            var show = articleHdr.getBoundingClientRect().bottom < getNavH();
            stickyBar.classList.toggle('is-visible', show);
            stickyBar.setAttribute('aria-hidden', show ? 'false' : 'true');
            positionStickyBar();
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── 글자 크기 조절 ── */
    var FS_KEY    = 'aj_font_size';
    var FS_LEVELS = [
        { size: '0.9375rem', lh: '1.75' },
        { size: '1.0rem',    lh: '1.9'  },
        { size: '1.1875rem', lh: '2.0'  }
    ];
    var fsIdx = 1;
    try {
        var saved = parseInt(localStorage.getItem(FS_KEY), 10);
        if (!isNaN(saved) && saved >= 0 && saved <= 2) fsIdx = saved;
    } catch (e) {}

    function applyFontSize(idx) {
        fsIdx = Math.max(0, Math.min(2, idx));
        if (articleBody) {
            articleBody.style.fontSize   = FS_LEVELS[fsIdx].size;
            articleBody.style.lineHeight = FS_LEVELS[fsIdx].lh;
        }
        var btnSets = [
            ['aj-sb-fz-sm',  'aj-sb-fz-md',  'aj-sb-fz-lg'],
            ['aj-hdr-fz-sm', 'aj-hdr-fz-md', 'aj-hdr-fz-lg']
        ];
        btnSets.forEach(function (set) {
            set.forEach(function (id, i) {
                var btn = document.getElementById(id);
                if (btn) btn.classList.toggle('aj-fz-active', i === fsIdx);
            });
        });
        try { localStorage.setItem(FS_KEY, String(fsIdx)); } catch (e) {}
    }
    window.ajFontSize = function (dir) { applyFontSize(dir === 0 ? 1 : fsIdx + dir); };
    applyFontSize(fsIdx);

    /* ── SNS 공유 ── */
    window.ajShare = function (platform) {
        var url   = encodeURIComponent(location.href);
        var title = encodeURIComponent(document.title);
        if (platform === 'facebook') {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + url, '_blank', 'width=620,height=440,noopener,noreferrer');
        } else if (platform === 'x') {
            window.open('https://twitter.com/intent/tweet?url=' + url + '&text=' + title, '_blank', 'width=620,height=440,noopener,noreferrer');
        } else if (platform === 'kakao') {
            if (window.Kakao && window.Kakao.isInitialized && window.Kakao.isInitialized()) {
                window.Kakao.Link.sendDefault({
                    objectType: 'feed',
                    content: { title: document.title, webUrl: location.href, mobileWebUrl: location.href, imageUrl: '', description: '' },
                    buttons: [{ title: '기사 보기', link: { webUrl: location.href } }]
                });
            } else {
                navigator.clipboard.writeText(location.href)
                    .then(function () { alert('카카오톡 SDK 미설치 환경입니다.\nURL이 클립보드에 복사되었습니다.'); })
                    .catch(function () { alert(location.href); });
            }
        }
    };

    /* ── URL 복사 ── */
    window.ajCopyUrl = function (btn) {
        var orig = btn ? btn.title : '';
        function done() {
            if (btn) { btn.title = '복사 완료!'; setTimeout(function () { btn.title = orig; }, 1500); }
            alert('URL이 클립보드에 복사되었습니다.');
        }
        if (navigator.clipboard) {
            navigator.clipboard.writeText(location.href).then(done).catch(function () {
                var ta = document.createElement('textarea');
                ta.value = location.href; document.body.appendChild(ta);
                ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done();
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = location.href; document.body.appendChild(ta);
            ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done();
        }
    };
})();
</script>

    <?php endif; /* have_posts */
    get_footer();
    return;
endif; /* amber-journal */

/* ════════════════════════════════════════════════════
   나머지 스킨 — Fresh(SWN) 스타일 차용
   ════════════════════════════════════════════════════ */
if ( have_posts() ) : the_post();
    $lp_s_url   = esc_url( get_permalink() );
    $lp_s_title = esc_js( get_the_title() );
?>
<!-- 스티키 기사 정보 바 + 열독률 진행바 -->
<div id="lp-sticky-bar" aria-hidden="true">
    <div class="lp-sb-inner <?php echo esc_attr( $container_class ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lp-sb-logo hidden sm:block">
            <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </a>
        <span class="lp-sb-sep hidden sm:block"></span>
        <div class="flex-1 min-w-0">
            <p class="lp-sb-title"><?php the_title(); ?></p>
            <p class="lp-sb-author"><?php
                $lp_sb_authors = lp_get_post_authors( get_the_ID() );
                $lp_sb_parts   = [];
                foreach ( $lp_sb_authors as $_a ) {
                    $lp_sb_parts[] = $_a['url']
                        ? '<a href="' . esc_url( $_a['url'] ) . '" style="color:inherit;text-decoration:none;font-weight:600;">' . esc_html( $_a['name'] ) . '</a>'
                        : '<span style="font-weight:600;">' . esc_html( $_a['name'] ) . '</span>';
                }
                echo implode( ', ', $lp_sb_parts );
            ?> 기자 · <?php echo get_the_date( 'Y.m.d' ); ?></p>
        </div>
        <div class="lp-font-ctrl hidden sm:flex" title="글자 크기 조절">
            <button class="lp-fz-sm" onclick="lpFontSize(-1)" id="lp-fz-small" title="글자 작게">가<sup style="font-size:.55em;vertical-align:super">−</sup></button>
            <button class="lp-fz-md" onclick="lpFontSize(0)"  id="lp-fz-mid"   title="기본 크기">가</button>
            <button class="lp-fz-lg" onclick="lpFontSize(1)"  id="lp-fz-large" title="글자 크게">가<sup style="font-size:.55em;vertical-align:super">+</sup></button>
        </div>
        <span class="lp-sb-share-sep hidden sm:block"></span>
        <div class="flex items-center gap-1">
            <button onclick="lpCopyUrl(this)" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="URL 복사"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg></button>
            <button onclick="lpShare('facebook')" class="p-1.5 bg-[#1877F2] rounded text-white hover:opacity-90 transition" title="페이스북"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></button>
            <button onclick="lpShare('twitter')" class="p-1.5 bg-[#000] rounded text-white hover:opacity-90 transition" title="X"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.638 5.903-5.638zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
            <button onclick="lpShare('kakao')" class="p-1.5 bg-[#FEE500] rounded text-[#000] hover:opacity-90 transition" title="카카오톡"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3C6.477 3 2 6.477 2 10.778c0 2.753 1.81 5.15 4.534 6.516-.178.617-.574 2.23-.657 2.576-.1.428.157.422.33.308.136-.09 2.157-1.454 3.027-2.04.25.035.504.053.766.053 5.52 0 10-3.477 10-7.778C20 6.477 17.52 3 12 3z"/></svg></button>
        </div>
    </div>
    <div id="lp-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
</div>

<main class="flex-grow <?php echo isset( $lp_main_class ) ? $lp_main_class : 'py-10 bg-slate-50'; ?>">
    <div class="<?php echo $container_class; ?> grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- 본문 -->
        <div class="lg:col-span-3" id="lp-article">
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'large', [ 'class' => 'w-full max-h-[420px] object-cover block' ] ); ?>
                <?php endif; ?>

                <div class="p-6 md:p-8">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <?php foreach ( get_the_category() as $lp_sc ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $lp_sc->term_id ) ); ?>"
                           class="text-xs font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded hover:bg-blue-100 transition">
                            <?php echo esc_html( $lp_sc->name ); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-slate-900 leading-tight tracking-tight mb-4">
                        <?php the_title(); ?>
                    </h1>
                    <header class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500 pb-4 mb-6 border-b-2 border-slate-900" id="lp-article-hdr">
                        <div class="flex flex-wrap items-center gap-3">
                        <?php
                        $lp_byline_authors = lp_get_post_authors( get_the_ID() );
                        $lp_byline_parts   = [];
                        foreach ( $lp_byline_authors as $_a ) {
                            $lp_byline_parts[] = $_a['url']
                                ? '<a href="' . esc_url( $_a['url'] ) . '" class="font-medium text-slate-700 hover:text-blue-600 transition">' . esc_html( $_a['name'] ) . '</a>'
                                : '<span class="font-medium text-slate-700">' . esc_html( $_a['name'] ) . '</span>';
                        }
                        echo implode( '<span class="text-slate-300">, </span>', $lp_byline_parts );
                        ?><span class="text-slate-500 text-sm">기자</span>
                            <span>·</span>
                            <span><?php echo get_the_date( 'Y.m.d H:i' ); ?></span>
                            <?php if ( get_the_modified_date( 'U' ) !== get_the_date( 'U' ) ) : ?>
                            <span>·</span>
                            <span class="text-blue-500"><?php echo get_the_modified_date( 'Y.m.d H:i' ); ?> 수정</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="lp-font-ctrl" title="글자 크기 조절">
                                <button class="lp-fz-sm" onclick="lpFontSize(-1)" id="lp-hdr-fz-small" title="글자 작게">가<sup style="font-size:.55em;vertical-align:super">−</sup></button>
                                <button class="lp-fz-md" onclick="lpFontSize(0)"  id="lp-hdr-fz-mid"   title="기본 크기">가</button>
                                <button class="lp-fz-lg" onclick="lpFontSize(1)"  id="lp-hdr-fz-large" title="글자 크게">가<sup style="font-size:.55em;vertical-align:super">+</sup></button>
                            </div>
                            <span class="w-px h-5 bg-slate-200"></span>
                            <button onclick="window.print()" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="프린트"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
                            <button onclick="lpCopyUrl(this)" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="URL 복사"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg></button>
                            <button onclick="lpShare('facebook')" class="p-1.5 bg-[#1877F2] rounded text-white hover:opacity-90 transition" title="페이스북"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></button>
                            <button onclick="lpShare('twitter')" class="p-1.5 bg-[#000] rounded text-white hover:opacity-90 transition" title="X"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.638 5.903-5.638zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
                            <button onclick="lpShare('kakao')" class="p-1.5 bg-[#FEE500] rounded text-[#000] hover:opacity-90 transition" title="카카오톡"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3C6.477 3 2 6.477 2 10.778c0 2.753 1.81 5.15 4.534 6.516-.178.617-.574 2.23-.657 2.576-.1.428.157.422.33.308.136-.09 2.157-1.454 3.027-2.04.25.035.504.053.766.053 5.52 0 10-3.477 10-7.824S17.523 3 12 3z"/></svg></button>
                        </div>
                    </header>

                    <div id="lp-article-body" class="text-slate-700 text-[1.0625rem] leading-[1.85] mb-8 break-words
                        [&_h2]:text-xl [&_h2]:font-black [&_h2]:text-slate-900 [&_h2]:mt-8
                        [&_h3]:text-lg [&_h3]:font-bold [&_h3]:mt-6
                        [&_img]:rounded-lg [&_img]:my-4 [&_img]:max-w-full
                        [&_a]:text-blue-600 [&_a]:underline
                        [&_blockquote]:border-l-4 [&_blockquote]:border-blue-500
                        [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-slate-500">
                        <?php the_content(); ?>
                    </div>

                    <!-- 태그 -->
                    <?php $lp_tags = get_the_tags(); ?>
                    <?php if ( $lp_tags ) : ?>
                    <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-100">
                        <?php foreach ( $lp_tags as $lp_t ) : ?>
                        <a href="<?php echo esc_url( get_tag_link( $lp_t->term_id ) ); ?>"
                           class="text-xs px-3 py-1 rounded-full border border-slate-200 text-slate-500
                                  hover:border-blue-400 hover:text-blue-600 transition">
                            #<?php echo esc_html( $lp_t->name ); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- 기자 프로필 서명 (복수 저자 지원) -->
                    <?php
                    $lp_bio_authors = lp_get_post_authors( get_the_ID(), 64, 'rounded-full border-2 border-slate-200 block' );
                    if ( $lp_bio_authors ) :
                        foreach ( $lp_bio_authors as $_ba ) :
                            if ( ! $_ba['name'] ) continue;
                    ?>
                    <div class="flex items-start gap-4 p-5 mt-8 mb-2 bg-slate-50 border border-slate-200 rounded-xl lp-author-bio">
                        <?php if ( $_ba['url'] ) : ?>
                        <a href="<?php echo esc_url( $_ba['url'] ); ?>" class="flex-shrink-0" aria-label="<?php echo esc_attr( $_ba['name'] ); ?> 기자 페이지">
                            <?php echo $_ba['avatar_html']; ?>
                        </a>
                        <?php else : ?>
                        <span class="flex-shrink-0"><?php echo $_ba['avatar_html']; ?></span>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="text-[0.65rem] font-bold text-blue-600 uppercase tracking-wider mb-0.5">기자 프로필</p>
                            <?php if ( $_ba['url'] ) : ?>
                            <a href="<?php echo esc_url( $_ba['url'] ); ?>" class="font-bold text-slate-800 hover:text-blue-600 transition text-base block leading-tight"><?php echo esc_html( $_ba['name'] ); ?></a>
                            <?php else : ?>
                            <span class="font-bold text-slate-800 text-base block leading-tight"><?php echo esc_html( $_ba['name'] ); ?></span>
                            <?php endif; ?>
                            <?php if ( $_ba['description'] ) : ?>
                            <p class="text-sm text-slate-500 mt-1 leading-relaxed"><?php echo esc_html( $_ba['description'] ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>

                    <!-- 이전 / 다음 -->
                    <?php
                    $lp_prev = get_previous_post();
                    $lp_next = get_next_post();
                    if ( $lp_prev || $lp_next ) :
                    ?>
                    <nav class="grid grid-cols-2 gap-3 mt-8 pt-6 border-t border-slate-200">
                        <?php if ( $lp_prev ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $lp_prev ) ); ?>"
                           class="flex flex-col gap-1 p-4 border border-slate-200 rounded-xl
                                  hover:border-blue-400 hover:bg-blue-50 transition group">
                            <span class="text-[0.68rem] text-blue-600 font-bold tracking-wide">← 이전 기사</span>
                            <span class="text-sm font-semibold text-slate-700 line-clamp-2 group-hover:text-blue-700">
                                <?php echo esc_html( get_the_title( $lp_prev ) ); ?>
                            </span>
                        </a>
                        <?php else : ?><span></span><?php endif; ?>
                        <?php if ( $lp_next ) : ?>
                        <a href="<?php echo esc_url( get_permalink( $lp_next ) ); ?>"
                           class="flex flex-col gap-1 p-4 border border-slate-200 rounded-xl text-right
                                  hover:border-blue-400 hover:bg-blue-50 transition group">
                            <span class="text-[0.68rem] text-blue-600 font-bold tracking-wide">다음 기사 →</span>
                            <span class="text-sm font-semibold text-slate-700 line-clamp-2 group-hover:text-blue-700">
                                <?php echo esc_html( get_the_title( $lp_next ) ); ?>
                            </span>
                        </a>
                        <?php endif; ?>
                    </nav>
                    <?php endif; ?>

                    <!-- 댓글 -->
                    <?php if ( comments_open() || get_comments_number() ) : ?>
                    <div class="mt-10 pt-6 border-t border-slate-200">
                        <?php comments_template(); ?>
                    </div>
                    <?php endif; ?>

                </div><!-- /card body -->
            </article>
        </div>

        <!-- 사이드바 -->
        <aside class="<?php echo isset( $lp_sidebar_class ) ? $lp_sidebar_class : ''; ?>">
            <!-- 많이 본 뉴스 -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">
                    많이 본 뉴스
                    <span class="ml-auto text-[10px] font-normal text-slate-400 tracking-wide">HOT</span>
                </h3>
                <?php
                $lp_hot_list = get_transient( 'lp_hot_news_v2' );
                if ( $lp_hot_list === false ) {
                    $gravity = 1.8; $now = time();
                    $pool = new WP_Query( [ 'post_type' => 'post', 'post_status' => 'publish',
                        'posts_per_page' => 200, 'date_query' => [ [ 'after' => '90 days ago' ] ],
                        'no_found_rows' => true, 'update_post_meta_cache' => true, 'update_post_term_cache' => false ] );
                    $scored = [];
                    foreach ( $pool->posts as $p ) {
                        $v = max( 1, (int) get_post_meta( $p->ID, 'lara_post_views', true ) );
                        $h = max( 0, ( $now - strtotime( $p->post_date_gmt ) ) / 3600 );
                        $scored[ $p->ID ] = ( $v - 1 ) / pow( $h + 2, $gravity );
                    }
                    wp_reset_postdata(); arsort( $scored );
                    $lp_hot_list = [];
                    foreach ( array_slice( array_keys( $scored ), 0, 5, true ) as $id ) {
                        $lp_hot_list[] = [ 'title' => get_the_title( $id ), 'url' => get_permalink( $id ),
                            'views' => number_format( (int) get_post_meta( $id, 'lara_post_views', true ) ) ];
                    }
                    set_transient( 'lp_hot_news_v2', $lp_hot_list, 10 * MINUTE_IN_SECONDS );
                }
                ?>
                <?php if ( $lp_hot_list ) : ?>
                <ol class="space-y-3 text-sm text-slate-600">
                    <?php foreach ( $lp_hot_list as $r => $item ) : ?>
                    <li class="flex items-start gap-2.5">
                        <span class="font-black text-base leading-none mt-0.5 w-4 flex-shrink-0 <?php echo $r===0?'text-blue-600':($r===1?'text-slate-500':'text-slate-400'); ?>"><?php echo $r+1; ?></span>
                        <div class="min-w-0">
                            <a href="<?php echo esc_url( $item['url'] ); ?>" class="font-medium text-slate-700 hover:text-blue-600 hover:underline line-clamp-2 leading-snug transition block"><?php echo esc_html( $item['title'] ); ?></a>
                            <span class="text-[11px] text-slate-400 mt-0.5 block">조회 <?php echo esc_html( $item['views'] ); ?>회</span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>
                <?php else : ?><p class="text-sm text-slate-400 text-center py-4">아직 데이터가 없습니다.</p><?php endif; ?>
            </div>

            <!-- 카테고리 -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4">카테고리</h3>
                <?php $s_cats = get_categories( [ 'hide_empty' => true, 'orderby' => 'name' ] ); ?>
                <?php if ( $s_cats ) : ?>
                <ul class="space-y-1.5 text-sm">
                    <?php foreach ( $s_cats as $sc ) : ?>
                    <li class="flex items-center justify-between gap-2">
                        <a href="<?php echo esc_url( get_category_link( $sc->term_id ) ); ?>" class="text-slate-600 hover:text-blue-600 transition truncate"><?php echo esc_html( $sc->name ); ?></a>
                        <span class="text-xs text-slate-400"><?php echo (int) $sc->count; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- 배너 -->
            <?php $lp_s_banner = get_theme_mod( 'lp_banner_side', '' ); ?>
            <?php if ( ! empty( $lp_s_banner ) ) : ?>
            <div class="overflow-hidden flex items-center justify-center"><?php echo $lp_s_banner; // phpcs:ignore ?></div>
            <?php else : ?>
            <div class="w-full h-[250px] bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-sm rounded">우측 배너 (300×250)</div>
            <?php endif; ?>
        </aside>

    </div>
</main>

<script>
(function () {
    var progressBar = document.getElementById('lp-progress-bar');
    var stickyBar   = document.getElementById('lp-sticky-bar');
    var article     = document.getElementById('lp-article');
    var articleBody = document.getElementById('lp-article-body');
    var articleHdr  = document.getElementById('lp-article-hdr');
    var gnav        = document.querySelector('nav');

    if (!stickyBar || !article) return;

    function getAdminBarH() { var ab = document.getElementById('wpadminbar'); return ab ? ab.getBoundingClientRect().height : 0; }
    function getNavH()      { return gnav ? getAdminBarH() + gnav.offsetHeight : getAdminBarH(); }
    function positionStickyBar() {
        stickyBar.style.top = stickyBar.classList.contains('is-visible') ? getAdminBarH() + 'px' : getNavH() + 'px';
    }
    positionStickyBar();
    window.addEventListener('resize', positionStickyBar, { passive: true });

    function onScroll() {
        var scrollY = window.scrollY || document.documentElement.scrollTop;
        var winH    = window.innerHeight;
        if (progressBar && articleBody) {
            var bodyTop = articleBody.getBoundingClientRect().top + scrollY;
            var bodyEnd = bodyTop + articleBody.offsetHeight - winH;
            var pct = bodyEnd > bodyTop ? Math.min(100, Math.max(0, (scrollY - bodyTop) / (bodyEnd - bodyTop) * 100)) : 0;
            progressBar.style.width = pct + '%';
            progressBar.setAttribute('aria-valuenow', Math.round(pct));
        }
        if (articleHdr) {
            var show = articleHdr.getBoundingClientRect().bottom < getNavH();
            stickyBar.classList.toggle('is-visible', show);
            stickyBar.setAttribute('aria-hidden', show ? 'false' : 'true');
            if (gnav) gnav.classList.toggle('lp-gnav-hidden', show);
            positionStickyBar();
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var FS_KEY = 'lp_font_size';
    var FS_LEVELS = [{ size: '0.9375rem', lh: '1.75' }, { size: '1.0625rem', lh: '1.85' }, { size: '1.25rem', lh: '1.9' }];
    var fsIdx = 1;
    try { var sv = parseInt(localStorage.getItem(FS_KEY), 10); if (!isNaN(sv) && sv >= 0 && sv <= 2) fsIdx = sv; } catch(e) {}

    function applyFontSize(idx) {
        fsIdx = Math.max(0, Math.min(2, idx));
        if (articleBody) { articleBody.style.fontSize = FS_LEVELS[fsIdx].size; articleBody.style.lineHeight = FS_LEVELS[fsIdx].lh; }
        ['lp-fz-small','lp-fz-mid','lp-fz-large','lp-hdr-fz-small','lp-hdr-fz-mid','lp-hdr-fz-large'].forEach(function(id, i) {
            var btn = document.getElementById(id); if (btn) btn.classList.toggle('lp-fz-active', (i % 3) === fsIdx);
        });
        try { localStorage.setItem(FS_KEY, String(fsIdx)); } catch(e) {}
    }
    window.lpFontSize = function(dir) { applyFontSize(dir === 0 ? 1 : fsIdx + dir); };
    applyFontSize(fsIdx);

    window.lpShare = function(platform) {
        var url = encodeURIComponent(location.href);
        var title = encodeURIComponent(document.title);
        if (platform === 'facebook') window.open('https://www.facebook.com/sharer/sharer.php?u=' + url, '_blank', 'width=620,height=440,noopener,noreferrer');
        else if (platform === 'twitter') window.open('https://twitter.com/intent/tweet?url=' + url + '&text=' + title, '_blank', 'width=620,height=440,noopener,noreferrer');
        else if (platform === 'kakao') {
            if (window.Kakao && window.Kakao.isInitialized && window.Kakao.isInitialized()) {
                window.Kakao.Link.sendDefault({ objectType:'feed', content:{ title:document.title, webUrl:location.href, mobileWebUrl:location.href, imageUrl:'', description:'' }, buttons:[{ title:'기사 보기', link:{ webUrl:location.href } }] });
            } else { navigator.clipboard.writeText(location.href).then(function(){ alert('카카오톡 SDK 미설치 환경입니다.\nURL이 복사되었습니다.'); }); }
        }
    };
    window.lpCopyUrl = function(btn) {
        var orig = btn ? btn.title : '';
        function done(){ if(btn){ btn.title='복사 완료!'; setTimeout(function(){ btn.title=orig; },1500); } alert('URL이 복사되었습니다.'); }
        if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(done).catch(function(){ var ta=document.createElement('textarea'); ta.value=location.href; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done(); });
        else { var ta=document.createElement('textarea'); ta.value=location.href; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done(); }
    };
})();
</script>

<?php endif; /* have_posts */

get_footer();
