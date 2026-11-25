<?php
/**
 * LaraPress 라이선스 — nest(www.swnest.net) License Server Manager 연동
 *
 * - 알림판 > 테마 설정 > 라이선스 탭에서 키를 입력하면 /activate → /verify 순서로 인증합니다.
 * - 인증 결과는 옵션(lp_license_state)에 저장하고, 하루 한 번 wp-cron 으로 다시 검증합니다.
 *   방문자 페이지에서는 원격 호출을 하지 않습니다.
 * - 라이선스 서버에 일시적으로 연결할 수 없으면 마지막 성공 후 LP_LICENSE_GRACE_DAYS 일 동안 PRO 를 유지합니다.
 * - PRO 전용 기능은 lp_pro_features() 한 곳에서 관리합니다.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'LP_LICENSE_SERVER' ) )     define( 'LP_LICENSE_SERVER', 'https://www.swnest.net/wp-json/license-manager/v1' );
if ( ! defined( 'LP_LICENSE_GRACE_DAYS' ) ) define( 'LP_LICENSE_GRACE_DAYS', 7 );

/* ─────────────────────────────────────────────────────────────
 * PRO 기능 목록 — 여기만 고치면 잠금 대상이 바뀝니다.
 * ───────────────────────────────────────────────────────────── */
function lp_pro_features() {
    return apply_filters( 'lp_pro_features', [
        // 프리미엄 스킨 (무료: swn-style 프레시, newyorktimes-style 클래식)
        'skins'          => [ 'basic', 'amber-journal', 'ailab' ],
        // 카테고리 아카이브 레이아웃 (무료: list)
        'archive_styles' => [ 'grid2', 'grid3', 'webzine' ],
        // 배너 광고 슬롯
        'banners'        => [ 'lp_banner_top', 'lp_banner_side' ],
        // 홈 위젯 빌더: 섹션 그룹·갤러리 위젯
        'home_builder'   => true,
    ] );
}

/* ─────────────────────────────────────────────────────────────
 * 상태 조회
 * ───────────────────────────────────────────────────────────── */
function lp_license_domain() {
    $host = wp_parse_url( home_url(), PHP_URL_HOST );
    return strtolower( preg_replace( '/^www\./i', '', (string) $host ) );
}

function lp_license_state() {
    $s = get_option( 'lp_license_state', [] );
    return is_array( $s ) ? $s : [];
}

/** PRO 사용 가능 여부 (원격 호출 없음) */
function lp_is_pro() {
    static $pro = null;
    if ( $pro !== null ) return $pro;

    $s   = lp_license_state();
    $pro = false;
    if ( get_option( 'lp_license_key' ) && ! empty( $s['last_valid_at'] ) && ( $s['domain'] ?? '' ) === lp_license_domain() ) {
        $expired = ! empty( $s['expires'] ) && strtotime( $s['expires'] . ' 23:59:59' ) < time();
        if ( ! $expired ) {
            if ( ( $s['status'] ?? '' ) === 'valid' ) {
                $pro = true;
            } elseif ( ( $s['status'] ?? '' ) === 'unreachable' ) {
                $pro = ( time() - (int) $s['last_valid_at'] ) < LP_LICENSE_GRACE_DAYS * DAY_IN_SECONDS;
            }
        }
    }
    $pro = (bool) apply_filters( 'lp_is_pro', $pro );
    return $pro;
}

/* ─────────────────────────────────────────────────────────────
 * 라이선스 서버 호출
 * ───────────────────────────────────────────────────────────── */
function lp_license_request( $endpoint, $key ) {
    $res = wp_remote_post( LP_LICENSE_SERVER . '/' . $endpoint, [
        'timeout' => 15,
        'body'    => [ 'license_key' => $key, 'domain' => lp_license_domain() ],
    ] );
    if ( is_wp_error( $res ) ) {
        return [ 'ok' => false, 'reachable' => false, 'message' => $res->get_error_message() ];
    }
    $code = (int) wp_remote_retrieve_response_code( $res );
    $body = json_decode( wp_remote_retrieve_body( $res ), true );
    if ( $code >= 500 || ! is_array( $body ) ) {
        return [ 'ok' => false, 'reachable' => false, 'message' => '라이선스 서버 응답 오류 (HTTP ' . $code . ')' ];
    }
    return [
        'ok'        => $code === 200,
        'reachable' => true,
        'code'      => $body['code'] ?? ( $body['status'] ?? '' ),
        'message'   => $body['message'] ?? '',
        'expires'   => $body['expires'] ?? null,
        'customer'  => $body['customer_name'] ?? '',
    ];
}

