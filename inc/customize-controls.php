<?php
/**
 * LaraPress — 커스터마이저 커스텀 컨트롤 클래스
 *
 * WP_Customize_Control 이 로드된 뒤에만 정의할 수 있으므로 functions.php 에서 바로 읽지 않고
 * larapress_load_customize_controls() 로 필요할 때(커스터마이저·테마 설정 페이지) 불러온다.
 */

/**
 * 홈 화면 위젯 빌더 — 커스터마이저 커스텀 컨트롤
 * 위젯 목록을 JSON으로 저장하고 드래그·추가·삭제 UI를 제공.
 */
if ( ! class_exists( 'LP_Home_Widgets_Control' ) ) :
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

        wp_localize_script( 'lp-customizer-widgets', 'lpWidgetData', [ 'categories' => self::categories() ] );
    }

    /** 위젯에서 고를 카테고리 목록 (미분류 제외). 테마 설정 페이지에서도 사용 */
    public static function categories() {
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
        return $cats;
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
endif; // LP_Home_Widgets_Control

/**
 * 아카이브 레이아웃 선택 — Radio-Image 커스텀 컨트롤
 * SVG 미리보기 + 라벨로 리스트/그리드2/그리드3/웹진형 중 하나를 선택.
 */
if ( ! class_exists( 'LP_Archive_Style_Control' ) ) :
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
        $choices = self::choices();
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
                    <?php echo self::get_preview_svg( $value ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <span class="lp-asp-label-text"><?php echo esc_html( $info['label'] ); ?></span>
                <span class="lp-asp-label-desc"><?php echo esc_html( $info['desc'] ); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /** 선택지 (테마 설정 페이지에서도 사용) */
    public static function choices() {
        return [
            'list'    => [ 'label' => '리스트형',   'desc' => '정통 뉴스 스타일' ],
            'grid2'   => [ 'label' => '2열 그리드',  'desc' => '매거진 스타일' ],
            'grid3'   => [ 'label' => '3열 그리드',  'desc' => '매거진 스타일' ],
            'webzine' => [ 'label' => '웹진형',      'desc' => '큰 썸네일 강조' ],
        ];
    }

    /** 레이아웃 유형별 SVG 미리보기 생성 (테마 설정 페이지에서도 사용) */
    public static function get_preview_svg( $type ) {
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
endif; // LP_Archive_Style_Control
