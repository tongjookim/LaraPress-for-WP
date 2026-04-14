<?php
/**
 * LaraPress Archive Template
 * 카테고리·태그·작성자 아카이브 페이지 렌더링.
 * 글 목록 스타일: lp_archive_style (list | grid2 | grid3 | webzine)
 * 헤더/푸터는 header.php / footer.php 에서 공통 관리합니다.
 */

// ── 레이아웃 스킨 ───────────────────────────────────────────
extract( lp_skin_vars() );

// ── 아카이브 글 목록 스타일 ────────────────────────────────
$allowed_arc = [ 'list', 'grid2', 'grid3', 'webzine' ];
$saved_arc   = get_theme_mod( 'lp_archive_style', 'list' );
$arc_style   = in_array( $saved_arc, $allowed_arc, true ) ? $saved_arc : 'list';

// ── 아카이브 제목·설명 ─────────────────────────────────────
$arc_title      = get_the_archive_title();
$arc_desc       = get_the_archive_description();
$arc_post_count = null;
$arc_type_label = '';

if ( is_category() ) {
    $queried_obj    = get_queried_object();
    $arc_title      = $queried_obj ? $queried_obj->name : $arc_title;
    $arc_desc       = $queried_obj ? $queried_obj->description : '';
    $arc_post_count = $queried_obj ? (int) $queried_obj->count : null;
    $arc_type_label = '카테고리';
} elseif ( is_tag() ) {
    $queried_obj    = get_queried_object();
    $arc_title      = $queried_obj ? '#' . $queried_obj->name : $arc_title;
    $arc_desc       = $queried_obj ? $queried_obj->description : '';
    $arc_post_count = $queried_obj ? (int) $queried_obj->count : null;
    $arc_type_label = '태그';
} elseif ( is_author() ) {
    $queried_obj    = get_queried_object();
    $arc_title      = $queried_obj ? $queried_obj->display_name : $arc_title;
    $arc_desc       = $queried_obj
        ? (string) get_user_meta( $queried_obj->ID, 'lara_reporter_bio', true )
        : '';
    $arc_type_label = '기자';
} elseif ( is_date() ) {
    $arc_type_label = '날짜별';
}

