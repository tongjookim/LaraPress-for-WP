<?php
/**
 * LaraPress — 알림판 "테마 설정" 페이지
 *
 * 테마 설정은 이 페이지에서만 관리한다. 커스터마이저 화면에서는 테마 섹션을 숨긴다(아래 맨 끝).
 *
 * 항목 정의는 커스터마이저 등록 함수(larapress_customize_register 등)를 그대로 쓴다.
 * 그 정의로 매니저를 따로 만들어 섹션·설정·컨트롤을 읽고 탭형 폼을 만든다.
 * 라벨·기본값·sanitize/validate 콜백을 공유하므로 항목을 추가할 때는 등록 함수에만 넣으면 된다.
 * 값은 theme_mod 에 저장되어 템플릿이 그대로 읽는다.
 *
 * 파비콘(사이트 아이콘)은 워드프레스 기본 옵션(site_icon)이라 여기서 따로 다룬다.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const LP_SETTINGS_SLUG = 'larapress-theme-settings';

/** 사이트 아이콘(파비콘) 전용 가상 섹션 ID — 커스터마이저 섹션이 아니다 */
const LP_SETTINGS_SITE_ICON = 'lp_site_icon';

/**
 * 탭 구성: 탭 ID => [ 제목, 섹션 ID 목록(표시 순서) ]
 * 여기에 없는 테마 섹션은 자동으로 '기타' 탭에 모인다.
 */
function larapress_settings_tabs() {
    $tabs = [
        'general'      => [ 'title' => '일반·브랜딩', 'sections' => [ LP_SETTINGS_SITE_ICON, 'lp_header_section', 'lp_splash_section' ] ],
        'layout'       => [ 'title' => '레이아웃',    'sections' => [ 'larapress_layout_section', 'lp_homepage_section', 'lp_sidebar_section' ] ],
        'footer'       => [ 'title' => '언론사 정보', 'sections' => [ 'larapress_footer_section' ] ],
        'ads'          => [ 'title' => '배너 광고',   'sections' => [ 'lp_banner_section' ] ],
        'amber'        => [ 'title' => '엠버 저널',   'sections' => [ 'lp_aj_section' ] ],
        'integrations' => [ 'title' => '외부 연동',   'sections' => [ 'lp_kakao_section' ] ],
    ];
    // 라이선스 관리 탭 — inc/license.php 가 로드되어 있고 사이트 관리자일 때만 (섹션 대신 전용 화면을 그린다)
    if ( function_exists( 'lp_license_page' ) && current_user_can( 'manage_options' ) ) {
        $tabs['license'] = [ 'title' => '라이선스', 'sections' => [], 'render' => 'lp_license_page' ];
    }
    return $tabs;
}

/**
 * 테마 설정 항목을 등록한 커스터마이저 매니저.
 * 코어·플러그인 항목은 빼고 테마 등록 함수만 호출한다.
 */
function larapress_settings_manager() {
    static $manager = null;
    if ( $manager ) {
        return $manager;
    }

    require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';

    // 위젯·메뉴 컴포넌트는 필요 없으므로 불러오지 않는다 (훅 등록 같은 부수 효과 최소화)
    add_filter( 'customize_loaded_components', '__return_empty_array', 999 );
    $manager = new WP_Customize_Manager();
    remove_filter( 'customize_loaded_components', '__return_empty_array', 999 );

    larapress_customize_register( $manager );
    larapress_customize_register_splash( $manager );

    /** 자식 테마 등에서 테마 설정 항목을 더할 때 쓰는 훅 (커스터마이저에도 같이 등록할 것) */
    do_action( 'larapress_settings_register', $manager );

    return $manager;
}

/**
 * 섹션별 컨트롤 목록 (우선순위 순).
 * 패널 안의 섹션은 패널 제목을 쓴다 (홈 화면 레이아웃).
 *
 * @return array 섹션 ID => [ title, description, controls[] ]
 */
function larapress_settings_sections() {
    $manager  = larapress_settings_manager();
    $sections = [];

    foreach ( $manager->sections() as $id => $section ) {
        $panel = $section->panel ? $manager->get_panel( $section->panel ) : null;
        $sections[ $id ] = [
            'title'       => $panel ? $panel->title : $section->title,
            'description' => $panel ? $panel->description : $section->description,
            'controls'    => [],
        ];
    }

    foreach ( $manager->controls() as $control ) {
        if ( isset( $sections[ $control->section ] ) && $control->setting ) {
            $sections[ $control->section ]['controls'][] = $control;
        }
    }

    foreach ( $sections as $id => $data ) {
        usort( $sections[ $id ]['controls'], static function ( $a, $b ) {
            return $a->priority <=> $b->priority;
        } );
    }

    return $sections;
}