/** 저장된 키를 검증해 상태를 갱신합니다. */
function lp_license_check() {
    $key  = get_option( 'lp_license_key' );
    $prev = lp_license_state();
    if ( ! $key ) {
        delete_option( 'lp_license_state' );
        return [];
    }
    $r     = lp_license_request( 'verify', $key );
    $state = [
        'domain'        => lp_license_domain(),
        'checked_at'    => time(),
        'last_valid_at' => $prev['last_valid_at'] ?? 0,
        'expires'       => $prev['expires'] ?? null,
        'customer'      => $prev['customer'] ?? '',
    ];
    if ( $r['ok'] ) {
        $state['status']        = 'valid';
        $state['last_valid_at'] = time();
        $state['expires']       = $r['expires'];
        $state['customer']      = $r['customer'];
        $state['message']       = $r['message'];
    } elseif ( ! $r['reachable'] ) {
        $state['status']  = 'unreachable';
        $state['message'] = $r['message'];
    } else {
        $state['status']        = 'invalid';
        $state['error']         = $r['code'];
        $state['message']       = $r['message'];
        $state['last_valid_at'] = 0;
    }
    update_option( 'lp_license_state', $state, false );
    return $state;
}

/* 하루 한 번 재검증 */
add_action( 'lp_license_daily_check', 'lp_license_check' );
add_action( 'init', function () {
    if ( get_option( 'lp_license_key' ) && ! wp_next_scheduled( 'lp_license_daily_check' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'lp_license_daily_check' );
    }
} );
add_action( 'switch_theme', function () {
    wp_clear_scheduled_hook( 'lp_license_daily_check' );
} );

/* ─────────────────────────────────────────────────────────────
 * 관리 화면: 알림판 > 테마 설정 > 라이선스 탭 (inc/theme-settings-page.php 가 lp_license_page 를 호출)
 * ───────────────────────────────────────────────────────────── */
function lp_license_admin_url() {
    return admin_url( 'admin.php?page=larapress-theme-settings&tab=license' );
}

add_action( 'admin_post_lp_license', function () {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( '권한이 없습니다.' );
    check_admin_referer( 'lp_license' );
    $do  = sanitize_key( $_POST['lp_do'] ?? '' );
    $msg = '';

    if ( $do === 'activate' ) {
        $key = strtoupper( trim( sanitize_text_field( wp_unslash( $_POST['lp_license_key'] ?? '' ) ) ) );
        if ( $key === '' ) {
            $msg = 'err:라이선스 키를 입력해 주세요.';
        } else {
            $a = lp_license_request( 'activate', $key );
            if ( ! $a['reachable'] ) {
                $msg = 'err:라이선스 서버에 연결할 수 없습니다. ' . $a['message'];
            } elseif ( ! $a['ok'] ) {
                $msg = 'err:' . ( $a['message'] ?: '활성화에 실패했습니다.' );
            } else {
                update_option( 'lp_license_key', $key, false );
                $s   = lp_license_check();
                $msg = ( ( $s['status'] ?? '' ) === 'valid' ) ? 'ok:라이선스가 인증되었습니다. PRO 기능이 켜졌습니다.' : 'err:' . ( $s['message'] ?? '인증 실패' );
            }
        }
    } elseif ( $do === 'recheck' ) {
        $s   = lp_license_check();
        $msg = ( ( $s['status'] ?? '' ) === 'valid' ) ? 'ok:다시 확인했습니다. 라이선스가 유효합니다.' : 'err:' . ( $s['message'] ?? '인증 실패' );
    } elseif ( $do === 'remove' ) {
        delete_option( 'lp_license_key' );
        delete_option( 'lp_license_state' );
        wp_clear_scheduled_hook( 'lp_license_daily_check' );
        $msg = 'ok:이 사이트에서 라이선스 키를 제거했습니다.';
    }
    set_transient( 'lp_license_notice_' . get_current_user_id(), $msg, 60 );
    wp_safe_redirect( lp_license_admin_url() );
    exit;
} );

