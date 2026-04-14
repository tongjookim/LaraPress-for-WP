<?php
/**
 * LaraPress Theme Footer
 * get_footer() 에 의해 index.php, search.php, archive.php 에서 공통 포함됩니다.
 * lp_skin_vars()로 스킨 변수를 가져와 푸터 출력에 사용합니다.
 */
extract( lp_skin_vars() );
$lp_hero_enable = get_theme_mod( 'lp_hero_enable', '1' );
?>

    <!-- CSS 3D 글래스 큐브 Hero Section — 메인 하단 (프론트 페이지만) -->
    <?php if ( $lp_hero_enable !== '0' && ( is_front_page() || is_home() ) ) : ?>
    <section class="lp-hero">
        <div class="lp-hero__text">
            <span class="lp-hero__eyebrow">LARAPRESS · NEWS ENGINE</span>
            <h2 class="lp-hero__title">신뢰와 속도의<br><span>뉴스를 전합니다</span></h2>
            <p class="lp-hero__sub">11년 발행인의 노하우가 담긴 AI 최적화 뉴스 플랫폼.<br>정확하고 빠른 소식을 매일 전해드립니다.</p>
        </div>
        <div class="lp-cube-scene">
            <div class="lp-cube-glow"></div>
            <div class="lp-cube">
                <div class="lp-face lp-face--front">BREAKING</div>
                <div class="lp-face lp-face--back">SCOOP</div>
                <div class="lp-face lp-face--right">속&nbsp;&nbsp;&nbsp;보</div>
                <div class="lp-face lp-face--left">단&nbsp;&nbsp;&nbsp;독</div>
                <div class="lp-face lp-face--top">TODAY</div>
                <div class="lp-face lp-face--bottom">NEWS</div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 푸터 영역 -->
    <?php if ( $current_theme_style === 'newyorktimes-style' ) :
        $lp_cf_top_cats = get_categories( [ 'parent' => 0, 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC' ] );
    ?>
    <footer class="lp-classic-footer mt-auto">

        <!-- ① 마스트헤드: 로고 + 소셜 아이콘 -->
        <div class="lp-cf-head">
            <div class="<?php echo $container_class; ?> lp-cf-head-inner">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="lp-cf-logo-link">
                    <?php if ( has_custom_logo() ) : the_custom_logo();
                    else : ?>
                    <span class="lp-cf-sitename"><?php echo esc_html( get_theme_mod( 'lp_footer_company_name', get_bloginfo( 'name' ) ) ); ?></span>
                    <?php endif; ?>
                </a>
                <div class="lp-cf-socials">
                    <?php echo lp_social_links_html( 'lp-cf-social', 18 ); ?>
                </div>
            </div>
        </div>

        <!-- ② 메인: 카테고리 메가메뉴 + 우측 서비스·회사정보 -->
        <div class="lp-cf-body">
            <div class="<?php echo $container_class; ?> lp-cf-body-grid">
                <div class="lp-cf-cats">
                    <?php foreach ( $lp_cf_top_cats as $lp_cf_cat ) :
                        $lp_cf_children = get_categories( [ 'parent' => $lp_cf_cat->term_id, 'hide_empty' => false ] );
                    ?>
                    <div class="lp-cf-cat-col">
                        <h4 class="lp-cf-cat-title">
                            <a href="<?php echo esc_url( get_category_link( $lp_cf_cat->term_id ) ); ?>"><?php echo esc_html( $lp_cf_cat->name ); ?></a>
                        </h4>
                        <?php if ( $lp_cf_children ) : ?>
                        <ul class="lp-cf-cat-list">
                            <?php foreach ( $lp_cf_children as $lp_cf_child ) : ?>
                            <li><a href="<?php echo esc_url( get_category_link( $lp_cf_child->term_id ) ); ?>"><?php echo esc_html( $lp_cf_child->name ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="lp-cf-right">
                    <?php if ( has_nav_menu( 'footer-menu-1' ) ) : ?>
                    <div class="lp-cf-service">
                        <?php wp_nav_menu( [ 'theme_location' => 'footer-menu-1', 'container' => false, 'fallback_cb' => false, 'menu_class' => 'lp-cf-service-list' ] ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ③ 언론사 법적 정보 -->
        <div class="lp-cf-legal">
            <div class="<?php echo $container_class; ?>">
                <div class="lp-cf-legal-rows">
                    <p class="lp-cf-legal-row">
                        <?php if ( get_theme_mod( 'lp_pub_editor_same', '0' ) === '1' ) : ?>
                        <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김동주' ) ); ?></span>
                        <?php else : ?>
                        <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김동주' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '김동주' ) ); ?></span>
                        <?php endif; ?>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김동주' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김동주' ) ); ?></span>
                    </p>
                    <p class="lp-cf-legal-row">
                        <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2015.03.27' ) ); ?></span>
                    </p>
                    <p class="lp-cf-legal-row">
                        <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '강원특별자치도 강릉시 (예시)' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>대표전화:</strong> <?php echo esc_html( get_theme_mod( 'lp_phone', '1588-0000' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>이메일:</strong> <?php echo esc_html( get_theme_mod( 'lp_email', 'support@larapress.io' ) ); ?></span>
                    </p>
                    <p class="lp-cf-legal-copy">
                        <?php echo esc_html( get_theme_mod( 'lp_copyright', 'Copyright © ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.' ) ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- ④ 하단 바: 정책 링크 -->
        <?php if ( has_nav_menu( 'footer-menu-2' ) ) : ?>
        <div class="lp-cf-foot">
            <div class="<?php echo $container_class; ?> lp-cf-foot-inner">
                <nav aria-label="정책 링크">
                    <?php wp_nav_menu( [ 'theme_location' => 'footer-menu-2', 'container' => false, 'fallback_cb' => false, 'menu_class' => 'lp-cf-policy-list' ] ); ?>
                </nav>
            </div>
        </div>
        <?php endif; ?>

    </footer>
    <?php elseif ( $current_theme_style === 'basic' ) : ?>
    <!-- ═══════════════════════════════════════════════════
         Basic 스킨 푸터
         ═══════════════════════════════════════════════════ -->
    <footer class="basic-footer">
        <div class="<?php echo $container_class; ?>">
            <div class="basic-footer-grid">
                <div>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="basic-footer-logo">
                        <?php echo esc_html( get_theme_mod( 'lp_footer_company_name', get_bloginfo( 'name' ) ) ); ?>
                    </a>
                    <?php $lp_fdesc = get_theme_mod( 'lp_footer_company_desc', '' ); ?>
                    <?php if ( $lp_fdesc ) : ?>
                    <p class="basic-footer-desc"><?php echo esc_html( $lp_fdesc ); ?></p>
                    <?php endif; ?>
                    <?php $lp_basic_socials = lp_social_links_html( 'lp-footer-social', 18 ); ?>
                    <?php if ( $lp_basic_socials ) : ?>
                    <div class="lp-footer-socials"><?php echo $lp_basic_socials; ?></div>
                    <?php endif; ?>
                </div>
                <?php if ( has_nav_menu( 'footer-menu-1' ) ) : ?>
                <div class="basic-footer-col">
                    <h3><?php echo esc_html( get_theme_mod( 'lp_footer_menu1_title', '회사 소개' ) ); ?></h3>
                    <?php wp_nav_menu( [ 'theme_location' => 'footer-menu-1', 'container' => false, 'fallback_cb' => false ] ); ?>
                </div>
                <?php endif; ?>
                <?php if ( has_nav_menu( 'footer-menu-2' ) ) : ?>
                <div class="basic-footer-col">
                    <h3><?php echo esc_html( get_theme_mod( 'lp_footer_menu2_title', '서비스 규범' ) ); ?></h3>
                    <?php wp_nav_menu( [ 'theme_location' => 'footer-menu-2', 'container' => false, 'fallback_cb' => false ] ); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="basic-footer-legal">
                <p>
                    <?php if ( get_theme_mod( 'lp_pub_editor_same', '0' ) === '1' ) : ?>
                    <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김동주' ) ); ?></span>
                    <?php else : ?>
                    <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김동주' ) ); ?></span>
                    <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '김동주' ) ); ?></span>
                    <?php endif; ?>
                    <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김동주' ) ); ?></span>
                    <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김동주' ) ); ?></span>
                </p>
                <p>
                    <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                    <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                    <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2015.03.27' ) ); ?></span>
                </p>
                <p>
                    <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '강원특별자치도 강릉시 (예시)' ) ); ?></span>
                    <span><strong>대표전화:</strong> <?php echo esc_html( get_theme_mod( 'lp_phone', '1588-0000' ) ); ?></span>
                    <span><strong>이메일:</strong> <?php echo esc_html( get_theme_mod( 'lp_email', 'support@larapress.io' ) ); ?></span>
                </p>
                <p class="basic-footer-copy">
                    <?php echo esc_html( get_theme_mod( 'lp_copyright', 'Copyright © ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.' ) ); ?>
                </p>
            </div>
        </div>
    </footer>

    <?php elseif ( $current_theme_style === 'amber-journal' ) : ?>
    <!-- ═══════════════════════════════════════════════════
         엠버 저널 (Amber Journal) 푸터
         ═══════════════════════════════════════════════════ -->
    <footer class="aj-footer mt-auto">
        <div class="<?php echo $container_class; ?>">
            <div class="aj-footer-body">

                <!-- Col 1: 사이트 정보 -->
                <div>
                    <p class="aj-footer-logo-text">
                        <?php echo esc_html( get_theme_mod( 'lp_footer_company_name', get_bloginfo( 'name' ) ) ); ?>
                    </p>
                    <?php $aj_fdesc = get_theme_mod( 'lp_footer_company_desc', '' ); ?>
                    <?php if ( $aj_fdesc ) : ?>
                    <p class="aj-footer-desc"><?php echo esc_html( $aj_fdesc ); ?></p>
                    <?php endif; ?>
                    <div class="aj-footer-legal">
                        <p>
                            <?php if ( get_theme_mod( 'lp_pub_editor_same', '0' ) === '1' ) : ?>
                            <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김동주' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <?php else : ?>
                            <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김동주' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '김동주' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <?php endif; ?>
                            <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김동주' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김동주' ) ); ?></span>
                        </p>
                        <p>
                            <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2015.03.27' ) ); ?></span>
                        </p>
                        <p>
                            <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '강원특별자치도 강릉시' ) ); ?></span>
                        </p>
                        <p>
                            <span><strong>대표전화:</strong> <?php echo esc_html( get_theme_mod( 'lp_phone', '1588-0000' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>이메일:</strong> <?php echo esc_html( get_theme_mod( 'lp_email', 'support@larapress.io' ) ); ?></span>
                        </p>
                    </div>
                </div>

                <!-- Col 2: 공지사항 리스트 -->
                <div>
                    <p class="aj-footer-col-title">공지사항</p>
                    <?php
                    $aj_notices = new WP_Query( [
                        'post_type'      => 'post',
                        'posts_per_page' => 5,
                        'category_name'  => '공지사항',
                        'no_found_rows'  => true,
                    ] );
                    if ( ! $aj_notices->have_posts() ) {
                        $aj_notices = new WP_Query( [ 'posts_per_page' => 5, 'no_found_rows' => true ] );
                    }
                    ?>
                    <ul class="aj-footer-notice-list">
                        <?php while ( $aj_notices->have_posts() ) : $aj_notices->the_post(); ?>
                        <li>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <p class="aj-footer-notice-date"><?php echo get_the_date( 'Y.m.d' ); ?></p>
                        </li>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </ul>
                </div>

                <!-- Col 3: 메뉴 링크 -->
                <div>
                    <p class="aj-footer-col-title"><?php echo esc_html( get_theme_mod( 'lp_footer_menu1_title', '회사 소개' ) ); ?></p>
                    <?php
                    if ( has_nav_menu( 'footer-menu-1' ) ) {
                        wp_nav_menu( [ 'theme_location' => 'footer-menu-1', 'container' => false, 'fallback_cb' => false ] );
                    }
                    ?>
                </div>

            </div>

            <!-- 하단 바: SNS + 카피라이트 -->
            <div class="aj-footer-bottom">
                <p class="aj-footer-copy">
                    <?php echo esc_html( get_theme_mod( 'lp_copyright', 'Copyright © ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.' ) ); ?>
                </p>
                <?php $aj_socials = lp_social_links_html( 'aj-footer-social', 16 ); ?>
                <?php if ( $aj_socials ) : ?>
                <div class="aj-footer-socials"><?php echo $aj_socials; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <?php else : /* SWN 스킨 — 다크 푸터 */ ?>
    <footer class="bg-slate-900 text-slate-400 py-12 mt-auto">
        <div class="<?php echo $container_class; ?>">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 border-b border-slate-800 pb-8">
                <div class="md:col-span-2">
                    <h2 class="text-2xl font-black text-white mb-4 tracking-tighter">
                        <?php echo esc_html( get_theme_mod( 'lp_footer_company_name', get_bloginfo( 'name' ) ) ); ?>
                    </h2>
                    <?php
                    $lp_footer_desc = get_theme_mod( 'lp_footer_company_desc', '' );
                    if ( $lp_footer_desc ) :
                    ?>
                    <p class="text-sm leading-relaxed text-slate-500 mb-4 max-w-sm">
                        <?php echo esc_html( $lp_footer_desc ); ?>
                    </p>
                    <?php endif; ?>
                    <?php $lp_swn_socials = lp_social_links_html( 'lp-footer-social', 18 ); ?>
                    <?php if ( $lp_swn_socials ) : ?>
                    <div class="lp-footer-socials"><?php echo $lp_swn_socials; ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">
                        <?php echo esc_html( get_theme_mod( 'lp_footer_menu1_title', '회사 소개' ) ); ?>
                    </h3>
                    <div class="footer-menu-container">
                        <?php
                        if ( has_nav_menu( 'footer-menu-1' ) ) {
                            wp_nav_menu( [ 'theme_location' => 'footer-menu-1', 'container' => false, 'fallback_cb' => false ] );
                        } else {
                            echo '<a href="' . admin_url( 'nav-menus.php' ) . '" class="text-blue-400 hover:text-white transition text-sm">메뉴를 설정해 주세요.</a>';
                        }
                        ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-white font-bold mb-4">
                        <?php echo esc_html( get_theme_mod( 'lp_footer_menu2_title', '서비스 규범' ) ); ?>
                    </h3>
                    <div class="footer-menu-container">
                        <?php
                        if ( has_nav_menu( 'footer-menu-2' ) ) {
                            wp_nav_menu( [ 'theme_location' => 'footer-menu-2', 'container' => false, 'fallback_cb' => false ] );
                        } else {
                            echo '<a href="' . admin_url( 'nav-menus.php' ) . '" class="text-blue-400 hover:text-white transition text-sm">메뉴를 설정해 주세요.</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="text-xs text-slate-500 space-y-2">
                <p class="md:flex md:gap-4 md:flex-wrap">
                    <?php if ( get_theme_mod( 'lp_pub_editor_same', '0' ) === '1' ) : ?>
                    <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김동주' ) ); ?></span>
                    <?php else : ?>
                    <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김동주' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '김동주' ) ); ?></span>
                    <?php endif; ?>
                    <span class="hidden md:inline">|</span>
                    <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김동주' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김동주' ) ); ?></span>
                </p>
                <p class="md:flex md:gap-4 md:flex-wrap">
                    <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2015.03.27' ) ); ?></span>
                </p>
                <p class="md:flex md:gap-4 md:flex-wrap">
                    <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '강원특별자치도 강릉시 (예시)' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>대표전화:</strong> <?php echo esc_html( get_theme_mod( 'lp_phone', '1588-0000' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>이메일:</strong> <?php echo esc_html( get_theme_mod( 'lp_email', 'support@larapress.io' ) ); ?></span>
                </p>
                <p class="mt-4 text-slate-600">
                    <?php echo esc_html( get_theme_mod( 'lp_copyright', 'Copyright © ' . date( 'Y' ) . ' 수완뉴스 & LaraPress. All rights reserved.' ) ); ?>
                </p>
            </div>
        </div>
    </footer>
    <?php endif; /* end layout conditional footer */ ?>

    <?php if ( $current_theme_style === 'amber-journal' ) : ?>
    <!-- 엠버 저널 검색 전면 모달 -->
    <div class="aj-search-modal" id="ajSearchModal" role="dialog" aria-modal="true" aria-label="검색">
        <div class="aj-search-modal-box">
            <button class="aj-search-close-btn" id="ajSearchClose" aria-label="닫기">✕</button>
            <p class="aj-search-modal-label">기사 검색</p>
            <form class="aj-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input class="aj-search-input" id="ajSearchInput" type="search" name="s"
                    placeholder="검색어를 입력하세요…"
                    value="<?php echo esc_attr( get_search_query() ); ?>"
                    autocomplete="off" autocorrect="off" spellcheck="false">
                <button type="submit" class="aj-search-submit">검색</button>
            </form>
            <p class="aj-search-hint">Enter 키 또는 검색 버튼 · Esc로 닫기</p>
        </div>
    </div>
    <?php endif; ?>

    <?php wp_footer(); ?>

    <?php if ( $current_theme_style === 'newyorktimes-style' ) : ?>
    <script>
    (function () {
        var openBtn  = document.getElementById('nytSearchOpen');
        var modal    = document.getElementById('nytSearchModal');
        var closeBtn = document.getElementById('nytSearchClose');
        var input    = document.getElementById('nytSearchInput');

        if (!openBtn || !modal) return;

        function openModal() {
            modal.classList.add('is-open');
            if (input) setTimeout(function () { input.focus(); }, 50);
        }
        function closeModal() {
            modal.classList.remove('is-open');
            openBtn.focus();
        }

        openBtn.addEventListener('click', openModal);
        closeBtn && closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    })();
    </script>
    <?php endif; ?>

    <!-- 모바일 햄버거 공통 JS -->
    <script>
    (function () {
        function initHamburger(btnId, navId) {
            var btn = document.getElementById(btnId);
            var nav = document.getElementById(navId);
            if (!btn || !nav) return;
            btn.addEventListener('click', function () {
                var open = nav.classList.toggle('is-open');
                btn.classList.toggle('is-open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.addEventListener('click', function (e) {
                if (!btn.contains(e.target) && !nav.contains(e.target)) {
                    nav.classList.remove('is-open');
                    btn.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        initHamburger('lpSwn-hamburger',   'lpSwn-mobile-nav');
        initHamburger('lpNyt-hamburger',   'lpNyt-mobile-nav');
        initHamburger('lpBasic-hamburger', 'lpBasic-mobile-nav');

        /* ── 드롭다운 서브메뉴 (SWN · NYT · Basic 공통) ── */
        (function () {
            var CARET_SVG = '<svg width="8" height="5" viewBox="0 0 8 5" fill="currentColor" aria-hidden="true"><path d="M0 0l4 5 4-5z"/></svg>';

            /* 데스크탑: 부모 항목 <a> 안에 ▾ 화살표 span 주입 */
            document.querySelectorAll(
                '.lp-desktop-nav .menu-item-has-children > a'
            ).forEach(function (a) {
                var span = document.createElement('span');
                span.className = 'lp-dd-caret';
                span.setAttribute('aria-hidden', 'true');
                span.innerHTML = CARET_SVG;
                a.appendChild(span);
            });

            /* 모바일: 각 부모 <li>에 토글 버튼 주입 */
            document.querySelectorAll(
                '.lp-mobile-nav .menu-item-has-children'
            ).forEach(function (li) {
                var a   = li.querySelector(':scope > a');
                var sub = li.querySelector(':scope > ul.sub-menu');
                if (!a || !sub) return;

                var btn = document.createElement('button');
                btn.className = 'lp-mob-dd-caret';
                btn.setAttribute('type', 'button');
                btn.setAttribute('aria-expanded', 'false');
                btn.setAttribute('aria-label', '하위 메뉴 열기');
                btn.innerHTML = CARET_SVG;
                a.insertAdjacentElement('afterend', btn);

                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var open = sub.classList.toggle('is-open');
                    btn.classList.toggle('is-open', open);
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });
        })();

    <?php if ( $current_theme_style === 'amber-journal' ) : ?>
    /* ── Amber Journal 검색 모달 ── */
    (function () {
        var openBtn = document.getElementById('ajSearchOpen');
        var modal   = document.getElementById('ajSearchModal');
        var closeBtn= document.getElementById('ajSearchClose');
        var input   = document.getElementById('ajSearchInput');
        if (!openBtn || !modal) return;

        function openModal() {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            if (input) setTimeout(function () { input.focus(); }, 60);
        }
        function closeModal() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            openBtn.focus();
        }
        openBtn.addEventListener('click', openModal);
        closeBtn && closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    })();

    /* ── Amber Journal 햄버거 ── */
    (function () {
        var btn  = document.getElementById('ajHamburger');
        var menu = document.getElementById('ajGnavMenu');
        if (!btn || !menu) return;
        btn.addEventListener('click', function () {
            var open = menu.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    })();
    <?php endif; ?>
    })();

    /* ── 다크모드 토글 ──────────────────────────── */
    (function () {
        var btn  = document.getElementById('lpDarkModeBtn');
        var html = document.documentElement;
        if (!btn) return;
        btn.addEventListener('click', function () {
            var isDark = html.classList.toggle('dark');
            try { localStorage.setItem('lp-dark-mode', isDark ? 'dark' : 'light'); } catch(e) {}
        });
    })();

    <?php if ( $current_theme_style === 'basic' ) : ?>
    /* ── Basic 검색 드롭다운 ─────────────────────── */
    (function () {
        var openBtn = document.getElementById('basicSearchOpen');
        var drop    = document.getElementById('basicSearchDrop');
        var input   = document.getElementById('basicSearchInput');
        if (!openBtn || !drop) return;

        openBtn.addEventListener('click', function () {
            var open = drop.classList.toggle('is-open');
            if (open && input) setTimeout(function () { input.focus(); }, 50);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') drop.classList.remove('is-open');
        });

        document.addEventListener('click', function (e) {
            if (!openBtn.contains(e.target) && !drop.contains(e.target)) {
                drop.classList.remove('is-open');
            }
        });
    })();
    <?php endif; ?>
    </script>
</body>
</html>
