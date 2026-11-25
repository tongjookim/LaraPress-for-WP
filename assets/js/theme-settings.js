/* LaraPress 알림판 "테마 설정" 페이지
 * - 이미지·아이콘 선택 (워드프레스 미디어 라이브러리)
 * - 조건부 항목 표시: 커스터마이저(functions.php 의 customize_controls_print_footer_scripts)와 같은 규칙
 */
(function ($) {
    'use strict';

    /* ── 미디어 선택 ─────────────────────────────────── */
    // data-lp-media="url" → 입력란에 이미지 URL, "id" → 첨부 ID(사이트 아이콘)
    $(document).on('click', '.lp-media__select', function (e) {
        e.preventDefault();
        var $box  = $(this).closest('.lp-media');
        var mode  = $box.data('lp-media');
        var frame = wp.media({
            title: mode === 'id' ? '사이트 아이콘 선택' : '이미지 선택',
            button: { text: '선택' },
            library: { type: 'image' },
            multiple: false
        });
        frame.on('select', function () {
            var att = frame.state().get('selection').first().toJSON();
            var url = (att.sizes && att.sizes.thumbnail && mode === 'id') ? att.sizes.thumbnail.url : att.url;
            $box.find('.lp-media__value').val(mode === 'id' ? att.id : att.url).trigger('change');
            $box.find('.lp-media__preview').prop('hidden', false).find('img').attr('src', url);
            $box.find('.lp-media__remove').prop('hidden', false);
        });
        frame.open();
    });

    $(document).on('click', '.lp-media__remove', function (e) {
        e.preventDefault();
        var $box = $(this).closest('.lp-media');
        $box.find('.lp-media__value').val($box.data('lp-media') === 'id' ? '0' : '').trigger('change');
        $box.find('.lp-media__preview').prop('hidden', true).find('img').attr('src', '');
        $(this).prop('hidden', true);
    });

    // URL 을 직접 입력해도 미리보기 갱신
    $(document).on('change', '.lp-media[data-lp-media="url"] .lp-media__value', function () {
        var $box = $(this).closest('.lp-media');
        var url  = $(this).val();
        $box.find('.lp-media__preview').prop('hidden', !url).find('img').attr('src', url);
        $box.find('.lp-media__remove').prop('hidden', !url);
    });

    /* ── 조건부 항목 ─────────────────────────────────── */
    function row(id) {
        return $('tr[data-lp-setting="' + id + '"]');
    }
    function fieldValue(id) {
        var $inputs   = row(id).find('input[name="lp_settings[' + id + ']"]');
        var $checkbox = $inputs.filter(':checkbox');
        if ($checkbox.length) {
            return $checkbox.is(':checked') ? '1' : '0';
        }
        var $checked = $inputs.filter(':checked');
        return $checked.length ? $checked.val() : $inputs.val();
    }

    // 발행인·편집인 동일 → 통합 이름만, 아니면 발행인·편집인 개별
    function togglePubEditor() {
        if (!row('lp_pub_editor_same').length) return;
        var same = fieldValue('lp_pub_editor_same') === '1';
        row('lp_pub_editor_name').toggle(same);
        row('lp_publisher_name').toggle(!same);
        row('lp_editor_name').toggle(!same);
    }

    // 로고 유형별 관련 항목
    function toggleLogo() {
        if (!row('lp_logo_type').length) return;
        var type = fieldValue('lp_logo_type');
        var isImg = type === 'image', isSvg = type === 'svg';
        row('lp_logo_image').toggle(isImg);
        row('lp_logo_svg').toggle(isSvg);
        row('lp_logo_width').toggle(isImg || isSvg);
        row('lp_logo_show_name').toggle(isImg || isSvg);
    }

    $(function () {
        togglePubEditor();
        toggleLogo();
        $(document).on('change', 'tr[data-lp-setting="lp_pub_editor_same"] input', togglePubEditor);
        $(document).on('change', 'tr[data-lp-setting="lp_logo_type"] input', toggleLogo);
    });
})(jQuery);