function lp_license_page() {
    $key    = (string) get_option( 'lp_license_key' );
    $s      = lp_license_state();
    $pro    = lp_is_pro();
    $notice = get_transient( 'lp_license_notice_' . get_current_user_id() );
    delete_transient( 'lp_license_notice_' . get_current_user_id() );
    $masked = $key ? substr( $key, 0, 8 ) . str_repeat( '•', max( 0, strlen( $key ) - 13 ) ) . substr( $key, -4 ) : '';
    $labels = [ 'valid' => '유효', 'invalid' => '무효', 'unreachable' => '서버 연결 실패' ];
    ?>
    <div class="lp-card">
        <h2>LaraPress 라이선스</h2>
        <?php if ( $notice ) : list( $t, $m ) = array_pad( explode( ':', $notice, 2 ), 2, '' ); ?>
            <div class="notice notice-<?php echo $t === 'ok' ? 'success' : 'error'; ?> is-dismissible"><p><?php echo esc_html( $m ); ?></p></div>
        <?php endif; ?>

        <table class="form-table" role="presentation">
            <tr><th>등급</th><td><strong><?php echo $pro ? 'PRO' : '무료'; ?></strong></td></tr>
            <tr><th>사이트 도메인</th><td><code><?php echo esc_html( lp_license_domain() ); ?></code></td></tr>
            <?php if ( $key ) : ?>
            <tr><th>라이선스 키</th><td><code><?php echo esc_html( $masked ); ?></code></td></tr>
            <tr><th>상태</th><td><?php echo esc_html( $labels[ $s['status'] ?? '' ] ?? '확인 전' ); ?><?php if ( ! empty( $s['message'] ) && ( $s['status'] ?? '' ) !== 'valid' ) echo ' — ' . esc_html( $s['message'] ); ?></td></tr>
            <tr><th>고객명</th><td><?php echo esc_html( $s['customer'] ?? '' ); ?></td></tr>
            <tr><th>만료일</th><td><?php echo esc_html( ! empty( $s['expires'] ) ? $s['expires'] : '없음 (무기한)' ); ?></td></tr>
            <tr><th>마지막 확인</th><td><?php echo ! empty( $s['checked_at'] ) ? esc_html( wp_date( 'Y-m-d H:i', $s['checked_at'] ) ) : '-'; ?></td></tr>
            <?php endif; ?>
        </table>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'lp_license' ); ?>
            <input type="hidden" name="action" value="lp_license">
            <?php if ( ! $key ) : ?>
                <p><input type="text" name="lp_license_key" class="regular-text code" placeholder="XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX" autocomplete="off"></p>
                <p><button class="button button-primary" name="lp_do" value="activate">라이선스 인증</button></p>
            <?php else : ?>
                <p>
                    <button class="button" name="lp_do" value="recheck">지금 다시 확인</button>
                    <button class="button button-link-delete" name="lp_do" value="remove" onclick="return confirm('이 사이트에서 라이선스 키를 제거할까요? PRO 기능이 꺼집니다.');">키 제거</button>
                </p>
            <?php endif; ?>
        </form>

    </div>
    <div class="lp-card">
        <h2>PRO 전용 기능</h2>
        <ul style="list-style:disc;padding-left:1.5em">
            <li>프리미엄 스킨: 미니멀, 엠버 저널, AI랩</li>
            <li>카테고리 아카이브 레이아웃: 2단·3단 그리드, 웹진</li>
            <li>배너 광고 슬롯 (상단 728×90, 사이드바 300×250)</li>
            <li>홈 화면 위젯 빌더: 섹션 그룹, 갤러리 위젯</li>
        </ul>
        <p class="description">라이선스가 없으면 PRO 항목은 테마 설정에서 잠기고, 이미 저장된 PRO 설정도 실제 사이트에는 무료 기능으로 표시됩니다.</p>
    </div>
    <?php
}

/* 라이선스가 없을 때 관리자 알림 (해당 화면 제외) */
add_action( 'admin_notices', function () {
    if ( lp_is_pro() || ! current_user_can( 'manage_options' ) ) return;
    $screen = get_current_screen();
    if ( ! in_array( $screen->id ?? '', [ 'dashboard', 'themes', 'toplevel_page_larapress-theme-settings' ], true ) ) return;
    if ( ( $screen->id ?? '' ) === 'toplevel_page_larapress-theme-settings' && ( $_GET['tab'] ?? '' ) === 'license' ) return; // phpcs:ignore WordPress.Security.NonceVerification
    printf(
        '<div class="notice notice-info"><p>LaraPress 무료 버전으로 사용 중입니다. <a href="%s">라이선스를 인증</a>하면 프리미엄 스킨·배너 광고·고급 레이아웃을 쓸 수 있습니다.</p></div>',
        esc_url( lp_license_admin_url() )
    );
} );

/* ─────────────────────────────────────────────────────────────
 * PRO 기능 잠금 — 실제 사이트 출력에만 적용
 * (관리 화면·사용자 정의하기 미리보기에서는 저장값 그대로)
 * ───────────────────────────────────────────────────────────── */
function lp_license_gate_active() {
    if ( is_admin() || is_customize_preview() ) return false;
    return ! lp_is_pro();
}

add_filter( 'theme_mod_larapress_layout_style', function ( $v ) {
    return ( lp_license_gate_active() && in_array( $v, lp_pro_features()['skins'], true ) ) ? 'swn-style' : $v;
} );

add_filter( 'theme_mod_lp_archive_style', function ( $v ) {
    return ( lp_license_gate_active() && in_array( $v, lp_pro_features()['archive_styles'], true ) ) ? 'list' : $v;
} );

foreach ( lp_pro_features()['banners'] as $lp_banner_mod ) {
    add_filter( 'theme_mod_' . $lp_banner_mod, function ( $v ) {
        return lp_license_gate_active() ? '' : $v;
    } );
}
unset( $lp_banner_mod );

