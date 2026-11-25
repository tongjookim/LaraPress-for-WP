<?php
/**
 * LaraPress Theme Functions
 */

// 라이선스(PRO 잠금) — nest License Server Manager 연동
require_once get_template_directory() . '/inc/license.php';

function larapress_theme_setup() {
    // 테마 기본 지원 기능
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['caption', 'gallery']);
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    // 워드프레스 메뉴 등록 (상단, 메인, 푸터)
    register_nav_menus([
        'top-menu'     => '상단 탑 메뉴 (로그인/회원가입 등)',
        'primary-menu' => '메인 카테고리 메뉴 (GNB)',
        'footer-menu-1'=> '푸터 메뉴 1 (회사 소개)',
        'footer-menu-2'=> '푸터 메뉴 2 (서비스 규범)'
    ]);
}
add_action('after_setup_theme', 'larapress_theme_setup');

function larapress_enqueue_scripts() {
    wp_enqueue_style('larapress-style', get_stylesheet_uri());
    // Tailwind CSS (프로토타입용)
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', [], null, false);
    // Dashicons — 카테고리 불릿 아이콘 (설정 활성화 시)
    if ( get_theme_mod( 'lp_cat_show_bullet', '1' ) === '1' ) {
        wp_enqueue_style( 'dashicons' );
    }

    // ── 카카오 JavaScript SDK ────────────────────────────────
    // 커스터마이저에 JavaScript 키가 입력된 경우에만 SDK 를 불러온다.
    $kakao_key = trim( (string) get_theme_mod( 'lp_kakao_js_key', '' ) );
    $deps      = [];
    if ( $kakao_key !== '' ) {
        wp_enqueue_script(
            'kakao-js-sdk',
            'https://t1.kakaocdn.net/kakao_js_sdk/' . LP_KAKAO_SDK_VERSION . '/kakao.min.js',
            [],
            LP_KAKAO_SDK_VERSION,
            true
        );
        $deps[] = 'kakao-js-sdk';
    }

    // 공유 헬퍼(window.lpKakaoShare)는 키가 없어도 등록 — URL 복사 폴백 제공
    wp_enqueue_script(
        'lp-kakao-share',
        get_template_directory_uri() . '/assets/js/kakao-share.js',
        $deps,
        '1.1.0',
        true
    );
    wp_localize_script( 'lp-kakao-share', 'lpKakaoData', [
        'jsKey'        => $kakao_key,
        'defaultImage' => lp_kakao_default_share_image(),
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'postId'       => is_singular( 'post' ) ? get_queried_object_id() : 0,
    ] );
}
add_action('wp_enqueue_scripts', 'larapress_enqueue_scripts');

/** 카카오 JavaScript SDK 버전 (CDN 무결성 해시와 짝을 이룸) */
define( 'LP_KAKAO_SDK_VERSION', '2.7.5' );
define( 'LP_KAKAO_SDK_INTEGRITY', 'sha384-dok87au0gKqJdxs7msEdBPNnKSRT+/mhTVzq+qOhcL464zXwvcrpjeWvyj1kCdq6' );

/**
 * 카카오 SDK CDN 스크립트에 무결성(SRI) 속성 부여.
 * wp_enqueue_script 는 integrity/crossorigin 을 지원하지 않으므로 태그를 직접 보정한다.
 */
function larapress_kakao_sdk_script_tag( $tag, $handle ) {
    if ( $handle !== 'kakao-js-sdk' ) return $tag;
    return str_replace(
        ' src=',
        ' integrity="' . esc_attr( LP_KAKAO_SDK_INTEGRITY ) . '" crossorigin="anonymous" src=',
        $tag
    );
}
add_filter( 'script_loader_tag', 'larapress_kakao_sdk_script_tag', 10, 2 );

/**
 * 카카오 공유 카드의 기본 이미지.
 * 단건 글이면 특성 이미지, 없으면 커스터마이저 로고 이미지를 사용한다.
 */
function lp_kakao_default_share_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        $thumb = get_the_post_thumbnail_url( null, 'large' );
        if ( $thumb ) return lp_encode_url_path( $thumb );
    }
    return lp_encode_url_path( (string) get_theme_mod( 'lp_logo_image', '' ) );
}

/**
 * URL 경로의 비ASCII 문자(한글 파일명 등)를 퍼센트 인코딩한다.
 * 인코딩되지 않은 한글 URL은 서버(Cloudflare)가 400을 반환해
 * 카카오톡·페이스북 등 공유 카드의 대표 이미지가 깨진다.
 */
function lp_encode_url_path( $url ) {
    if ( ! is_string( $url ) || $url === '' ) return $url;
    return preg_replace_callback(
        '/[^\x00-\x7F]+/',
        static function ( $m ) { return rawurlencode( $m[0] ); },
        $url
    );
}
add_filter( 'wpseo_opengraph_image', 'lp_encode_url_path' );
add_filter( 'wpseo_twitter_image', 'lp_encode_url_path' );

// ─────────────────────────────────────────────────────────────
// 일반 WP post 조회수 추적 — lara_post_views 메타 (플러그인과 동일 키)
// lara_post CPT는 플러그인(LaraPress_Board_System)이 직접 처리하므로 여기서는 제외.
// ─────────────────────────────────────────────────────────────
add_action('wp', function () {
    if ( ! is_singular( 'post' ) ) return;          // 일반 포스트 단건 페이지만
    if ( current_user_can( 'edit_posts' ) ) return; // 편집자 이상은 카운트 제외

    global $post;
    if ( ! $post ) return;

    // 새로고침으로 인한 중복 카운트 방지: 같은 방문자는 쿠키 유효기간(30분) 내 재조회 시 제외
    $cookie_name = 'lara_viewed_' . $post->ID;
    if ( isset( $_COOKIE[ $cookie_name ] ) ) return;

    $views = (int) get_post_meta( $post->ID, 'lara_post_views', true );
    update_post_meta( $post->ID, 'lara_post_views', $views + 1 );

    setcookie( $cookie_name, '1', time() + 30 * MINUTE_IN_SECONDS, '/' );
} );

// Polylang이 번역본 생성 시 "언어 복사" 기능으로 커스텀 필드를 복사하는데,
// lara_post_views까지 그대로 복사되면 새로 만든 번역글이 원본의 누적 조회수를
// 그대로 물려받아 "많이 본 뉴스" 랭킹이 즉시 왜곡된다. 복사 대상에서 제외.
add_filter( 'pll_copy_post_metas', function ( $keys ) {
    return array_diff( $keys, [ 'lara_post_views', 'lp_shares' ] );
} );

/**
 * 커스터마이저 커스텀 컨트롤 클래스 로드
 * (커스터마이저와 "테마 설정" 페이지가 같은 컨트롤 정의를 쓴다 — inc/customize-controls.php)
 */
function larapress_load_customize_controls() {
    if ( class_exists( 'LP_Home_Widgets_Control' ) ) {
        return;
    }
    if ( ! class_exists( 'WP_Customize_Control' ) ) {
        require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
    }
    require_once get_template_directory() . '/inc/customize-controls.php';
}

/**
 * 테마 사용자 정의하기(Customizer)에 한국형 언론사 설정 폼 추가
 */