/** 탭 목록 + 어느 탭에도 없는 섹션을 모은 '기타' 탭 */
function larapress_settings_tabs_resolved() {
    $tabs   = larapress_settings_tabs();
    $mapped = [];
    foreach ( $tabs as $tab ) {
        $mapped = array_merge( $mapped, $tab['sections'] );
    }

    $extra = array_diff( array_keys( larapress_settings_sections() ), $mapped );
    if ( $extra ) {
        $tabs['etc'] = [ 'title' => '기타', 'sections' => array_values( $extra ) ];
    }

    // 엠버 저널 설정은 그 레이아웃을 쓸 때만 보인다. 위에서 '기타' 로 흘러가지 않도록 매핑을 끝낸 뒤에 뺀다.
    // 저장된 값은 지우지 않는다(레이아웃을 다시 엠버 저널로 바꾸면 그대로 나타난다).
    if ( 'amber-journal' !== get_theme_mod( 'larapress_layout_style', 'swn-style' ) ) {
        unset( $tabs['amber'] );
    }
    return $tabs;
}

/** 현재 탭 ID */
function larapress_settings_current_tab() {
    $tabs = larapress_settings_tabs_resolved();
    $tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification
    return isset( $tabs[ $tab ] ) ? $tab : 'general';
}

/* ── 메뉴 · 스크립트 ──────────────────────────────────────── */

function larapress_admin_menu() {
    add_menu_page(
        '테마 설정',
        '테마 설정',
        'edit_theme_options',
        LP_SETTINGS_SLUG,
        'larapress_render_theme_settings_page',
        'dashicons-admin-customizer',
        61 // '모양(Appearance)' 메뉴(60) 바로 아래
    );
}
add_action( 'admin_menu', 'larapress_admin_menu' );

function larapress_settings_enqueue( $hook ) {
    if ( 'toplevel_page_' . LP_SETTINGS_SLUG !== $hook ) {
        return;
    }

    $dir  = get_template_directory_uri() . '/assets';
    $path = get_template_directory() . '/assets';

    wp_enqueue_media();
    // 버전에 파일 수정 시각을 붙여, 고친 뒤 브라우저·CDN 캐시에 예전 파일이 남지 않게 한다
    wp_enqueue_style( 'lp-theme-settings', $dir . '/css/theme-settings.css', [], (string) filemtime( $path . '/css/theme-settings.css' ) );
    wp_enqueue_script( 'lp-theme-settings', $dir . '/js/theme-settings.js', [ 'jquery' ], (string) filemtime( $path . '/js/theme-settings.js' ), true );

    // 홈 화면 위젯 빌더 — 커스터마이저와 같은 JS/CSS 를 쓴다 (저장소만 숨은 입력란으로 바뀜)
    if ( 'layout' === larapress_settings_current_tab() ) {
        larapress_settings_manager(); // 컨트롤 클래스 로드
        wp_enqueue_script( 'lp-customizer-widgets', $dir . '/js/customizer-widgets.js', [ 'jquery', 'jquery-ui-sortable' ], '1.9.0', true );
        wp_enqueue_style( 'lp-customizer-widgets', $dir . '/css/customizer-widgets.css', [], '1.3.0' );
        wp_localize_script( 'lp-customizer-widgets', 'lpWidgetData', [ 'categories' => LP_Home_Widgets_Control::categories() ] );
    }
}
add_action( 'admin_enqueue_scripts', 'larapress_settings_enqueue' );

/* ── 입력 필드 ────────────────────────────────────────────── */

/** 이미지 URL 선택 필드 (로고 등 — WP_Customize_Image_Control 대응) */
function larapress_settings_image_field( $name, $field_id, $value ) {
    ?>
    <div class="lp-media" data-lp-media="url">
        <div class="lp-media__preview"<?php echo $value ? '' : ' hidden'; ?>>
            <img src="<?php echo esc_url( $value ); ?>" alt="">
        </div>
        <input type="url" class="regular-text lp-media__value" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="https://">
        <p>
            <button type="button" class="button lp-media__select">이미지 선택</button>
            <button type="button" class="button-link lp-media__remove"<?php echo $value ? '' : ' hidden'; ?>>제거</button>
        </p>
    </div>
    <?php
}

/** 선택지 하나가 잠겼는지 (라이선스 등에서 larapress_settings_choice_locked 필터로 지정) */
function larapress_settings_choice_locked( $id, $choice ) {
    return (bool) apply_filters( 'larapress_settings_choice_locked', false, $id, (string) $choice );
}