/* 홈 위젯: 섹션 그룹은 풀어서 순서대로, 갤러리는 목록형으로 */
add_filter( 'theme_mod_lp_home_widgets', function ( $v ) {
    if ( ! lp_license_gate_active() || empty( lp_pro_features()['home_builder'] ) ) return $v;
    // 저장값은 JSON 문자열, 저장 전 기본값은 배열일 수 있다 — 둘 다 같은 규칙으로 처리하고 같은 형태로 돌려준다
    $d = is_array( $v ) ? $v : json_decode( (string) $v, true );
    if ( ! is_array( $d ) ) return $v;
    $out = [];
    foreach ( $d as $w ) {
        if ( ! is_array( $w ) ) continue;
        $items = ( ( $w['type'] ?? '' ) === 'section' ) ? (array) ( $w['items'] ?? [] ) : [ $w ];
        foreach ( $items as $i ) {
            if ( ! is_array( $i ) ) continue;
            if ( ( $i['type'] ?? '' ) === 'gallery' ) $i['type'] = 'list';
            $out[] = $i;
        }
    }
    return is_array( $v ) ? $out : wp_json_encode( $out );
} );

/* 테마 설정 페이지(및 사용자 정의하기)에 PRO 표시 */
function lp_license_mark_pro( $wp_customize ) {
    if ( lp_is_pro() ) return;
    $f    = lp_pro_features();
    $note = ' 🔒 PRO 라이선스가 필요합니다. 라이선스를 인증하기 전에는 선택·저장할 수 없습니다.';

    if ( $c = $wp_customize->get_control( 'larapress_layout_style' ) ) {
        foreach ( $f['skins'] as $k ) {
            if ( isset( $c->choices[ $k ] ) ) $c->choices[ $k ] = '🔒 PRO · ' . $c->choices[ $k ];
        }
    }
    if ( $c = $wp_customize->get_control( 'lp_archive_style' ) ) {
        $c->description .= ' 2단·3단 그리드와 웹진형은' . $note;
    }
    if ( $s = $wp_customize->get_section( 'lp_banner_section' ) ) {
        $s->description .= $note;
    }
    if ( $s = $wp_customize->get_section( 'lp_homepage_section' ) ) {
        $s->description .= ' 섹션 그룹과 갤러리 위젯은' . $note;
    }
}
add_action( 'customize_register', 'lp_license_mark_pro', 20 );
add_action( 'larapress_settings_register', 'lp_license_mark_pro', 20 );

/* ─────────────────────────────────────────────────────────────
 * 라이선스가 없으면 PRO 항목을 잠근다
 * - 테마 설정 페이지: PRO 선택지·배너 입력란 비활성화
 * - 저장(테마 설정·사용자 정의하기 공통): PRO 값이면 검증 오류로 거부
 * ───────────────────────────────────────────────────────────── */
function lp_license_value_is_pro( $id, $value ) {
    $f = lp_pro_features();
    if ( $id === 'larapress_layout_style' ) return in_array( (string) $value, $f['skins'], true );
    if ( $id === 'lp_archive_style' )       return in_array( (string) $value, $f['archive_styles'], true );
    if ( in_array( $id, $f['banners'], true ) ) return trim( (string) $value ) !== '';
    if ( $id === 'lp_home_widgets' && ! empty( $f['home_builder'] ) ) {
        $d = json_decode( (string) $value, true );
        if ( ! is_array( $d ) ) return false;
        foreach ( $d as $w ) {
            if ( ! is_array( $w ) ) continue;
            if ( ( $w['type'] ?? '' ) === 'section' || ( $w['type'] ?? '' ) === 'gallery' ) return true;
        }
    }
    return false;
}

add_filter( 'larapress_settings_choice_locked', function ( $locked, $id, $choice ) {
    return $locked || ( ! lp_is_pro() && lp_license_value_is_pro( $id, $choice ) );
}, 10, 3 );

add_filter( 'larapress_settings_control_locked', function ( $locked, $id ) {
    return $locked || ( ! lp_is_pro() && in_array( $id, lp_pro_features()['banners'], true ) );
}, 10, 2 );

foreach ( array_merge( [ 'larapress_layout_style', 'lp_archive_style', 'lp_home_widgets' ], lp_pro_features()['banners'] ) as $lp_locked_id ) {
    add_filter( 'customize_validate_' . $lp_locked_id, function ( $validity, $value, $setting ) {
        if ( ! lp_is_pro() && lp_license_value_is_pro( $setting->id, $value ) ) {
            $validity->add( 'lp_pro_required', 'PRO 라이선스가 필요한 기능입니다. 테마 설정 > 라이선스 탭에서 인증해 주세요.' );
        }
        return $validity;
    }, 10, 3 );
}
unset( $lp_locked_id );
