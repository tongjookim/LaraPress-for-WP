<?php
/**
 * LaraPress 기본 레이아웃 (WP Menu, Customizer 연동 및 뉴스 포털 메인 위젯 적용)
 * 레이아웃 스킨은 외모 > 사용자 정의하기 > 레이아웃 스킨 설정에서 선택·저장합니다.
 * 헤더/푸터는 header.php / footer.php 에서 공통 관리합니다.
 */

extract( lp_skin_vars() );

// 메인 콘텐츠 CSS 클래스 (스킨별 분기)
if ( $current_theme_style === 'newyorktimes-style' ) {
    $lp_main_class    = 'py-6 bg-white';
    $lp_content_class = 'nyt-content-area';
    $lp_sidebar_class = 'nyt-sidebar lg:border-l lg:border-gray-200 lg:pl-6';
} elseif ( $current_theme_style === 'basic' ) {
    $lp_main_class    = 'py-8';
    $lp_content_class = 'basic-content-area';
    $lp_sidebar_class = 'basic-sidebar-area';
} elseif ( $current_theme_style === 'amber-journal' ) {
    // Amber Journal은 자체 레이아웃을 그린 뒤 바로 get_footer() 호출
} else {
    $lp_main_class    = 'py-10 bg-slate-50';
    $lp_content_class = 'bg-white p-6 md:p-8 rounded-xl border border-slate-200 shadow-sm';
    $lp_sidebar_class = '';
}

get_header();

/* ════════════════════════════════════════════════════════
   엠버 저널 전용 레이아웃 — 헤더/푸터 사이에 완전히 독립 출력
   ════════════════════════════════════════════════════════ */
if ( $current_theme_style === 'amber-journal' ) : ?>

<main class="aj-main flex-grow">
    <div class="<?php echo $container_class; ?>">
        <?php
        /* ── 전면 기사 그리드 ── */
        $aj_feat_enable     = get_theme_mod( 'lp_aj_feat_enable',     '1' );
        $aj_feat_count      = (int) get_theme_mod( 'lp_aj_feat_count', '5' );
        $aj_feat_show_thumb = get_theme_mod( 'lp_aj_feat_show_thumb', '1' ) !== '0';
        $aj_feat_exc_len    = max( 50, (int) get_theme_mod( 'lp_aj_feat_excerpt_len', '150' ) );
        if ( ! in_array( $aj_feat_count, [ 3, 4, 5 ], true ) ) { $aj_feat_count = 5; }

        if ( ( is_front_page() || is_home() ) && $aj_feat_enable !== '0' ) :
            $aj_feat_q = new WP_Query( [
                'posts_per_page'      => $aj_feat_count,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'ignore_sticky_posts' => true,
                'orderby'             => 'date',
                'order'               => 'DESC',
            ] );
            $aj_feat_posts = $aj_feat_q->posts;
            wp_reset_postdata();

            if ( count( $aj_feat_posts ) >= 3 ) :
                /* 센터: [0], 좌: [1..], 우: 뒷부분 */
                $aj_fc = $aj_feat_posts[0];
                if ( $aj_feat_count === 4 ) {
                    $aj_fl = array_slice( $aj_feat_posts, 1, 2 );
                    $aj_fr = array_slice( $aj_feat_posts, 3, 1 );
                } elseif ( $aj_feat_count === 3 ) {
                    $aj_fl = array_slice( $aj_feat_posts, 1, 1 );
                    $aj_fr = array_slice( $aj_feat_posts, 2, 1 );
                } else { // 5
                    $aj_fl = array_slice( $aj_feat_posts, 1, 2 );
                    $aj_fr = array_slice( $aj_feat_posts, 3, 2 );
                }

                /* 센터 카드 요약문 */
                $aj_fc_excerpt = '';
                if ( $aj_fc->post_excerpt ) {
                    $aj_fc_excerpt = $aj_fc->post_excerpt;
                } else {
                    $aj_fc_raw = wp_strip_all_tags( $aj_fc->post_content );
                    if ( mb_strlen( $aj_fc_raw ) > $aj_feat_exc_len ) {
                        $aj_fc_excerpt = mb_substr( $aj_fc_raw, 0, $aj_feat_exc_len ) . '…';
                    } else {
                        $aj_fc_excerpt = $aj_fc_raw;
                    }
                }

                /* 헬퍼 — 소형 카드 */
                $aj_render_sm = function( $p ) use ( $aj_feat_show_thumb ) { ?>
                    <article class="aj-feat-sm">
                        <?php if ( $aj_feat_show_thumb && has_post_thumbnail( $p ) ) :
                            echo get_the_post_thumbnail( $p, [ 480, 270 ], [ 'class' => 'aj-feat-sm-thumb', 'loading' => 'eager' ] );
                        endif;
                        $sm_cats = get_the_category( $p->ID );
                        if ( $sm_cats ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $sm_cats[0]->term_id ) ); ?>" class="aj-feat-sm-cat">
                            <?php echo esc_html( $sm_cats[0]->name ); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_permalink( $p ) ); ?>" class="aj-feat-sm-title">
                            <?php echo esc_html( get_the_title( $p ) ); ?>
                        </a>
                        <p class="aj-feat-sm-date"><?php echo esc_html( get_the_date( 'Y.m.d', $p ) ); ?></p>
                    </article>
                <?php };
        ?>
        <section class="aj-featured-section">
            <div class="aj-featured-grid">

                <!-- 좌측 -->
                <div class="aj-featured-side">
                    <?php foreach ( $aj_fl as $aj_fp ) { $aj_render_sm( $aj_fp ); } ?>
                </div>

                <!-- 센터 메인 카드 -->
                <div class="aj-feat-center">
                    <?php
                    $fc_cats = get_the_category( $aj_fc->ID );
                    $fc_cat  = $fc_cats ? $fc_cats[0] : null;
                    ?>
                    <article>
                        <?php if ( $aj_feat_show_thumb && has_post_thumbnail( $aj_fc ) ) :
                            echo get_the_post_thumbnail( $aj_fc, [ 720, 480 ], [ 'class' => 'aj-feat-main-thumb', 'loading' => 'eager' ] );
                        endif; ?>
                        <?php if ( $fc_cat ) : ?>
                        <a href="<?php echo esc_url( get_category_link( $fc_cat->term_id ) ); ?>" class="aj-feat-main-cat">
                            <?php echo esc_html( $fc_cat->name ); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_permalink( $aj_fc ) ); ?>" class="aj-feat-main-title">
                            <?php echo esc_html( get_the_title( $aj_fc ) ); ?>
                        </a>
                        <?php if ( $aj_fc_excerpt ) : ?>
                        <p class="aj-feat-main-excerpt"><?php echo esc_html( $aj_fc_excerpt ); ?></p>
                        <?php endif; ?>
                        <p class="aj-feat-main-date"><?php echo esc_html( get_the_date( 'Y.m.d', $aj_fc ) ); ?></p>
                    </article>
                </div>

                <!-- 우측 -->
                <div class="aj-featured-side">
                    <?php foreach ( $aj_fr as $aj_fp ) { $aj_render_sm( $aj_fp ); } ?>
                </div>

            </div>
        </section>
        <?php
            endif; // count >= 3
        endif; // is_front_page/home && feat_enable
        ?>

        <div class="aj-layout">

            <!-- ═══ 메인 컬럼 ═══ -->
            <div>
            <?php if ( is_front_page() || is_home() ) :
                /* ── 홈: 테마 공통 홈 위젯 렌더링 ── */
                $lp_grid_map = [
                    1 => 'grid-cols-1',
                    2 => 'grid-cols-1 md:grid-cols-2',
                    3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
                ];
                $lp_span_map = [
                    1 => '',
                    2 => 'md:col-span-2',
                    3 => 'md:col-span-2 lg:col-span-3',
                ];
                $lp_cols_default_span = [ 1 => 6, 2 => 3, 3 => 2 ];
                $lp_widgets = lp_parse_home_widgets();
            ?>
                <div class="aj-widget-block">
                <div class="space-y-10">
                <?php foreach ( $lp_widgets as $lp_w ) :
                    if ( $lp_w['type'] === 'section' ) :
                        $sec_cols  = (int) ( $lp_w['cols'] ?? 2 );
                        $sec_title = (string) ( $lp_w['title'] ?? '' );
                        $sec_items = $lp_w['items'] ?? [];
                        $sec_def_span = $lp_cols_default_span[ $sec_cols ] ?? 3;
                ?>
                    <div>
                        <?php if ( ! empty( $sec_title ) ) : ?>
                        <h2 class="text-xl font-bold mb-4 border-b border-slate-200 pb-2">
                            <span class="text-blue-700"><?php echo esc_html( $sec_title ); ?></span>
                        </h2>
                        <?php endif; ?>
                        <div class="grid grid-cols-6 gap-x-8 gap-y-10 items-start">
                            <?php foreach ( $sec_items as $sec_w ) :
                                $sw_span = lp_width_to_span( $sec_w['width'] ?? '', $sec_def_span );
                            ?>
                            <div class="col-span-6 md:col-span-<?php echo (int) $sw_span; ?>">
                                <?php lp_render_widget_body( $sec_w, $lp_grid_map, $lp_span_map ); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div><?php lp_render_widget_body( $lp_w, $lp_grid_map, $lp_span_map ); ?></div>
                <?php endif; endforeach; ?>
                </div>
                </div><!-- /.aj-widget-block -->

            <?php else :
                /* ── 카테고리·태그·검색: AJ 기사 카드 리스트 ── */
                $aj_paged  = max( 1, get_query_var( 'paged' ) );
                $aj_q_args = [
                    'post_status'    => 'publish',
                    'posts_per_page' => 12,
                    'paged'          => $aj_paged,
                ];
                if ( is_category() ) {
                    $aj_q_args['cat'] = get_queried_object_id();
                } elseif ( is_tag() ) {
                    $aj_q_args['tag_id'] = get_queried_object_id();
                } elseif ( is_search() ) {
                    $aj_q_args['s'] = get_search_query();
                }
                $aj_q = new WP_Query( $aj_q_args );
            ?>
                <div class="aj-article-list">
                <?php
                if ( $aj_q->have_posts() ) :
                    while ( $aj_q->have_posts() ) : $aj_q->the_post();
                        $aj_cats    = get_the_category();
                        $aj_cat     = $aj_cats ? $aj_cats[0] : null;
                        $aj_excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( get_the_content(), 30 );
                        $aj_author  = get_the_author();
                        $aj_date    = get_the_date( 'Y.m.d' );
                ?>
                    <article class="aj-article-card">
                        <div class="aj-card-body">
                            <div class="aj-card-meta">
                                <?php if ( $aj_cat ) : ?>
                                <a href="<?php echo esc_url( get_category_link( $aj_cat->term_id ) ); ?>" class="aj-cat-tag">
                                    <?php echo esc_html( $aj_cat->name ); ?>
                                </a>
                                <?php endif; ?>
                                <span class="aj-card-byline"><?php echo esc_html( $aj_author ); ?> &middot; <?php echo esc_html( $aj_date ); ?></span>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="aj-card-title"><?php the_title(); ?></a>
                            <p class="aj-card-excerpt"><?php echo esc_html( $aj_excerpt ); ?></p>
                        </div>
                        <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" class="aj-card-thumb-wrap" tabindex="-1" aria-hidden="true">
                            <?php the_post_thumbnail( 'medium', [ 'class' => 'aj-card-thumb' ] ); ?>
                        </a>
                        <?php else : ?>
                        <span class="aj-card-thumb" aria-hidden="true"></span>
                        <?php endif; ?>
                    </article>
                <?php
                    endwhile;
                else :
                ?>
                    <p style="padding:2rem 1.25rem;color:#9ca3af;text-align:center;">발행된 기사가 없습니다.</p>
                <?php
                endif;
                wp_reset_postdata();
                ?>
                </div>

                <!-- 페이지네이션 -->
                <?php if ( isset( $aj_q ) && $aj_q->max_num_pages > 1 ) : ?>
                <div class="lp-pagination" style="padding:1.5rem 0;">
                    <?php
                    echo paginate_links( [
                        'total'     => $aj_q->max_num_pages,
                        'current'   => $aj_paged,
                        'format'    => '?paged=%#%',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ] );
                    ?>
                </div>
                <?php endif; ?>

            <?php endif; /* home vs category/tag/search */ ?>
            </div>

            <!-- ═══ 사이드바 (오피니언 + 위젯) ═══ -->
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

                <!-- 추천 기사 (둥근 썸네일) 위젯 -->
                <?php
                $aj_picks_custom  = get_theme_mod( 'lp_aj_picks_title', '' );
                $aj_picks_cat_label = get_theme_mod( 'lp_aj_picks_cat', '' )
                    ? get_category_by_slug( get_theme_mod( 'lp_aj_picks_cat', '' ) )
                    : null;
                $aj_picks_title = $aj_picks_custom
                    ? esc_html( $aj_picks_custom )
                    : ( $aj_picks_cat_label ? esc_html( $aj_picks_cat_label->name ) . ' 추천' : '추천 기사' );
                ?>
                <div class="aj-widget">
                    <div class="aj-widget-head">
                        <span class="aj-widget-mark"></span>
                        <h2 class="aj-widget-title"><?php echo $aj_picks_title; ?></h2>
                    </div>
                    <?php echo lp_aj_picks_html(); ?>
                </div>

                <!-- 사이드 배너 -->
                <?php $aj_banner_side = get_theme_mod( 'lp_banner_side', '' ); ?>
                <?php if ( $aj_banner_side ) : ?>
                <div style="overflow:hidden;"><?php echo $aj_banner_side; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                <?php endif; ?>

            </aside>

        </div><!-- /.aj-layout -->
    </div><!-- /.container -->
