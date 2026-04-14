<?php
/**
 * LaraPress Theme Functions
 */

function larapress_theme_setup() {
    // 테마 기본 지원 기능
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
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
}
add_action('wp_enqueue_scripts', 'larapress_enqueue_scripts');

// ─────────────────────────────────────────────────────────────
// 일반 WP post 조회수 추적 — lara_post_views 메타 (플러그인과 동일 키)
// lara_post CPT는 플러그인(LaraPress_Board_System)이 직접 처리하므로 여기서는 제외.
// ─────────────────────────────────────────────────────────────
add_action('wp', function () {
    if ( ! is_singular( 'post' ) ) return;          // 일반 포스트 단건 페이지만
    if ( current_user_can( 'edit_posts' ) ) return; // 편집자 이상은 카운트 제외

    global $post;
    if ( ! $post ) return;

    $views = (int) get_post_meta( $post->ID, 'lara_post_views', true );
    update_post_meta( $post->ID, 'lara_post_views', $views + 1 );
} );

/**
 * 홈 화면 위젯 빌더 — 커스터마이저 커스텀 컨트롤
 * 위젯 목록을 JSON으로 저장하고 드래그·추가·삭제 UI를 제공.
 */
if ( class_exists( 'WP_Customize_Control' ) ) :
class LP_Home_Widgets_Control extends WP_Customize_Control {

    public $type = 'lp_home_widgets';

    /** 컨트롤 전용 JS/CSS 인큐 */
    public function enqueue() {
        wp_enqueue_script(
            'lp-customizer-widgets',
            get_template_directory_uri() . '/assets/js/customizer-widgets.js',
            [ 'jquery', 'jquery-ui-sortable', 'customize-controls' ],
            '1.9.0',
            true
        );
        wp_enqueue_style(
            'lp-customizer-widgets',
            get_template_directory_uri() . '/assets/css/customizer-widgets.css',
            [],
            '1.3.0'
        );

        // 카테고리 목록을 JS에 전달 (미분류 제외)
        $raw_cats = get_categories( [
            'hide_empty' => false,
            'exclude'    => get_option( 'default_category' ),
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );
        $cats = [ [ 'id' => '0', 'name' => '전체 (모든 카테고리)' ] ];
        foreach ( $raw_cats as $c ) {
            $cats[] = [ 'id' => (string) $c->term_id, 'name' => esc_js( $c->name ) ];
        }
        wp_localize_script( 'lp-customizer-widgets', 'lpWidgetData', [ 'categories' => $cats ] );
    }

    /** 컨트롤 HTML 출력 */
    public function render_content() {
        ?>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <div id="lp-layout-builder">
            <div id="lp-views-wrapper">

                <!-- ── View 1: 메인 목록 ── -->
                <div id="lp-main-view" class="lp-view">
                    <div id="lp-main-list"></div>
                    <button type="button" id="lp-add-item" class="button button-primary">＋ 추가하기</button>
                    <div id="lp-item-picker">
                        <button type="button" class="button" data-type="list">📋 목록형 위젯</button>
                        <button type="button" class="button" data-type="gallery">🖼 갤러리형 위젯</button>
                        <button type="button" class="button" data-type="section">🗂 섹션 그룹 (가로 배치)</button>
                    </div>
                </div>

                <!-- ── View 2: 섹션 상세 ── -->
                <div id="lp-section-view" class="lp-view">
                    <div class="lp-view-header">
                        <button type="button" id="lp-back-btn">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                            <span id="lp-section-view-title">섹션 그룹</span>
                        </button>
                    </div>
                    <div id="lp-section-detail"></div>
                </div>

            </div><!-- #lp-views-wrapper -->
        </div><!-- #lp-layout-builder -->
        <input type="hidden" id="<?php echo esc_attr( $this->id ); ?>"
               <?php $this->link(); ?>>
        <?php
    }
}
endif; // class_exists WP_Customize_Control (LP_Home_Widgets_Control)

/**
 * 아카이브 레이아웃 선택 — Radio-Image 커스텀 컨트롤
 * SVG 미리보기 + 라벨로 리스트/그리드2/그리드3/웹진형 중 하나를 선택.
 */
if ( class_exists( 'WP_Customize_Control' ) ) :
class LP_Archive_Style_Control extends WP_Customize_Control {

    public $type = 'lp_archive_style';