function larapress_customize_register($wp_customize) {
    larapress_load_customize_controls();

    // ── 섹션: 헤더 브랜딩 설정 ────────────────────────────────
    $wp_customize->add_section( 'lp_header_section', [
        'title'       => '헤더 브랜딩 설정',
        'description' => '사이트명·슬로건·로고 이미지를 설정합니다. 모든 스킨(프레시·클래식·미니멀)에 공통 적용됩니다.',
        'priority'    => 25,
    ] );

    // 로고 타입 선택
    $wp_customize->add_setting( 'lp_logo_type', [
        'default'           => 'text',
        'sanitize_callback' => 'lp_sanitize_logo_type',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_logo_type', [
        'label'       => '로고 유형',
        'description' => '헤더에 표시할 로고 형식을 선택하세요.',
        'section'     => 'lp_header_section',
        'type'        => 'radio',
        'choices'     => [
            'text'  => '텍스트 로고 (사이트명을 스타일 텍스트로 표시)',
            'image' => '이미지 로고 (PNG · JPG · WebP 업로드)',
            'svg'   => 'SVG 로고 (SVG 코드 직접 입력 또는 파일 업로드)',
        ],
        'priority'    => 1,
    ] );

    // 사이트명 (헤더 표시용 — WP 기본 사이트 제목과 별개로 지정 가능)
    $wp_customize->add_setting( 'lp_site_name', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_site_name', [
        'label'       => '사이트명 (헤더 표시용)',
        'description' => '비워두면 WP 설정의 사이트 제목을 그대로 사용합니다.',
        'section'     => 'lp_header_section',
        'type'        => 'text',
        'priority'    => 2,
    ] );

    // 슬로건/태그라인 (헤더 표시용)
    $wp_customize->add_setting( 'lp_site_tagline', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_site_tagline', [
        'label'       => '슬로건 / 태그라인',
        'description' => '헤더 로고 아래·옆에 표시되는 부제 문구. 비워두면 WP 태그라인을 사용합니다.',
        'section'     => 'lp_header_section',
        'type'        => 'text',
        'priority'    => 3,
    ] );

    // 이미지 로고 업로드 (WP_Customize_Image_Control)
    $wp_customize->add_setting( 'lp_logo_image', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lp_logo_image', [
        'label'       => '로고 이미지 업로드',
        'description' => 'PNG · JPG · WebP 권장. 투명 배경은 PNG 사용.',
        'section'     => 'lp_header_section',
        'priority'    => 10,
    ] ) );

    // 다크모드 전용 로고 이미지 (선택 — 비워두면 위 로고를 다크모드에서도 그대로 사용)
    $wp_customize->add_setting( 'lp_logo_image_dark', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lp_logo_image_dark', [
        'label'       => '다크모드 전용 로고 이미지 (선택)',
        'description' => '다크모드에서 표시할 로고. 비워두면 위에서 업로드한 로고를 다크모드에서도 그대로 사용합니다.',
        'section'     => 'lp_header_section',
        'priority'    => 11,
    ] ) );

    // SVG 로고 코드 입력
    $wp_customize->add_setting( 'lp_logo_svg', [
        'default'           => '',
        'sanitize_callback' => 'lp_sanitize_logo_svg',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_logo_svg', [
        'label'       => 'SVG 코드 입력',
        'description' => '<svg ...> ... </svg> 전체 코드를 붙여넣으세요.',
        'section'     => 'lp_header_section',
        'type'        => 'textarea',
        'priority'    => 15,
    ] );

    // 로고 표시 너비 (px)
    $wp_customize->add_setting( 'lp_logo_width', [
        'default'           => '200',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_logo_width', [
        'label'       => '로고 표시 너비 (px)',
        'description' => '이미지/SVG 로고의 최대 가로 크기. 높이는 비율에 따라 자동 조정됩니다.',
        'section'     => 'lp_header_section',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 40, 'max' => 600, 'step' => 1 ],
        'priority'    => 20,
    ] );

    // 이미지/SVG 사용 시 사이트명 병행 표시 여부
    $wp_customize->add_setting( 'lp_logo_show_name', [
        'default'           => '0',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_logo_show_name', [
        'label'       => '로고 이미지 아래에 사이트명 텍스트도 함께 표시',
        'section'     => 'lp_header_section',
        'type'        => 'checkbox',
        'priority'    => 25,
    ] );

    // ── 섹션: 레이아웃 스킨 설정 ──────────────────────────────
    $wp_customize->add_section('larapress_layout_section', [
        'title'       => '레이아웃 스킨 설정',
        'description' => '사이트 전체에 적용할 디자인 스킨을 선택하세요. 저장 후 즉시 반영됩니다.',
        'priority'    => 30,   // 사이트 정체성 바로 아래
    ]);

    $wp_customize->add_setting('larapress_layout_style', [
        'default'           => 'swn-style',
        'sanitize_callback' => 'larapress_sanitize_layout_style',
        'transport'         => 'refresh',   // 레이아웃 전체 교체이므로 전체 새로고침
    ]);

    $wp_customize->add_control('larapress_layout_style', [
        'label'       => '레이아웃 스킨 선택',
        'description' => '사이트 전체 디자인 분위기를 결정합니다. 저장 후 즉시 반영됩니다.',
        'section'     => 'larapress_layout_section',
        'type'        => 'radio',
        'choices'     => [
            'swn-style'          => '🔵 프레시 (Fresh) — 깔끔한 청색 계열 모던 뉴스 레이아웃 (기본)',
            'newyorktimes-style' => '🗞️ 클래식 (Classic) — 세리프 서체 기반 고품격 정통 신문 스타일',
            'basic'              => '⬜ 미니멀 (Minimal) — 장식 없는 심플 기본 레이아웃',
            'amber-journal'      => '🟠 엠버 저널 (Amber Journal) — 오렌지/브라운 액센트 한국형 매거진 뉴스',
        ],
    ]);

    // ── 섹션: 사이드바 위젯 설정 ──────────────────────────────
    $wp_customize->add_section( 'lp_sidebar_section', [
        'title'       => '사이드바 위젯 설정',
        'description' => '우측 사이드바에 표시되는 위젯 옵션을 설정합니다.',
        'priority'    => 40,
    ] );

    // 카테고리 불릿 아이콘 표시 여부
    $wp_customize->add_setting( 'lp_cat_show_bullet', [
        'default'           => '1',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_cat_show_bullet', [
        'label'       => '카테고리 목록 — 왼쪽 불릿 아이콘 표시 (Dashicons)',
        'description' => '각 카테고리 항목 왼쪽에 화살표 아이콘을 표시합니다. 체크 해제 시 숨깁니다.',
        'section'     => 'lp_sidebar_section',
        'type'        => 'checkbox',
        'priority'    => 1,
    ] );

    // ── 아카이브 글 목록 레이아웃 ──────────────────────────
    $wp_customize->add_setting( 'lp_archive_style', [
        'default'           => 'list',
        'sanitize_callback' => 'lp_sanitize_archive_style',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control(
        new LP_Archive_Style_Control( $wp_customize, 'lp_archive_style', [
            'label'       => '카테고리 아카이브 글 목록 스타일',
            'description' => '카테고리·태그·작성자 아카이브 페이지의 기사 나열 형태를 선택하세요.',
            'section'     => 'lp_sidebar_section',
            'priority'    => 2,
        ] )
    );

    // ── 패널: 홈 화면 레이아웃 ──────────────────────────────
    $wp_customize->add_panel( 'lp_home_layout', [
        'title'       => '홈 화면 레이아웃',
        'description' => '섹션 그룹과 위젯을 추가하고 순서를 조정하세요.',
        'priority'    => 35,
    ] );

    // ── 섹션: 레이아웃 관리 (패널 내부) ──────────────────────
    $wp_customize->add_section('lp_homepage_section', [
        'title'  => '레이아웃 관리',
        'panel'  => 'lp_home_layout',
        'priority' => 10,
    ]);

    $wp_customize->add_setting( 'lp_home_widgets', [
        'default'           => '[{"type":"list","cat":"0","cols":"2","title":""}]',
        'sanitize_callback' => 'lp_sanitize_home_widgets',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control(
        new LP_Home_Widgets_Control( $wp_customize, 'lp_home_widgets', [
            'label'   => '위젯 목록',
            'section' => 'lp_homepage_section',
        ] )
    );

    // 섹션 추가: 언론사 하단 정보 설정
    $wp_customize->add_section('larapress_footer_section', [
        'title'       => '언론사 푸터(하단) 정보 설정',
        'description' => '한국형 인터넷 신문사 기준에 맞춘 하단 정보를 입력하세요.',
        'priority'    => 120,
    ]);

    // ── 푸터 브랜딩 (회사명·소개·메뉴 타이틀) ───────────────────
    $wp_customize->add_setting( 'lp_footer_company_name', [
        'default'           => get_bloginfo( 'name' ),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_footer_company_name', [
        'label'       => '푸터 회사명',
        'description' => '푸터 좌측 상단에 표시되는 언론사 이름. 아래 로고를 업로드하지 않은 경우에만 텍스트로 표시됩니다.',
        'section'     => 'larapress_footer_section',
        'type'        => 'text',
        'priority'    => -4,
    ] );

    // 푸터 로고 — 라이트모드 / 다크모드
    $wp_customize->add_setting( 'lp_footer_logo_light', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lp_footer_logo_light', [
        'label'       => '푸터 로고 — 라이트모드',
        'description' => '라이트모드에서 푸터에 표시할 로고. 비워두면 위 "푸터 회사명" 텍스트가 표시됩니다.',
        'section'     => 'larapress_footer_section',
        'priority'    => -3.5,
    ] ) );

    $wp_customize->add_setting( 'lp_footer_logo_dark', [
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'lp_footer_logo_dark', [
        'label'       => '푸터 로고 — 다크모드',
        'description' => '다크모드에서 표시할 푸터 로고. 비워두면 라이트모드 로고를 다크모드에서도 그대로 사용합니다.',
        'section'     => 'larapress_footer_section',
        'priority'    => -3.3,
    ] ) );

    $wp_customize->add_setting( 'lp_footer_company_desc', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_footer_company_desc', [
        'label'       => '사이트 소개 문구',
        'description' => '푸터 좌측에 표시되는 한 줄 소개 (최대 150자)',
        'section'     => 'larapress_footer_section',
        'type'        => 'textarea',
        'priority'    => -3,
    ] );

    $wp_customize->add_setting( 'lp_footer_menu1_title', [
        'default'           => '회사 소개',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_footer_menu1_title', [
        'label'       => '메뉴 1 타이틀',
        'description' => '푸터 첫 번째 메뉴 컬럼 제목 (기본: 회사 소개)',
        'section'     => 'larapress_footer_section',
        'type'        => 'text',
        'priority'    => -2,
    ] );

    $wp_customize->add_setting( 'lp_footer_menu2_title', [
        'default'           => '서비스 규범',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_footer_menu2_title', [
        'label'       => '메뉴 2 타이틀',
        'description' => '푸터 두 번째 메뉴 컬럼 제목 (기본: 서비스 규범)',
        'section'     => 'larapress_footer_section',
        'type'        => 'text',
        'priority'    => -1,
    ] );

    // ── 발행인·편집인 동일 여부 체크박스 ──────────────────────
    $wp_customize->add_setting( 'lp_pub_editor_same', [
        'default'           => '0',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_pub_editor_same', [
        'label'    => '발행인과 편집인이 동일합니다 (발행·편집인으로 통합)',
        'section'  => 'larapress_footer_section',
        'type'     => 'checkbox',
        'priority' => 1,
    ] );

    // 통합 발행·편집인 이름 (체크 시 JS로 표시)
    $wp_customize->add_setting( 'lp_pub_editor_name', [
        'default'           => '김아무개',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'lp_pub_editor_name', [
        'label'    => '발행·편집인',
        'section'  => 'larapress_footer_section',
        'type'     => 'text',
        'priority' => 2,
    ] );

    // 개별 발행인
    $wp_customize->add_setting( 'lp_publisher_name', [
        'default'           => '김아무개',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'lp_publisher_name', [
        'label'    => '발행인',
        'section'  => 'larapress_footer_section',
        'type'     => 'text',
        'priority' => 3,
    ] );

    // 개별 편집인
    $wp_customize->add_setting( 'lp_editor_name', [
        'default'           => '박아무개',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'lp_editor_name', [
        'label'    => '편집인',
        'section'  => 'larapress_footer_section',
        'type'     => 'text',
        'priority' => 4,
    ] );

    // 나머지 푸터 필드
    $footer_fields = [
        'lp_youth_officer'  => ['label' => '청소년보호책임자',   'default' => '김아무개',                   'priority' => 5],
        'lp_privacy_officer'=> ['label' => '개인정보보호책임자', 'default' => '김아무개',                   'priority' => 6],
        'lp_reg_num'        => ['label' => '정기간행물등록번호', 'default' => '강원 아00000',             'priority' => 7],
        'lp_reg_date'       => ['label' => '등록일자',           'default' => '2025.06.25',              'priority' => 8],
        'lp_est_date'       => ['label' => '창간일자',           'default' => '2026.11.25',              'priority' => 9],
        'lp_address'        => ['label' => '주소',               'default' => '서울특별시 강남구 테헤란로 123', 'priority' => 10],
        'lp_phone'          => ['label' => '대표전화',           'default' => '1588-0000',               'priority' => 11],
        'lp_email'          => ['label' => '이메일',             'default' => 'support@larapress.io',    'priority' => 12],
        'lp_copyright'      => ['label' => '카피라이트',         'default' => 'Copyright © '.date('Y').' '.get_bloginfo('name').'. All rights reserved.', 'priority' => 13], // 사이트 제목(설정 › 일반)을 따른다
    ];

    foreach ($footer_fields as $setting_id => $args) {
        $wp_customize->add_setting($setting_id, [
            'default'           => $args['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control($setting_id, [
            'label'    => $args['label'],
            'section'  => 'larapress_footer_section',
            'type'     => 'text',
            'priority' => $args['priority'],
        ]);
    }

    // ── 큐브 히어로 배너 표시 여부 ──────────────────────────
    $wp_customize->add_setting( 'lp_hero_enable', [
        'default'           => '1',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_hero_enable', [
        'label'       => '큐브 애니메이션 히어로 배너 표시',
        'description' => '메인 페이지 하단의 CSS 3D 글래스 큐브 히어로 배너를 켜거나 끕니다.',
        'section'     => 'larapress_footer_section',
        'type'        => 'checkbox',
        'priority'    => 14,
    ] );

    // ── SNS 소셜 링크 ────────────────────────────────────────
    $social_fields = [
        'lp_social_facebook'  => [ 'label' => 'Facebook 페이지 URL',    'priority' => 20 ],
        'lp_social_instagram' => [ 'label' => 'Instagram 페이지 URL',   'priority' => 21 ],
        'lp_social_x'         => [ 'label' => 'X (Twitter) 페이지 URL', 'priority' => 22 ],
        'lp_social_youtube'   => [ 'label' => 'YouTube 채널 URL',       'priority' => 23 ],
        'lp_social_naverblog' => [ 'label' => '네이버 블로그 URL',       'priority' => 24 ],
        'lp_social_kakao'     => [ 'label' => '카카오 채널 URL',         'priority' => 25 ],
        'lp_social_linkedin'  => [ 'label' => 'LinkedIn 페이지 URL',    'priority' => 26 ],
    ];
    foreach ( $social_fields as $setting_id => $args ) {
        $wp_customize->add_setting( $setting_id, [
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ] );
        $wp_customize->add_control( $setting_id, [
            'label'       => $args['label'],
            'description' => '비워두면 해당 아이콘이 표시되지 않습니다.',
            'section'     => 'larapress_footer_section',
            'type'        => 'url',
            'priority'    => $args['priority'],
        ] );
    }

    // ── 섹션: 배너 광고 설정 ────────────────────────────────
    $wp_customize->add_section( 'lp_banner_section', [
        'title'       => '배너 광고 설정',
        'description' => '구글 애드센스, 네이버 성과형 광고 등의 스크립트·이미지 코드를 직접 입력하세요. 빈 칸이면 빈 영역으로 숨겨집니다.',
        'priority'    => 115,
    ] );

    // 상단 배너 728×90
    $wp_customize->add_setting( 'lp_banner_top', [
        'default'           => '',
        'sanitize_callback' => 'lp_sanitize_banner_code',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_banner_top', [
        'label'       => '상단 배너 코드 (728×90)',
        'description' => '헤더 로고 우측에 표시됩니다. 데스크톱 전용.',
        'section'     => 'lp_banner_section',
        'type'        => 'textarea',
        'priority'    => 1,
    ] );

    // 우측 사이드바 배너 300×250
    $wp_customize->add_setting( 'lp_banner_side', [
        'default'           => '',
        'sanitize_callback' => 'lp_sanitize_banner_code',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_banner_side', [
        'label'       => '우측 사이드바 배너 코드 (300×250)',
        'description' => '우측 사이드바 하단에 표시됩니다.',
        'section'     => 'lp_banner_section',
        'type'        => 'textarea',
        'priority'    => 2,
    ] );

    // ── 섹션: 엠버 저널 전용 설정 ───────────────────────────
    $wp_customize->add_section( 'lp_aj_section', [
        'title'       => '🟠 엠버 저널 설정',
        'description' => '엠버 저널 레이아웃에만 적용되는 세부 설정입니다.',
        'priority'    => 121,
    ] );

    // 추천 기사 위젯 카테고리
    $aj_cat_choices = [ '' => '모든 카테고리 (최신순)' ];
    foreach ( get_categories( [ 'hide_empty' => false ] ) as $_aj_c ) {
        $aj_cat_choices[ $_aj_c->slug ] = $_aj_c->name . ' (' . $_aj_c->count . '건)';
    }
    $wp_customize->add_setting( 'lp_aj_picks_cat', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_picks_cat', [
        'label'       => '추천 기사 위젯 — 카테고리',
        'description' => '선택한 카테고리 기사를 추천 기사 위젯에 표시합니다. 비워두면 전체 최신 기사를 무작위로 보여줍니다.',
        'section'     => 'lp_aj_section',
        'type'        => 'select',
        'choices'     => $aj_cat_choices,
        'priority'    => 1,
    ] );

    // 추천 기사 위젯 커스텀 타이틀
    $wp_customize->add_setting( 'lp_aj_picks_title', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_picks_title', [
        'label'       => '추천 기사 위젯 — 타이틀명',
        'description' => '비워두면 카테고리명 + "추천" 또는 "추천 기사"로 자동 설정됩니다.',
        'section'     => 'lp_aj_section',
        'type'        => 'text',
        'priority'    => 2,
    ] );

    // ── 전면 기사 그리드 설정 ───────────────────────────────
    // ① 전면 기사 그리드 표시 여부
    $wp_customize->add_setting( 'lp_aj_feat_enable', [
        'default'           => '1',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_feat_enable', [
        'label'       => '전면 기사 그리드 표시',
        'description' => '홈 화면 상단에 전면 기사 카드 그리드(좌·중·우)를 표시합니다.',
        'section'     => 'lp_aj_section',
        'type'        => 'checkbox',
        'priority'    => 20,
    ] );

    // ② 전면 기사 개수 (3·4·5)
    $wp_customize->add_setting( 'lp_aj_feat_count', [
        'default'           => '5',
        'sanitize_callback' => 'lp_sanitize_feat_count',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_feat_count', [
        'label'       => '전면 기사 개수',
        'description' => '센터 1개는 항상 표시됩니다. 좌우에 각각 배치할 기사 수를 선택하세요.',
        'section'     => 'lp_aj_section',
        'type'        => 'select',
        'choices'     => [
            '3' => '3개 — 좌 1 · 센터 1 · 우 1',
            '4' => '4개 — 좌 2 · 센터 1 · 우 1',
            '5' => '5개 — 좌 2 · 센터 1 · 우 2',
        ],
        'priority'    => 21,
    ] );

    // ③ 썸네일(특성 이미지) 표시 여부
    $wp_customize->add_setting( 'lp_aj_feat_show_thumb', [
        'default'           => '1',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_feat_show_thumb', [
        'label'       => '전면 기사 썸네일(특성 이미지) 표시',
        'description' => '체크 해제 시 모든 카드에서 이미지를 숨깁니다.',
        'section'     => 'lp_aj_section',
        'type'        => 'checkbox',
        'priority'    => 22,
    ] );

    // ④ 센터 카드 요약문 최대 글자 수
    $wp_customize->add_setting( 'lp_aj_feat_excerpt_len', [
        'default'           => '150',
        'sanitize_callback' => 'lp_sanitize_feat_excerpt_len',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_aj_feat_excerpt_len', [
        'label'       => '센터 카드 요약문 최대 글자 수',
        'description' => '발췌(요약문)가 없을 때 본문에서 자를 최대 글자 수입니다. (50~500)',
        'section'     => 'lp_aj_section',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 50, 'max' => 500, 'step' => 10 ],
        'priority'    => 23,
    ] );

    // ── 섹션: 카카오 SDK 설정 ───────────────────────────────
    $wp_customize->add_section( 'lp_kakao_section', [
        'title'       => '💬 카카오 SDK 설정',
        'description' => '카카오 개발자센터(developers.kakao.com)에서 발급한 JavaScript 키를 입력하면 카카오톡 공유 버튼이 활성화됩니다.',
        'priority'    => 122,
    ] );

    $wp_customize->add_setting( 'lp_kakao_js_key', [
        'default'             => '',
        'sanitize_callback'   => 'lp_sanitize_kakao_key',
        'validate_callback'   => 'lp_validate_kakao_key',
        'transport'           => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_kakao_js_key', [
        'label'       => '카카오 JavaScript 키',
        'description' => '내 애플리케이션 → 앱 키 → JavaScript 키(32자리)를 붙여넣으세요. '
                       . '카카오 로그인/공유가 동작하려면 [플랫폼 → Web] 에 사이트 도메인('
                       . esc_html( home_url() ) . ')도 등록해야 합니다. '
                       . '비워두면 공유 버튼은 URL 복사로 대체 동작합니다.',
        'section'     => 'lp_kakao_section',
        'type'        => 'text',
        'input_attrs' => [ 'placeholder' => '예: 0123456789abcdef0123456789abcdef' ],
        'priority'    => 1,
    ] );
}
add_action('customize_register', 'larapress_customize_register');

/** 카카오 JavaScript 키 정규화 — 영문·숫자만 허용(공백·따옴표 등 제거) */
function lp_sanitize_kakao_key( $value ) {
    return preg_replace( '/[^A-Za-z0-9]/', '', (string) $value );
}

/** 카카오 JavaScript 키 검증 — 비우거나 32자리 앱 키만 허용 */
function lp_validate_kakao_key( $validity, $value ) {
    $value = preg_replace( '/[^A-Za-z0-9]/', '', (string) $value );
    if ( $value !== '' && strlen( $value ) !== 32 ) {
        $validity->add( 'lp_kakao_key_length', '카카오 JavaScript 키는 영문·숫자 32자리입니다. 앱 키를 다시 확인해 주세요.' );
    }
    return $validity;
}

/* ============================================================
 * 워드프레스 알림판 좌측 메뉴 — "테마 설정" 페이지
 * 레이아웃·파비콘·로고·푸터 등 테마 설정을 알림판에서 바로 입력·저장한다.
 * 입력 항목은 위 커스터마이저 등록 내용을 그대로 읽어 만든다 (inc/theme-settings-page.php).
 * ============================================================ */
require_once get_template_directory() . '/inc/theme-settings-page.php';

/* ============================================================
 * 사이트 아이콘 스플래시 화면
 * - "모양 → 사이트 아이덴티티"에 사이트 아이콘(파비콘)이 등록되어
 *   있을 때만 자동으로 표시된다.
 * - 배경색은 현재 레이아웃 스킨의 대표(primary) 색상을 사용한다.
 * - 애니메이션 스타일(가로/세로/대각선 와이프, 모으기·흩어지기)은
 *   "테마 설정 → 일반·브랜딩 → 스플래시 화면 설정"에서 선택한다.
 * ============================================================ */

// ── Customizer: 스플래시 화면 설정 섹션 ─────────────────────────
function larapress_customize_register_splash( $wp_customize ) {
    $wp_customize->add_section( 'lp_splash_section', [
        'title'       => '스플래시 화면 설정',
        'description' => '사이트 아이콘(파비콘)이 등록되어 있을 때, 브라우저 세션당 첫 접속 시 한 번만 잠깐 표시되는 로딩 스플래시 화면입니다. 배경색은 현재 테마 스킨의 대표 색상으로 자동 지정됩니다.',
        'priority'    => 26,
    ] );

    $wp_customize->add_setting( 'lp_splash_enable', [
        'default'           => '1',
        'sanitize_callback' => 'lp_sanitize_checkbox',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_splash_enable', [
        'label'       => '스플래시 화면 사용',
        'description' => '사이트 아이콘이 등록된 경우에만 표시됩니다. 아이콘이 없으면 이 옵션을 켜도 표시되지 않습니다.',
        'section'     => 'lp_splash_section',
        'type'        => 'checkbox',
        'priority'    => 1,
    ] );

    $wp_customize->add_setting( 'lp_splash_animation', [
        'default'           => 'gather',
        'sanitize_callback' => 'lp_sanitize_splash_animation',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_splash_animation', [
        'label'       => '스플래시 애니메이션 스타일',
        'description' => '아이콘과 배경이 나타나고 사라지는 방식을 선택하세요.',
        'section'     => 'lp_splash_section',
        'type'        => 'radio',
        'choices'     => [
            'horizontal' => '↔ 가로 — 화면이 좌우로 갈라지며 나타났다 사라짐',
            'vertical'   => '↕ 세로 — 화면이 상하로 갈라지며 나타났다 사라짐',
            'diagonal'   => '⤡ 대각선 — 화면이 대각선으로 갈라지며 나타났다 사라짐',
            'gather'     => '✦ 모으기·흩어지기 — 조각이 모여 아이콘을 이루고 다시 흩어짐',
        ],
        'priority'    => 2,
    ] );

    $wp_customize->add_setting( 'lp_splash_duration', [
        'default'           => '1200',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );
    $wp_customize->add_control( 'lp_splash_duration', [
        'label'       => '표시 유지 시간 (ms)',
        'description' => '아이콘이 나타난 뒤, 사라지는 애니메이션이 시작되기까지의 대기 시간입니다. (500~4000)',
        'section'     => 'lp_splash_section',
        'type'        => 'number',
        'input_attrs' => [ 'min' => 500, 'max' => 4000, 'step' => 100 ],
        'priority'    => 3,
    ] );
}
add_action( 'customize_register', 'larapress_customize_register_splash' );

/** 스플래시 애니메이션 값 sanitize — 허용된 4종만 통과 */
function lp_sanitize_splash_animation( $value ) {
    $allowed = [ 'horizontal', 'vertical', 'diagonal', 'gather' ];
    return in_array( $value, $allowed, true ) ? $value : 'gather';
}

/** 현재 레이아웃 스킨의 대표(primary) 색상을 스플래시 배경색으로 반환 */
function larapress_get_splash_bg_color() {
    $colors = [
        'swn-style'          => '#7400ad', // --swn-primary (청라온 브랜드 보라)
        'amber-journal'      => '#D97706', // --aj-amber
        'newyorktimes-style' => '#121212',
        'basic'              => '#0d6efd',
    ];
    $skin = lp_skin_vars()['current_theme_style'];
    return $colors[ $skin ] ?? '#7400ad';
}

/** 현재 요청에서 스플래시 화면을 표시해야 하는지 판단 */
function larapress_splash_should_render() {
    if ( is_admin() || is_feed() || wp_doing_ajax() ) {
        return false;
    }
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return false;
    }
    if ( ! has_site_icon() ) {
        return false; // 사이트 아이콘이 없으면 표시하지 않음
    }
    if ( get_theme_mod( 'lp_splash_enable', '1' ) !== '1' ) {
        return false;
    }
    return true;
}

/** <head> — 스플래시 화면 스타일 (FOUC 방지를 위해 body 콘텐츠보다 먼저 출력) */
function larapress_splash_head_styles() {
    if ( ! larapress_splash_should_render() ) {
        return;
    }
    ?>
    <script>
    // 이번 브라우저 세션에서 이미 스플래시를 본 경우, 첫 페인트 전에 숨긴다.
    try { if (sessionStorage.getItem('lp-splash-seen')) document.documentElement.classList.add('lp-splash-seen'); } catch (e) {}
    </script>
    <style id="lp-splash-style">
        html.lp-splash-seen #lp-splash { display: none !important; }
        html.lp-splash-lock,
        html.lp-splash-lock body { overflow: hidden !important; height: 100%; }
        .lp-splash {
            position: fixed; inset: 0; z-index: 999999;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; pointer-events: none;
        }
        .lp-splash__panel { position: absolute; inset: 0; background: var(--lp-splash-bg, #1a1a1a); }
        .lp-splash__stage { position: relative; z-index: 2; width: 120px; height: 120px; }
        .lp-splash__icon {
            position: absolute; inset: 0; margin: auto; width: 100px; height: 100px; object-fit: contain;
            opacity: 0; transform: scale(.55);
            animation: lpSplashIconIn .5s cubic-bezier(.34,1.56,.64,1) forwards .2s;
            filter: drop-shadow(0 6px 20px rgba(0,0,0,.3));
        }
        @keyframes lpSplashIconIn { to { opacity: 1; transform: scale(1); } }
        .lp-splash.is-hiding .lp-splash__icon { animation: lpSplashIconOut .35s ease forwards; }
        @keyframes lpSplashIconOut { to { opacity: 0; transform: scale(.6); } }

        /* ↔ 가로 — 좌우 패널이 갈라지며 사라짐 */
        .lp-splash--horizontal .lp-splash__panel--a { clip-path: inset(0 50% 0 0); }
        .lp-splash--horizontal .lp-splash__panel--b { clip-path: inset(0 0 0 50%); }
        .lp-splash--horizontal.is-hiding .lp-splash__panel--a { transform: translateX(-100%); transition: transform .6s cubic-bezier(.65,0,.35,1); }
        .lp-splash--horizontal.is-hiding .lp-splash__panel--b { transform: translateX(100%); transition: transform .6s cubic-bezier(.65,0,.35,1); }

        /* ↕ 세로 — 상하 패널이 갈라지며 사라짐 */
        .lp-splash--vertical .lp-splash__panel--a { clip-path: inset(0 0 50% 0); }
        .lp-splash--vertical .lp-splash__panel--b { clip-path: inset(50% 0 0 0); }
        .lp-splash--vertical.is-hiding .lp-splash__panel--a { transform: translateY(-100%); transition: transform .6s cubic-bezier(.65,0,.35,1); }
        .lp-splash--vertical.is-hiding .lp-splash__panel--b { transform: translateY(100%); transition: transform .6s cubic-bezier(.65,0,.35,1); }

        /* ⤡ 대각선 — 대각선으로 나뉜 패널이 반대 모서리로 사라짐 */
        .lp-splash--diagonal .lp-splash__panel--a { clip-path: polygon(0 0, 100% 0, 0 100%); }
        .lp-splash--diagonal .lp-splash__panel--b { clip-path: polygon(100% 0, 100% 100%, 0 100%); }
        .lp-splash--diagonal.is-hiding .lp-splash__panel--a { transform: translate(-100%,-100%); transition: transform .65s cubic-bezier(.65,0,.35,1); }
        .lp-splash--diagonal.is-hiding .lp-splash__panel--b { transform: translate(100%,100%); transition: transform .65s cubic-bezier(.65,0,.35,1); }

        /* ✦ 모으기·흩어지기 — 조각(dot)이 모여 아이콘을 이루고 다시 흩어짐 */
        .lp-splash--gather .lp-splash__panel--b { display: none; }
        .lp-splash--gather.is-hiding .lp-splash__panel--a { opacity: 0; transition: opacity .6s ease .2s; }
        .lp-splash__dot {
            position: absolute; top: 50%; left: 50%; width: 8px; height: 8px; border-radius: 50%;
            background: #fff; opacity: 1; transform: translate(-50%,-50%);
            animation: lpDotGather .5s cubic-bezier(.34,1.56,.64,1) forwards;
            display: none;
        }
        .lp-splash--gather .lp-splash__dot { display: block; }
        @keyframes lpDotGather {
            0%   { opacity: .9; transform: translate(calc(-50% + var(--tx)), calc(-50% + var(--ty))) scale(1); }
            100% { opacity: 0;  transform: translate(-50%, -50%) scale(.3); }
        }
        .lp-splash.is-hiding .lp-splash__dot { animation: lpDotScatter .5s cubic-bezier(.34,1.56,.64,1) forwards; }
        @keyframes lpDotScatter {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(.3); }
            100% { opacity: .9; transform: translate(calc(-50% + var(--tx)), calc(-50% + var(--ty))) scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .lp-splash__icon, .lp-splash__dot, .lp-splash__panel { animation: none !important; transition: none !important; }
            .lp-splash { transition: opacity .3s ease !important; }
            .lp-splash.is-hiding { opacity: 0; }
        }
    </style>
    <?php
}
add_action( 'wp_head', 'larapress_splash_head_styles', 1 );

/** <body> 시작 직후 — 스플래시 마크업 + 표시/제거 스크립트 출력 */
function larapress_splash_markup() {
    if ( ! larapress_splash_should_render() ) {
        return;
    }

    $animation = get_theme_mod( 'lp_splash_animation', 'gather' );
    $animation = lp_sanitize_splash_animation( $animation );

    $duration = (int) get_theme_mod( 'lp_splash_duration', 1200 );
    $duration = max( 500, min( 4000, $duration ) );

    $bg_color = larapress_get_splash_bg_color();
    // 배경이 투명한 스플래시 전용 로고가 있으면 우선 사용 (사이트 아이콘은 흰 배경이 포함된 RGB 이미지)
    $icon_url = file_exists( get_template_directory() . '/assets/images/splash-logo.png' )
        ? get_template_directory_uri() . '/assets/images/splash-logo.png'
        : get_site_icon_url( 192 );
    if ( ! $icon_url ) {
        return;
    }
    ?>
    <div id="lp-splash" class="lp-splash lp-splash--<?php echo esc_attr( $animation ); ?>" style="--lp-splash-bg: <?php echo esc_attr( $bg_color ); ?>;" role="presentation" aria-hidden="true">
        <div class="lp-splash__panel lp-splash__panel--a"></div>
        <div class="lp-splash__panel lp-splash__panel--b"></div>
        <div class="lp-splash__stage">
            <?php for ( $i = 0; $i < 8; $i++ ) :
                $angle = $i * 45;
                $tx    = round( cos( deg2rad( $angle ) ) * 70 );
                $ty    = round( sin( deg2rad( $angle ) ) * 70 );
            ?>
                <span class="lp-splash__dot" style="--tx: <?php echo esc_attr( $tx ); ?>px; --ty: <?php echo esc_attr( $ty ); ?>px;"></span>
            <?php endfor; ?>
            <img class="lp-splash__icon" src="<?php echo esc_url( $icon_url ); ?>" width="100" height="100" alt="" />
        </div>
    </div>
    <noscript><style>#lp-splash{display:none!important;} html.lp-splash-lock{overflow:auto!important;}</style></noscript>
    <script>
    (function(){
        var el = document.getElementById('lp-splash');
        if (!el) return;
        if (document.documentElement.classList.contains('lp-splash-seen')) {
            el.parentNode.removeChild(el);
            return;
        }
        try { sessionStorage.setItem('lp-splash-seen', '1'); } catch (e) {}
        document.documentElement.classList.add('lp-splash-lock');
        var DURATION = <?php echo (int) $duration; ?>;
        var done = false;
        function hide(){
            if (done) return;
            done = true;
            el.classList.add('is-hiding');
            document.documentElement.classList.remove('lp-splash-lock');
            setTimeout(function(){
                if (el && el.parentNode) { el.parentNode.removeChild(el); }
            }, 750);
        }
        // 페이지 로드 완료를 기다리지 않고, 표시 시점부터 DURATION 뒤에 사라진다.
        setTimeout(hide, DURATION);
    })();
    </script>
    <?php
}
add_action( 'wp_body_open', 'larapress_splash_markup' );

/**
 * SNS 소셜 링크 HTML 생성 헬퍼
 * @param string $link_class  <a> 태그에 적용할 CSS 클래스
 * @param int    $icon_size   SVG 아이콘 크기(px)
 * @return string             SNS 링크 HTML (URL이 비어있으면 해당 아이콘 제외)
 */
function lp_social_links_html( $link_class = '', $icon_size = 20 ) {
    $socials = [
        'facebook'  => [
            'label' => 'Facebook',
            'url'   => get_theme_mod( 'lp_social_facebook', '' ),
            'path'  => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'url'   => get_theme_mod( 'lp_social_instagram', '' ),
            'path'  => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z',
        ],
        'x'         => [
            'label' => 'X (Twitter)',
            'url'   => get_theme_mod( 'lp_social_x', '' ),
            'path'  => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.261 5.638 5.903-5.638zm-1.161 17.52h1.833L7.084 4.126H5.117z',
        ],
        'youtube'   => [
            'label' => 'YouTube',
            'url'   => get_theme_mod( 'lp_social_youtube', '' ),
            'path'  => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z',
        ],
        'naverblog' => [
            'label' => '네이버 블로그',
            'url'   => get_theme_mod( 'lp_social_naverblog', '' ),
            'path'  => 'M16.273 12.845L7.376 0H0v24h7.727V11.155L16.624 24H24V0h-7.727z',
        ],
        'kakao'     => [
            'label' => '카카오',
            'url'   => get_theme_mod( 'lp_social_kakao', '' ),
            'path'  => 'M12 3C6.477 3 2 6.477 2 10.778c0 2.753 1.81 5.15 4.534 6.516-.178.617-.574 2.23-.657 2.576-.1.428.157.422.33.308.136-.09 2.157-1.454 3.027-2.04.25.035.504.053.766.053 5.52 0 10-3.477 10-7.778C20 6.477 17.52 3 12 3z',
        ],
        'linkedin'  => [
            'label' => 'LinkedIn',
            'url'   => get_theme_mod( 'lp_social_linkedin', '' ),
            'path'  => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
        ],
    ];

    $html = '';
    $s    = (int) $icon_size;
    foreach ( $socials as $data ) {
        $url = trim( $data['url'] );
        if ( ! $url ) continue;
        $html .= sprintf(
            '<a href="%s" class="%s" title="%s" aria-label="%s" target="_blank" rel="noopener noreferrer">'
            . '<svg width="%d" height="%d" fill="currentColor" viewBox="0 0 24 24"><path d="%s"/></svg></a>',
            esc_url( $url ),
            esc_attr( $link_class ),
            esc_attr( $data['label'] ),
            esc_attr( $data['label'] ),
            $s, $s,
            $data['path']
        );
    }
    return $html;
}

/**
 * 카테고리별 배지 색상을 반환합니다.
 * neeon 테마가 사용하던 'rt_category_color' 텀 메타(있으면)를 그대로 재사용하고,
 * 값이 없으면 SWN 스킨 기본 브랜드 컬러(보라)로 대체합니다.
 *
 * @param int    $term_id
 * @param string $fallback
 * @return string 6자리 hex (# 없이 또는 포함 모두 허용, 항상 # 포함해 반환)
 */
function lp_get_category_color( $term_id, $fallback = '#7400ad' ) {
    $term_id = (int) $term_id;
    if ( $term_id <= 0 ) return $fallback;

    static $cache = [];
    if ( isset( $cache[ $term_id ] ) ) return $cache[ $term_id ];

    $color = get_term_meta( $term_id, 'rt_category_color', true );
    $color = trim( (string) $color );
    if ( $color && $color[0] !== '#' ) {
        $color = '#' . $color;
    }
    if ( ! preg_match( '/^#[0-9a-fA-F]{6}$/', $color ) ) {
        $color = $fallback;
    }

    $cache[ $term_id ] = $color;
    return $color;
}

/** 많이 본 뉴스 가중치 — 조회수 · 기사 반응 · 공유 (합계 1) */
define( 'LP_HOT_WEIGHT_VIEWS',     0.4 );
define( 'LP_HOT_WEIGHT_REACTIONS', 0.5 );
define( 'LP_HOT_WEIGHT_SHARES',    0.1 );

/**
 * 많이 본 뉴스 목록 (상위 5개) — 모든 스킨의 "많이 본 뉴스/기사" 위젯 공용
 *
 *  engagement = 0.4 × 조회수/최대조회수 + 0.5 × 반응수/최대반응수 + 0.1 × 공유수/최대공유수
 *  score      = engagement / (t + 2)^g
 *    각 지표는 후보군(최근 90일) 내 최댓값으로 나눠 0~1로 정규화 — 단위가 다른 지표를 비율대로 합산
 *    t : 발행 후 경과 시간(시간 단위), g : gravity 1.8 (해커뉴스 랭킹 공식의 시간 감쇠)
 *  결과는 10분 Transient 캐시에 저장해 DB 부하를 최소화.
 *
 * @return array[] [ 'title', 'url', 'views' ] 목록
 */
function lp_hot_news_list() {
    $cache_key = 'lp_hot_news_v3';
    $list      = get_transient( $cache_key );
    if ( $list !== false ) {
        return $list;
    }

    $gravity = 1.8;
    $now     = time();
    $pool    = new WP_Query( [
        'post_type'              => 'post', /* 라라프레스 게시판(lara_post) 제외, WP 기사만 */
        'post_status'            => 'publish',
        'posts_per_page'         => 200,
        'date_query'             => [ [ 'after' => '90 days ago' ] ],
        'no_found_rows'          => true,   // COUNT(*) 생략으로 속도 향상
        'update_post_meta_cache' => true,   // 메타 일괄 로드 (쿼리 1회)
        'update_post_term_cache' => false,
    ] );

    $metrics = [];
    $max     = [ 'views' => 0, 'reactions' => 0, 'shares' => 0 ];
    foreach ( $pool->posts as $p ) {
        $rx = get_post_meta( $p->ID, 'lp_reactions', true );
        $m  = [
            'views'     => (int) get_post_meta( $p->ID, 'lara_post_views', true ),
            'reactions' => is_array( $rx ) ? (int) array_sum( $rx ) : 0,
            'shares'    => (int) get_post_meta( $p->ID, 'lp_shares', true ),
            'hours'     => max( 0, ( $now - strtotime( $p->post_date_gmt ) ) / 3600 ),
        ];
        foreach ( $max as $k => $v ) {
            $max[ $k ] = max( $v, $m[ $k ] );
        }
        $metrics[ $p->ID ] = $m;
    }
    wp_reset_postdata();

    $scored = [];
    foreach ( $metrics as $id => $m ) {
        $engagement = ( $max['views']     ? LP_HOT_WEIGHT_VIEWS     * $m['views']     / $max['views']     : 0 )
                    + ( $max['reactions'] ? LP_HOT_WEIGHT_REACTIONS * $m['reactions'] / $max['reactions'] : 0 )
                    + ( $max['shares']    ? LP_HOT_WEIGHT_SHARES    * $m['shares']    / $max['shares']    : 0 );
        $scored[ $id ] = $engagement / pow( $m['hours'] + 2, $gravity );
    }
    arsort( $scored );

    $list = [];
    foreach ( array_slice( array_keys( $scored ), 0, 5 ) as $id ) {
        $list[] = [
            'title' => get_the_title( $id ),
            'url'   => get_permalink( $id ),
            'views' => number_format( $metrics[ $id ]['views'] ),
        ];
    }
    set_transient( $cache_key, $list, 10 * MINUTE_IN_SECONDS );
    return $list;
}

/**
 * 엠버 저널 — 많이 본 기사 위젯 HTML 반환 (랭킹은 lp_hot_news_list())
 *
 * @return string
 */
function lp_aj_hot_news_html() {
    $list = lp_hot_news_list();

    if ( ! $list ) {
        return '<p style="padding:0.75rem 1rem;font-size:0.8rem;color:#9ca3af;">아직 조회 데이터가 없습니다.</p>';
    }

    $html = '<ol class="aj-opinion-list">';
    foreach ( $list as $i => $item ) {
        $num_color = $i === 0 ? 'color:var(--aj-amber)' : ( $i === 1 ? 'color:#9ca3af' : 'color:#d1d5db' );
        $html .= '<li class="aj-opinion-item">';
        $html .= '<span class="aj-opinion-num" style="' . $num_color . '">' . ( $i + 1 ) . '</span>';
        $html .= '<div class="aj-opinion-text">';
        $html .= '<a href="' . esc_url( $item['url'] ) . '" class="aj-opinion-link">' . esc_html( $item['title'] ) . '</a>';
        $html .= '<p class="aj-opinion-date">조회 ' . esc_html( $item['views'] ) . '회</p>';
        $html .= '</div></li>';
    }
    $html .= '</ol>';
    return $html;
}

// ─────────────────────────────────────────────────────────────
// 인기 검색어 — option 'lp_search_log' : [ 'Y-m-d' => [ '키워드' => 횟수 ] ]
// 페이지 캐시(WP Super Cache)로 검색 결과 페이지가 PHP를 거치지 않을 수 있어
// 결과가 있는 검색 페이지에서 admin-ajax.php로 집계 요청을 보낸다.
// 같은 브라우저의 같은 키워드는 하루 1회만 집계, 최근 7일 합산으로 순위 산정.
// ─────────────────────────────────────────────────────────────
define( 'LP_SEARCH_LOG_DAYS', 7 );
define( 'LP_SEARCH_LOG_MAX_PER_DAY', 300 ); // 하루 저장 키워드 상한 (스팸성 키워드 폭주 방지)

/** 검색어 정규화 — 공백 정리 · 소문자화 · 길이 제한. 부적합하면 '' */
function lp_normalize_search_keyword( $kw ) {
    $kw = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $kw ) ) );
    $kw = mb_strtolower( $kw, 'UTF-8' );
    // $len = mb_strlen( $kw, 'UTF-8' );
    // if ( $len < 2 || $len > 30 ) return '';
    // if ( preg_match( '#https?://|www\.|[<>{}\[\]]#i', $kw ) ) return '';
    return $kw;
}