/** 항목 전체가 잠겼는지 (larapress_settings_control_locked 필터) */
function larapress_settings_control_locked( $id ) {
    return (bool) apply_filters( 'larapress_settings_control_locked', false, $id );
}

/** 컨트롤 하나를 폼 행으로 출력 */
function larapress_settings_render_control( WP_Customize_Control $control ) {
    $setting  = $control->setting;
    $id       = $setting->id;
    $value    = get_theme_mod( $id, $setting->default );
    $name     = 'lp_settings[' . $id . ']';
    $field_id = 'lp-field-' . $id;
    $type     = $control->type;
    $desc     = $control->description ? '<p class="description">' . esc_html( $control->description ) . '</p>' : '';
    $attrs    = '';
    foreach ( (array) $control->input_attrs as $attr => $attr_value ) {
        $attrs .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
    }
    ?>
    <tr data-lp-setting="<?php echo esc_attr( $id ); ?>">
        <th scope="row">
            <?php if ( 'checkbox' !== $type ) : ?>
                <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $control->label ); ?></label>
            <?php endif; ?>
        </th>
        <td>
            <?php
            if ( $control instanceof LP_Home_Widgets_Control ) :
                // 커스터마이저와 같은 빌더 마크업 — assets/js/customizer-widgets.js 가 숨은 입력란에 JSON 을 기록한다
                ?>
                <div class="lp-builder-wrap">
                    <div id="lp-layout-builder">
                        <div id="lp-views-wrapper">
                            <div id="lp-main-view" class="lp-view">
                                <div id="lp-main-list"></div>
                                <button type="button" id="lp-add-item" class="button button-primary">＋ 추가하기</button>
                                <div id="lp-item-picker">
                                    <button type="button" class="button" data-type="list">📋 목록형 위젯</button>
                                    <button type="button" class="button" data-type="gallery">🖼 갤러리형 위젯</button>
                                    <button type="button" class="button" data-type="section">🗂 섹션 그룹 (가로 배치)</button>
                                </div>
                            </div>
                            <div id="lp-section-view" class="lp-view">
                                <div class="lp-view-header">
                                    <button type="button" id="lp-back-btn">
                                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                                        <span id="lp-section-view-title">섹션 그룹</span>
                                    </button>
                                </div>
                                <div id="lp-section-detail"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                <?php

            elseif ( $control instanceof LP_Archive_Style_Control ) :
                ?>
                <div class="lp-asp-picker">
                    <?php foreach ( LP_Archive_Style_Control::choices() as $choice => $info ) : ?>
                        <label class="lp-asp-label">
                            <input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $choice ); ?>" <?php checked( $value, $choice ); ?> <?php disabled( larapress_settings_choice_locked( $id, $choice ) ); ?>>
                            <span class="lp-asp-preview"><?php echo LP_Archive_Style_Control::get_preview_svg( $choice ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
                            <span class="lp-asp-label-text"><?php echo esc_html( $info['label'] ); ?></span>
                            <span class="lp-asp-label-desc"><?php echo esc_html( $info['desc'] ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php

            elseif ( $control instanceof WP_Customize_Image_Control ) :
                larapress_settings_image_field( $name, $field_id, (string) $value );

            elseif ( 'checkbox' === $type ) :
                ?>
                <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0">
                <label for="<?php echo esc_attr( $field_id ); ?>">
                    <input type="checkbox" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( (string) $value, '1' ); ?>>
                    <?php echo esc_html( $control->label ); ?>
                </label>
                <?php

            elseif ( 'radio' === $type ) :
                ?>
                <fieldset id="<?php echo esc_attr( $field_id ); ?>">
                    <?php foreach ( $control->choices as $choice => $label ) : ?>
                        <label class="lp-radio">
                            <input type="radio" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $choice ); ?>" <?php checked( (string) $value, (string) $choice ); ?> <?php disabled( larapress_settings_choice_locked( $id, $choice ) ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php

            elseif ( 'select' === $type ) :
                ?>
                <select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>">
                    <?php foreach ( $control->choices as $choice => $label ) : ?>
                        <option value="<?php echo esc_attr( $choice ); ?>" <?php selected( (string) $value, (string) $choice ); ?> <?php disabled( larapress_settings_choice_locked( $id, $choice ) ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php

            elseif ( 'textarea' === $type ) :
                ?>
                <textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="large-text code" rows="5"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php disabled( larapress_settings_control_locked( $id ) ); ?>><?php echo esc_textarea( (string) $value ); ?></textarea>
                <?php

            else : // text · url · number · email 등
                $input_type = in_array( $type, [ 'url', 'number', 'email', 'tel' ], true ) ? $type : 'text';
                $class      = 'number' === $input_type ? 'small-text' : 'regular-text';
                ?>
                <input type="<?php echo esc_attr( $input_type ); ?>" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>" class="<?php echo esc_attr( $class ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
                <?php
            endif;

            echo $desc; // phpcs:ignore WordPress.Security.EscapeOutput — esc_html 처리됨
            ?>
        </td>
    </tr>
    <?php
}

/** 사이트 아이콘(파비콘) 섹션 */
function larapress_settings_render_site_icon() {
    $icon_id  = (int) get_option( 'site_icon' );
    $icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, [ 128, 128 ] ) : '';
    $can_edit = current_user_can( 'manage_options' );
    ?>
    <div class="lp-card">
        <h2>파비콘 (사이트 아이콘)</h2>
        <p class="lp-card__desc">브라우저 탭·북마크·홈 화면 아이콘으로 쓰입니다. 512×512px 이상의 정사각형 PNG 를 권장합니다. 스플래시 화면도 이 아이콘을 사용합니다.</p>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">사이트 아이콘</th>
                <td>
                    <?php if ( $can_edit ) : ?>
                        <div class="lp-media lp-media--icon" data-lp-media="id">
                            <div class="lp-media__preview"<?php echo $icon_url ? '' : ' hidden'; ?>>
                                <img src="<?php echo esc_url( $icon_url ); ?>" alt="">
                            </div>
                            <input type="hidden" class="lp-media__value" name="lp_site_icon" value="<?php echo esc_attr( $icon_id ); ?>">
                            <p>
                                <button type="button" class="button lp-media__select">아이콘 선택</button>
                                <button type="button" class="button-link lp-media__remove"<?php echo $icon_id ? '' : ' hidden'; ?>>제거</button>
                            </p>
                        </div>
                    <?php else : ?>
                        <?php if ( $icon_url ) : ?><img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="64" height="64"><?php endif; ?>
                        <p class="description">사이트 아이콘은 사이트 관리자(manage_options)만 바꿀 수 있습니다.</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

/* ── 페이지 ───────────────────────────────────────────────── */

function larapress_render_theme_settings_page() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }

    $tabs     = larapress_settings_tabs_resolved();
    $current  = larapress_settings_current_tab();
    $sections = larapress_settings_sections();
    $notice   = get_transient( 'lp_settings_notice_' . get_current_user_id() );
    if ( $notice ) {
        delete_transient( 'lp_settings_notice_' . get_current_user_id() );
    }
    ?>
    <div class="wrap larapress-theme-settings">
        <h1 class="wp-heading-inline">테마 설정</h1>
        <hr class="wp-header-end">

        <?php if ( $notice ) : ?>
            <div class="notice notice-success is-dismissible"><p>설정을 저장했습니다.</p></div>
            <?php foreach ( $notice['errors'] as $error ) : ?>
                <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <nav class="nav-tab-wrapper">
            <?php foreach ( $tabs as $tab_id => $tab ) : ?>
                <a href="<?php echo esc_url( add_query_arg( [ 'page' => LP_SETTINGS_SLUG, 'tab' => $tab_id ], admin_url( 'admin.php' ) ) ); ?>"
                   class="nav-tab<?php echo $tab_id === $current ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $tab['title'] ); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ( ! empty( $tabs[ $current ]['render'] ) && is_callable( $tabs[ $current ]['render'] ) ) : // 전용 화면 탭(라이선스) ?>
            <?php call_user_func( $tabs[ $current ]['render'] ); ?>
        <?php else : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lp-settings-form">
            <input type="hidden" name="action" value="larapress_save_settings">
            <input type="hidden" name="tab" value="<?php echo esc_attr( $current ); ?>">
            <?php wp_nonce_field( 'larapress_save_settings' ); ?>

            <?php
            foreach ( $tabs[ $current ]['sections'] as $section_id ) :
                if ( LP_SETTINGS_SITE_ICON === $section_id ) {
                    larapress_settings_render_site_icon();
                    continue;
                }
                if ( empty( $sections[ $section_id ]['controls'] ) ) {
                    continue;
                }
                $section = $sections[ $section_id ];
                ?>
                <div class="lp-card">
                    <h2><?php echo esc_html( $section['title'] ); ?></h2>
                    <?php if ( $section['description'] ) : ?>
                        <p class="lp-card__desc"><?php echo esc_html( $section['description'] ); ?></p>
                    <?php endif; ?>
                    <table class="form-table" role="presentation">
                        <?php
                        foreach ( $section['controls'] as $control ) {
                            larapress_settings_render_control( $control );
                        }
                        ?>
                    </table>
                </div>
            <?php endforeach; ?>

            <?php submit_button( '설정 저장' ); ?>
        </form>
        <?php endif; ?>
    </div>
    <?php
}