    /** 커스터마이저 패널에만 적용되는 전용 CSS 인큐 */
    public function enqueue() {
        wp_add_inline_style( 'customize-controls', '
            .lp-asp-picker {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                margin-top: 8px;
            }
            .lp-asp-label {
                cursor: pointer;
                text-align: center;
                position: relative;
            }
            .lp-asp-label input[type="radio"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }
            .lp-asp-preview {
                border: 2px solid #ddd;
                border-radius: 6px;
                padding: 8px 6px 6px;
                background: #fafafa;
                transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
                display: block;
            }
            .lp-asp-preview svg { width: 100%; height: auto; display: block; }
            .lp-asp-label:hover .lp-asp-preview {
                border-color: #2271b1;
                background: #f0f6fc;
            }
            .lp-asp-label input:checked ~ .lp-asp-preview {
                border-color: #2271b1;
                box-shadow: 0 0 0 2px #bcd1e8;
                background: #f0f6fc;
            }
            .lp-asp-label-text {
                display: block;
                font-size: 11px;
                font-weight: 600;
                color: #1d2327;
                margin-top: 5px;
                line-height: 1.3;
            }
            .lp-asp-label-desc {
                display: block;
                font-size: 10px;
                color: #646970;
                margin-top: 1px;
            }
            .lp-asp-label input:checked ~ .lp-asp-preview + .lp-asp-label-text {
                color: #2271b1;
            }
        ' );
    }

    /** 컨트롤 HTML 출력 */
    public function render_content() {
        $choices = [
            'list'    => [ 'label' => '리스트형',   'desc' => '정통 뉴스 스타일' ],
            'grid2'   => [ 'label' => '2열 그리드',  'desc' => '매거진 스타일' ],
            'grid3'   => [ 'label' => '3열 그리드',  'desc' => '매거진 스타일' ],
            'webzine' => [ 'label' => '웹진형',      'desc' => '큰 썸네일 강조' ],
        ];
        ?>
        <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
        <?php if ( $this->description ) : ?>
        <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        <?php endif; ?>
        <div class="lp-asp-picker">
            <?php foreach ( $choices as $value => $info ) : ?>
            <label class="lp-asp-label">
                <input type="radio"
                       name="_customize-radio-<?php echo esc_attr( $this->id ); ?>"
                       value="<?php echo esc_attr( $value ); ?>"
                       <?php $this->link(); ?>
                       <?php checked( $this->value(), $value ); ?>>
                <div class="lp-asp-preview">
                    <?php echo $this->get_preview_svg( $value ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <span class="lp-asp-label-text"><?php echo esc_html( $info['label'] ); ?></span>
                <span class="lp-asp-label-desc"><?php echo esc_html( $info['desc'] ); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /** 레이아웃 유형별 SVG 미리보기 생성 */
    private function get_preview_svg( $type ) {
        switch ( $type ) {
            case 'list':
                return '
                <svg viewBox="0 0 80 72" xmlns="http://www.w3.org/2000/svg">
                  <rect x="4" y="6"  width="18" height="14" rx="2" fill="#cbd5e1"/>
                  <rect x="26" y="7"  width="50" height="3" rx="1" fill="#64748b"/>
                  <rect x="26" y="12" width="38" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="26" y="16" width="44" height="2" rx="1" fill="#e2e8f0"/>
                  <line x1="4" y1="25" x2="76" y2="25" stroke="#e2e8f0" stroke-width="1"/>
                  <rect x="4" y="29" width="18" height="14" rx="2" fill="#cbd5e1"/>
                  <rect x="26" y="30" width="50" height="3" rx="1" fill="#64748b"/>
                  <rect x="26" y="35" width="38" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="26" y="39" width="44" height="2" rx="1" fill="#e2e8f0"/>
                  <line x1="4" y1="48" x2="76" y2="48" stroke="#e2e8f0" stroke-width="1"/>
                  <rect x="4" y="52" width="18" height="14" rx="2" fill="#cbd5e1"/>
                  <rect x="26" y="53" width="50" height="3" rx="1" fill="#64748b"/>
                  <rect x="26" y="58" width="38" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="26" y="62" width="44" height="2" rx="1" fill="#e2e8f0"/>
                </svg>';

            case 'grid2':
                return '
                <svg viewBox="0 0 80 72" xmlns="http://www.w3.org/2000/svg">
                  <rect x="4"  y="4" width="34" height="22" rx="2" fill="#cbd5e1"/>
                  <rect x="42" y="4" width="34" height="22" rx="2" fill="#cbd5e1"/>
                  <rect x="4"  y="29" width="34" height="3" rx="1" fill="#64748b"/>
                  <rect x="42" y="29" width="34" height="3" rx="1" fill="#64748b"/>
                  <rect x="4"  y="34" width="26" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="42" y="34" width="26" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="4"  y="38" width="30" height="2" rx="1" fill="#e2e8f0"/>
                  <rect x="42" y="38" width="30" height="2" rx="1" fill="#e2e8f0"/>
                  <rect x="4"  y="46" width="34" height="18" rx="2" fill="#e2e8f0"/>
                  <rect x="42" y="46" width="34" height="18" rx="2" fill="#e2e8f0"/>
                </svg>';

            case 'grid3':
                return '
                <svg viewBox="0 0 80 72" xmlns="http://www.w3.org/2000/svg">
                  <rect x="2"  y="4" width="22" height="18" rx="2" fill="#cbd5e1"/>
                  <rect x="29" y="4" width="22" height="18" rx="2" fill="#cbd5e1"/>
                  <rect x="56" y="4" width="22" height="18" rx="2" fill="#cbd5e1"/>
                  <rect x="2"  y="24" width="22" height="3" rx="1" fill="#64748b"/>
                  <rect x="29" y="24" width="22" height="3" rx="1" fill="#64748b"/>
                  <rect x="56" y="24" width="22" height="3" rx="1" fill="#64748b"/>
                  <rect x="2"  y="29" width="17" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="29" y="29" width="17" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="56" y="29" width="17" height="2" rx="1" fill="#cbd5e1"/>
                  <rect x="2"  y="38" width="22" height="16" rx="2" fill="#e2e8f0"/>
                  <rect x="29" y="38" width="22" height="16" rx="2" fill="#e2e8f0"/>
                  <rect x="56" y="38" width="22" height="16" rx="2" fill="#e2e8f0"/>
                  <rect x="2"  y="56" width="22" height="3" rx="1" fill="#94a3b8"/>
                  <rect x="29" y="56" width="22" height="3" rx="1" fill="#94a3b8"/>
                  <rect x="56" y="56" width="22" height="3" rx="1" fill="#94a3b8"/>
                </svg>';

            case 'webzine':
                return '
                <svg viewBox="0 0 80 72" xmlns="http://www.w3.org/2000/svg">
                  <rect x="4" y="4"  width="72" height="36" rx="3" fill="#cbd5e1"/>
                  <rect x="8" y="7"  width="22" height="5"  rx="2" fill="#3b82f6" opacity="0.9"/>
                  <rect x="4" y="44" width="65" height="5"  rx="1" fill="#1e293b"/>
                  <rect x="4" y="52" width="72" height="3"  rx="1" fill="#94a3b8"/>
                  <rect x="4" y="57" width="55" height="3"  rx="1" fill="#cbd5e1"/>
                  <rect x="4" y="63" width="36" height="2"  rx="1" fill="#e2e8f0"/>
                </svg>';
        }
        return '';
    }
}
endif; // class_exists WP_Customize_Control (LP_Archive_Style_Control)

/**
 * 테마 사용자 정의하기(Customizer)에 한국형 언론사 설정 폼 추가
 */
function larapress_customize_register($wp_customize) {

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
        'description' => '푸터 좌측 상단에 표시되는 언론사 이름',
        'section'     => 'larapress_footer_section',
        'type'        => 'text',
        'priority'    => -4,
    ] );

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
        'default'           => '김동주',
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
        'default'           => '김동주',
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
        'default'           => '김동주',
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
        'lp_youth_officer'  => ['label' => '청소년보호책임자',   'default' => '김동주',                   'priority' => 5],
        'lp_privacy_officer'=> ['label' => '개인정보보호책임자', 'default' => '김동주',                   'priority' => 6],
        'lp_reg_num'        => ['label' => '정기간행물등록번호', 'default' => '강원 아00000',             'priority' => 7],
        'lp_reg_date'       => ['label' => '등록일자',           'default' => '2025.06.25',              'priority' => 8],
        'lp_est_date'       => ['label' => '창간일자',           'default' => '2015.03.27',              'priority' => 9],
        'lp_address'        => ['label' => '주소',               'default' => '강원특별자치도 강릉시 (예시)', 'priority' => 10],
        'lp_phone'          => ['label' => '대표전화',           'default' => '1588-0000',               'priority' => 11],
        'lp_email'          => ['label' => '이메일',             'default' => 'support@larapress.io',    'priority' => 12],
        'lp_copyright'      => ['label' => '카피라이트',         'default' => 'Copyright © '.date('Y').' 수완뉴스 & LaraPress. All rights reserved.', 'priority' => 13],
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
}
add_action('customize_register', 'larapress_customize_register');

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
 * 엠버 저널 — 많이 본 기사 위젯 HTML 반환
 * 해커뉴스 랭킹 공식: score = (views - 1) / (hours + 2)^1.8
 * 10분 Transient 캐시 사용
 *
 * @param string $link_class  순위 번호 강조 클래스
 * @return string
 */
function lp_aj_hot_news_html() {
    $cache_key = 'lp_aj_hot_v1';
    $list      = get_transient( $cache_key );

    if ( $list === false ) {
        $gravity = 1.8;
        $now     = time();
        $pool    = new WP_Query( [
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => 200,
            'date_query'             => [ [ 'after' => '90 days ago' ] ],
            'no_found_rows'          => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ] );

        $scored = [];
        foreach ( $pool->posts as $p ) {
            $views          = max( 1, (int) get_post_meta( $p->ID, 'lara_post_views', true ) );
            $hours          = max( 0, ( $now - strtotime( $p->post_date_gmt ) ) / 3600 );
            $scored[ $p->ID ] = ( $views - 1 ) / pow( $hours + 2, $gravity );
        }
        wp_reset_postdata();
        arsort( $scored );

        $list = [];
        foreach ( array_slice( array_keys( $scored ), 0, 5, true ) as $id ) {
            $list[] = [
                'title' => get_the_title( $id ),
                'url'   => get_permalink( $id ),
                'views' => number_format( (int) get_post_meta( $id, 'lara_post_views', true ) ),
            ];
        }
        set_transient( $cache_key, $list, 10 * MINUTE_IN_SECONDS );
    }

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

    /* ── Molongui Authorship ── */
    if ( function_exists( 'molongui_get_post_authors' ) ) {
        $ma_list = molongui_get_post_authors( $post_id );
        foreach ( (array) $ma_list as $ma ) {
            $uid  = ! empty( $ma->ID ) ? (int) $ma->ID : 0;
            $bio  = ! empty( $ma->description )
                ? $ma->description
                : ( $uid ? (string) get_the_author_meta( 'description', $uid ) : '' );
            $url  = $uid
                ? get_author_posts_url( $uid )
                : ( ! empty( $ma->link ) ? $ma->link : '' );
            $name = ! empty( $ma->display_name ) ? $ma->display_name : ( ! empty( $ma->name ) ? $ma->name : '' );
            if ( ! empty( $ma->avatar ) ) {
                $av_html = '<img src="' . esc_url( $ma->avatar ) . '" width="' . (int) $avatar_size . '" height="' . (int) $avatar_size . '"'
                         . ( $avatar_class ? ' class="' . esc_attr( $avatar_class ) . '"' : '' ) . ' alt="' . esc_attr( $name ) . '" loading="lazy">';
            } else {
                $av_html = get_avatar( $uid, $avatar_size, '', esc_attr( $name ), $av_attr );
            }
            $authors[] = [
                'id'          => $uid,
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
        $body_class      = 'bg-white text-slate-900 font-sans';
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

    ob_start();

    if ( $logo_type === 'image' && $logo_img ) {
        // ── 이미지 로고 ──────────────────────────────────────
        echo '<a href="' . $home . '" class="lp-logo-wrap" aria-label="' . esc_attr( $site_name ) . '">';
        echo '<img src="' . esc_url( $logo_img ) . '"'
           . ' alt="' . esc_attr( $site_name ) . '"'
           . ' style="max-width:' . $logo_w . 'px;height:auto;display:block;"'
           . ' loading="eager">';
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
 * 헤더 태그라인 반환 — 커스터마이저 값 우선, 없으면 WP 태그라인
 */
function lp_get_tagline() {
    $tag = get_theme_mod( 'lp_site_tagline', '' );
    return $tag !== '' ? $tag : get_bloginfo( 'description' );
}

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
 * 비로그인 사용자: 카운트 증가만, 중복 방지는 클라이언트 localStorage
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

    $active = true;

    if ( is_user_logged_in() ) {
        $user_id        = get_current_user_id();
        $user_reactions = get_user_meta( $user_id, 'lp_user_reactions', true );
        if ( ! is_array( $user_reactions ) ) $user_reactions = [];

        $post_key            = 'p' . $post_id;
        $user_post_reactions = $user_reactions[ $post_key ] ?? [];

        $prev_type = null;

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
        // 비로그인: 증가만
        $reactions[ $type ]++;
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