<?php
/**
 * LaraPress Search Template
 * 키워드 검색 결과 목록 페이지.
 * 출력 항목: 썸네일 / 카테고리 / 날짜 / 제목 / 내용 발췌(50~100자)
 * 검색어 하이라이트 지원.
 * 헤더/푸터는 header.php / footer.php 에서 공통 관리합니다.
 */

// ── 레이아웃 스킨 ───────────────────────────────────────────
extract( lp_skin_vars() );

// ── 검색 키워드 ────────────────────────────────────────────
$lp_search_query = trim( get_search_query() );
$lp_found_posts  = (int) $GLOBALS['wp_query']->found_posts;

/**
 * 검색 키워드를 <mark>로 하이라이트.
 * @param string $text     원본 텍스트 (이미 esc_html 처리된 것)
 * @param string $keyword  검색어
 * @return string          하이라이트된 HTML
 */
function lp_highlight( $text, $keyword ) {
    if ( empty( $keyword ) ) return $text;
    $escaped = preg_quote( esc_html( $keyword ), '/' );
    return preg_replace(
        '/(' . $escaped . ')/ui',
        '<mark class="lp-hl">$1</mark>',
        $text
    );
}

// ── 검색 전용 CSS (wp_head에 추가) ─────────────────────────
add_action( 'wp_head', function () {
    echo '<style>
        /* ── 검색 결과 리스트 ────────────────────────────── */
        .lp-sr-item { display: flex; gap: 1.25rem; padding: 1.25rem 0; border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
        .lp-sr-item:first-child { border-top: none; }
        .nyt-skin .lp-sr-item { border-bottom-color: #e5e5e5; }
        .lp-sr-thumb { flex-shrink: 0; width: 8rem; height: 5.5rem; overflow: hidden; border-radius: 0.5rem; background: #e2e8f0; }
        .nyt-skin .lp-sr-thumb { border-radius: 0; }
        .lp-sr-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; display: block; }
        .lp-sr-item:hover .lp-sr-thumb img { transform: scale(1.05); }
        .lp-sr-thumb-ph { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f1f5f9; }
        .lp-sr-thumb-ph svg { opacity: 0.35; }
        .lp-sr-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: 0.3rem; }
        .lp-sr-meta { display: flex; align-items: center; gap: 0.5rem; font-size: 0.7rem; color: #94a3b8; font-family: Arial, Helvetica, sans-serif; }
        .nyt-skin .lp-sr-meta { letter-spacing: 0.04em; }
        .lp-sr-cat { font-weight: 700; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.06em; color: #2563eb; text-decoration: none; }
        .lp-sr-cat:hover { text-decoration: underline; }
        .nyt-skin .lp-sr-cat { color: #326891; }
        .lp-sr-title { font-size: 1.0625rem; font-weight: 700; line-height: 1.4; color: #1e293b; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.18s; }
        .lp-sr-item:hover .lp-sr-title { color: #2563eb; }
        .nyt-skin .lp-sr-title { font-family: Georgia,"Times New Roman",serif; color: #121212; }
        .nyt-skin .lp-sr-item:hover .lp-sr-title { color: #326891; }
        .lp-sr-excerpt { font-size: 0.875rem; line-height: 1.65; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .nyt-skin .lp-sr-excerpt { font-family: Georgia,"Times New Roman",serif; color: #444; font-size: 0.9rem; }
        /* 키워드 하이라이트 */
        .lp-hl { background: #fef08a; color: #713f12; border-radius: 2px; padding: 0 1px; font-style: normal; }
        .nyt-skin .lp-hl { background: #fef9c3; color: #000; }
        /* 검색 헤더 바 */
        .lp-search-header { padding-bottom: 1.25rem; margin-bottom: 0.25rem; border-bottom: 3px solid #0f172a; }
        .nyt-skin .lp-search-header { border-bottom-color: #000; }
        .lp-search-kw { font-size: 1.75rem; font-weight: 900; color: #0f172a; line-height: 1.2; word-break: break-all; }
        .nyt-skin .lp-search-kw { font-family: Georgia,"Times New Roman",serif; }
        .lp-search-count { margin-top: 0.4rem; font-size: 0.8rem; color: #94a3b8; font-family: Arial, Helvetica, sans-serif; }
        /* 검색 폼 인라인 */
        .lp-search-form-inline { display: flex; margin-top: 1rem; max-width: 480px; }
        .lp-search-form-inline input { flex: 1; border: 1.5px solid #cbd5e1; border-right: none; padding: 0.45rem 0.75rem; font-size: 0.875rem; outline: none; transition: border-color 0.15s; border-radius: 0.375rem 0 0 0.375rem; background: #fff; color: #0f172a; }
        .lp-search-form-inline input:focus { border-color: #2563eb; }
        .lp-search-form-inline button { background: #0f172a; color: #fff; border: 1.5px solid #0f172a; padding: 0 1rem; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: background 0.15s; border-radius: 0 0.375rem 0.375rem 0; }
        .lp-search-form-inline button:hover { background: #1e293b; }
        .nyt-skin .lp-search-form-inline input { border-radius: 0; border-color: #000; font-family: Georgia,serif; }
        .nyt-skin .lp-search-form-inline input:focus { border-color: #000; }
        .nyt-skin .lp-search-form-inline button { border-radius: 0; background: #000; border-color: #000; }
        .nyt-skin .lp-search-form-inline button:hover { background: #333; }
        @media (max-width: 480px) {
            .lp-sr-thumb { width: 5.5rem; height: 4rem; }
            .lp-sr-title { font-size: 0.9375rem; }
        }
    </style>' . "\n";
}, 20 );

// ── 메인 콘텐츠 CSS 클래스 ─────────────────────────────────
if ( $current_theme_style === 'newyorktimes-style' ) {
    $lp_main_class    = 'py-6 bg-white';
    $lp_content_class = '';
    $lp_sidebar_class = 'nyt-sidebar lg:border-l lg:border-gray-200 lg:pl-6';
} elseif ( $current_theme_style === 'basic' ) {
    $lp_main_class    = 'py-8';
    $lp_content_class = 'basic-content-area';
    $lp_sidebar_class = 'basic-sidebar-area';
} else {
    $lp_main_class    = 'py-10 bg-slate-50';
    $lp_content_class = 'bg-white p-6 md:p-8 rounded-xl border border-slate-200 shadow-sm';
    $lp_sidebar_class = '';
}

get_header();
?>
    <main class="flex-grow <?php echo $lp_main_class; ?>">
        <div class="<?php echo $container_class; ?> grid grid-cols-1 lg:grid-cols-4 gap-8">

            <!-- 검색 결과 본문 -->
            <div class="lg:col-span-3 min-h-[500px] <?php echo $lp_content_class; ?>">

                <!-- 검색 헤더 -->
                <div class="lp-search-header">
                    <!-- 브레드크럼 -->
                    <nav class="text-xs text-slate-400 mb-2 flex items-center gap-1">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-blue-500 transition">홈</a>
                        <span>›</span>
                        <span class="text-slate-600">검색</span>
                    </nav>

                    <?php if ( $lp_search_query ) : ?>
                    <h1 class="lp-search-kw">
                        "<?php echo esc_html( $lp_search_query ); ?>" 검색 결과
                    </h1>
                    <p class="lp-search-count">
                        <?php if ( $lp_found_posts > 0 ) : ?>
                            총 <strong><?php echo number_format( $lp_found_posts ); ?></strong>건의 기사가 검색되었습니다.
                        <?php else : ?>
                            검색 결과가 없습니다.
                        <?php endif; ?>
                    </p>
                    <?php else : ?>
                    <h1 class="lp-search-kw">기사 검색</h1>
                    <?php endif; ?>

                    <!-- 검색어 재입력 폼 -->
                    <form class="lp-search-form-inline" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <input type="search" name="s"
                               value="<?php echo esc_attr( $lp_search_query ); ?>"
                               placeholder="다른 키워드로 검색…"
                               autocomplete="off">
                        <button type="submit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px">
                                <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>검색
                        </button>
                    </form>
                </div>

                <?php if ( have_posts() ) : ?>

                    <div class="divide-y divide-slate-100">
                    <?php while ( have_posts() ) : the_post();

                        /* ── 발췌문 생성 (50~100자 목표) ──────────────────
                           wp_trim_words(20단어)는 영어 기준이므로
                           한국어는 mb_strimwidth로 100자(+…) 강제 절단. */
                        $lp_raw_excerpt = get_the_excerpt();
                        if ( empty( $lp_raw_excerpt ) ) {
                            $lp_raw_excerpt = wp_strip_all_tags( get_the_content() );
                        }
                        $lp_excerpt = mb_strimwidth( wp_strip_all_tags( $lp_raw_excerpt ), 0, 100, '…' );

                        /* ── 카테고리 ─────────────────────────── */
                        $lp_cats = get_the_category();

                    ?>
                    <article class="lp-sr-item group">

                        <!-- 썸네일 -->
                        <a href="<?php the_permalink(); ?>" class="lp-sr-thumb block flex-shrink-0" tabindex="-1" aria-hidden="true">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', [ 'class' => 'w-full h-full', 'alt' => esc_attr( get_the_title() ) ] ); ?>
                            <?php else : ?>
                                <div class="lp-sr-thumb-ph">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </a>

                        <!-- 텍스트 -->
                        <div class="lp-sr-body">

                            <!-- 카테고리 + 날짜 메타 -->
                            <div class="lp-sr-meta">
                                <?php if ( $lp_cats ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $lp_cats[0]->term_id ) ); ?>"
                                   class="lp-sr-cat"><?php echo esc_html( $lp_cats[0]->name ); ?></a>
                                <span>·</span>
                                <?php endif; ?>
                                <time datetime="<?php echo get_the_date( 'c' ); ?>">
                                    <?php echo get_the_date( 'Y.m.d' ); ?>
                                </time>
                                <span>·</span>
                                <span><?php the_author(); ?></span>
                            </div>

                            <!-- 제목 (키워드 하이라이트) -->
                            <a href="<?php the_permalink(); ?>" class="lp-sr-title">
                                <?php echo lp_highlight( esc_html( get_the_title() ), $lp_search_query ); ?>
                            </a>

                            <!-- 발췌문 (키워드 하이라이트) -->
                            <p class="lp-sr-excerpt">
                                <?php echo lp_highlight( esc_html( $lp_excerpt ), $lp_search_query ); ?>
                            </p>

                        </div>
                    </article>
                    <?php endwhile; ?>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="lp-pagination">
                        <?php
                        echo paginate_links( [
                            'type'      => 'plain',
                            'prev_text' => '‹',
                            'next_text' => '›',
                            'before_page_number' => '',
                        ] );
                        ?>
                    </div>

                <?php else : ?>

                    <!-- 검색 결과 없음 -->
                    <div class="text-center py-16 text-slate-400">
                        <svg class="mx-auto mb-5 opacity-25" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <p class="font-bold text-slate-600 text-lg mb-2">
                            "<?php echo esc_html( $lp_search_query ); ?>"에 대한 검색 결과가 없습니다.
                        </p>
                        <p class="text-sm mb-6 leading-relaxed">
                            다른 키워드로 검색하거나, 철자를 확인해 주세요.
                        </p>
                        <div class="flex justify-center gap-3 flex-wrap">
                            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                               class="inline-block text-sm font-medium <?php echo $current_theme_style === 'newyorktimes-style' ? 'border border-black text-black px-5 py-2 hover:bg-black hover:text-white transition' : 'bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition'; ?>">
                                ← 홈으로 돌아가기
                            </a>
                        </div>
                    </div>

                <?php endif; ?>

            </div><!-- /본문 -->

            <!-- 우측 사이드바 -->
            <aside class="lg:col-span-1 space-y-6 <?php echo $lp_sidebar_class; ?>">

                <!-- 검색 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4">기사 검색</h3>
                    <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex">
                        <input type="text" name="s" value="<?php echo esc_attr( $lp_search_query ); ?>" placeholder="검색어 입력..."
                               class="w-full border border-slate-300 rounded-l px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <button type="submit" class="bg-slate-900 text-white px-4 rounded-r text-sm font-medium hover:bg-slate-800">검색</button>
                    </form>
                </div>

                <!-- 카테고리 목록 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4">카테고리</h3>
                    <?php
                    $lp_show_bullet  = get_theme_mod( 'lp_cat_show_bullet', '1' );
                    $lp_sidebar_cats = get_categories( [
                        'hide_empty' => false,
                        'exclude'    => get_option( 'default_category' ),
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                    ] );
                    if ( ! empty( $lp_sidebar_cats ) ) : ?>
                    <ul class="lp-cat-list">
                        <?php foreach ( $lp_sidebar_cats as $lp_sc ) : ?>
                        <li class="lp-cat-item">
                            <?php if ( $lp_show_bullet === '1' ) : ?>
                            <span class="lp-cat-bullet dashicons dashicons-arrow-right-alt2"></span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_category_link( $lp_sc->term_id ) ); ?>" class="lp-cat-link">
                                <span class="lp-cat-name"><?php echo esc_html( $lp_sc->name ); ?></span>
                                <span class="lp-cat-count"><?php echo (int) $lp_sc->count; ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else : ?>
                    <p class="text-sm text-slate-400 text-center py-2">카테고리가 없습니다.</p>
                    <?php endif; ?>
                </div>

                <!-- 많이 본 뉴스 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">
                        많이 본 뉴스
                        <span class="ml-auto text-[10px] font-normal text-slate-400 tracking-wide">HOT</span>
                    </h3>
                    <?php
                    $lp_hot_cache_key = 'lp_hot_news_v2';
                    $lp_hot_list      = get_transient( $lp_hot_cache_key );
                    if ( $lp_hot_list === false ) {
                        $lp_gravity = 1.8;
                        $lp_now     = time();
                        $lp_pool_q  = new WP_Query( [
                            'post_type'              => 'post',
                            'post_status'            => 'publish',
                            'posts_per_page'         => 200,
                            'date_query'             => [ [ 'after' => '90 days ago' ] ],
                            'no_found_rows'          => true,
                            'update_post_meta_cache' => true,
                            'update_post_term_cache' => false,
                        ] );
                        $lp_scored = [];
                        foreach ( $lp_pool_q->posts as $lp_p ) {
                            $lp_views   = max( 1, (int) get_post_meta( $lp_p->ID, 'lara_post_views', true ) );
                            $lp_hours   = max( 0, ( $lp_now - strtotime( $lp_p->post_date_gmt ) ) / 3600 );
                            $lp_scored[ $lp_p->ID ] = ( $lp_views - 1 ) / pow( $lp_hours + 2, $lp_gravity );
                        }
                        wp_reset_postdata();
                        arsort( $lp_scored );
                        $lp_hot_list = [];
                        foreach ( array_slice( array_keys( $lp_scored ), 0, 5, true ) as $lp_id ) {
                            $lp_hot_list[] = [
                                'title' => get_the_title( $lp_id ),
                                'url'   => get_permalink( $lp_id ),
                                'views' => number_format( (int) get_post_meta( $lp_id, 'lara_post_views', true ) ),
                            ];
                        }
                        set_transient( $lp_hot_cache_key, $lp_hot_list, 10 * MINUTE_IN_SECONDS );
                    }
                    ?>
                    <?php if ( $lp_hot_list ) : ?>
                    <ol class="space-y-3 text-sm text-slate-600">
                        <?php foreach ( $lp_hot_list as $lp_rank => $lp_item ) : ?>
                        <li class="flex items-start gap-2.5">
                            <span class="font-black text-base leading-none mt-0.5 w-4 flex-shrink-0 <?php echo $lp_rank === 0 ? 'text-blue-600' : ( $lp_rank === 1 ? 'text-slate-500' : 'text-slate-400' ); ?>">
                                <?php echo $lp_rank + 1; ?>
                            </span>
                            <div class="min-w-0">
                                <a href="<?php echo esc_url( $lp_item['url'] ); ?>"
                                   class="font-medium text-slate-700 hover:text-blue-600 hover:underline line-clamp-2 leading-snug transition block">
                                    <?php echo esc_html( $lp_item['title'] ); ?>
                                </a>
                                <span class="text-[11px] text-slate-400 mt-0.5 block">조회 <?php echo esc_html( $lp_item['views'] ); ?>회</span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <?php else : ?>
                    <p class="text-sm text-slate-400 text-center py-4">아직 데이터가 없습니다.</p>
                    <?php endif; ?>
                </div>

                <!-- 우측 배너 -->
                <?php $lp_banner_side = get_theme_mod( 'lp_banner_side', '' ); ?>
                <?php if ( ! empty( $lp_banner_side ) ) : ?>
                <div class="w-full overflow-hidden flex items-center justify-center">
                    <?php echo $lp_banner_side; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <?php else : ?>
                <div class="w-full h-[250px] bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-sm rounded">
                    우측 배너 (300×250)
                </div>
                <?php endif; ?>

            </aside>
        </div>
    </main>

<?php get_footer(); ?>