function larapress_track_search() {
    $kw = lp_normalize_search_keyword( wp_unslash( $_POST['keyword'] ?? '' ) );
    if ( $kw === '' ) {
        wp_send_json_error( [ 'message' => 'invalid' ] );
    }

    $cookie_name = 'lp_kw_' . md5( $kw );
    if ( isset( $_COOKIE[ $cookie_name ] ) ) {
        wp_send_json_success( [ 'counted' => false ] );
    }

    // 실제로 결과가 있는 검색어만 집계 (임의 문자열 주입 방지)
    $hit = new WP_Query( [
        's'                      => $kw,
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ] );
    if ( ! $hit->posts ) {
        wp_send_json_success( [ 'counted' => false ] );
    }

    setcookie( $cookie_name, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

    $log   = get_option( 'lp_search_log', [] );
    $log   = is_array( $log ) ? $log : [];
    $today = current_time( 'Y-m-d' );
    $day   = $log[ $today ] ?? [];

    if ( isset( $day[ $kw ] ) || count( $day ) < LP_SEARCH_LOG_MAX_PER_DAY ) {
        $day[ $kw ]    = (int) ( $day[ $kw ] ?? 0 ) + 1;
        $log[ $today ] = $day;
    }

    // 보관 기간이 지난 날짜 정리
    $cutoff = wp_date( 'Y-m-d', time() - ( LP_SEARCH_LOG_DAYS - 1 ) * DAY_IN_SECONDS );
    foreach ( array_keys( $log ) as $d ) {
        if ( $d < $cutoff ) unset( $log[ $d ] );
    }
    update_option( 'lp_search_log', $log, false );

    wp_send_json_success( [ 'counted' => true ] );
}
add_action( 'wp_ajax_lp_track_search', 'larapress_track_search' );
add_action( 'wp_ajax_nopriv_lp_track_search', 'larapress_track_search' );

/** 결과가 있는 검색 페이지 첫 페이지에서 집계 요청 전송 (편집자 이상 제외) */
add_action( 'wp_footer', function () {
    if ( ! is_search() || is_paged() || ! have_posts() ) return;
    if ( current_user_can( 'edit_posts' ) ) return;
    $kw = lp_normalize_search_keyword( get_search_query( false ) );
    if ( $kw === '' ) return;
    ?>
<script>
(function () {
    var body = new FormData();
    body.append('action', 'lp_track_search');
    body.append('keyword', <?php echo wp_json_encode( $kw ); ?>);
    fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', body: body, credentials: 'same-origin' }).catch(function () {});
})();
</script>
    <?php
} );

/**
 * 인기 검색어 목록 (상위 10개) — 최근 7일 집계, 10분 Transient 캐시
 *
 * @return array[] [ 'keyword', 'url', 'count' ] 목록
 */
function lp_popular_keywords_list() {
    $cache_key = 'lp_popular_keywords_v1';
    $list      = get_transient( $cache_key );
    if ( $list !== false ) {
        return $list;
    }

    $log    = get_option( 'lp_search_log', [] );
    $cutoff = wp_date( 'Y-m-d', time() - ( LP_SEARCH_LOG_DAYS - 1 ) * DAY_IN_SECONDS );
    $totals = [];
    foreach ( (array) $log as $d => $day ) {
        if ( $d < $cutoff || ! is_array( $day ) ) continue;
        foreach ( $day as $kw => $cnt ) {
            $totals[ $kw ] = ( $totals[ $kw ] ?? 0 ) + (int) $cnt;
        }
    }
    arsort( $totals );

    $list = [];
    foreach ( array_slice( $totals, 0, 10, true ) as $kw => $cnt ) {
        $kw     = (string) $kw; // 숫자 키워드는 정수 키로 저장됨
        $list[] = [
            'keyword' => $kw,
            'url'     => home_url( '/?s=' . rawurlencode( $kw ) ),
            'count'   => (int) $cnt,
        ];
    }
    set_transient( $cache_key, $list, 10 * MINUTE_IN_SECONDS );
    return $list;
}

/**
 * 인기 검색어 위젯 HTML (기본/SWN/NYT 스킨 — Tailwind 카드)
 *
 * @param string $extra_class 카드에 추가할 클래스 (예: 'mb-6')
 * @return string
 */
function lp_popular_keywords_widget_html( $extra_class = '' ) {
    $list = lp_popular_keywords_list();

    $html  = '<div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm ' . esc_attr( $extra_class ) . '">';
    $html .= '<h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">인기 검색어';
    $html .= '<span class="ml-auto text-[10px] font-normal text-slate-400 tracking-wide">최근 7일</span></h3>';

    if ( ! $list ) {
        $html .= '<p class="text-sm text-slate-400 text-center py-4">아직 데이터가 없습니다.</p></div>';
        return $html;
    }

    $html .= '<ol class="grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">';
    foreach ( $list as $i => $item ) {
        $num_class = $i === 0 ? 'text-blue-600' : ( $i < 3 ? 'text-slate-500' : 'text-slate-400' );
        $html .= '<li class="flex items-center gap-2 min-w-0">';
        $html .= '<span class="font-black w-4 flex-shrink-0 text-right ' . $num_class . '">' . ( $i + 1 ) . '</span>';
        $html .= '<a href="' . esc_url( $item['url'] ) . '" class="text-slate-700 hover:text-blue-600 hover:underline truncate transition">' . esc_html( $item['keyword'] ) . '</a>';
        $html .= '</li>';
    }
    $html .= '</ol></div>';
    return $html;
}

/**
 * 엠버 저널 — 인기 검색어 위젯 본문 HTML
 *
 * @return string
 */
function lp_aj_popular_keywords_html() {
    $list = lp_popular_keywords_list();

    if ( ! $list ) {
        return '<p style="padding:0.75rem 1rem;font-size:0.8rem;color:#9ca3af;">아직 검색 데이터가 없습니다.</p>';
    }

    $html = '<ol class="aj-opinion-list">';
    foreach ( $list as $i => $item ) {
        $num_color = $i === 0 ? 'color:var(--aj-amber)' : ( $i === 1 ? 'color:#9ca3af' : 'color:#d1d5db' );
        $html .= '<li class="aj-opinion-item">';
        $html .= '<span class="aj-opinion-num" style="' . $num_color . '">' . ( $i + 1 ) . '</span>';
        $html .= '<div class="aj-opinion-text">';
        $html .= '<a href="' . esc_url( $item['url'] ) . '" class="aj-opinion-link">' . esc_html( $item['keyword'] ) . '</a>';
        $html .= '</div></li>';
    }
    $html .= '</ol>';
    return $html;
}

// ─────────────────────────────────────────────────────────────
// 기자 순위 — 월(지난달 1일~말일) · 주(지난주 월~일) · 일(어제) 기간별
//  기간 내 발행한 기사를 기자별로 묶어 아래 지표를 합산 → 기자 간 최댓값으로 0~1 정규화 → 가중 합
//  공동 저자 기사는 각 저자에게 모두 반영.
// ─────────────────────────────────────────────────────────────
define( 'LP_RANK_WEIGHTS', [
    'posts'     => 0.4, // 작성 기사 수
    'views'     => 0.3, // 조회수
    'reactions' => 0.1, // 기사 반응
    'comments'  => 0.1, // 댓글
    'shares'    => 0.1, // 공유
] );

/**
 * 기자 순위 기간 정의 — 키 => [ 버튼 라벨, 시작 timestamp, 끝 timestamp(미포함), 집계기준 문구 ]
 * 모두 사이트 시간대 기준으로 직전에 끝난 기간
 *   월 : 지난달 1일 00:00 ~ 이번 달 1일 00:00
 *   주 : 지난주 월요일 00:00 ~ 이번 주 월요일 00:00
 *   일 : 어제 00:00 ~ 오늘 00:00
 */
function lp_reporter_rank_periods() {
    $tz         = wp_timezone();
    $this_month = new DateTimeImmutable( 'first day of this month midnight', $tz );
    $last_month = $this_month->modify( '-1 month' );
    $this_week  = new DateTimeImmutable( 'monday this week midnight', $tz );
    $last_week  = $this_week->modify( '-1 week' );
    $today      = new DateTimeImmutable( 'today', $tz );
    $yesterday  = $today->modify( '-1 day' );
    return [
        'month' => [ '월', $last_month->getTimestamp(), $this_month->getTimestamp(),
                     $last_month->format( 'Y.m.d' ) . ' ~ ' . $this_month->modify( '-1 day' )->format( 'Y.m.d' ) . ' (지난달)' ],
        'week'  => [ '주', $last_week->getTimestamp(), $this_week->getTimestamp(),
                     $last_week->format( 'Y.m.d' ) . ' ~ ' . $this_week->modify( '-1 day' )->format( 'Y.m.d' ) . ' (지난주)' ],
        'day'   => [ '일', $yesterday->getTimestamp(), $today->getTimestamp(),
                     $yesterday->format( 'Y.m.d' ) . ' (어제)' ],
    ];
}

/**
 * 기자 순위 (기간별 상위 5명) — 30분 Transient 캐시
 *
 * @return array [ 'month' => [...], 'week' => [...], 'day' => [...] ]
 *               각 항목: [ 'name', 'url', 'avatar_html', 'posts', 'views' ]
 */
function lp_reporter_rank_list() {
    $lang      = function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '';
    $cache_key = 'lp_reporter_rank_v4_' . $lang . '_' . wp_date( 'Ymd' ); // 날짜가 바뀌면 즉시 새로 집계
    $ranks     = get_transient( $cache_key );
    if ( $ranks !== false ) {
        return $ranks;
    }

    $periods = lp_reporter_rank_periods();
    $since   = min( array_column( $periods, 1 ) );
    $pool    = new WP_Query( [
        'post_type'              => 'post', /* 라라프레스 게시판(lara_post) 제외, WP 기사만 */
        'post_status'            => 'publish',
        'posts_per_page'         => 2000,
        'date_query'             => [ [ 'column' => 'post_date_gmt', 'after' => gmdate( 'Y-m-d H:i:s', $since ), 'inclusive' => true ] ],
        'no_found_rows'          => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
    ] );

    $profiles = []; // 기자 키 => [ name, url, avatar_html ]
    $stats    = array_fill_keys( array_keys( $periods ), [] );
    foreach ( $pool->posts as $p ) {
        $ts  = strtotime( $p->post_date_gmt . ' UTC' );
        $rx  = get_post_meta( $p->ID, 'lp_reactions', true );
        $m   = [
            'posts'     => 1,
            'views'     => (int) get_post_meta( $p->ID, 'lara_post_views', true ),
            'reactions' => is_array( $rx ) ? (int) array_sum( $rx ) : 0,
            'comments'  => (int) $p->comment_count,
            'shares'    => (int) get_post_meta( $p->ID, 'lp_shares', true ),
        ];
        foreach ( lp_get_post_authors( $p->ID, 64, 'lp-rk-avatar' ) as $a ) {
            $key = md5( $a['id'] . '|' . $a['name'] );
            if ( ! isset( $profiles[ $key ] ) ) {
                $profiles[ $key ] = [ 'name' => $a['name'], 'url' => $a['url'], 'avatar_html' => $a['avatar_html'] ];
            }
            foreach ( $periods as $pk => $pd ) {
                if ( $ts < $pd[1] || $ts >= $pd[2] ) continue;
                foreach ( $m as $k => $v ) {
                    $stats[ $pk ][ $key ][ $k ] = ( $stats[ $pk ][ $key ][ $k ] ?? 0 ) + $v;
                }
            }
        }
    }
    wp_reset_postdata();

    $ranks = [];
    foreach ( $stats as $pk => $rows ) {
        $max = array_fill_keys( array_keys( LP_RANK_WEIGHTS ), 0 );
        foreach ( $rows as $row ) {
            foreach ( $max as $k => $v ) $max[ $k ] = max( $v, $row[ $k ] );
        }
        $scored = [];
        foreach ( $rows as $key => $row ) {
            $s = 0;
            foreach ( LP_RANK_WEIGHTS as $k => $w ) {
                $s += $max[ $k ] ? $w * $row[ $k ] / $max[ $k ] : 0;
            }
            $scored[ $key ] = $s;
        }
        arsort( $scored );

        $ranks[ $pk ] = [];
        foreach ( array_slice( array_keys( $scored ), 0, 5 ) as $key ) {
            $ranks[ $pk ][] = $profiles[ $key ] + [
                'posts' => number_format( $rows[ $key ]['posts'] ),
                'views' => number_format( $rows[ $key ]['views'] ),
            ];
        }
    }
    set_transient( $cache_key, $ranks, 30 * MINUTE_IN_SECONDS );
    return $ranks;
}

/**
 * 기자 순위 위젯 HTML — 우측 상단 월/주/일 버튼으로 패널 전환
 * 세 기간을 모두 렌더링해 두고 JS로 보이기만 전환 (페이지 캐시 환경에서도 동작)
 *
 * @param string $skin        'aj'(엠버 저널) | 'default'(기본/SWN/NYT — Tailwind 카드)
 * @param string $extra_class 카드에 추가할 클래스 (예: 'mb-6')
 * @return string
 */
function lp_reporter_rank_widget_html( $skin = 'default', $extra_class = '' ) {
    static $printed_assets = false;
    $ranks   = lp_reporter_rank_list();
    $periods = lp_reporter_rank_periods();
    $is_aj   = $skin === 'aj';
    $first   = array_key_first( $periods );

    $html = '';
    if ( ! $printed_assets ) {
        $printed_assets = true;
        $html .= '<style>
            .lp-rk-tabs { margin-left:auto; display:flex; gap:2px; background:#f1f5f9; border-radius:9999px; padding:2px; }
            .lp-rk-tab { font-size:11px; font-weight:600; line-height:1; padding:4px 9px; border-radius:9999px; color:#64748b; cursor:pointer; background:transparent; border:0; }
            .lp-rk-tab[aria-selected="true"] { background:#0f172a; color:#fff; }
            .amber-skin .lp-rk-tab[aria-selected="true"] { background:var(--aj-amber); }
            .lp-rk-item { display:flex; align-items:center; gap:0.6rem; }
            .lp-rk-num { width:1rem; flex-shrink:0; text-align:center; font-weight:900; }
            .lp-rk-avatar { width:2rem !important; height:2rem !important; border-radius:9999px; object-fit:cover; flex-shrink:0; }
            .lp-rk-name { display:block; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
            .lp-rk-meta { display:block; font-size:11px; color:#94a3b8; margin-top:1px; }
            .lp-rk-basis { font-size:11px; line-height:1.5; color:#94a3b8; margin-top:0.9rem; padding-top:0.6rem; border-top:1px dashed #e2e8f0; }
        </style>
        <script>
        document.addEventListener("click", function (e) {
            var tab = e.target.closest(".lp-rk-tab");
            if (!tab) return;
            var box = tab.closest(".lp-rk-widget");
            box.querySelectorAll(".lp-rk-tab").forEach(function (t) { t.setAttribute("aria-selected", t === tab ? "true" : "false"); });
            box.querySelectorAll(".lp-rk-panel").forEach(function (p) { p.hidden = p.getAttribute("data-period") !== tab.getAttribute("data-period"); });
        });
        </script>';
    }

    $tabs = '<div class="lp-rk-tabs" role="tablist">';
    foreach ( $periods as $pk => $pd ) {
        $tabs .= '<button type="button" class="lp-rk-tab" role="tab" data-period="' . esc_attr( $pk ) . '" aria-selected="' . ( $pk === $first ? 'true' : 'false' ) . '">' . esc_html( $pd[0] ) . '</button>';
    }
    $tabs .= '</div>';

    if ( $is_aj ) {
        $html .= '<div class="aj-widget lp-rk-widget">';
        $html .= '<div class="aj-widget-head"><span class="aj-widget-mark"></span><h2 class="aj-widget-title">기자 순위</h2>' . $tabs . '</div>';
        $html .= '<div style="padding:0.75rem 1rem;">';
    } else {
        $html .= '<div class="lp-rk-widget bg-white p-6 rounded-xl border border-slate-200 shadow-sm ' . esc_attr( $extra_class ) . '">';
        $html .= '<h3 class="font-bold text-slate-800 border-b border-slate-900 pb-2 mb-4 flex items-center gap-2">기자 순위' . $tabs . '</h3>';
    }

    foreach ( $periods as $pk => $pd ) {
        $list   = $ranks[ $pk ] ?? [];
        $html  .= '<div class="lp-rk-panel" role="tabpanel" data-period="' . esc_attr( $pk ) . '"' . ( $pk === $first ? '' : ' hidden' ) . '>';
        if ( ! $list ) {
            $html .= '<p class="text-sm text-slate-400 text-center py-4" style="font-size:0.8rem;color:#9ca3af;">해당 기간에 작성된 기사가 없습니다.</p>';
            $html .= '<p class="lp-rk-basis">집계기간: ' . esc_html( $pd[3] ) . '</p></div>';
            continue;
        }
        $html .= '<ol class="space-y-3 text-sm" style="display:flex;flex-direction:column;gap:0.75rem;">';
        foreach ( $list as $i => $item ) {
            $num_color = $i === 0
                ? ( $is_aj ? 'var(--aj-amber)' : '#2563eb' )
                : ( $i === 1 ? '#64748b' : '#94a3b8' );
            $name = esc_html( $item['name'] );
            $html .= '<li class="lp-rk-item">';
            $html .= '<span class="lp-rk-num" style="color:' . $num_color . ';">' . ( $i + 1 ) . '</span>';
            $html .= $item['avatar_html']; // get_avatar / Molongui 출력 (이미 이스케이프됨)
            $html .= '<div style="min-width:0;flex:1;">';
            $html .= $item['url']
                ? '<a href="' . esc_url( $item['url'] ) . '" class="lp-rk-name text-slate-700 hover:text-blue-600 hover:underline transition">' . $name . '</a>'
                : '<span class="lp-rk-name text-slate-700">' . $name . '</span>';
            $html .= '<span class="lp-rk-meta">기사 ' . esc_html( $item['posts'] ) . '건 · 조회 ' . esc_html( $item['views'] ) . '회</span>';
            $html .= '</div></li>';
        }
        $html .= '</ol>';
        $html .= '<p class="lp-rk-basis">집계기간: ' . esc_html( $pd[3] ) . '</p></div>';
    }

    // 집계기준 — 가중치 (LP_RANK_WEIGHTS와 자동 동기화)
    $labels = [ 'posts' => '기사 수', 'views' => '조회수', 'reactions' => '반응', 'comments' => '댓글', 'shares' => '공유' ];
    $basis  = [];
    foreach ( LP_RANK_WEIGHTS as $k => $w ) {
        $basis[] = $labels[ $k ] . ' ' . round( $w * 100 ) . '%';
    }
    $html .= '<p class="lp-rk-basis" style="margin-top:0.25rem;border-top:0;padding-top:0;">집계기준: ' . esc_html( implode( ' · ', $basis ) ) . '</p>';

    $html .= $is_aj ? '</div></div>' : '</div>';
    return $html;
}

/**
 * 복수 저자 통합 헬퍼 — Co-Authors Plus / Molongui / 기본 WP 지원
 *
 * 반환 배열 구조 (원소별):
 *   'id'          => int     WP User ID (게스트 저자는 0)
 *   'name'        => string  표시 이름
 *   'description' => string  소개 문구
 *   'url'         => string  저자 페이지 URL
 *   'avatar_html' => string  <img> 태그 (이미 이스케이프됨)
 *
 * @param int|null $post_id
 * @param int      $avatar_size  픽셀 단위 아바타 크기
 * @param string   $avatar_class img 요소에 적용할 CSS 클래스
 * @return array
 */
if ( ! function_exists( 'lp_get_post_authors' ) ) :
function lp_get_post_authors( $post_id = null, $avatar_size = 64, $avatar_class = '' ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $authors = [];
    $av_attr = $avatar_class ? [ 'class' => $avatar_class ] : [];

    /* ── Co-Authors Plus ── */
    if ( function_exists( 'get_coauthors' ) ) {
        foreach ( get_coauthors( $post_id ) as $ca ) {
            $uid   = isset( $ca->ID ) ? (int) $ca->ID : 0;
            $email = ! empty( $ca->user_email ) ? $ca->user_email : '';
            $bio   = ! empty( $ca->description ) ? $ca->description : '';
            if ( ! $bio && $uid ) {
                $bio = (string) get_the_author_meta( 'description', $uid );
            }
            $url = $uid
                ? get_author_posts_url( $uid )
                : ( ! empty( $ca->link ) ? $ca->link : '' );
            $authors[] = [
                'id'          => $uid,
                'name'        => (string) $ca->display_name,
                'description' => $bio,
                'url'         => $url,
                'avatar_html' => get_avatar( $email ?: $uid, $avatar_size, '', esc_attr( (string) $ca->display_name ), $av_attr ),
            ];
        }
        if ( $authors ) {
            return $authors;
        }
    }

    /* ── Molongui Authorship (5.x, 공동 저자 개별 카드 지원) ──
       구버전 API(molongui_get_post_authors())는 이 플러그인 버전에 존재하지 않아
       실제 클래스(\Molongui\Authorship\Post / Author)를 직접 사용합니다. */
    if ( class_exists( '\Molongui\Authorship\Post' ) && class_exists( '\Molongui\Authorship\Author' ) ) {
        $ma_refs = \Molongui\Authorship\Post::get_authors( $post_id );
        foreach ( (array) $ma_refs as $ma_ref ) {
            if ( empty( $ma_ref->id ) || empty( $ma_ref->type ) ) {
                continue;
            }
            $ma_author = new \Molongui\Authorship\Author( (int) $ma_ref->id, $ma_ref->type );

            $name = (string) $ma_author->get_display_name();
            if ( ! $name ) {
                continue;
            }

            $bio = (string) $ma_author->get_description();
            if ( ! $bio && 'user' === $ma_ref->type ) {
                $bio = (string) get_the_author_meta( 'description', (int) $ma_ref->id );
            }

            $url = (string) $ma_author->get_archive_url();
            if ( '#molongui-disabled-link' === $url ) {
                $url = ''; // 게스트 저자는 아카이브 페이지가 없음
            }

            $av_html = $ma_author->get_avatar( [ $avatar_size, $avatar_size ], 'screen' );
            if ( $avatar_class && $av_html ) {
                $av_html = preg_replace( '/class=([\'"])/', 'class=$1' . esc_attr( $avatar_class ) . ' ', $av_html, 1 );
            }

            $authors[] = [
                'id'          => (int) $ma_ref->id,
                'name'        => $name,
                'description' => $bio,
                'url'         => $url,
                'avatar_html' => $av_html,
            ];
        }
        if ( $authors ) {
            return $authors;
        }
    }

    /* ── 기본 WordPress ── */
    $post = get_post( $post_id );
    if ( $post ) {
        $uid = (int) $post->post_author;
        $authors[] = [
            'id'          => $uid,
            'name'        => (string) get_the_author_meta( 'display_name', $uid ),
            'description' => (string) get_the_author_meta( 'description', $uid ),
            'url'         => get_author_posts_url( $uid ),
            'avatar_html' => get_avatar( $uid, $avatar_size, '', '', $av_attr ),
        ];
    }
    return $authors;
}
endif;

/**
 * 엠버 저널 — 추천 기사 위젯 HTML 반환
 * Customizer의 lp_aj_picks_cat 설정으로 카테고리 지정 가능
 *
 * @return string
 */
function lp_aj_picks_html() {
    $cat_slug = get_theme_mod( 'lp_aj_picks_cat', '' );
    $args     = [
        'posts_per_page' => 5,
        'orderby'        => 'rand',
        'no_found_rows'  => true,
    ];
    if ( $cat_slug ) {
        $args['category_name'] = sanitize_text_field( $cat_slug );
    }
    $q = new WP_Query( $args );

    if ( ! $q->have_posts() ) {
        return '<p style="padding:0.75rem 1rem;font-size:0.8rem;color:#9ca3af;">기사가 없습니다.</p>';
    }

    $html = '<ul class="aj-thumb-list">';
    while ( $q->have_posts() ) {
        $q->the_post();
        $pid   = get_the_ID();
        $url   = get_permalink();
        $title = get_the_title();

        if ( has_post_thumbnail( $pid ) ) {
            $thumb_url = get_the_post_thumbnail_url( $pid, 'thumbnail' );
            $thumb = '<a href="' . esc_url( $url ) . '" class="aj-thumb-img-wrap" tabindex="-1" aria-hidden="true">'
                   . '<img src="' . esc_url( $thumb_url ) . '" alt="' . esc_attr( $title ) . '" class="aj-thumb-img" loading="lazy">'
                   . '</a>';
        } else {
            $thumb = '<span class="aj-thumb-img-wrap aj-thumb-no-img" aria-hidden="true"></span>';
        }

        $html .= '<li class="aj-thumb-item">' . $thumb;
        $html .= '<a href="' . esc_url( $url ) . '" class="aj-thumb-link">' . esc_html( $title ) . '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    wp_reset_postdata();
    return $html;
}

/**
 * 엠버 저널 — 댓글 렌더링 콜백
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 */
function lp_aj_comment_cb( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    $tag = ( $args['style'] === 'div' ) ? 'div' : 'li';
    ?>
    <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'aj-comment-item', $comment ); ?>>
        <div class="aj-comment-body">
            <div class="aj-comment-author-row">
                <?php echo get_avatar( $comment, 36, '', '', [ 'class' => 'aj-comment-avatar' ] ); ?>
                <div class="aj-comment-meta-info">
                    <span class="aj-comment-name"><?php echo get_comment_author_link( $comment ); ?></span>
                    <time class="aj-comment-time" datetime="<?php comment_time( 'c' ); ?>">
                        <?php echo get_comment_date( 'Y.m.d', $comment ); ?>
                    </time>
                </div>
                <?php comment_reply_link( array_merge( $args, [
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<span class="aj-comment-reply">',
                    'after'     => '</span>',
                ] ) ); ?>
            </div>
            <?php if ( '0' === $comment->comment_approved ) : ?>
                <p class="aj-comment-moderation">검토 중인 댓글입니다.</p>
            <?php endif; ?>
            <div class="aj-comment-text"><?php comment_text(); ?></div>
        </div>
    <?php
}

/**
 * 일반 스킨용(Fresh·Classic·Minimal) 댓글 콜백
 *
 * @param WP_Comment $comment
 * @param array      $args
 * @param int        $depth
 */
if ( ! function_exists( 'lp_generic_comment_cb' ) ) :
function lp_generic_comment_cb( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    $author_url = get_comment_author_url( $comment );
    $author     = get_comment_author( $comment );
    ?>
    <li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'lp-cmt-item', $comment ); ?>>
        <div class="lp-cmt-body">
            <div class="lp-cmt-header">
                <div class="lp-cmt-avatar">
                    <?php echo get_avatar( $comment, 40, '', '', [ 'class' => 'lp-cmt-av-img' ] ); ?>
                </div>
                <div class="lp-cmt-info">
                    <span class="lp-cmt-author">
                        <?php if ( $author_url ) : ?>
                            <a href="<?php echo esc_url( $author_url ); ?>" rel="nofollow noopener" target="_blank"><?php echo esc_html( $author ); ?></a>
                        <?php else : ?>
                            <?php echo esc_html( $author ); ?>
                        <?php endif; ?>
                    </span>
                    <time class="lp-cmt-time" datetime="<?php comment_time( 'c' ); ?>">
                        <?php echo get_comment_date( 'Y.m.d', $comment ) . ' ' . get_comment_time( 'H:i', false, false, $comment ); ?>
                    </time>
                </div>
                <div class="lp-cmt-reply">
                    <?php comment_reply_link( array_merge( $args, [
                        'depth'      => $depth,
                        'max_depth'  => $args['max_depth'],
                        'reply_text' => '답글',
                        'before'     => '',
                        'after'      => '',
                    ] ) ); ?>
                </div>
            </div>
            <?php if ( '0' === $comment->comment_approved ) : ?>
            <p class="lp-cmt-moderation">검토 대기 중인 댓글입니다.</p>
            <?php endif; ?>
            <div class="lp-cmt-text"><?php comment_text(); ?></div>
        </div>
    <?php // </li> 는 WordPress 가 자동으로 닫습니다
}
endif;

/**
 * 로고 타입 sanitize
 */
function lp_sanitize_feat_count( $value ) {
    return in_array( (string) $value, [ '3', '4', '5' ], true ) ? (string) $value : '5';
}
function lp_sanitize_feat_excerpt_len( $value ) {
    $v = (int) $value;
    return ( $v >= 50 && $v <= 500 ) ? $v : 150;
}
function lp_sanitize_logo_type( $value ) {
    $allowed = [ 'text', 'image', 'svg' ];
    return in_array( $value, $allowed, true ) ? $value : 'text';
}

/**
 * SVG 코드 sanitize — 관리자는 원본 유지, 그 외 태그 제거
 */
function lp_sanitize_logo_svg( $value ) {
    if ( current_user_can( 'unfiltered_html' ) ) {
        return $value;
    }
    // SVG 허용 태그 목록 (XSS 방지)
    $allowed = [
        'svg'      => [ 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'viewBox' => true, 'fill' => true, 'class' => true, 'style' => true, 'role' => true, 'aria-label' => true, 'aria-hidden' => true ],
        'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'fill-rule' => true, 'clip-rule' => true, 'opacity' => true ],
        'rect'     => [ 'width' => true, 'height' => true, 'x' => true, 'y' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
        'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
        'ellipse'  => [ 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true ],
        'g'        => [ 'fill' => true, 'transform' => true, 'opacity' => true, 'clip-path' => true ],
        'text'     => [ 'x' => true, 'y' => true, 'font-size' => true, 'fill' => true, 'font-family' => true, 'font-weight' => true, 'text-anchor' => true ],
        'tspan'    => [ 'x' => true, 'y' => true, 'dy' => true ],
        'polygon'  => [ 'points' => true, 'fill' => true, 'stroke' => true ],
        'polyline' => [ 'points' => true, 'stroke' => true, 'fill' => true ],
        'line'     => [ 'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true ],
        'defs'     => [],
        'title'    => [],
        'desc'     => [],
        'mask'     => [ 'id' => true ],
        'use'      => [ 'href' => true, 'xlink:href' => true, 'x' => true, 'y' => true ],
        'symbol'   => [ 'id' => true, 'viewBox' => true, 'viewbox' => true ],
        'clipPath' => [ 'id' => true ],
        'stop'     => [ 'offset' => true, 'stop-color' => true, 'stop-opacity' => true, 'style' => true ],
        'linearGradient' => [ 'id' => true, 'x1' => true, 'x2' => true, 'y1' => true, 'y2' => true, 'gradientUnits' => true ],
        'radialGradient' => [ 'id' => true, 'cx' => true, 'cy' => true, 'r' => true, 'gradientUnits' => true ],
    ];
    return wp_kses( $value, $allowed );
}

/**
 * 레이아웃 스킨 변수를 반환합니다.
 * header.php, footer.php, 각 템플릿에서 extract(lp_skin_vars())로 사용합니다.
 * 정적 캐시로 한 요청 내에서 중복 계산을 방지합니다.
 *
 * @return array { current_theme_style, body_class, container_class }
 */
function lp_skin_vars() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $allowed_styles      = [ 'swn-style', 'newyorktimes-style', 'basic', 'amber-journal' ];
    $saved_style         = get_theme_mod( 'larapress_layout_style', 'swn-style' );
    $current_theme_style = in_array( $saved_style, $allowed_styles, true ) ? $saved_style : 'swn-style';

    if ( $current_theme_style === 'swn-style' ) {
        $body_class      = 'bg-white text-slate-900 font-sans swn-skin';
        $container_class = 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
    } elseif ( $current_theme_style === 'newyorktimes-style' ) {
        $body_class      = 'bg-white text-black font-serif nyt-skin';
        $container_class = 'max-w-5xl mx-auto px-4';
    } elseif ( $current_theme_style === 'amber-journal' ) {
        $body_class      = 'bg-white text-gray-900 font-sans amber-skin';
        $container_class = 'max-w-6xl mx-auto px-4 sm:px-6';
    } else {
        $body_class      = 'bg-slate-50 text-slate-800 font-sans basic-skin';
        $container_class = 'max-w-6xl mx-auto px-4';
    }

    $cache = compact( 'current_theme_style', 'body_class', 'container_class' );
    return $cache;
}

/**
 * 홈 위젯 — 너비 자유 텍스트 → grid-column span (6열 기준) 변환
 */
if ( ! function_exists( 'lp_width_to_span' ) ) :
function lp_width_to_span( $width, $default_span ) {
    $width = trim( (string) $width );
    if ( $width === '' || $width === 'auto' ) return $default_span;
    if ( in_array( $width, [ '100%', 'full', '전체' ], true ) ) return 6;
    if ( preg_match( '/^(\d+)\/(\d+)$/', $width, $m ) && (int) $m[2] > 0 ) {
        return max( 1, min( 6, (int) round( (int) $m[1] / (int) $m[2] * 6 ) ) );
    }
    if ( preg_match( '/^(\d+(?:\.\d+)?)%$/', $width, $m ) ) {
        return max( 1, min( 6, (int) round( (float) $m[1] / 100 * 6 ) ) );
    }
    if ( is_numeric( $width ) && (int) $width >= 1 && (int) $width <= 6 ) {
        return (int) $width;
    }
    return $default_span;
}
endif;

/**
 * 홈 위젯 — 단일 위젯 본문 렌더
 *
 * @param array  $w        위젯 설정 배열 (type, cat, cols, title, width)
 * @param array  $grid_map cols → Tailwind grid 클래스
 * @param array  $span_map cols → Tailwind span 클래스
 */
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

/**
 * 홈 위젯 데이터 파싱 — lp_home_widgets JSON → 구조화된 배열 반환
 *
 * @return array
 */
function lp_parse_home_widgets() {
    $lp_parse_widget = function ( $r ) {
        if ( ! is_array( $r ) ) return null;
        $t = $r['type'] ?? '';
        if ( ! in_array( $t, [ 'list', 'gallery' ], true ) ) return null;
        $c = $r['cols']  ?? '2';
        $w = $r['width'] ?? 'auto';
        return [
            'type'  => $t,
            'cat'   => (int) ( $r['cat'] ?? 0 ),
            'cols'  => in_array( $c, [ '1', '2', '3' ], true ) ? (int) $c : 2,
            'title' => sanitize_text_field( $r['title'] ?? '' ),
            'width' => sanitize_text_field( $w ),
        ];
    };

    $raw     = get_theme_mod( 'lp_home_widgets', '[{"type":"list","cat":"0","cols":"2","title":""}]' );
    $decoded = json_decode( $raw, true );
    $widgets = [];

    if ( is_array( $decoded ) ) {
        foreach ( $decoded as $raw_w ) {
            if ( ! is_array( $raw_w ) ) continue;
            if ( ( $raw_w['type'] ?? '' ) === 'section' ) {
                $sec_c     = $raw_w['cols'] ?? '2';
                $sec_items = [];
                if ( is_array( $raw_w['items'] ?? null ) ) {
                    foreach ( $raw_w['items'] as $sub ) {
                        $parsed = $lp_parse_widget( $sub );
                        if ( $parsed ) $sec_items[] = $parsed;
                    }
                }
                $widgets[] = [
                    'type'  => 'section',
                    'cols'  => in_array( $sec_c, [ '1', '2', '3' ], true ) ? (int) $sec_c : 2,
                    'title' => sanitize_text_field( $raw_w['title'] ?? '' ),
                    'items' => $sec_items,
                ];
            } else {
                $parsed = $lp_parse_widget( $raw_w );
                if ( $parsed ) $widgets[] = $parsed;
            }
        }
    }

    return empty( $widgets )
        ? [ [ 'type' => 'list', 'cat' => 0, 'cols' => 2, 'title' => '' ] ]
        : $widgets;
}

/**
 * 헤더 로고 HTML 반환 — 세 스킨 공통 진입점
 *
 * @param string $skin  'swn' | 'nyt' | 'basic'
 * @return string
 */
function lp_get_logo_html( $skin = 'swn' ) {
    $logo_type  = get_theme_mod( 'lp_logo_type', 'text' );
    $site_name  = get_theme_mod( 'lp_site_name', '' );
    $site_name  = $site_name !== '' ? $site_name : get_bloginfo( 'name' );
    $show_name  = get_theme_mod( 'lp_logo_show_name', '0' ) === '1';
    $logo_img   = get_theme_mod( 'lp_logo_image', '' );
    $logo_svg   = get_theme_mod( 'lp_logo_svg', '' );
    $logo_w     = max( 40, (int) get_theme_mod( 'lp_logo_width', 200 ) );
    $home       = esc_url( home_url( '/' ) );

    // '로고 유형'을 따로 "이미지"로 바꾸지 않고 이미지/SVG만 업로드한 경우에도
    // 텍스트 로고 대신 업로드한 로고가 바로 표시되도록 자동 전환합니다.
    // (유형을 명시적으로 "이미지"/"SVG"로 선택한 경우는 기존 로직 그대로 존중됩니다.)
    if ( $logo_type === 'text' ) {
        if ( $logo_img !== '' ) {
            $logo_type = 'image';
        } elseif ( $logo_svg !== '' ) {
            $logo_type = 'svg';
        }
    }

    ob_start();

    if ( $logo_type === 'image' && $logo_img ) {
        // ── 이미지 로고 (다크모드 전용 로고가 있으면 함께 출력 후 CSS로 전환) ──
        $logo_img_dark = get_theme_mod( 'lp_logo_image_dark', '' );
        echo '<a href="' . $home . '" class="lp-logo-wrap" aria-label="' . esc_attr( $site_name ) . '">';
        echo '<img src="' . esc_url( $logo_img ) . '"'
           . ' alt="' . esc_attr( $site_name ) . '"'
           . ' class="' . ( $logo_img_dark ? 'lp-logo-light-img' : '' ) . '"'
           . ' style="max-width:' . $logo_w . 'px;height:auto;display:block;"'
           . ' loading="eager">';
        if ( $logo_img_dark ) {
            echo '<img src="' . esc_url( $logo_img_dark ) . '"'
               . ' alt="' . esc_attr( $site_name ) . '"'
               . ' class="lp-logo-dark-img"'
               . ' style="max-width:' . $logo_w . 'px;height:auto;display:block;"'
               . ' loading="eager">';
        }
        if ( $show_name ) {
            echo '<span class="lp-logo-name-sub">' . esc_html( $site_name ) . '</span>';
        }
        echo '</a>';

    } elseif ( $logo_type === 'svg' && $logo_svg ) {
        // ── SVG 로고 ─────────────────────────────────────────
        echo '<a href="' . $home . '" class="lp-logo-wrap" aria-label="' . esc_attr( $site_name ) . '"'
           . ' style="display:inline-block;max-width:' . $logo_w . 'px;">';
        echo $logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput — sanitized on save
        if ( $show_name ) {
            echo '<span class="lp-logo-name-sub">' . esc_html( $site_name ) . '</span>';
        }
        echo '</a>';

    } else {
        // ── 텍스트 로고 — 스킨별 스타일 ─────────────────────
        switch ( $skin ) {
            case 'nyt':
                echo '<a href="' . $home . '" class="nyt-masthead-logo">' . esc_html( $site_name ) . '</a>';
                break;
            case 'basic':
                echo '<a href="' . $home . '" class="basic-logo">'
                   . esc_html( $site_name )
                   . '<span class="basic-logo-badge">WP</span></a>';
                break;
            case 'amber-journal':
                echo '<a href="' . $home . '" class="aj-logo-link" aria-label="' . esc_attr( $site_name ) . '">';
                echo '<span class="aj-logo-mark">' . esc_html( mb_substr( $site_name, 0, 1 ) ) . '</span>';
                echo '<span class="aj-logo-text">' . esc_html( $site_name ) . '</span>';
                echo '</a>';
                break;
            default: // swn
                echo '<a href="' . $home . '" class="text-4xl font-black text-blue-700 tracking-tighter lp-swn-logo">'
                   . esc_html( $site_name )
                   . '<span class="text-sm font-bold text-slate-400 align-top ml-1">WP</span></a>';
        }
    }

    return ob_get_clean();
}

/**
 * 푸터 로고 HTML 반환 — 라이트/다크모드 로고를 업로드한 경우 함께 출력해
 * CSS(html.dark)로 자동 전환되도록 하고, 둘 다 없으면 푸터 회사명 텍스트로 대체합니다.
 * 한쪽만 업로드된 경우 같은 이미지를 라이트/다크 모두에 사용합니다.
 *
 * @return string 이미지 태그(들) 또는 esc_html 처리된 회사명 텍스트
 */
function lp_get_footer_logo_html() {
    $light = get_theme_mod( 'lp_footer_logo_light', '' );
    $dark  = get_theme_mod( 'lp_footer_logo_dark', '' );
    $name  = get_theme_mod( 'lp_footer_company_name', get_bloginfo( 'name' ) );

    if ( ! $light && ! $dark ) {
        return esc_html( $name );
    }

    $light_src = $light ?: $dark;
    $dark_src  = $dark ?: $light;

    return '<img src="' . esc_url( $light_src ) . '" alt="' . esc_attr( $name ) . '" class="lp-logo-light-img" loading="eager">'
         . '<img src="' . esc_url( $dark_src ) . '" alt="' . esc_attr( $name ) . '" class="lp-logo-dark-img" loading="eager">';
}

/**
 * 헤더 로고(lp_logo_image) 이미지의 첨부파일 ID를 반환합니다.
 * 특성 이미지가 없는 게시물의 대체 썸네일로 사용됩니다.
 *
 * @return int 첨부파일 ID (없으면 0)
 */
function lp_get_default_thumbnail_attachment_id() {
    static $id = null;
    if ( $id !== null ) {
        return $id;
    }

    $logo_url = get_theme_mod( 'lp_logo_image', '' );
    $id       = $logo_url ? (int) attachment_url_to_postid( $logo_url ) : 0;

    if ( ! $id ) {
        $id = (int) get_theme_mod( 'custom_logo', 0 );
    }

    return $id;
}

/**
 * 특성 이미지가 없는 글에 기본 SVG 로고를 대체 썸네일로 강제 지정합니다.
 * has_post_thumbnail() / the_post_thumbnail() / get_the_post_thumbnail()가
 * 내부적으로 참조하는 get_post_thumbnail_id() 필터를 사용하므로,
 * 테마 템플릿 코드를 수정하지 않고도 모든 위치에 동일하게 적용됩니다.
 */
add_filter( 'post_thumbnail_id', 'lp_force_default_thumbnail', 10, 2 );
function lp_force_default_thumbnail( $thumbnail_id, $post ) {
    if ( $thumbnail_id ) {
        return $thumbnail_id; // 이미 특성 이미지가 있으면 그대로 사용
    }
    if ( ! $post || 'post' !== $post->post_type ) {
        return $thumbnail_id;
    }

    $default_id = lp_get_default_thumbnail_attachment_id();
    return $default_id ?: $thumbnail_id;
}

/**
 * 위 대체 썸네일로 렌더링된 <img> 태그에만 식별용 클래스를 부여합니다.
 * (헤더·푸터 로고는 wp_get_attachment_image()를 거치지 않는 별도 마크업이라 영향 없음)
 */
add_filter( 'wp_get_attachment_image_attributes', 'lp_mark_default_thumbnail_img', 10, 2 );
function lp_mark_default_thumbnail_img( $attr, $attachment ) {
    $default_id = lp_get_default_thumbnail_attachment_id();
    if ( $default_id && (int) $attachment->ID === $default_id ) {
        $attr['class'] = trim( ( $attr['class'] ?? '' ) . ' lp-thumb-fallback-logo' );
    }
    return $attr;
}

/**
 * 헤더 상단 날짜 — "2026년 10월 7일 수요일 · 최종편집 2026.10.07 14:32"
 * PHP date()는 UTC·영문 요일이라 사이트 시간대(wp_date) + 한국어 요일로 직접 조합.
 * 최종편집 = 발행된 기사(post) 중 가장 최근 수정 시각.
 *
 * @return string HTML (이스케이프 완료)
 */
function lp_header_date_html() {
    $weekdays = [ '일', '월', '화', '수', '목', '금', '토' ];
    $html     = esc_html( wp_date( 'Y년 n월 j일 ' ) . $weekdays[ (int) wp_date( 'w' ) ] . '요일' );

    $last = get_lastpostmodified( 'blog', 'post' ); // 사이트 시간대 'Y-m-d H:i:s'
    if ( $last ) {
        $html .= ' <span class="lp-last-edit">· 최종편집 ' . esc_html( mysql2date( 'Y.m.d H:i', $last ) ) . '</span>';
    }
    return $html;
}

/**
 * 헤더 태그라인 반환 — 커스터마이저 값 우선, 없으면 WP 태그라인
 */
function lp_get_tagline() {
    $tag = get_theme_mod( 'lp_site_tagline', '' );
    return $tag !== '' ? $tag : get_bloginfo( 'description' );
}

/**
 * 헤더 우측 "유저 메뉴" HTML — 다크모드 토글 옆에 출력한다.
 * 비로그인: 사람 아이콘 버튼 → 드롭다운에 로그인 · 회원가입
 * 로그인:   이름 앞글자 아바타 버튼 → 드롭다운에 내 프로필 · 내 기사 목록
 *
 * 주의: 이 함수는 로그인 여부와 무관하게 항상 "비로그인" 상태의 마크업만
 * 서버에서 렌더링한다. 이 사이트처럼 전체 페이지 캐시(WP Super Cache 등)가
 * 켜져 있으면, 로그인한 특정 회원의 이름·프로필 링크를 여기서 직접 출력할 경우
 * 그 값이 캐시된 정적 HTML에 그대로 구워져 이후 그 페이지를 방문하는 다른
 * 모든 사용자(다른 회원 포함)에게 동일하게 노출되는 문제가 생긴다.
 * ("내 기사 목록"에 다른 회원의 글이 뜨는 문제가 바로 이것)
 * 그래서 로그인 상태의 개인화된 내용은 여기서 굽지 않고, 아래
 * lp_ajax_user_menu()를 통해 매 방문마다 admin-ajax.php로 따로 받아와
 * (이 요청은 캐시 플러그인이 캐시하지 않는다) JS가 클라이언트에서 채워 넣는다.
 * → 관련 JS: footer.php의 "유저 메뉴 개인화 정보" 스크립트
 *
 * @param int $icon_size 아이콘의 px 크기 (스킨별 다크모드 아이콘 크기와 맞춤)
 */
function lp_get_user_menu_html( $icon_size = 18 ) {
    ob_start();
    ?>
    <div class="lp-user-menu" id="lpUserMenu">
        <button type="button" class="lp-dm-btn lp-user-btn" id="lpUserBtn" aria-haspopup="true" aria-expanded="false" title="로그인 / 회원가입">
            <svg width="<?php echo (int) $icon_size; ?>" height="<?php echo (int) $icon_size; ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
        </button>
        <ul class="lp-user-dropdown" id="lpUserDropdown">
            <li><a href="<?php echo esc_url( wp_login_url( home_url( add_query_arg( [], null ) ) ) ); ?>">로그인</a></li>
            <li><a href="<?php echo esc_url( wp_registration_url() ); ?>">회원가입</a></li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX: 현재 로그인한 사용자의 유저 메뉴 개인화 데이터를 반환한다.
 * admin-ajax.php 요청은 페이지 캐시 플러그인이 캐시하지 않으므로,
 * 매 방문자마다 항상 "그 방문자 본인"의 정확한 정보를 반환한다.
 */
function lp_ajax_user_menu() {
    nocache_headers(); // 혹시 모를 중간 캐시(프록시 등)까지 차단

    if ( ! is_user_logged_in() ) {
        wp_send_json_success( [ 'loggedIn' => false ] );
    }

    $lp_user    = wp_get_current_user();
    $lp_initial = function_exists( 'mb_strtoupper' )
        ? mb_strtoupper( mb_substr( $lp_user->display_name, 0, 1 ) )
        : strtoupper( substr( $lp_user->display_name, 0, 1 ) );

    wp_send_json_success( [
        'loggedIn'   => true,
        'name'       => $lp_user->display_name,
        'initial'    => $lp_initial,
        'profileUrl' => get_edit_profile_url( $lp_user->ID ),
        'postsUrl'   => get_author_posts_url( $lp_user->ID ),
    ] );
}
add_action( 'wp_ajax_lp_user_menu', 'lp_ajax_user_menu' );
add_action( 'wp_ajax_nopriv_lp_user_menu', 'lp_ajax_user_menu' );

/**
 * larapress_layout_style Customizer 설정값 sanitize — 허용 목록 외 값은 기본값으로 대체
 */
function larapress_sanitize_layout_style($value) {
    $allowed = ['swn-style', 'newyorktimes-style', 'basic', 'amber-journal'];
    return in_array($value, $allowed, true) ? $value : 'swn-style';
}

/** 아카이브 스타일 sanitize — 허용 목록 외 값은 기본값으로 */
function lp_sanitize_archive_style( $value ) {
    $allowed = [ 'list', 'grid2', 'grid3', 'webzine' ];
    return in_array( $value, $allowed, true ) ? $value : 'list';
}

/** 체크박스 sanitize — '1' 또는 '0' 반환 */
function lp_sanitize_checkbox( $value ) {
    return ( $value === true || $value === '1' || $value === 1 ) ? '1' : '0';
}

/**
 * 배너 코드 sanitize — 관리자는 스크립트 포함 HTML 허용, 그 외는 wp_kses_post
 */
function lp_sanitize_banner_code( $value ) {
    if ( current_user_can( 'unfiltered_html' ) ) {
        return $value;
    }
    return wp_kses_post( $value );
}

/**
 * 커스터마이저 컨트롤 패널에서 발행인·편집인 필드를 체크박스 값에 따라 동적으로 표시/숨김
 */
add_action( 'customize_controls_print_footer_scripts', function () {
    ?>
    <script>
    (function () {
        wp.customize.bind('ready', function () {

            /* ── 발행인·편집인 동일 토글 ── */
            function lpTogglePubEditor(same) {
                same = (same === true || same === '1' || same === 1);
                var combined  = wp.customize.control('lp_pub_editor_name');
                var publisher = wp.customize.control('lp_publisher_name');
                var editor    = wp.customize.control('lp_editor_name');
                if (combined)  combined.container.toggle(same);
                if (publisher) publisher.container.toggle(!same);
                if (editor)    editor.container.toggle(!same);
            }
            wp.customize('lp_pub_editor_same', function (value) {
                lpTogglePubEditor(value.get());
                value.bind(lpTogglePubEditor);
            });

            /* ── 로고 타입 변경 시 관련 컨트롤 표시/숨김 ── */
            function lpToggleLogoControls(type) {
                var isImg = (type === 'image');
                var isSvg = (type === 'svg');
                var needMedia = isImg || isSvg;

                var ctrlMap = {
                    lp_logo_image    : isImg,
                    lp_logo_svg      : isSvg,
                    lp_logo_width    : needMedia,
                    lp_logo_show_name: needMedia,
                };
                Object.keys(ctrlMap).forEach(function(id) {
                    wp.customize.control(id, function(ctrl) {
                        if (ctrlMap[id]) { ctrl.activate(); } else { ctrl.deactivate(); }
                    });
                });
            }
            wp.customize('lp_logo_type', function (value) {
                lpToggleLogoControls(value.get());
                value.bind(lpToggleLogoControls);
            });

        });
    }());
    </script>
    <?php
} );

/**
 * lp_home_widgets JSON sanitize
 * standalone(list|gallery)·section 타입 유효성 검사 후 재직렬화.
 */
function lp_sanitize_home_widgets( $value ) {
    $decoded = json_decode( $value, true );
    if ( ! is_array( $decoded ) ) {
        return '[{"type":"list","cat":"0","cols":"2","title":""}]';
    }

    $clean_widget = function ( $w ) {
        if ( ! is_array( $w ) ) return null;
        $type = $w['type'] ?? '';
        if ( ! in_array( $type, [ 'list', 'gallery' ], true ) ) return null;
        $cols = $w['cols'] ?? '2';
        return [
            'type'  => $type,
            'cat'   => (string) (int) ( $w['cat'] ?? 0 ),
            'cols'  => in_array( $cols, [ '1', '2', '3' ], true ) ? $cols : '2',
            'title' => sanitize_text_field( $w['title'] ?? '' ),
            'width' => sanitize_text_field( $w['width'] ?? '' ),
        ];
    };

    $clean = [];
    foreach ( $decoded as $w ) {
        if ( ! is_array( $w ) ) continue;
        if ( ( $w['type'] ?? '' ) === 'section' ) {
            $sec_cols = $w['cols'] ?? '2';
            $items    = [];
            if ( is_array( $w['items'] ?? null ) ) {
                foreach ( $w['items'] as $sub ) {
                    $cleaned = $clean_widget( $sub );
                    if ( $cleaned ) $items[] = $cleaned;
                }
            }
            $clean[] = [
                'type'  => 'section',
                'cols'  => in_array( $sec_cols, [ '1', '2', '3' ], true ) ? $sec_cols : '2',
                'title' => sanitize_text_field( $w['title'] ?? '' ),
                'items' => $items,
            ];
        } else {
            $cleaned = $clean_widget( $w );
            if ( $cleaned ) $clean[] = $cleaned;
        }
    }
    return empty( $clean )
        ? '[{"type":"list","cat":"0","cols":"2","title":""}]'
        : wp_json_encode( $clean );
}

// ─────────────────────────────────────────────────────────────
// 기자 서명(프로필 소개) 필드 — 회원 프로필 편집 페이지에 추가
// ─────────────────────────────────────────────────────────────

/**
 * 기자 서명 입력 폼을 WP 회원 프로필 편집 페이지에 출력한다.
 * show_user_profile  → 본인 프로필 편집 (/wp-admin/profile.php)
 * edit_user_profile  → 관리자가 타 사용자 편집 (/wp-admin/user-edit.php)
 */
function larapress_reporter_bio_field( $user ) {
    $bio = get_user_meta( $user->ID, 'lara_reporter_bio', true );
    ?>
    <h3>기자 프로필 서명</h3>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="lara_reporter_bio">기자 소개 / 서명</label></th>
            <td>
                <textarea name="lara_reporter_bio" id="lara_reporter_bio"
                          rows="4" cols="50" class="regular-text"><?php echo esc_textarea( $bio ); ?></textarea>
                <p class="description">기사 하단 기자 프로필 박스에 표시되는 소개 문구입니다. (최대 300자)</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'larapress_reporter_bio_field' );
add_action( 'edit_user_profile', 'larapress_reporter_bio_field' );

/**
 * 기자 서명 저장 — 현재 사용자 또는 관리자만 저장 가능
 */
function larapress_save_reporter_bio( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    if ( ! isset( $_POST['lara_reporter_bio'] ) ) {
        return;
    }
    $bio = sanitize_textarea_field( $_POST['lara_reporter_bio'] );
    // 최대 300자 제한
    $bio = mb_substr( $bio, 0, 300 );
    update_user_meta( $user_id, 'lara_reporter_bio', $bio );
}
add_action( 'personal_options_update',  'larapress_save_reporter_bio' );
add_action( 'edit_user_profile_update', 'larapress_save_reporter_bio' );

// ─────────────────────────────────────────────────────────────
// 기사 댓글 삭제 핸들러 (admin-post.php 라우팅)
// ─────────────────────────────────────────────────────────────

/**
 * 로그인한 사용자만 호출 가능 (admin_post_{action}).
 * 작성자 본인 또는 moderate_comments 권한 보유자만 삭제 허용.
 */
function larapress_delete_article_comment() {
    $comment_id = intval( $_POST['comment_id'] ?? 0 );
    $redirect   = esc_url_raw( $_POST['redirect_to'] ?? home_url() );

    if ( ! wp_verify_nonce( $_POST['lara_del_nonce'] ?? '', 'lara_delete_comment_' . $comment_id ) ) {
        wp_die( '보안 토큰이 유효하지 않습니다.', '오류', [ 'response' => 403 ] );
    }

    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        wp_die( '존재하지 않는 댓글입니다.', '오류', [ 'response' => 404 ] );
    }

    $is_own   = ( int ) $comment->user_id === get_current_user_id();
    $is_admin = current_user_can( 'moderate_comments' );
    if ( ! $is_own && ! $is_admin ) {
        wp_die( '삭제 권한이 없습니다.', '권한 없음', [ 'response' => 403 ] );
    }

    wp_delete_comment( $comment_id, true );
    wp_redirect( $redirect );
    exit;
}
add_action( 'admin_post_lara_delete_article_comment', 'larapress_delete_article_comment' );

// ─────────────────────────────────────────────────────────────
// 댓글 모더레이션 핸들러 — 승인 / 보류 / 스팸 / 휴지통
// ─────────────────────────────────────────────────────────────

/**
 * moderate_comments 권한자만 호출 가능 (admin_post_lara_moderate_comment).
 * 허용 액션: approve, hold, spam, trash
 */
function larapress_moderate_comment() {
    $comment_id = intval( $_POST['comment_id'] ?? 0 );
    $mod_action = sanitize_key( $_POST['mod_action'] ?? '' );
    $redirect   = esc_url_raw( $_POST['redirect_to'] ?? home_url() );

    // 1) nonce 검증
    if ( ! wp_verify_nonce( $_POST['lara_mod_nonce'] ?? '', 'lara_moderate_comment_' . $comment_id ) ) {
        wp_die( '보안 토큰이 유효하지 않습니다.', '오류', [ 'response' => 403 ] );
    }

    // 2) 권한 검증
    if ( ! current_user_can( 'moderate_comments' ) ) {
        wp_die( '관리 권한이 없습니다.', '권한 없음', [ 'response' => 403 ] );
    }

    // 3) 댓글 존재 확인
    $comment = get_comment( $comment_id );
    if ( ! $comment ) {
        wp_die( '존재하지 않는 댓글입니다.', '오류', [ 'response' => 404 ] );
    }

    // 4) 허용 액션만 처리
    $allowed = [ 'approve', 'hold', 'spam', 'trash' ];
    if ( ! in_array( $mod_action, $allowed, true ) ) {
        wp_die( '허용되지 않는 동작입니다.', '오류', [ 'response' => 400 ] );
    }

    switch ( $mod_action ) {
        case 'approve':
            wp_set_comment_status( $comment_id, 'approve' );
            break;
        case 'hold':
            wp_set_comment_status( $comment_id, 'hold' );
            break;
        case 'spam':
            wp_spam_comment( $comment_id );
            break;
        case 'trash':
            wp_trash_comment( $comment_id );
            break;
    }

    wp_redirect( $redirect );
    exit;
}
add_action( 'admin_post_lara_moderate_comment', 'larapress_moderate_comment' );

// ─────────────────────────────────────────────────────────────
// 기사 공유 수 집계 — post_meta 'lp_shares' ("많이 본 뉴스" 랭킹에 사용)
// 페이지 캐시(WP Super Cache) 환경이라 nonce 대신 쿠키로 같은 브라우저의
// 같은 글 공유는 하루 1회만 집계한다.
// ─────────────────────────────────────────────────────────────
function larapress_track_share() {
    $post_id  = intval( $_POST['post_id'] ?? 0 );
    $platform = sanitize_key( $_POST['platform'] ?? '' );

    if ( ! $post_id || ! in_array( $platform, [ 'facebook', 'x', 'twitter', 'kakao' ], true ) ) {
        wp_send_json_error( [ 'message' => 'invalid' ] );
    }
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'post' || $post->post_status !== 'publish' ) {
        wp_send_json_error( [ 'message' => 'not_found' ] );
    }

    $cookie_name = 'lp_sh_' . $post_id;
    if ( isset( $_COOKIE[ $cookie_name ] ) ) {
        wp_send_json_success( [ 'counted' => false ] );
    }
    setcookie( $cookie_name, '1', time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

    $shares = (int) get_post_meta( $post_id, 'lp_shares', true ) + 1;
    update_post_meta( $post_id, 'lp_shares', $shares );
    wp_send_json_success( [ 'counted' => true ] );
}
add_action( 'wp_ajax_lp_track_share', 'larapress_track_share' );
add_action( 'wp_ajax_nopriv_lp_track_share', 'larapress_track_share' );

// ─────────────────────────────────────────────────────────────
// 기사 반응(Reaction) 시스템
// ─────────────────────────────────────────────────────────────

/**
 * 허용된 반응 타입 목록 반환
 * post_meta 'lp_reactions' : ['useful' => 5, 'exciting' => 2, ...]
 * user_meta 'lp_user_reactions' : ['p123' => ['useful', 'like'], ...]
 */
function lp_reaction_types() {
    return [
        'useful'     => [ 'label' => '유용해요',       'emoji' => '💡' ],
        'exciting'   => [ 'label' => '흥미진진해요',   'emoji' => '🔥' ],
        'empathize'  => [ 'label' => '공감해요',        'emoji' => '🤝' ],
        'like'       => [ 'label' => '좋아요',          'emoji' => '👍' ],
        'insightful' => [ 'label' => '분석이 탁월해요', 'emoji' => '🧠' ],
        'follow'     => [ 'label' => '후속강추',        'emoji' => '📢' ],
    ];
}

/**
 * 현재 로그인 사용자가 특정 포스트에 남긴 반응 목록 반환
 *
 * @param int $post_id
 * @return string[]
 */
function lp_get_user_reactions( $post_id ) {
    if ( ! is_user_logged_in() ) return [];
    $all = get_user_meta( get_current_user_id(), 'lp_user_reactions', true );
    if ( ! is_array( $all ) ) return [];
    return $all[ 'p' . $post_id ] ?? [];
}

/**
 * AJAX 핸들러 — 반응 토글
 * 로그인 사용자 : user_meta 로 중복 방지 + 토글(취소 가능)
 * 비로그인 사용자: 쿠키(lp_rx_<post_id>)로 중복 방지 + 토글(취소 가능)
 */
function larapress_handle_reaction() {
    check_ajax_referer( 'lp_reaction_nonce', 'nonce' );

    $post_id = intval( $_POST['post_id'] ?? 0 );
    $type    = sanitize_key( $_POST['type'] ?? '' );
    $allowed = array_keys( lp_reaction_types() );

    if ( ! $post_id || ! in_array( $type, $allowed, true ) ) {
        wp_send_json_error( [ 'message' => 'invalid' ] );
    }

    $post = get_post( $post_id );
    if ( ! $post || $post->post_status !== 'publish' ) {
        wp_send_json_error( [ 'message' => 'not_found' ] );
    }

    $reactions = get_post_meta( $post_id, 'lp_reactions', true );
    if ( ! is_array( $reactions ) ) $reactions = [];
    if ( ! isset( $reactions[ $type ] ) ) $reactions[ $type ] = 0;

    $active    = true;
    $prev_type = null;

    if ( is_user_logged_in() ) {
        $user_id        = get_current_user_id();
        $user_reactions = get_user_meta( $user_id, 'lp_user_reactions', true );
        if ( ! is_array( $user_reactions ) ) $user_reactions = [];

        $post_key            = 'p' . $post_id;
        $user_post_reactions = $user_reactions[ $post_key ] ?? [];

        if ( in_array( $type, $user_post_reactions, true ) ) {
            // 같은 반응 다시 클릭 → 취소(토글 오프)
            $user_post_reactions = array_values( array_diff( $user_post_reactions, [ $type ] ) );
            $reactions[ $type ]  = max( 0, $reactions[ $type ] - 1 );
            $active              = false;
        } else {
            // 기존 반응이 있으면 취소 후 교체 (포스트당 1개 제한)
            if ( ! empty( $user_post_reactions ) ) {
                $prev_type = $user_post_reactions[0];
                if ( isset( $reactions[ $prev_type ] ) ) {
                    $reactions[ $prev_type ] = max( 0, $reactions[ $prev_type ] - 1 );
                }
                $user_post_reactions = [];
            }
            $user_post_reactions[] = $type;
            $reactions[ $type ]++;
        }

        $user_reactions[ $post_key ] = $user_post_reactions;
        update_user_meta( $user_id, 'lp_user_reactions', $user_reactions );
    } else {
        // 비로그인: 쿠키에 저장된 기존 반응과 비교해 서버 측에서 중복 증가를 차단
        $cookie_name = 'lp_rx_' . $post_id;
        $prev_saved  = isset( $_COOKIE[ $cookie_name ] ) ? sanitize_key( $_COOKIE[ $cookie_name ] ) : '';
        $new_cookie_value = $type;

        if ( $prev_saved !== '' && $prev_saved === $type ) {
            // 같은 반응 다시 클릭 → 취소(토글 오프)
            $reactions[ $type ] = max( 0, $reactions[ $type ] - 1 );
            $active           = false;
            $new_cookie_value = '';
        } else {
            if ( $prev_saved !== '' && in_array( $prev_saved, $allowed, true ) ) {
                // 기존 반응이 있으면 취소 후 교체 (포스트당 1개 제한)
                $prev_type = $prev_saved;
                if ( ! isset( $reactions[ $prev_type ] ) ) $reactions[ $prev_type ] = 0;
                $reactions[ $prev_type ] = max( 0, $reactions[ $prev_type ] - 1 );
            }
            $reactions[ $type ]++;
        }

        if ( $new_cookie_value === '' ) {
            setcookie( $cookie_name, '', time() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
            unset( $_COOKIE[ $cookie_name ] );
        } else {
            setcookie( $cookie_name, $new_cookie_value, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
            $_COOKIE[ $cookie_name ] = $new_cookie_value;
        }
    }

    update_post_meta( $post_id, 'lp_reactions', $reactions );

    $response = [
        'count'  => $reactions[ $type ],
        'active' => $active,
        'type'   => $type,
    ];
    if ( $prev_type !== null ) {
        $response['prev_type']  = $prev_type;
        $response['prev_count'] = $reactions[ $prev_type ];
    }
    wp_send_json_success( $response );
}
add_action( 'wp_ajax_lp_react',        'larapress_handle_reaction' );
add_action( 'wp_ajax_nopriv_lp_react', 'larapress_handle_reaction' );

// ─────────────────────────────────────────────────────────────
// 753번 "About Us" 페이지 — "청라온을 만드는 사람들" 인물 모달
//
// 예전에 사용하던 팀원 소개 플러그인(neeon_team CPT)은 삭제되었지만
// 그 당시 등록된 인물 데이터(postmeta 포함)는 DB에 그대로 남아있음.
// 해당 CPT를 다시 등록하지 않고도 get_post()/get_post_meta()로
// 직접 조회해 모달에 뿌려준다. (post_type이 등록되어 있지 않아도
// 이 함수들은 정상 동작함 — WP_Query 등 목록 조회에만 등록이 필요)
// ─────────────────────────────────────────────────────────────

/**
 * 팀원 소개글(경력/수상이력 형식의 텍스트)을 항목별 리스트 HTML로 변환.
 * 원본 형식 예:
 *   경력:
 *   1. ...
 *   2. ...
 *
 *   수상이력:
 *   1. ...
 */
function larapress_parse_team_bio( $raw ) {
    $raw   = str_replace( [ "\r\n", "\r" ], "\n", (string) $raw );
    $lines = explode( "\n", $raw );

    $sections = [ 'career' => [], 'awards' => [] ];
    $current  = null;

    foreach ( $lines as $line ) {
        $line = trim( wp_strip_all_tags( $line ) );
        $line = str_replace( '&nbsp;', '', $line );
        $line = trim( $line );

        if ( $line === '' ) {
            continue;
        }

        if ( preg_match( '/^경력\s*:?$/u', $line ) ) {
            $current = 'career';
            continue;
        }
        if ( preg_match( '/^수상\s*이력\s*:?$/u', $line ) ) {
            $current = 'awards';
            continue;
        }
        if ( ! $current ) {
            continue;
        }

        // "1. 내용" 형태면 번호를 떼고 저장 (목록 태그가 알아서 번호를 매김)
        if ( preg_match( '/^\d+\.\s*(.+)$/u', $line, $m ) ) {
            $sections[ $current ][] = $m[1];
        } else {
            $sections[ $current ][] = $line;
        }
    }

    $to_html = function ( $items ) {
        if ( empty( $items ) ) {
            return '<p class="text-slate-400">등록된 내용이 없습니다.</p>';
        }
        $html = '<ol class="list-decimal list-inside space-y-1.5 leading-relaxed">';
        foreach ( $items as $item ) {
            $html .= '<li>' . esc_html( $item ) . '</li>';
        }
        $html .= '</ol>';
        return $html;
    };

    return [
        'career_html' => $to_html( $sections['career'] ),
        'awards_html' => $to_html( $sections['awards'] ),
    ];
}

/**
 * AJAX: 팀원 1명의 데이터를 JSON으로 반환 (753페이지 모달용)
 */
function larapress_ajax_get_team_member() {
    check_ajax_referer( 'lp_team_modal', 'nonce' );

    $member_id = isset( $_POST['member_id'] ) ? absint( $_POST['member_id'] ) : 0;
    $post      = $member_id ? get_post( $member_id ) : null;

    // neeon_team CPT는 더이상 등록되어 있지 않으므로, 저장된 post_type 문자열로만 검증.
    // 게시(publish) 상태인 팀원만 공개 — 초안(draft)은 노출하지 않음.
    if ( ! $post || $post->post_type !== 'neeon_team' || $post->post_status !== 'publish' ) {
        wp_send_json_error( [ 'message' => 'not_found' ], 404 );
    }

    $position = get_post_meta( $member_id, 'neeon_team_position', true );
    $email    = get_post_meta( $member_id, 'neeon_team_email', true );
    $website  = get_post_meta( $member_id, 'neeon_team_website', true );
    $socials  = get_post_meta( $member_id, 'neeon_team_socials', true );

    if ( ! is_array( $socials ) ) {
        $socials = [];
    }
    $socials = array_filter( $socials, function ( $v ) {
        return is_string( $v ) && trim( $v ) !== '';
    } );

    $photo = get_the_post_thumbnail_url( $member_id, 'medium' );
    if ( ! $photo ) {
        $photo = get_the_post_thumbnail_url( $member_id, 'full' );
    }

    $bio = larapress_parse_team_bio( $post->post_content );

    // 이메일은 로그인 회원에게만 공개 — 비로그인 사용자에게는 잠금 여부만 알려준다.
    $has_email       = is_email( $email );
    $can_view_email  = is_user_logged_in();

    wp_send_json_success( [
        'name'        => get_the_title( $member_id ),
        'position'    => $position ?: '',
        'email'       => ( $has_email && $can_view_email ) ? $email : '',
        'email_locked'=> ( $has_email && ! $can_view_email ),
        'website'     => $website ? esc_url_raw( $website ) : '',
        'socials'     => $socials,
        'photo'       => $photo ?: '',
        'career_html' => $bio['career_html'],
        'awards_html' => $bio['awards_html'],
    ] );
}
add_action( 'wp_ajax_lp_get_team_member',        'larapress_ajax_get_team_member' );
add_action( 'wp_ajax_nopriv_lp_get_team_member', 'larapress_ajax_get_team_member' );

/**
 * 753페이지에서만 모달 마크업 + 클릭 핸들러 JS 출력.
 * (인물 카드 자체는 post_content 안에 class="lp-team-card" data-member-id="..." 로 이미 마킹되어 있음)
 */
function larapress_print_team_modal() {
    if ( ! is_page( 753 ) ) {
        return;
    }

    $ajax_url  = esc_url( admin_url( 'admin-ajax.php' ) );
    $nonce     = wp_create_nonce( 'lp_team_modal' );
    $login_url = esc_url( wp_login_url( get_permalink() ) );
    ?>
    <div id="lp-team-modal" class="hidden fixed inset-0 z-[9999] items-center justify-center p-4" aria-hidden="true">
        <div class="absolute inset-0 bg-black/60" data-lp-team-close></div>
        <div class="relative bg-white w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl shadow-xl p-6 md:p-8" role="dialog" aria-modal="true" aria-labelledby="lp-team-modal-name">
            <button type="button" data-lp-team-close aria-label="닫기" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div id="lp-team-modal-loading" class="text-center text-sm text-slate-400 py-16">불러오는 중...</div>
            <div id="lp-team-modal-error" class="hidden text-center text-sm text-red-500 py-16">정보를 불러오지 못했습니다.</div>

            <div id="lp-team-modal-content" class="hidden">
                <div class="text-center mb-6">
                    <img id="lp-team-modal-photo" src="" alt="" class="w-28 h-28 rounded-full object-cover mx-auto mb-4 border border-slate-200">
                    <h3 id="lp-team-modal-name" class="text-xl font-black text-slate-900"></h3>
                    <p id="lp-team-modal-position" class="text-sm font-bold text-blue-700 mt-1"></p>
                </div>

                <div id="lp-team-modal-contact" class="flex flex-wrap items-center justify-center gap-2 mb-6"></div>

                <div class="space-y-5 text-left">
                    <div>
                        <h4 class="text-xs font-extrabold tracking-widest text-slate-400 uppercase mb-2">경력</h4>
                        <div id="lp-team-modal-career" class="text-sm text-slate-600"></div>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold tracking-widest text-slate-400 uppercase mb-2">수상이력</h4>
                        <div id="lp-team-modal-awards" class="text-sm text-slate-600"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var AJAX_URL   = '<?php echo $ajax_url; ?>';
        var NONCE      = '<?php echo esc_js( $nonce ); ?>';
        var LOGIN_URL  = '<?php echo $login_url; ?>';
        var modal      = document.getElementById('lp-team-modal');
        var loadingEl  = document.getElementById('lp-team-modal-loading');
        var errorEl    = document.getElementById('lp-team-modal-error');
        var contentEl  = document.getElementById('lp-team-modal-content');
        var cache      = {};
        var lastFocus  = null;

        function openModal() {
            lastFocus = document.activeElement;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            if (lastFocus) lastFocus.focus();
        }

        function showState(state) {
            loadingEl.classList.toggle('hidden', state !== 'loading');
            errorEl.classList.toggle('hidden', state !== 'error');
            contentEl.classList.toggle('hidden', state !== 'content');
        }

        function socialLabel(key) {
            var labels = {
                facebook: '페이스북', twitter: '트위터', linkedin: '링크드인',
                behance: '비핸스', dribbble: '드리블', skype: '스카이프',
                youtube: '유튜브', pinterest: '핀터레스트', instagram: '인스타그램',
                github: '깃허브', stackoverflow: '스택오버플로우', tiktok: '틱톡'
            };
            return labels[key] || key;
        }

        function render(data) {
            document.getElementById('lp-team-modal-photo').src = data.photo || '';
            document.getElementById('lp-team-modal-photo').alt = data.name || '';
            document.getElementById('lp-team-modal-name').textContent = data.name || '';
            document.getElementById('lp-team-modal-position').textContent = data.position || '';
            document.getElementById('lp-team-modal-career').innerHTML = data.career_html || '';
            document.getElementById('lp-team-modal-awards').innerHTML = data.awards_html || '';

            var contact = document.getElementById('lp-team-modal-contact');
            contact.innerHTML = '';

            var chipClass = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-slate-200 text-xs font-bold text-slate-600 hover:border-blue-500 hover:text-blue-700 transition';

            if (data.email) {
                var a = document.createElement('a');
                a.href = 'mailto:' + data.email;
                a.className = chipClass;
                a.textContent = '✉ ' + data.email;
                contact.appendChild(a);
            } else if (data.email_locked) {
                var locked = document.createElement('a');
                locked.href = LOGIN_URL;
                locked.className = chipClass;
                locked.title = '로그인 후 이메일을 확인할 수 있습니다';
                locked.textContent = '🔒 로그인 후 이메일 확인';
                contact.appendChild(locked);
            }
            if (data.website) {
                var w = document.createElement('a');
                w.href = data.website;
                w.target = '_blank';
                w.rel = 'noopener noreferrer';
                w.className = chipClass;
                w.textContent = '🔗 웹사이트';
                contact.appendChild(w);
            }
            if (data.socials) {
                Object.keys(data.socials).forEach(function (key) {
                    var url = data.socials[key];
                    if (!url) return;
                    var s = document.createElement('a');
                    s.href = url;
                    s.target = '_blank';
                    s.rel = 'noopener noreferrer';
                    s.className = chipClass;
                    s.textContent = socialLabel(key);
                    contact.appendChild(s);
                });
            }

            showState('content');
        }

        function loadMember(id) {
            openModal();

            if (cache[id]) {
                render(cache[id]);
                return;
            }

            showState('loading');

            var fd = new FormData();
            fd.append('action', 'lp_get_team_member');
            fd.append('nonce', NONCE);
            fd.append('member_id', id);

            fetch(AJAX_URL, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res || !res.success) { showState('error'); return; }
                    cache[id] = res.data;
                    render(res.data);
                })
                .catch(function () { showState('error'); });
        }

        document.addEventListener('click', function (e) {
            var card = e.target.closest('.lp-team-card');
            if (card && card.dataset.memberId) {
                loadMember(card.dataset.memberId);
                return;
            }
            if (e.target.closest('[data-lp-team-close]')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var card = e.target.closest && e.target.closest('.lp-team-card');
                if (card && card.dataset.memberId) {
                    e.preventDefault();
                    loadMember(card.dataset.memberId);
                }
                return;
            }
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'larapress_print_team_modal' );