// ── 아카이브 전용 CSS (wp_head에 추가) ─────────────────────
add_action( 'wp_head', function () {
    echo '<style>
        /* 리스트형 */
        .lp-arc-list-item { display: flex; gap: 1.25rem; padding: 1.25rem 0; }
        .lp-arc-list-thumb { flex-shrink: 0; width: 9rem; height: 6rem; overflow: hidden; border-radius: 0.5rem; background: #e2e8f0; }
        .lp-arc-list-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
        .lp-arc-list-item:hover .lp-arc-list-thumb img { transform: scale(1.05); }
        .lp-arc-list-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
        .lp-arc-list-title { font-weight: 700; font-size: 1rem; line-height: 1.4; color: #1e293b; transition: color 0.2s; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .lp-arc-list-item:hover .lp-arc-list-title { color: #1a73e8; }
        .nyt-skin .lp-arc-list-item:hover .lp-arc-list-title { color: #326891; }
        /* 그리드형 (2열·3열 공통) */
        .lp-arc-grid-thumb { width: 100%; overflow: hidden; border-radius: 0.75rem; background: #e2e8f0; aspect-ratio: 16/9; }
        .lp-arc-grid-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
        .lp-arc-grid article:hover .lp-arc-grid-thumb img { transform: scale(1.05); }
        .lp-arc-grid-title { font-weight: 700; color: #1e293b; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s; }
        .lp-arc-grid article:hover .lp-arc-grid-title { color: #1a73e8; }
        .nyt-skin .lp-arc-grid article:hover .lp-arc-grid-title { color: #326891; }
        /* 웹진형 */
        .lp-arc-webzine-thumb { width: 100%; overflow: hidden; border-radius: 1rem; background: #e2e8f0; aspect-ratio: 21/9; }
        .lp-arc-webzine-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .lp-arc-webzine article:hover .lp-arc-webzine-thumb img { transform: scale(1.03); }
        .lp-arc-webzine-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s; }
        .lp-arc-webzine article:hover .lp-arc-webzine-title { color: #1a73e8; }
        .nyt-skin .lp-arc-webzine-title { font-family: Georgia,"Times New Roman",serif; }
        .nyt-skin .lp-arc-webzine article:hover .lp-arc-webzine-title { color: #326891; }
        @media (max-width: 480px) {
            .lp-arc-list-thumb { width: 6rem; height: 4.5rem; }
        }
    </style>' . "\n";
}, 20 );

// ── 메인 콘텐츠 CSS 클래스 ─────────────────────────────────
if ( $current_theme_style === 'newyorktimes-style' ) {
    $lp_main_class    = 'py-6 bg-white';
    $lp_content_class = 'nyt-content-area';
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

            <!-- 아카이브 본문 -->
            <div class="lg:col-span-3 min-h-[500px] <?php echo $lp_content_class; ?>">

                <!-- 아카이브 헤더 -->
                <div class="mb-8 pb-5 border-b-2 <?php echo $current_theme_style === 'newyorktimes-style' ? 'border-black' : 'border-slate-900'; ?>">
                    <!-- 브레드크럼 -->
                    <nav class="text-xs text-slate-400 mb-2 flex items-center gap-1">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-blue-500 transition">홈</a>
                        <span>›</span>
                        <?php if ( $arc_type_label ) : ?>
                        <span><?php echo esc_html( $arc_type_label ); ?></span>
                        <span>›</span>
                        <?php endif; ?>
                        <span class="text-slate-600"><?php echo esc_html( $arc_title ); ?></span>
                    </nav>
                    <h1 class="text-3xl font-black <?php echo $current_theme_style === 'newyorktimes-style' ? 'font-serif' : ''; ?> text-slate-900 tracking-tight">
                        <?php echo esc_html( $arc_title ); ?>
                    </h1>
                    <?php if ( $arc_desc ) : ?>
                    <p class="text-slate-500 text-sm mt-2 leading-relaxed"><?php echo esc_html( $arc_desc ); ?></p>
                    <?php endif; ?>
                    <div class="flex items-center gap-3 mt-3">
                        <?php if ( $arc_post_count !== null ) : ?>
                        <span class="text-xs text-slate-400">총 <strong class="text-slate-600"><?php echo number_format( $arc_post_count ); ?></strong>개의 기사</span>
                        <span class="text-slate-300">|</span>
                        <?php endif; ?>
                        <!-- 현재 스타일 배지 -->
                        <span class="text-xs text-slate-400">
                            표시 형식:
                            <span class="font-semibold text-slate-600"><?php
                                $arc_style_labels = [ 'list' => '리스트형', 'grid2' => '2열 그리드', 'grid3' => '3열 그리드', 'webzine' => '웹진형' ];
                                echo esc_html( $arc_style_labels[ $arc_style ] ?? '리스트형' );
                            ?></span>
                            <span class="text-slate-300 ml-1">(외모 › 사용자 정의하기 › 사이드바 위젯 설정에서 변경)</span>
                        </span>
                    </div>
                </div>

                <?php if ( have_posts() ) : ?>

                    <!-- ╔══════════════════════════════════════════
                         ║  리스트형 — 왼쪽 이미지 + 오른쪽 텍스트
                         ╚══════════════════════════════════════════ -->
                    <?php if ( $arc_style === 'list' ) : ?>
                    <div class="divide-y divide-slate-100">
                        <?php while ( have_posts() ) : the_post(); ?>
                        <article class="lp-arc-list-item group">
                            <!-- 썸네일 -->
                            <a href="<?php the_permalink(); ?>" class="lp-arc-list-thumb block flex-shrink-0">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium', [ 'class' => 'w-full h-full' ] ); ?>
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">No Image</div>
                                <?php endif; ?>
                            </a>
                            <!-- 텍스트 -->
                            <div class="lp-arc-list-body">
                                <?php $lp_ac_cat = get_the_category(); if ( $lp_ac_cat ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $lp_ac_cat[0]->term_id ) ); ?>"
                                   class="text-[11px] font-bold text-blue-600 uppercase tracking-wide mb-1 hover:underline inline-block">
                                    <?php echo esc_html( $lp_ac_cat[0]->name ); ?>
                                </a>
                                <?php endif; ?>
                                <h2 class="lp-arc-list-title mb-1.5">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <p class="text-sm text-slate-500 leading-relaxed mb-2 line-clamp-2">
                                    <?php echo wp_trim_words( get_the_excerpt(), 22, '…' ); ?>
                                </p>
                                <div class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
                                    <span><?php echo get_the_date( 'Y.m.d' ); ?></span>
                                    <span>·</span>
                                    <span><?php the_author(); ?></span>
                                    <?php $lp_ac_views = (int) get_post_meta( get_the_ID(), 'lara_post_views', true ); if ( $lp_ac_views ) : ?>
                                    <span>·</span>
                                    <span>조회 <?php echo number_format( $lp_ac_views ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- ╔══════════════════════════════════════════
                         ║  그리드형 — 2열 또는 3열 카드
                         ╚══════════════════════════════════════════ -->
                    <?php elseif ( $arc_style === 'grid2' || $arc_style === 'grid3' ) :
                        $grid_cols = ( $arc_style === 'grid2' )
                            ? 'grid-cols-1 sm:grid-cols-2'
                            : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3';
                    ?>
                    <div class="lp-arc-grid grid <?php echo $grid_cols; ?> gap-6">
                        <?php while ( have_posts() ) : the_post(); ?>
                        <article class="group flex flex-col">
                            <!-- 썸네일 -->
                            <a href="<?php the_permalink(); ?>" class="lp-arc-grid-thumb block mb-3">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', [ 'class' => 'w-full h-full' ] ); ?>
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm">No Image</div>
                                <?php endif; ?>
                            </a>
                            <?php $lp_ac_cat = get_the_category(); if ( $lp_ac_cat ) : ?>
                            <a href="<?php echo esc_url( get_category_link( $lp_ac_cat[0]->term_id ) ); ?>"
                               class="text-[11px] font-bold text-blue-600 uppercase tracking-wide mb-1 hover:underline inline-block">
                                <?php echo esc_html( $lp_ac_cat[0]->name ); ?>
                            </a>
                            <?php endif; ?>
                            <h2 class="lp-arc-grid-title <?php echo $arc_style === 'grid3' ? 'text-sm' : 'text-base'; ?> mb-2">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="text-sm text-slate-500 leading-relaxed line-clamp-2 mb-auto">
                                <?php echo wp_trim_words( get_the_excerpt(), $arc_style === 'grid3' ? 15 : 20, '…' ); ?>
                            </p>
                            <div class="flex items-center gap-2 text-xs text-slate-400 mt-3">
                                <span><?php echo get_the_date( 'Y.m.d' ); ?></span>
                                <span>·</span>
                                <span><?php the_author(); ?></span>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- ╔══════════════════════════════════════════
                         ║  웹진형 — 큰 썸네일 + 강조 텍스트
                         ╚══════════════════════════════════════════ -->
                    <?php elseif ( $arc_style === 'webzine' ) : ?>
                    <div class="lp-arc-webzine space-y-12">
                        <?php while ( have_posts() ) : the_post(); ?>
                        <article class="group">
                            <!-- 대형 썸네일 (카테고리 배지 오버레이) -->
                            <a href="<?php the_permalink(); ?>" class="lp-arc-webzine-thumb block relative mb-5">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'large', [ 'class' => 'w-full h-full' ] ); ?>
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">No Image</div>
                                <?php endif; ?>
                                <?php $lp_ac_cat = get_the_category(); if ( $lp_ac_cat ) : ?>
                                <span class="absolute top-4 left-4 bg-blue-600 text-white text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                    <?php echo esc_html( $lp_ac_cat[0]->name ); ?>
                                </span>
                                <?php endif; ?>
                            </a>
                            <!-- 제목 -->
                            <h2 class="lp-arc-webzine-title mb-3">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <!-- 발췌 -->
                            <p class="text-slate-600 leading-relaxed line-clamp-3 text-base mb-4">
                                <?php echo wp_trim_words( get_the_excerpt(), 35, '…' ); ?>
                            </p>
                            <!-- 메타 -->
                            <div class="flex items-center gap-3 text-sm text-slate-400 border-t border-slate-100 pt-4">
                                <span><?php echo get_the_date( 'Y년 m월 d일' ); ?></span>
                                <span>·</span>
                                <span class="font-medium text-slate-600"><?php the_author(); ?></span>
                                <?php $lp_ac_views = (int) get_post_meta( get_the_ID(), 'lara_post_views', true ); if ( $lp_ac_views ) : ?>
                                <span>·</span>
                                <span>조회 <?php echo number_format( $lp_ac_views ); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>

                    <?php endif; // end arc_style switch ?>

                    <!-- ── 페이지네이션 ─────────────────────────── -->
                    <div class="lp-pagination">
                        <?php
                        echo paginate_links( [
                            'prev_text' => '‹ 이전',
                            'next_text' => '다음 ›',
                            'type'      => 'plain',   // <a>/<span> 나열 — .lp-pagination flex에 최적
                        ] );
                        ?>
                    </div>

                <?php else : ?>
                    <div class="text-center py-20 text-slate-400">
                        <p class="text-5xl mb-4">📭</p>
                        <p class="font-bold text-slate-600 text-lg mb-1">등록된 기사가 없습니다.</p>
                        <p class="text-sm">아직 이 <?php echo esc_html( $arc_type_label ?: '아카이브' ); ?>에 기사가 없습니다.</p>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
                           class="mt-6 inline-block text-sm font-medium text-blue-600 hover:underline">← 홈으로 돌아가기</a>
                    </div>
                <?php endif; ?>

            </div><!-- /본문 -->

            <!-- 우측 사이드바 -->
            <aside class="lg:col-span-1 space-y-6 <?php echo $lp_sidebar_class; ?>">

                <!-- 검색 -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">기사 검색</h3>
                    <form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex">
                        <input type="text" name="s" placeholder="검색어 입력..."
                               class="w-full border border-slate-300 rounded-l px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <button type="submit"
                                class="bg-slate-900 text-white px-4 rounded-r text-sm font-medium hover:bg-slate-800">검색</button>
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
                        <?php foreach ( $lp_sidebar_cats as $lp_sc ) :
                            $is_current = is_category( $lp_sc->term_id );
                        ?>
                        <li class="lp-cat-item">
                            <?php if ( $lp_show_bullet === '1' ) : ?>
                            <span class="lp-cat-bullet dashicons dashicons-arrow-right-alt2"></span>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_category_link( $lp_sc->term_id ) ); ?>"
                               class="lp-cat-link <?php echo $is_current ? 'font-bold text-blue-600' : ''; ?>">
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