/* ── 저장 ─────────────────────────────────────────────────── */

/**
 * 폼 저장. 커스터마이저와 같은 validate → sanitize 를 거쳐 theme_mod 에 기록한다.
 * 현재 탭에서 넘어온 항목만 바뀐다 (체크박스는 숨은 입력란으로 해제 값 '0' 이 넘어온다).
 */
function larapress_save_settings() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( '테마 설정을 바꿀 권한이 없습니다.', '', [ 'response' => 403 ] );
    }
    check_admin_referer( 'larapress_save_settings' );

    $tab     = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'general';
    $input   = ( isset( $_POST['lp_settings'] ) && is_array( $_POST['lp_settings'] ) ) ? wp_unslash( $_POST['lp_settings'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput — 아래에서 설정별 sanitize
    $manager = larapress_settings_manager();
    $errors  = [];

    // 테마 섹션에 속한 설정만 받는다
    $labels = [];
    foreach ( larapress_settings_sections() as $section ) {
        foreach ( $section['controls'] as $control ) {
            $labels[ $control->setting->id ] = $control->label;
        }
    }

    foreach ( $input as $id => $raw ) {
        $setting = isset( $labels[ $id ] ) ? $manager->get_setting( $id ) : null;
        if ( ! $setting || 'theme_mod' !== $setting->type || ! $setting->check_capabilities() ) {
            continue;
        }

        $validity = $setting->validate( $raw );
        if ( is_wp_error( $validity ) ) {
            $errors[] = $labels[ $id ] . ': ' . $validity->get_error_message();
            continue;
        }

        $value = $setting->sanitize( $raw );
        if ( null === $value || is_wp_error( $value ) ) {
            $errors[] = $labels[ $id ] . ': 올바른 값이 아닙니다.';
            continue;
        }

        set_theme_mod( $id, $value );
    }

    // 사이트 아이콘 (워드프레스 기본 옵션)
    if ( isset( $_POST['lp_site_icon'] ) && current_user_can( 'manage_options' ) ) {
        $icon_id = absint( $_POST['lp_site_icon'] );
        if ( ! $icon_id ) {
            delete_option( 'site_icon' );
        } elseif ( wp_attachment_is_image( $icon_id ) ) {
            update_option( 'site_icon', $icon_id );
        } else {
            $errors[] = '사이트 아이콘: 이미지 파일을 선택해 주세요.';
        }
    }

    set_transient( 'lp_settings_notice_' . get_current_user_id(), [ 'errors' => $errors ], MINUTE_IN_SECONDS );

    wp_safe_redirect( add_query_arg( [ 'page' => LP_SETTINGS_SLUG, 'tab' => $tab ], admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_larapress_save_settings', 'larapress_save_settings' );

/* ── 커스터마이저에서 테마 섹션 숨기기 ─────────────────────── */

/**
 * 테마 설정은 이 페이지에서만 관리하므로 커스터마이저 화면에서 테마 섹션·패널을 뺀다.
 * 테마 섹션 ID 는 모두 lp_ / larapress_ 로 시작한다 — 새 섹션도 이 규칙을 따르면 자동으로 빠진다.
 *
 * 설정(setting) 등록은 남겨 둔다. 이 페이지는 커스터마이저 훅이 아니라 등록 함수를 직접 호출해
 * 정의를 읽으므로 영향이 없고, 다른 코드가 커스터마이저 설정을 참조해도 깨지지 않는다.
 * 워드프레스 기본 섹션(사이트 아이덴티티·메뉴·위젯·추가 CSS 등)은 그대로 둔다.
 */
function larapress_hide_customizer_sections( $wp_customize ) {
    $is_theme_id = static function ( $id ) {
        return 0 === strpos( $id, 'lp_' ) || 0 === strpos( $id, 'larapress_' );
    };

    foreach ( array_keys( $wp_customize->sections() ) as $id ) {
        if ( $is_theme_id( $id ) ) {
            $wp_customize->remove_section( $id );
        }
    }
    foreach ( array_keys( $wp_customize->panels() ) as $id ) {
        if ( $is_theme_id( $id ) ) {
            $wp_customize->remove_panel( $id );
        }
    }
}
add_action( 'customize_register', 'larapress_hide_customizer_sections', 999 ); // 테마 등록(우선순위 10) 뒤