</main>

<?php get_footer(); return; ?>
<?php else : /* 나머지 스킨 — 기존 레이아웃 */ ?>
    <main class="flex-grow <?php echo $lp_main_class; ?>">
        <div class="<?php echo $container_class; ?> grid grid-cols-1 lg:grid-cols-4 gap-8">

            <div class="lg:col-span-3 min-h-[500px] <?php echo $lp_content_class; ?>">
                
                <?php if (is_front_page() || is_home()) : ?>
                    <!-- 프론트 페이지 뉴스 위젯 (Breaking News) -->
                    <div class="mb-12">
                        <h2 class="text-2xl font-black mb-6 border-b-2 border-slate-900 pb-3 flex items-center gap-2 lp-section-head">
                            <span class="w-2 h-6 bg-red-600 inline-block"></span> Breaking News
                        </h2>
                        <?php
                        $top_query = new WP_Query(['posts_per_page' => 3, 'ignore_sticky_posts' => 1]);
                        if ($top_query->have_posts()) :
                            echo '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
                            $count = 0;
                            while ($top_query->have_posts()) : $top_query->the_post();
                                $count++;
                                if ($count === 1) {
                                    echo '<div class="md:row-span-2 group">';
                                    if(has_post_thumbnail()) {
                                        echo '<a href="' . get_permalink() . '" class="block overflow-hidden rounded-xl mb-4" style="height:18rem;">';
                                        the_post_thumbnail('large', ['class' => 'lp-bn-thumb-main', 'style' => 'width:100%;height:100%;object-fit:cover;display:block;']);
                                        echo '</a>';
                                    } else {
                                        echo '<div class="w-full bg-slate-200 rounded-xl mb-4 flex items-center justify-center text-slate-400" style="height:18rem;">No Image</div>';
                                    }
                                    echo '<h3 class="text-3xl font-bold group-hover:text-blue-600 transition tracking-tight leading-tight mb-3"><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';
                                    echo '<p class="text-slate-600 line-clamp-3 leading-relaxed">'.get_the_excerpt().'</p>';
                                    echo '</div>';

                                    echo '<div class="flex flex-col gap-6 justify-between">';
                                } else {
                                    echo '<div class="flex gap-4 group">';
                                    if(has_post_thumbnail()) {
                                        echo '<a href="' . get_permalink() . '" class="flex-shrink-0 overflow-hidden rounded-lg" style="width:8rem;height:6rem;">';
                                        the_post_thumbnail('medium', ['style' => 'width:100%;height:100%;object-fit:cover;display:block;']);
                                        echo '</a>';
                                    } else {
                                        echo '<div class="flex-shrink-0 bg-slate-200 rounded-lg flex items-center justify-center text-slate-400 text-xs" style="width:8rem;height:6rem;">No Image</div>';
                                    }
                                    echo '<div>';
                                    echo '<h3 class="text-lg font-bold group-hover:text-blue-600 transition line-clamp-2 leading-snug"><a href="'.get_permalink().'">'.get_the_title().'</a></h3>';
                                    echo '<span class="text-xs text-slate-400 mt-2 block">'.get_the_date('Y.m.d').'</span>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            endwhile;
                            if ($count >= 1) echo '</div>'; // 우측 컬럼 종료
                            echo '</div>';
                        else:
                            echo '<p class="text-slate-500 py-10 text-center bg-slate-50 rounded-xl">발행된 기사가 없습니다.</p>';
                        endif; wp_reset_postdata();
                        ?>
                    </div>

                    <!-- 홈 화면 위젯 (Customizer → 홈 화면 위젯 설정) -->
                    <?php
                    $lp_grid_map = [
                        1 => 'grid-cols-1',
                        2 => 'grid-cols-1 md:grid-cols-2',
                        3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
                    ];
                    $lp_span_map = [
                        1 => '',
                        2 => 'md:col-span-2',
                        3 => 'md:col-span-2 lg:col-span-3',
                    ];

                    /* ── 단일 위젯 본문 렌더 헬퍼 (functions.php로 이전, 하위 호환 유지) ── */
                    if ( ! function_exists( 'lp_render_widget_body' ) ) :
                    function lp_render_widget_body( $w, $grid_map, $span_map ) {
                        $wcat   = (int) ( $w['cat']   ?? 0 );
                        $wcols  = max( 1, min( 3, (int) ( $w['cols'] ?? 2 ) ) );
                        $wtitle = (string) ( $w['title'] ?? '' );
                        $wgrid  = $grid_map[ $wcols ] ?? 'grid-cols-1 md:grid-cols-2';
                        $wspan  = $span_map[ $wcols ] ?? 'md:col-span-2';

                        if ( ! empty( $wtitle ) ) : ?>
                            <h2 class="text-xl font-bold mb-4 border-b border-slate-200 pb-2">
                                <span class="text-blue-700"><?php echo esc_html( $wtitle ); ?></span>
                            </h2>
                        <?php endif;

                        if ( $w['type'] === 'gallery' ) :
                            /* 갤러리형 */
                            $gcount = [ 1 => 6, 2 => 6, 3 => 9 ][ $wcols ] ?? 6;
                            $gargs  = [
                                'post_type' => 'post', 'post_status' => 'publish',
                                'posts_per_page' => $gcount,
                                'no_found_rows' => true, 'ignore_sticky_posts' => true,
                            ];
                            if ( $wcat > 0 ) $gargs['cat'] = $wcat;
                            $gq = new WP_Query( $gargs );
                            ?>
                            <div class="grid <?php echo esc_attr( $wgrid ); ?> gap-6">
                                <?php if ( $gq->have_posts() ) :
                                    while ( $gq->have_posts() ) : $gq->the_post();
                                        $gc  = get_the_category();
                                        $gc  = ! empty( $gc ) ? $gc[0] : null;
                                        $gth = get_the_post_thumbnail_url( null, 'medium_large' ); ?>
                                    <article class="group flex flex-col">
                                        <a href="<?php the_permalink(); ?>" class="block overflow-hidden rounded-xl mb-3 bg-slate-100 aspect-video flex-shrink-0">
                                            <?php if ( $gth ) : ?>
                                                <img src="<?php echo esc_url( $gth ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <?php else : ?>
                                                <div class="w-full h-full flex items-center justify-center"><svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                            <?php endif; ?>
                                        </a>
                                        <div class="flex-1 flex flex-col">
                                            <?php if ( $gc ) : ?><a href="<?php echo esc_url( get_category_link( $gc->term_id ) ); ?>" class="text-[11px] font-bold text-blue-600 uppercase tracking-wide mb-1 hover:underline"><?php echo esc_html( $gc->name ); ?></a><?php endif; ?>
                                            <h3 class="font-bold text-slate-800 leading-snug line-clamp-2 group-hover:text-blue-600 transition mb-auto"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <p class="text-xs text-slate-400 mt-2"><?php echo get_the_date( 'Y.m.d' ); ?></p>
                                        </div>
                                    </article>
                                <?php endwhile; wp_reset_postdata();
                                else : ?>
                                    <p class="<?php echo esc_attr( $wspan ); ?> text-center text-slate-500 py-10">발행된 기사가 없습니다.</p>
                                <?php endif; ?>
                            </div>

                        <?php elseif ( $wcat > 0 ) :
                            /* 목록형 + 특정 카테고리 */
                            $scat = get_category( $wcat );
                            $cppc = [ 1 => 6, 2 => 6, 3 => 9 ][ $wcols ] ?? 6;
                            if ( $scat && ! is_wp_error( $scat ) ) :
                                if ( empty( $wtitle ) ) : ?>
                                    <h2 class="text-xl font-bold mb-4 border-b border-slate-200 pb-2 flex justify-between items-end">
                                        <span class="text-blue-700"><?php echo esc_html( $scat->name ); ?></span>
                                        <a href="<?php echo esc_url( get_category_link( $wcat ) ); ?>" class="text-xs text-slate-400 hover:text-blue-500 font-normal">더보기 +</a>
                                    </h2>
                                <?php endif;
                                $cq = new WP_Query( [ 'cat' => $wcat, 'posts_per_page' => $cppc, 'no_found_rows' => true, 'ignore_sticky_posts' => true ] );
                                if ( $wcols > 1 ) : ?>
                                    <div class="grid <?php echo esc_attr( $wgrid ); ?> gap-x-8 gap-y-3">
                                        <?php if ( $cq->have_posts() ) : while ( $cq->have_posts() ) : $cq->the_post();
                                            $lp_th = get_the_post_thumbnail_url( null, 'thumbnail' ); ?>
                                        <div class="flex items-center gap-3 group">
                                            <a href="<?php the_permalink(); ?>" class="flex-shrink-0 w-16 h-11 overflow-hidden rounded bg-slate-100 block">
                                                <?php if ( $lp_th ) : ?>
                                                <img src="<?php echo esc_url( $lp_th ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                <?php else : ?>
                                                <div class="w-full h-full flex items-center justify-center"><svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                                <?php endif; ?>
                                            </a>
                                            <div class="flex-1 min-w-0">
                                                <a href="<?php the_permalink(); ?>" class="text-slate-700 hover:text-blue-600 transition font-medium line-clamp-2 text-sm leading-snug block"><?php the_title(); ?></a>
                                                <span class="text-xs text-slate-400"><?php echo get_the_date( 'm.d' ); ?></span>
                                            </div>
                                        </div>
                                        <?php endwhile; else : echo '<div class="text-sm text-slate-400">게시물이 없습니다.</div>'; endif; wp_reset_postdata(); ?>
                                    </div>
                                <?php else : ?>
                                    <ul class="space-y-3">
                                        <?php if ( $cq->have_posts() ) : while ( $cq->have_posts() ) : $cq->the_post();
                                            $lp_th = get_the_post_thumbnail_url( null, 'thumbnail' ); ?>
                                        <li class="flex items-center gap-3 group">
                                            <a href="<?php the_permalink(); ?>" class="flex-shrink-0 w-20 h-14 overflow-hidden rounded bg-slate-100 block">
                                                <?php if ( $lp_th ) : ?>
                                                <img src="<?php echo esc_url( $lp_th ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                <?php else : ?>
                                                <div class="w-full h-full flex items-center justify-center"><svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                                <?php endif; ?>
                                            </a>
                                            <div class="flex-1 min-w-0">
                                                <a href="<?php the_permalink(); ?>" class="text-slate-700 hover:text-blue-600 transition font-medium line-clamp-2 text-sm leading-snug block"><?php the_title(); ?></a>
                                                <span class="text-xs text-slate-400 mt-0.5 block"><?php echo get_the_date( 'm.d' ); ?></span>
                                            </div>
                                        </li>
                                        <?php endwhile; else : echo '<li class="text-sm text-slate-400">게시물이 없습니다.</li>'; endif; wp_reset_postdata(); ?>
                                    </ul>
                                <?php endif;
                            else : ?>
                                <p class="text-slate-400 text-sm text-center py-6">카테고리를 찾을 수 없습니다.</p>
                            <?php endif;

                        else :
                            /* 목록형 + 전체 카테고리 */
                            $climit = $wcols === 3 ? 6 : 4;
                            $ppc    = [ 1 => 6, 2 => 4, 3 => 4 ][ $wcols ] ?? 4;
                            $lcats  = get_categories( [ 'number' => $climit, 'hide_empty' => false, 'exclude' => get_option( 'default_category' ) ] );
                            ?>
                            <div class="grid <?php echo esc_attr( $wgrid ); ?> gap-x-8 gap-y-10">
                                <?php if ( ! empty( $lcats ) ) :
                                    foreach ( $lcats as $lcat ) : ?>
                                    <div>
                                        <h2 class="text-xl font-bold mb-4 border-b border-slate-200 pb-2 flex justify-between items-end">
                                            <span class="text-blue-700"><?php echo esc_html( $lcat->name ); ?></span>
                                            <a href="<?php echo esc_url( get_category_link( $lcat->term_id ) ); ?>" class="text-xs text-slate-400 hover:text-blue-500 font-normal">더보기 +</a>
                                        </h2>
                                        <ul class="space-y-3">
                                            <?php $cq = new WP_Query( [ 'cat' => $lcat->term_id, 'posts_per_page' => $ppc ] );
                                            if ( $cq->have_posts() ) : while ( $cq->have_posts() ) : $cq->the_post();
                                                $lp_th = get_the_post_thumbnail_url( null, 'thumbnail' ); ?>
                                            <li class="flex items-center gap-3 group">
                                                <a href="<?php the_permalink(); ?>" class="flex-shrink-0 w-16 h-11 overflow-hidden rounded bg-slate-100 block">
                                                    <?php if ( $lp_th ) : ?>
                                                    <img src="<?php echo esc_url( $lp_th ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                    <?php else : ?>
                                                    <div class="w-full h-full flex items-center justify-center"><svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                                    <?php endif; ?>
                                                </a>
                                                <div class="flex-1 min-w-0">
                                                    <a href="<?php the_permalink(); ?>" class="text-slate-700 hover:text-blue-600 transition font-medium line-clamp-2 text-sm leading-snug block"><?php the_title(); ?></a>
                                                    <span class="text-xs text-slate-400"><?php echo get_the_date( 'm.d' ); ?></span>
                                                </div>
                                            </li>
                                            <?php endwhile; else : echo '<li class="text-sm text-slate-400">게시물이 없습니다.</li>'; endif; wp_reset_postdata(); ?>
                                        </ul>
                                    </div>
                                <?php endforeach;
                                else : ?>
                                    <div class="<?php echo esc_attr( $wspan ); ?> text-center text-slate-500 py-8">카테고리를 생성해 주세요.</div>
                                <?php endif; ?>
                            </div>
                        <?php endif;
                    } // end lp_render_widget_body
                    endif;

                    /* ── 위젯 데이터 파싱 (functions.php의 lp_parse_home_widgets 사용) ── */
                    $lp_cols_default_span = [ 1 => 6, 2 => 3, 3 => 2 ];
                    $lp_widgets = lp_parse_home_widgets();
                    ?>

                    <!-- ── 위젯 렌더링 ── -->
                    <div class="space-y-10 border-t border-slate-200 pt-8">
                    <?php foreach ( $lp_widgets as $lp_w ) : ?>

                        <?php if ( $lp_w['type'] === 'section' ) :
                            /* ── 섹션 그룹: 6열 기준 grid, 각 위젯 span 개별 적용 ── */
                            $sec_cols  = (int) ( $lp_w['cols'] ?? 2 );
                            $sec_title = (string) ( $lp_w['title'] ?? '' );
                            $sec_items = $lp_w['items'] ?? [];
                            $sec_def_span = $lp_cols_default_span[ $sec_cols ] ?? 3;
                        ?>
                        <div>
                            <?php if ( ! empty( $sec_title ) ) : ?>
                            <h2 class="text-xl font-bold mb-4 border-b border-slate-200 pb-2">
                                <span class="text-blue-700"><?php echo esc_html( $sec_title ); ?></span>
                            </h2>
                            <?php endif; ?>
                            <div class="grid grid-cols-6 gap-x-8 gap-y-10 items-start">
                                <?php foreach ( $sec_items as $sec_w ) :
                                    $sw_width = $sec_w['width'] ?? '';
                                    $sw_span  = lp_width_to_span( $sw_width, $sec_def_span );
                                ?>
                                <div class="col-span-6 md:col-span-<?php echo (int) $sw_span; ?>">
                                    <?php lp_render_widget_body( $sec_w, $lp_grid_map, $lp_span_map ); ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php else : ?>
                        <div><?php lp_render_widget_body( $lp_w, $lp_grid_map, $lp_span_map ); ?></div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                    </div>

                <?php elseif (is_single()) : ?>
                    <!-- 기사(싱글) 보기 화면 -->
                    <?php while (have_posts()) : the_post(); ?>

                    <!-- 스티키 기사 정보 바 (position:fixed) + 열독률 진행바 (하단) -->
                    <div id="lp-sticky-bar" aria-hidden="true">
                        <div class="lp-sb-inner <?php echo esc_attr($container_class); ?>">

                            <!-- 로고 -->
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="lp-sb-logo hidden sm:block">
                                <?php echo esc_html(get_bloginfo('name')); ?>
                            </a>
                            <span class="lp-sb-sep hidden sm:block"></span>

                            <!-- 기사 제목 / 작성자 -->
                            <div class="flex-1 min-w-0">
                                <p class="lp-sb-title"><?php echo esc_html(get_the_title()); ?></p>
                                <p class="lp-sb-author"><?php echo esc_html(get_the_author()); ?> 기자 · <?php echo esc_html(get_the_date('Y.m.d')); ?></p>
                            </div>

                            <!-- 글자 크기 조절 -->
                            <div class="lp-font-ctrl hidden sm:flex" title="글자 크기 조절">
                                <button class="lp-fz-sm" onclick="lpFontSize(-1)" id="lp-fz-small" title="글자 작게">가<sup style="font-size:0.55em;vertical-align:super">−</sup></button>
                                <button class="lp-fz-md" onclick="lpFontSize(0)"  id="lp-fz-mid"   title="기본 크기">가</button>
                                <button class="lp-fz-lg" onclick="lpFontSize(1)"  id="lp-fz-large" title="글자 크게">가<sup style="font-size:0.55em;vertical-align:super">+</sup></button>
                            </div>

                            <span class="lp-sb-share-sep hidden sm:block"></span>

                            <!-- 공유 버튼 -->
                            <div class="flex items-center gap-1">
                                <button onclick="lpCopyUrl(this)" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="URL 복사">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </button>
                                <button onclick="lpShare('facebook')" class="p-1.5 bg-[#1877F2] rounded text-white hover:opacity-90 transition" title="페이스북">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </button>
                                <button onclick="lpShare('twitter')" class="p-1.5 bg-[#1DA1F2] rounded text-white hover:opacity-90 transition" title="트위터">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723 10.054 10.054 0 01-3.127 1.195 4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </button>
                                <button onclick="lpShare('kakao')" class="p-1.5 bg-[#FEE500] rounded text-[#000000] hover:opacity-90 transition" title="카카오톡">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-5.523 0-10 3.5-10 7.824 0 2.735 1.722 5.127 4.318 6.544-.223.784-.81 2.875-.845 3.013-.043.163.056.235.176.155.133-.09 2.83-1.921 3.963-2.686.76.15 1.554.232 2.388.232 5.523 0 10-3.5 10-7.824S17.523 3 12 3z"/></svg>
                                </button>
                            </div>

                        </div>
                        <!-- 열독률 진행바 — 스티키 바 하단에 고정 -->
                        <div id="lp-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                    </div>

                        <article class="article-view" id="lp-article">
                            <header class="border-b border-slate-200 pb-3 mb-4">
                                <?php
                                $categories = get_the_category();
                                if ( ! empty( $categories ) ) {
                                    echo '<span class="text-blue-600 font-bold text-sm mb-1.5 block">' . esc_html( $categories[0]->name ) . '</span>';
                                }
                                ?>
                                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight leading-tight mb-2"><?php the_title(); ?></h1>
                                <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500">
                                    <div class="flex items-center gap-4">
                                        <span class="font-medium text-slate-700 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            <?php the_author(); ?> 기자
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <?php echo get_the_date('Y.m.d H:i'); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <!-- 글자 크기 조절 (기사 헤더) -->
                                        <div class="lp-font-ctrl" title="글자 크기 조절">
                                            <button class="lp-fz-sm" onclick="lpFontSize(-1)" id="lp-hdr-fz-small" title="글자 작게">가<sup style="font-size:0.55em;vertical-align:super">−</sup></button>
                                            <button class="lp-fz-md" onclick="lpFontSize(0)"  id="lp-hdr-fz-mid"   title="기본 크기">가</button>
                                            <button class="lp-fz-lg" onclick="lpFontSize(1)"  id="lp-hdr-fz-large" title="글자 크게">가<sup style="font-size:0.55em;vertical-align:super">+</sup></button>
                                        </div>
                                        <span class="w-px h-5 bg-slate-200"></span>
                                        <button onclick="window.print()" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="프린트"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
                                        <button onclick="lpCopyUrl(this)" class="p-1.5 border border-slate-200 rounded hover:bg-slate-50 transition" title="URL 복사"><svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg></button>
                                        <button onclick="lpShare('facebook')" class="p-1.5 bg-[#1877F2] rounded text-white hover:opacity-90 transition" title="페이스북"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></button>
                                        <button onclick="lpShare('twitter')" class="p-1.5 bg-[#1DA1F2] rounded text-white hover:opacity-90 transition" title="트위터"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723 10.054 10.054 0 01-3.127 1.195 4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></button>
                                        <button onclick="lpShare('kakao')" class="p-1.5 bg-[#FEE500] rounded text-[#000000] hover:opacity-90 transition" title="카카오톡"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-5.523 0-10 3.5-10 7.824 0 2.735 1.722 5.127 4.318 6.544-.223.784-.81 2.875-.845 3.013-.043.163.056.235.176.155.133-.09 2.83-1.921 3.963-2.686.76.15 1.554.232 2.388.232 5.523 0 10-3.5 10-7.824S17.523 3 12 3z"/></svg></button>
                                    </div>
                                </div>
                            </header>
                            
                            <div id="lp-article-body" class="text-slate-800 text-lg leading-relaxed mb-12 break-words" style="line-height: 1.85;">
                                <?php 
                                if(has_post_thumbnail()) {
                                    echo '<figure class="mb-10 text-center">';
                                    the_post_thumbnail('full', ['class' => 'w-full max-w-3xl mx-auto h-auto rounded shadow-sm']);
                                    $caption = get_the_post_thumbnail_caption();
                                    if($caption) {
                                        echo '<figcaption class="text-sm text-slate-500 mt-3">' . esc_html($caption) . '</figcaption>';
                                    }
                                    echo '</figure>';
                                }
                                the_content(); 
                                ?>
                            </div>

                            <?php // ── 기사 반응 (Reaction) ──────────────────────── ?>
                            <?php
                            $lp_rx_types    = lp_reaction_types();
                            $lp_rx_counts   = get_post_meta( get_the_ID(), 'lp_reactions', true );
                            if ( ! is_array( $lp_rx_counts ) ) $lp_rx_counts = [];
                            $lp_rx_user     = lp_get_user_reactions( get_the_ID() );
                            $lp_rx_nonce    = wp_create_nonce( 'lp_reaction_nonce' );
                            $lp_rx_ajax_url = esc_url( admin_url( 'admin-ajax.php' ) );
                            $lp_rx_post_id  = get_the_ID();
                            $lp_rx_logged   = is_user_logged_in();
                            ?>
                            <div class="mt-10 mb-2 p-6 bg-gradient-to-br from-slate-50 to-blue-50/40 border border-slate-200 rounded-2xl">
                                <p class="text-center text-sm font-semibold text-slate-700 mb-5">
                                    이 기사, 어떠셨나요?
                                    <span class="text-slate-400 font-normal">· 반응을 선택해 주세요</span>
                                </p>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2" id="lp-reaction-grid">
                                    <?php foreach ( $lp_rx_types as $lp_rx_key => $lp_rx_info ) :
                                        $lp_is_active = in_array( $lp_rx_key, $lp_rx_user, true );
                                        $lp_count     = intval( $lp_rx_counts[ $lp_rx_key ] ?? 0 );
                                    ?>
                                    <button type="button"
                                        class="lp-rx-btn group flex flex-col items-center gap-1.5 py-3.5 px-2 rounded-xl border-2 cursor-pointer transition-all duration-150
                                               <?php echo $lp_is_active
                                                   ? 'border-blue-500 bg-blue-50 shadow-sm'
                                                   : 'border-slate-200 bg-white hover:border-blue-300 hover:bg-blue-50/60 hover:shadow-sm'; ?>"
                                        data-type="<?php echo esc_attr( $lp_rx_key ); ?>"
                                        data-post="<?php echo esc_attr( $lp_rx_post_id ); ?>"
                                        data-nonce="<?php echo esc_attr( $lp_rx_nonce ); ?>"
                                        data-ajax="<?php echo $lp_rx_ajax_url; ?>"
                                        data-active="<?php echo $lp_is_active ? '1' : '0'; ?>"
                                        data-logged="<?php echo $lp_rx_logged ? '1' : '0'; ?>"
                                        aria-pressed="<?php echo $lp_is_active ? 'true' : 'false'; ?>"
                                        title="<?php echo esc_attr( $lp_rx_info['label'] ); ?>">
                                        <span class="text-2xl leading-none group-active:scale-125 transition-transform select-none"
                                              aria-hidden="true"><?php echo $lp_rx_info['emoji']; ?></span>
                                        <span class="text-[11px] font-medium text-slate-600 leading-tight text-center">
                                            <?php echo esc_html( $lp_rx_info['label'] ); ?>
                                        </span>
                                        <span class="lp-rx-count min-h-[1rem] text-xs font-bold tabular-nums
                                                     <?php echo $lp_is_active ? 'text-blue-600' : 'text-slate-400'; ?>">
                                            <?php echo $lp_count > 0 ? number_format( $lp_count ) : ''; ?>
                                        </span>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ( ! $lp_rx_logged ) : ?>
                                <p class="text-center text-[11px] text-slate-400 mt-4">
                                    <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"
                                       class="text-blue-500 hover:underline">로그인</a> 후 반응을 취소하거나 변경할 수 있습니다.
                                </p>
                                <?php endif; ?>
                            </div>

                            <script>
                            (function () {
                                /* 비로그인 전용 localStorage 키 (포스트별 분리) */
                                var LS_KEY  = 'lp_reacted_<?php echo $lp_rx_post_id; ?>';
                                var reacted = {};
                                try { reacted = JSON.parse(localStorage.getItem(LS_KEY) || '{}'); } catch (e) {}

                                /* 비로그인: 페이지 로드 시 localStorage 기준으로 활성 상태 복원 */
                                <?php if ( ! $lp_rx_logged ) : ?>
                                document.querySelectorAll('#lp-reaction-grid .lp-rx-btn').forEach(function (btn) {
                                    if (!reacted[btn.dataset.type]) return;
                                    btn.classList.add('border-blue-500', 'bg-blue-50', 'shadow-sm');
                                    btn.classList.remove('border-slate-200', 'bg-white');
                                    btn.dataset.active = '1';
                                    btn.setAttribute('aria-pressed', 'true');
                                    var c = btn.querySelector('.lp-rx-count');
                                    c.classList.replace('text-slate-400', 'text-blue-600');
                                });
                                <?php endif; ?>

                                document.querySelectorAll('#lp-reaction-grid .lp-rx-btn').forEach(function (btn) {
                                    btn.addEventListener('click', function () {
                                        var type   = btn.dataset.type;
                                        var active = btn.dataset.active === '1';

                                        /* 비로그인: 이미 어떤 반응이든 했으면 차단 (포스트당 1회, 변경 불가) */
                                        if (btn.dataset.logged === '0') {
                                            var alreadyReacted = Object.keys(reacted).some(function(k) { return reacted[k]; });
                                            if (alreadyReacted) return;
                                        }

                                        btn.disabled = true;

                                        var fd = new FormData();
                                        fd.append('action',  'lp_react');
                                        fd.append('nonce',   btn.dataset.nonce);
                                        fd.append('post_id', btn.dataset.post);
                                        fd.append('type',    type);

                                        fetch(btn.dataset.ajax, { method: 'POST', body: fd })
                                            .then(function (r) { return r.json(); })
                                            .then(function (res) {
                                                if (!res.success) { btn.disabled = false; return; }

                                                var nowActive = res.data.active;
                                                var count     = res.data.count;
                                                var countEl   = btn.querySelector('.lp-rx-count');

                                                if (nowActive) {
                                                    btn.classList.add('border-blue-500', 'bg-blue-50', 'shadow-sm');
                                                    btn.classList.remove('border-slate-200', 'bg-white',
                                                                         'hover:border-blue-300', 'hover:bg-blue-50/60', 'hover:shadow-sm');
                                                    countEl.classList.replace('text-slate-400', 'text-blue-600');
                                                    btn.setAttribute('aria-pressed', 'true');
                                                    btn.dataset.active = '1';
                                                } else {
                                                    btn.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-sm');
                                                    btn.classList.add('border-slate-200', 'bg-white',
                                                                      'hover:border-blue-300', 'hover:bg-blue-50/60', 'hover:shadow-sm');
                                                    countEl.classList.replace('text-blue-600', 'text-slate-400');
                                                    btn.setAttribute('aria-pressed', 'false');
                                                    btn.dataset.active = '0';
                                                }
                                                countEl.textContent = count > 0 ? count.toLocaleString('ko-KR') : '';

                                                /* 교체된 이전 반응 버튼 UI 초기화 (로그인 사용자 교체 시) */
                                                if (res.data.prev_type) {
                                                    var prevBtn = document.querySelector('#lp-reaction-grid .lp-rx-btn[data-type="' + res.data.prev_type + '"]');
                                                    if (prevBtn) {
                                                        prevBtn.classList.remove('border-blue-500', 'bg-blue-50', 'shadow-sm');
                                                        prevBtn.classList.add('border-slate-200', 'bg-white',
                                                                              'hover:border-blue-300', 'hover:bg-blue-50/60', 'hover:shadow-sm');
                                                        var prevCountEl = prevBtn.querySelector('.lp-rx-count');
                                                        prevCountEl.classList.replace('text-blue-600', 'text-slate-400');
                                                        prevCountEl.textContent = res.data.prev_count > 0 ? res.data.prev_count.toLocaleString('ko-KR') : '';
                                                        prevBtn.setAttribute('aria-pressed', 'false');
                                                        prevBtn.dataset.active = '0';
                                                    }
                                                }

                                                /* 비로그인: localStorage에 기록 */
                                                if (btn.dataset.logged === '0') {
                                                    reacted[type] = true;
                                                    try { localStorage.setItem(LS_KEY, JSON.stringify(reacted)); } catch (e) {}
                                                }

                                                btn.disabled = false;
                                            })
                                            .catch(function () { btn.disabled = false; });
                                    });
                                });
                            })();
                            </script>

                            <?php
                            $lp_author_id  = get_the_author_meta('ID');
                            $lp_author_url = get_author_posts_url($lp_author_id);
                            $lp_reporter_bio = get_the_author_meta('lara_reporter_bio');
                            ?>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 flex flex-col sm:flex-row items-center sm:items-start gap-5 text-sm text-slate-600 mt-12">
                                <a href="<?php echo esc_url($lp_author_url); ?>"
                                   class="w-16 h-16 bg-slate-200 rounded-full overflow-hidden flex items-center justify-center text-slate-400 flex-shrink-0 shadow-inner hover:ring-2 hover:ring-blue-400 hover:ring-offset-2 transition">
                                    <?php echo get_avatar($lp_author_id, 64); ?>
                                </a>
                                <div class="flex-grow text-center sm:text-left">
                                    <a href="<?php echo esc_url($lp_author_url); ?>"
                                       class="font-bold text-slate-900 text-base mb-1 hover:text-blue-600 transition inline-block">
                                        <?php the_author(); ?> 기자
                                    </a>
                                    <p class="text-slate-500 mb-3"><?php echo esc_html($lp_reporter_bio ?: '항상 정확하고 빠른 소식을 전해드리겠습니다.'); ?></p>
                                    <a href="<?php echo esc_url($lp_author_url); ?>"
                                       class="inline-block text-xs text-blue-600 border border-blue-200 bg-white px-3 py-1.5 rounded hover:bg-blue-50 transition">
                                        이 기자의 다른 기사 보기
                                    </a>
                                </div>
                                <div class="sm:ml-auto self-center sm:self-start mt-4 sm:mt-0">
                                    <a href="mailto:<?php echo esc_attr(get_the_author_meta('user_email')); ?>"
                                       class="inline-flex items-center gap-1.5 text-slate-500 hover:text-blue-600 transition bg-white border border-slate-200 px-3 py-1.5 rounded text-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <?php echo esc_html(get_the_author_meta('user_email')); ?>
                                    </a>
                                </div>
                            </div>

                            <?php
                            // ── 기사 댓글 섹션 (투트랙) ──────────────────────
                            // Track 1: 승인된 댓글 (전체 공개)
                            // Track 2: 전체 댓글 관리 (moderate_comments 권한자 전용)
                            $lp_cm_is_admin = current_user_can( 'moderate_comments' );

                            // Track 1 — 승인된 댓글
                            $lp_approved_comments = get_comments( [
                                'post_id' => get_the_ID(),
                                'status'  => 'approve',
                                'order'   => 'ASC',
                            ] );
                            $lp_approved_count = count( $lp_approved_comments );

                            // Track 2 — 전체 댓글 (관리자 전용): 승인 + 보류 + 스팸 병합
                            $lp_all_comments = [];
                            $lp_all_count    = 0;
                            $lp_pending_count = 0;
                            if ( $lp_cm_is_admin ) {
                                $lp_hold_comments = get_comments( [
                                    'post_id' => get_the_ID(),
                                    'status'  => 'hold',
                                    'order'   => 'ASC',
                                ] );
                                $lp_spam_comments = get_comments( [
                                    'post_id' => get_the_ID(),
                                    'status'  => 'spam',
                                    'order'   => 'ASC',
                                ] );
                                $lp_all_comments  = array_merge( $lp_approved_comments, $lp_hold_comments, $lp_spam_comments );
                                usort( $lp_all_comments, function ( $a, $b ) {
                                    return strcmp( $a->comment_date, $b->comment_date );
                                } );
                                $lp_all_count     = count( $lp_all_comments );
                                $lp_pending_count = count( $lp_hold_comments );
                            }

                            // 상태 배지 맵 (comment_approved 컬럼 값 기준)
                            $lp_status_map = [
                                '1'    => [ 'label' => '승인됨',    'cls' => 'bg-green-100 text-green-700 border-green-200' ],
                                '0'    => [ 'label' => '검토 대기', 'cls' => 'bg-amber-100 text-amber-700 border-amber-200' ],
                                'spam' => [ 'label' => '스팸',      'cls' => 'bg-red-100   text-red-700   border-red-200'   ],
                            ];

                            // 모더레이션 폼 출력 헬퍼
                            $lp_post_url = get_permalink();
                            $lp_mod_form = function ( $cm_id, $action, $label, $btn_cls ) use ( $lp_post_url ) {
                                $nonce    = wp_create_nonce( 'lara_moderate_comment_' . $cm_id );
                                $redirect = esc_url( $lp_post_url . '#lp-comments' );
                                echo '<form method="POST" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="inline">';
                                echo '<input type="hidden" name="action"       value="lara_moderate_comment">';
                                echo '<input type="hidden" name="comment_id"   value="' . esc_attr( $cm_id ) . '">';
                                echo '<input type="hidden" name="mod_action"   value="' . esc_attr( $action ) . '">';
                                echo '<input type="hidden" name="redirect_to"  value="' . $redirect . '">';
                                echo '<input type="hidden" name="lara_mod_nonce" value="' . esc_attr( $nonce ) . '">';
                                echo '<button type="submit" class="' . esc_attr( $btn_cls ) . '">' . esc_html( $label ) . '</button>';
                                echo '</form>';
                            };
                            ?>
                            <?php if ( comments_open() || $lp_approved_count || $lp_cm_is_admin ) : ?>
                            <section id="lp-comments" class="mt-14 pt-10 border-t border-slate-200">

                                <!-- 댓글 섹션 헤더 + 탭 스위처 -->
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 16c0 1.1-.9 2-2 2H7l-4 4V6a2 2 0 012-2h14a2 2 0 012 2v10z"/>
                                        </svg>
                                        댓글
                                    </h3>
                                    <!-- 탭 버튼 -->
                                    <div class="flex items-center gap-1 bg-slate-100 rounded-lg p-1 text-sm font-medium" role="tablist">
                                        <button id="lp-tab-approved" role="tab" aria-selected="true" aria-controls="lp-panel-approved"
                                            class="lp-cm-tab px-3 py-1.5 rounded-md bg-white shadow-sm text-slate-800 transition-all">
                                            승인된 댓글
                                            <span class="ml-1 font-bold text-blue-600"><?php echo $lp_approved_count; ?></span>
                                        </button>
                                        <?php if ( $lp_cm_is_admin ) : ?>
                                        <button id="lp-tab-all" role="tab" aria-selected="false" aria-controls="lp-panel-all"
                                            class="lp-cm-tab relative px-3 py-1.5 rounded-md text-slate-500 hover:text-slate-800 transition-all">
                                            전체 댓글
                                            <span class="ml-1 font-bold <?php echo $lp_pending_count > 0 ? 'text-amber-600' : 'text-slate-400'; ?>">
                                                <?php echo $lp_all_count; ?>
                                            </span>
                                            <?php if ( $lp_pending_count > 0 ) : ?>
                                            <span class="absolute -top-0.5 -right-0.5 flex h-3 w-3">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                            </span>
                                            <?php endif; ?>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- ══ Track 1: 승인된 댓글 패널 ══ -->
                                <div id="lp-panel-approved" role="tabpanel" aria-labelledby="lp-tab-approved">

                                    <?php if ( $lp_approved_comments ) : ?>
                                    <div class="space-y-3 mb-8">
                                        <?php foreach ( $lp_approved_comments as $lp_cm ) :
                                            $lp_is_own = is_user_logged_in() && (int) $lp_cm->user_id === get_current_user_id();
                                        ?>
                                        <div class="flex gap-3 p-4 bg-white border border-slate-100 rounded-xl shadow-sm">
                                            <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                                                <?php echo get_avatar( $lp_cm->comment_author_email, 36 ); ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                                    <span class="font-semibold text-sm text-slate-800"><?php echo esc_html( $lp_cm->comment_author ); ?></span>
                                                    <span class="text-xs text-slate-400 flex-shrink-0"><?php echo esc_html( mysql2date( 'Y.m.d H:i', $lp_cm->comment_date ) ); ?></span>
                                                </div>
                                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line break-words">
                                                    <?php echo esc_html( $lp_cm->comment_content ); ?>
                                                </p>
                                                <?php if ( $lp_is_own || $lp_cm_is_admin ) : ?>
                                                <div class="flex items-center gap-2 mt-2 justify-end">
                                                    <?php if ( $lp_cm_is_admin ) :
                                                        $lp_mod_form(
                                                            $lp_cm->comment_ID, 'hold', '보류',
                                                            'text-xs text-slate-400 hover:text-amber-600 border border-transparent hover:border-amber-300 px-2 py-0.5 rounded transition'
                                                        );
                                                    endif; ?>
                                                    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                                          class="inline"
                                                          onsubmit="return confirm('이 댓글을 삭제하시겠습니까?');">
                                                        <input type="hidden" name="action"      value="lara_delete_article_comment">
                                                        <input type="hidden" name="comment_id"  value="<?php echo esc_attr( $lp_cm->comment_ID ); ?>">
                                                        <input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() . '#lp-comments' ); ?>">
                                                        <?php wp_nonce_field( 'lara_delete_comment_' . $lp_cm->comment_ID, 'lara_del_nonce' ); ?>
                                                        <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition">삭제</button>
                                                    </form>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else : ?>
                                    <p class="text-sm text-slate-400 text-center py-8 bg-slate-50 rounded-xl mb-8">
                                        아직 등록된 댓글이 없습니다. 첫 댓글을 남겨보세요.
                                    </p>
                                    <?php endif; ?>

                                    <!-- 댓글 등록 폼 -->
                                    <?php if ( comments_open() ) : ?>
                                        <?php if ( is_user_logged_in() ) : ?>
                                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                            <div class="flex gap-3 items-start">
                                                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 mt-0.5">
                                                    <?php echo get_avatar( get_current_user_id(), 36 ); ?>
                                                </div>
                                                <form method="POST" action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" class="flex-1">
                                                    <input type="hidden" name="comment_post_ID" value="<?php the_ID(); ?>">
                                                    <input type="hidden" name="redirect_to"     value="<?php echo esc_url( get_permalink() . '#lp-comments' ); ?>">
                                                    <textarea name="comment" required rows="3"
                                                        placeholder="댓글을 입력하세요..."
                                                        class="w-full border border-slate-200 bg-white rounded-lg px-3 py-2.5 text-sm text-slate-800 resize-none focus:outline-none focus:ring-2 focus:ring-blue-300 transition"></textarea>
                                                    <div class="flex justify-between items-center mt-2">
                                                        <span class="text-xs text-slate-400">
                                                            <?php echo esc_html( wp_get_current_user()->display_name ); ?>님으로 등록됩니다
                                                        </span>
                                                        <button type="submit"
                                                            class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 active:scale-95 transition font-medium">
                                                            댓글 등록
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <?php else : ?>
                                        <div class="text-center py-8 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500">
                                            댓글을 작성하려면
                                            <a href="<?php echo esc_url( wp_login_url( get_permalink() . '#lp-comments' ) ); ?>"
                                               class="text-blue-600 font-medium hover:underline">로그인</a>이 필요합니다.
                                        </div>
                                        <?php endif; ?>
                                    <?php else : ?>
                                    <p class="text-sm text-slate-400 text-center py-4">이 기사에는 댓글을 달 수 없습니다.</p>
                                    <?php endif; ?>

                                </div><!-- #lp-panel-approved -->

                                <!-- ══ Track 2: 전체 댓글 관리 패널 (관리자 전용) ══ -->
                                <?php if ( $lp_cm_is_admin ) : ?>
                                <div id="lp-panel-all" role="tabpanel" aria-labelledby="lp-tab-all" hidden>

                                    <!-- 관리자 안내 -->
                                    <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 mb-5 text-xs text-amber-800">
                                        <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        <span>관리자 전용 뷰입니다. 승인 · 보류 · 스팸 처리된 모든 댓글을 확인하고 개별 모더레이션할 수 있습니다.</span>
                                    </div>

                                    <!-- 상태별 요약 카운트 -->
                                    <?php
                                    $lp_cnt = [ 'approve' => 0, 'hold' => 0, 'spam' => 0 ];
                                    foreach ( $lp_all_comments as $_cm ) {
                                        $st = $_cm->comment_approved;
                                        if ( $st === '1' )      $lp_cnt['approve']++;
                                        elseif ( $st === '0' )  $lp_cnt['hold']++;
                                        elseif ( $st === 'spam' ) $lp_cnt['spam']++;
                                    }
                                    ?>
                                    <div class="flex flex-wrap gap-2 mb-5">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 border border-green-200 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            승인됨 <?php echo $lp_cnt['approve']; ?>건
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-700 border border-amber-200 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                            검토 대기 <?php echo $lp_cnt['hold']; ?>건
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 text-red-700 border border-red-200 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            스팸 <?php echo $lp_cnt['spam']; ?>건
                                        </span>
                                    </div>

                                    <?php if ( $lp_all_comments ) : ?>
                                    <div class="space-y-2 mb-8">
                                        <?php foreach ( $lp_all_comments as $lp_cm ) :
                                            $lp_st       = $lp_cm->comment_approved;
                                            $lp_badge    = $lp_status_map[ $lp_st ] ?? $lp_status_map['0'];
                                            $lp_is_appr  = ( $lp_st === '1' );
                                            $lp_is_hold  = ( $lp_st === '0' );
                                            $lp_is_spam  = ( $lp_st === 'spam' );

                                            // 상태별 카드 배경
                                            if ( $lp_is_hold ) {
                                                $lp_card_cls = 'bg-amber-50 border-amber-200';
                                            } elseif ( $lp_is_spam ) {
                                                $lp_card_cls = 'bg-red-50 border-red-200';
                                            } else {
                                                $lp_card_cls = 'bg-white border-slate-100';
                                            }
                                        ?>
                                        <div class="flex gap-3 p-4 border rounded-xl <?php echo $lp_card_cls; ?>">
                                            <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 <?php echo $lp_is_spam ? 'opacity-50' : ''; ?>">
                                                <?php echo get_avatar( $lp_cm->comment_author_email, 36 ); ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                                    <span class="font-semibold text-sm text-slate-800"><?php echo esc_html( $lp_cm->comment_author ); ?></span>
                                                    <span class="inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded border font-medium <?php echo $lp_badge['cls']; ?>">
                                                        <?php echo $lp_badge['label']; ?>
                                                    </span>
                                                    <span class="text-xs text-slate-400 ml-auto flex-shrink-0">
                                                        <?php echo esc_html( mysql2date( 'Y.m.d H:i', $lp_cm->comment_date ) ); ?>
                                                    </span>
                                                </div>
                                                <p class="text-sm <?php echo $lp_is_spam ? 'text-slate-400 line-through' : 'text-slate-700'; ?> leading-relaxed whitespace-pre-line break-words">
                                                    <?php echo esc_html( $lp_cm->comment_content ); ?>
                                                </p>
                                                <!-- 모더레이션 액션 버튼 -->
                                                <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                                                    <?php if ( $lp_is_hold ) : ?>
                                                        <?php $lp_mod_form(
                                                            $lp_cm->comment_ID, 'approve', '✓ 승인',
                                                            'text-xs text-white bg-green-600 hover:bg-green-700 px-2.5 py-1 rounded transition font-medium'
                                                        ); ?>
                                                        <?php $lp_mod_form(
                                                            $lp_cm->comment_ID, 'spam', '스팸',
                                                            'text-xs text-red-600 border border-red-200 bg-white hover:bg-red-50 px-2.5 py-1 rounded transition'
                                                        ); ?>
                                                    <?php elseif ( $lp_is_appr ) : ?>
                                                        <?php $lp_mod_form(
                                                            $lp_cm->comment_ID, 'hold', '보류',
                                                            'text-xs text-amber-600 border border-amber-200 bg-white hover:bg-amber-50 px-2.5 py-1 rounded transition'
                                                        ); ?>
                                                        <?php $lp_mod_form(
                                                            $lp_cm->comment_ID, 'spam', '스팸',
                                                            'text-xs text-red-600 border border-red-200 bg-white hover:bg-red-50 px-2.5 py-1 rounded transition'
                                                        ); ?>
                                                    <?php elseif ( $lp_is_spam ) : ?>
                                                        <?php $lp_mod_form(
                                                            $lp_cm->comment_ID, 'approve', '스팸 해제',
                                                            'text-xs text-green-600 border border-green-200 bg-white hover:bg-green-50 px-2.5 py-1 rounded transition'
                                                        ); ?>
                                                    <?php endif; ?>
                                                    <!-- 삭제(휴지통) — 모든 상태 공통 -->
                                                    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                                          class="inline"
                                                          onsubmit="return confirm('이 댓글을 삭제하시겠습니까?');">
                                                        <input type="hidden" name="action"      value="lara_delete_article_comment">
                                                        <input type="hidden" name="comment_id"  value="<?php echo esc_attr( $lp_cm->comment_ID ); ?>">
                                                        <input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() . '#lp-comments' ); ?>">
                                                        <?php wp_nonce_field( 'lara_delete_comment_' . $lp_cm->comment_ID, 'lara_del_nonce' ); ?>
                                                        <button type="submit" class="text-xs text-slate-400 hover:text-red-600 transition ml-1">삭제</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else : ?>
                                    <p class="text-sm text-slate-400 text-center py-8 bg-slate-50 rounded-xl mb-8">등록된 댓글이 없습니다.</p>
                                    <?php endif; ?>

                                </div><!-- #lp-panel-all -->
                                <?php endif; ?>

                                <!-- 탭 전환 스크립트 -->
                                <script>
                                (function () {
                                    var tabs   = document.querySelectorAll('#lp-comments .lp-cm-tab');
                                    var panels = {
                                        'lp-panel-approved': document.getElementById('lp-panel-approved'),
                                        'lp-panel-all':      document.getElementById('lp-panel-all')
                                    };

                                    tabs.forEach(function (tab) {
                                        tab.addEventListener('click', function () {
                                            /* 모든 탭 비활성화 */
                                            tabs.forEach(function (t) {
                                                t.setAttribute('aria-selected', 'false');
                                                t.classList.remove('bg-white', 'shadow-sm', 'text-slate-800');
                                                t.classList.add('text-slate-500');
                                            });
                                            /* 클릭한 탭 활성화 */
                                            tab.setAttribute('aria-selected', 'true');
                                            tab.classList.add('bg-white', 'shadow-sm', 'text-slate-800');
                                            tab.classList.remove('text-slate-500');
                                            /* 패널 전환 */
                                            var targetId = tab.getAttribute('aria-controls');
                                            Object.keys(panels).forEach(function (k) {
                                                if (panels[k]) panels[k].hidden = (k !== targetId);
                                            });
                                        });
                                    });
                                })();
                                </script>

                            </section>
                            <?php endif; ?>

                        </article>

                    <script>
                    (function () {
                        var progressBar = document.getElementById('lp-progress-bar');
                        var stickyBar   = document.getElementById('lp-sticky-bar');
                        var article     = document.getElementById('lp-article');
                        var articleBody = document.getElementById('lp-article-body');
                        var articleHdr  = article ? article.querySelector('header') : null;
                        var gnav        = document.querySelector('nav');

                        if (!stickyBar || !article) return;

                        /* ── WP 관리자 바 높이 ─────────────────────────────
                           로그인 시 상단 고정 바(32px/46px). 없으면 0. */
                        function getAdminBarHeight() {
                            var ab = document.getElementById('wpadminbar');
                            return ab ? ab.getBoundingClientRect().height : 0;
                        }

                        /* ── GNB 실제 하단 위치 ────────────────────────────
                           GNB가 슬라이드업 중일 땐 원래 높이를 사용해야 하므로
                           offsetHeight(레이아웃 높이)로 계산. */
                        function getNavBottom() {
                            if (!gnav) return 0;
                            return getAdminBarHeight() + gnav.offsetHeight;
                        }

                        /* ── 메타 바 위치 결정 ─────────────────────────────
                           활성: GNB가 슬라이드업되므로 메타 바가 그 자리(admin-bar 바로 아래)로 이동.
                           비활성: GNB 하단에 대기. */
                        function positionStickyBar() {
                            if (stickyBar.classList.contains('is-visible')) {
                                stickyBar.style.top = getAdminBarHeight() + 'px';
                            } else {
                                stickyBar.style.top = getNavBottom() + 'px';
                            }
                        }
                        positionStickyBar();
                        window.addEventListener('resize', positionStickyBar, { passive: true });

                        /* ── 스크롤 핸들러 ── */
                        function onScroll() {
                            var scrollY = window.scrollY || document.documentElement.scrollTop;
                            var winH    = window.innerHeight;

                            /* 열독률 진행바 — 기사 본문 기준 */
                            if (progressBar && articleBody) {
                                var body    = articleBody;
                                var bodyTop = body.getBoundingClientRect().top + scrollY;
                                var bodyEnd = bodyTop + body.offsetHeight - winH;
                                var pct     = bodyEnd > bodyTop
                                    ? Math.min(100, Math.max(0, (scrollY - bodyTop) / (bodyEnd - bodyTop) * 100))
                                    : 0;
                                progressBar.style.width = pct + '%';
                                progressBar.setAttribute('aria-valuenow', Math.round(pct));
                            }

                            /* 스티키 바 표시 — 기사 헤더 하단이 GNB 아래로 사라질 때 표시.
                               GNB는 위로 슬라이드아웃, 메타 바가 그 자리로 올라옴. */
                            if (articleHdr) {
                                var hdrBottom = articleHdr.getBoundingClientRect().bottom;
                                var show      = hdrBottom < getNavBottom();
                                stickyBar.classList.toggle('is-visible', show);
                                stickyBar.setAttribute('aria-hidden', show ? 'false' : 'true');
                                if (gnav) gnav.classList.toggle('lp-gnav-hidden', show);
                                /* is-visible 토글 후 위치 재계산 */
                                positionStickyBar();
                            }
                        }

                        window.addEventListener('scroll', onScroll, { passive: true });
                        onScroll();

                        /* ── 글자 크기 조절 ──────────────────────────────
                           0: 소(0.9375rem) / 1: 기본(1.0625rem) / 2: 대(1.25rem)
                           선택값은 localStorage에 저장 → 다음 방문 시 자동 복원. */
                        var FS_KEY    = 'lp_font_size';
                        var FS_LEVELS = [
                            { size: '0.9375rem', lh: '1.75' },  /* 소 */
                            { size: '1.0625rem', lh: '1.85' },  /* 기본 */
                            { size: '1.25rem',   lh: '1.9'  }   /* 대 */
                        ];
                        var fsIdx = 1; /* 기본값: 중간 */

                        /* localStorage에서 저장된 크기 불러오기 */
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
                            /* 스티키 바 버튼 활성 상태 */
                            var ids = ['lp-fz-small', 'lp-fz-mid', 'lp-fz-large',
                                       'lp-hdr-fz-small', 'lp-hdr-fz-mid', 'lp-hdr-fz-large'];
                            ids.forEach(function (id, i) {
                                var btn = document.getElementById(id);
                                if (!btn) return;
                                var level = i % 3; /* 0=소,1=중,2=대 */
                                btn.classList.toggle('lp-fz-active', level === fsIdx);
                            });
                            try { localStorage.setItem(FS_KEY, String(fsIdx)); } catch (e) {}
                        }

                        window.lpFontSize = function (dir) {
                            /* dir: -1 한 단계 작게 / 0 기본 리셋 / 1 한 단계 크게 */
                            applyFontSize(dir === 0 ? 1 : fsIdx + dir);
                        };

                        /* 초기화 — 저장된 크기 적용 */
                        applyFontSize(fsIdx);

                        /* ── SNS 공유 함수 ── */
                        window.lpShare = function (platform) {
                            var url   = encodeURIComponent(location.href);
                            var title = encodeURIComponent(document.title);
                            var targets = {
                                facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + url,
                                twitter:  'https://twitter.com/intent/tweet?url=' + url + '&text=' + title
                            };
                            if (platform === 'kakao') {
                                /* Kakao SDK 미설치 환경 폴백: URL 복사 */
                                if (window.Kakao && window.Kakao.isInitialized && window.Kakao.isInitialized()) {
                                    window.Kakao.Link.sendDefault({ objectType: 'feed',
                                        content: { title: document.title, webUrl: location.href,
                                            mobileWebUrl: location.href,
                                            imageUrl: '', description: '' },
                                        buttons: [{ title: '기사 보기', link: { webUrl: location.href } }] });
                                } else {
                                    navigator.clipboard.writeText(location.href).then(function () {
                                        alert('카카오톡 SDK 미설치 환경입니다.\nURL이 클립보드에 복사되었습니다.');
                                    });
                                }
                                return;
                            }
                            if (targets[platform]) {
                                window.open(targets[platform], '_blank', 'width=620,height=440,noopener,noreferrer');
                            }
                        };

                        /* ── URL 복사 함수 ── */
                        window.lpCopyUrl = function (btn) {
                            navigator.clipboard.writeText(location.href).then(function () {
                                var orig = btn ? btn.title : '';
                                if (btn) btn.title = '복사 완료!';
                                setTimeout(function () { if (btn) btn.title = orig; }, 1500);
                                alert('URL이 클립보드에 복사되었습니다.');
                            }).catch(function () {
                                /* 구형 브라우저 폴백 */
                                var ta = document.createElement('textarea');
                                ta.value = location.href;
                                document.body.appendChild(ta);
                                ta.select(); document.execCommand('copy');
                                document.body.removeChild(ta);
                                alert('URL이 클립보드에 복사되었습니다.');
                            });
                        };
                    })();
                    </script>
                    <?php endwhile; ?>

                <?php elseif (is_author()) : ?>
                    <!-- 기자 페이지 (Author Archive) -->
                    <?php
                    $lp_reporter        = get_queried_object(); // WP_User
                    $lp_reporter_bio    = get_user_meta($lp_reporter->ID, 'lara_reporter_bio', true);
                    $lp_reporter_count  = count_user_posts($lp_reporter->ID, 'post', true);
                    ?>

                    <!-- 기자 프로필 히어로 카드 -->
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-8 mb-8 border-b border-slate-200">
                        <div class="w-24 h-24 rounded-full overflow-hidden flex-shrink-0 shadow-md ring-4 ring-white border border-slate-200">
                            <?php echo get_avatar($lp_reporter->ID, 96, '', esc_attr($lp_reporter->display_name), ['class' => 'w-full h-full object-cover']); ?>
                        </div>
                        <div class="flex-grow text-center sm:text-left">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-1">
                                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                                    <?php echo esc_html($lp_reporter->display_name); ?> 기자
                                </h1>
                                <span class="inline-flex items-center gap-1 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-full px-2.5 py-0.5 font-medium self-center sm:self-auto">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                                    기사 <?php echo number_format($lp_reporter_count); ?>건
                                </span>
                            </div>
                            <p class="text-slate-500 text-sm leading-relaxed mb-4 max-w-lg">
                                <?php echo esc_html($lp_reporter_bio ?: '항상 정확하고 빠른 소식을 전해드리겠습니다.'); ?>
                            </p>
                            <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                                <?php if ($lp_reporter->user_email) : ?>
                                <a href="mailto:<?php echo esc_attr($lp_reporter->user_email); ?>"
                                   class="inline-flex items-center gap-1.5 text-slate-500 hover:text-blue-600 transition bg-white border border-slate-200 hover:border-blue-300 px-3 py-1.5 rounded-lg text-xs font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <?php echo esc_html($lp_reporter->user_email); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 기자 기사 목록 -->
                    <h2 class="text-lg font-bold text-slate-800 mb-5 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-blue-600 rounded inline-block"></span>
                        작성 기사
                    </h2>

                    <?php if (have_posts()) : ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <?php while (have_posts()) : the_post(); ?>
                        <article class="group flex gap-4 p-4 bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-200 rounded-xl transition">
                            <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" class="flex-shrink-0">
                                <?php the_post_thumbnail('thumbnail', ['class' => 'w-24 h-20 object-cover rounded-lg shadow-sm group-hover:shadow-md transition']); ?>
                            </a>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0">
                                <?php
                                $lp_cats = get_the_category();
                                if ($lp_cats) {
                                    echo '<span class="text-xs text-blue-600 font-semibold mb-1 block">' . esc_html($lp_cats[0]->name) . '</span>';
                                }
                                ?>
                                <h3 class="font-bold text-slate-800 group-hover:text-blue-700 transition leading-snug line-clamp-2 text-sm mb-2">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <span class="text-xs text-slate-400"><?php echo get_the_date('Y.m.d'); ?></span>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- 페이지네이션 -->
                    <?php
                    $lp_pagination = paginate_links([
                        'total'     => $wp_query->max_num_pages,
                        'current'   => max(1, get_query_var('paged')),
                        'prev_text' => '&laquo; 이전',
                        'next_text' => '다음 &raquo;',
                        'type'      => 'array',
                    ]);
                    if ($lp_pagination) :
                    ?>
                    <nav class="flex justify-center gap-1 mt-8" aria-label="페이지네이션">
                        <?php foreach ($lp_pagination as $lp_page_link) : ?>
                            <span class="[&>a]:inline-flex [&>a]:items-center [&>a]:px-3 [&>a]:py-1.5 [&>a]:text-sm [&>a]:rounded-lg [&>a]:border [&>a]:border-slate-200 [&>a]:text-slate-600 [&>a]:hover:bg-blue-50 [&>a]:hover:border-blue-300 [&>a]:transition
                                        [&>.current]:inline-flex [&>.current]:items-center [&>.current]:px-3 [&>.current]:py-1.5 [&>.current]:text-sm [&>.current]:rounded-lg [&>.current]:bg-blue-600 [&>.current]:text-white [&>.current]:font-bold [&>.current]:border [&>.current]:border-blue-600">
                                <?php echo $lp_page_link; ?>
                            </span>
                        <?php endforeach; ?>
                    </nav>
                    <?php endif; ?>

                    <?php else : ?>
                    <div class="text-center py-16 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l6 6v10a2 2 0 01-2 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 2v6h6"/></svg>
                        <p class="text-sm">작성된 기사가 없습니다.</p>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- 일반 페이지 -->
                    <?php
                    if (have_posts()) :
                        while (have_posts()) : the_post();
                            the_content();
                        endwhile;
                    else: ?>
                        <div class="text-center py-20 text-slate-500">표시할 콘텐츠가 없습니다.</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- 우측 사이드바 -->
            <aside class="lg:col-span-1 space-y-6 <?php echo $lp_sidebar_class; ?>">
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">기사 검색</h3>
                    <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="flex">
                        <input type="text" name="s" placeholder="검색어 입력..." class="w-full border border-slate-300 rounded-l px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <button type="submit" class="bg-slate-900 text-white px-4 rounded-r text-sm font-medium hover:bg-slate-800">검색</button>
                    </form>
                </div>

                <!-- 카테고리 목록 위젯 -->
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

                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">
                        많이 본 뉴스
                        <span class="ml-auto text-[10px] font-normal text-slate-400 tracking-wide">HOT</span>
                    </h3>
                    <?php
                    /* ── 많이 본 뉴스 : 해커뉴스 랭킹 공식 ────────────────────
                     *  score = (p - 1) / (t + 2)^g
                     *    p : 조회수(lara_post_views), 최솟값 1
                     *    t : 발행 후 경과 시간(시간 단위)
                     *    g : gravity 상수 — 높을수록 오래된 기사가 빠르게 하락
                     *  결과는 10분 Transient 캐시에 저장해 DB 부하를 최소화.
                     * ──────────────────────────────────────────────────────── */
                    $lp_hot_cache_key = 'lp_hot_news_v2';
                    $lp_hot_list      = get_transient( $lp_hot_cache_key );

                    if ( $lp_hot_list === false ) {
                        $lp_gravity  = 1.8;
                        $lp_now      = time();
                        $lp_pool_q   = new WP_Query( [
                            'post_type'              => 'post', /* 라라프레스 게시판(lara_post) 제외, WP 기사만 */
                            'post_status'            => 'publish',
                            'posts_per_page'         => 200,
                            'date_query'             => [ [ 'after' => '90 days ago' ] ],
                            'no_found_rows'          => true,   // COUNT(*) 생략으로 속도 향상
                            'update_post_meta_cache' => true,   // 메타 일괄 로드 (쿼리 1회)
                            'update_post_term_cache' => false,  // 태그/카테고리 캐시 불필요
                        ] );

                        $lp_scored = [];
                        foreach ( $lp_pool_q->posts as $lp_p ) {
                            $lp_views    = max( 1, (int) get_post_meta( $lp_p->ID, 'lara_post_views', true ) );
                            $lp_hours    = max( 0, ( $lp_now - strtotime( $lp_p->post_date_gmt ) ) / 3600 );
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
                                'score' => round( $lp_scored[ $lp_id ], 3 ),
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
<?php endif; /* amber-journal else end */ ?>
