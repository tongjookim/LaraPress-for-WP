/* LaraPress 홈 화면 레이아웃 빌더 — Customizer 전용
 * 의존: jQuery, jQuery UI Sortable, wp.customize
 * View 1 (main): 전체 항목 목록
 * View 2 (section): 특정 섹션 그룹 상세
 */
(function ($) {
    'use strict';

    var SETTING_ID = 'lp_home_widgets';
    var isUpdating = false;
    var widgets    = [];
    var activeSectionIdx = null; // null=main, number=section detail

    /* ── 유틸 ───────────────────────────────��────────── */

    function getCategories() {
        return (window.lpWidgetData && Array.isArray(window.lpWidgetData.categories))
            ? window.lpWidgetData.categories
            : [{ id: '0', name: '전체 (모든 카테고리)' }];
    }
    function saveWidgets() {
        isUpdating = true;
        wp.customize(SETTING_ID).set(JSON.stringify(widgets));
        isUpdating = false;
    }
    function reloadWidgets() {
        try {
            var p = JSON.parse(wp.customize(SETTING_ID).get());
            widgets = Array.isArray(p) ? p : defaultState();
        } catch (e) {
            widgets = defaultState();
        }
    }
    function defaultState() {
        return [{ type: 'list', cat: '0', cols: '2', title: '' }];
    }

    /* ── 위젯 설정 패널 (타이틀·카테고리·열수·너비) ─── */

    function buildWidgetPanel(w, getRef, showWidth) {
        var $p = $('<div>', { class: 'lp-wi-panel' });

        // 타이틀
        $p.append($('<label>', { class: 'lp-panel-label', text: '타이틀' }));
        $p.append(
            $('<input>', {
                class: 'lp-wi-title', type: 'text',
                placeholder: '위젯 타이틀 (선택)', value: w.title || '',
            }).on('input', function () {
                var ref = getRef(); if (ref) { ref.title = $(this).val(); saveWidgets(); }
            })
        );

        // 카테고리 + 열 수
        var $row = $('<div>', { class: 'lp-wi-panel-row' });

        var $cat = $('<select>', { class: 'lp-wi-cat', title: '카테고리' });
        getCategories().forEach(function (c) {
            $('<option>', { value: c.id, text: c.name })
                .prop('selected', String(w.cat || '0') === String(c.id))
                .appendTo($cat);
        });
        $cat.on('change', function () {
            var ref = getRef(); if (ref) { ref.cat = $(this).val(); saveWidgets(); }
        });

        var $cols = $('<select>', { class: 'lp-wi-cols', title: '열 수' });
        [['1', '1열'], ['2', '2열'], ['3', '3열']].forEach(function (opt) {
            $('<option>', { value: opt[0], text: opt[1] })
                .prop('selected', String(w.cols || '2') === opt[0])
                .appendTo($cols);
        });
        $cols.on('change', function () {
            var ref = getRef(); if (ref) { ref.cols = $(this).val(); saveWidgets(); }
        });

        $row.append($cat, $cols);
        $p.append($row);

        // 너비 (섹션 내 위젯에만 표시) — 자유 텍스트 입력
        if (showWidth) {
            var $wrow = $('<div>', { class: 'lp-wi-panel-row lp-wi-panel-row--width' });
            $wrow.append($('<span>', { class: 'lp-panel-row-label', text: '너비' }));
            $wrow.append(
                $('<input>', {
                    class: 'lp-wi-width-input', type: 'text',
                    placeholder: '예: 1/2, 2/3, 100%',
                    value: w.width || '',
                }).on('input', function () {
                    var ref = getRef(); if (ref) { ref.width = $(this).val(); saveWidgets(); }
                })
            );
            $p.append($wrow);
        }

        return $p;
    }

    /* ══════════════════════════��═══════════════════════
       View 1 — 메인 목록
       ══════════════════════════════════════════════════ */

    function buildMainItem(w, i) {
        var isSection = (w.type === 'section');
        var $el = $('<div>', {
            class: 'lp-main-item ' + (isSection ? 'lp-main-section-card' : 'lp-main-widget-row'),
            'data-top': i,
        });
        var $row = $('<div>', { class: 'lp-main-row' });

        $row.append($('<span>', {
            class: 'lp-wi-handle lp-sort-outer dashicons dashicons-menu',
            title: '드래그하여 순서 변경',
        }));

        if (isSection) {
            /* 섹션 카드 — 클릭 시 View 2로 이동 */
            var secLabel = w.title || ('섹션 그룹 ' + (i + 1));
            $row.append($('<span>', { class: 'lp-main-icon', text: '🗂' }));
            $row.append($('<span>', { class: 'lp-main-label', text: secLabel }));
            $row.append(
                $('<button>', { class: 'lp-main-nav', type: 'button', title: '섹션 설정' })
                    .append($('<span>', { class: 'dashicons dashicons-arrow-right-alt2' }))
                    .on('click', function (e) { e.preventDefault(); navigateToSection(i); })
            );
        } else {
            /* 독립 위젯 행 — ⚙로 인라인 설정 패널 토글 */
            var wLabel = (w.type === 'gallery' ? '🖼 갤러리형' : '📋 목록형');
            if (w.title) wLabel += ' — ' + w.title;
            $row.append($('<span>', { class: 'lp-main-label', text: wLabel }));

            var $panel = buildWidgetPanel(w, function () { return widgets[i] || null; }, false);
            $panel.hide();

            $row.append(
                $('<button>', { class: 'button lp-wi-cfg', type: 'button', title: '설정', text: '⚙' })
                    .on('click', function (e) {
                        e.preventDefault(); e.stopPropagation();
                        $panel.slideToggle(180);
                        $el.toggleClass('is-open');
                    })
            );
            $el.append($row, $panel);
        }

        $row.append(
            $('<button>', { class: 'button lp-wi-del', type: 'button', title: '삭제', text: '✕' })
                .on('click', function (e) {
                    e.preventDefault(); e.stopPropagation();
                    widgets.splice(i, 1);
                    saveWidgets();
                    renderMainView();
                })
        );

        if (isSection) $el.append($row);
        return $el;
    }

    function renderMainView() {
        var $list = $('#lp-main-list');
        if (!$list.length) return;
        if ($list.hasClass('ui-sortable')) $list.sortable('destroy');
        $list.empty();

        if (widgets.length === 0) {
            $list.append($('<p>', { class: 'lp-wi-empty', text: '항목이 없습니다. 아래 버튼으로 추가하세요.' }));
        } else {
            widgets.forEach(function (w, i) { $list.append(buildMainItem(w, i)); });
        }

        $list.sortable({
            handle: '.lp-sort-outer',
            items: '> .lp-main-item',
            placeholder: 'lp-wi-placeholder',
            axis: 'y',
            tolerance: 'pointer',
            update: function () {
                var snap = widgets.slice(), nw = [];
                $list.children('.lp-main-item').each(function () {
                    var i = parseInt($(this).data('top'), 10);
                    if (!isNaN(i) && snap[i]) nw.push(snap[i]);
                });
                widgets = nw;
                saveWidgets();
                renderMainView();
            },
        });
    }

    /* ══════════════════════════════════════════════════
       View 2 — 섹션 상세
       ══════════════════════════════════════════════════ */

    function buildSubWidgetRow(w, topIdx, subIdx) {
        var label = (w.type === 'gallery' ? '🖼 갤러리형' : '📋 목록형');
        if (w.title) label += ' — ' + w.title;

        var $el = $('<div>', { class: 'lp-sub-item', 'data-top': topIdx, 'data-sub': subIdx });
        var $row = $('<div>', { class: 'lp-main-row' });

        $row.append($('<span>', {
            class: 'lp-wi-handle lp-sort-inner dashicons dashicons-menu',
            title: '드래그',
        }));
        $row.append($('<span>', { class: 'lp-main-label', text: label }));

        var $panel = buildWidgetPanel(w, function () {
            return (widgets[topIdx] && Array.isArray(widgets[topIdx].items))
                ? widgets[topIdx].items[subIdx] : null;
        }, true); // showWidth = true
        $panel.hide();

        $row.append(
            $('<button>', { class: 'button lp-wi-cfg', type: 'button', title: '설정', text: '⚙' })
                .on('click', function (e) {
                    e.preventDefault(); e.stopPropagation();
                    $panel.slideToggle(180);
                    $el.toggleClass('is-open');
                })
        );
        $row.append(
            $('<button>', { class: 'button lp-wi-del', type: 'button', title: '삭제', text: '✕' })
                .on('click', function (e) {
                    e.preventDefault(); e.stopPropagation();
                    widgets[topIdx].items.splice(subIdx, 1);
                    saveWidgets();
                    renderSectionView(topIdx);
                })
        );
        $el.append($row, $panel);
        return $el;
    }

    function renderSectionView(idx) {
        activeSectionIdx = idx;
        var sec = widgets[idx];
        if (!sec) { navigateBack(); return; }

        $('#lp-section-view-title').text(sec.title || ('섹션 그룹 ' + (idx + 1)));

        var $detail = $('#lp-section-detail').empty();

        /* 섹션 기본 설정 */
        var $form = $('<div>', { class: 'lp-section-form' });

        $form.append($('<label>', { class: 'lp-panel-label', text: '섹션 제목' }));
        $form.append(
            $('<input>', {
                class: 'lp-wi-title', type: 'text',
                placeholder: '섹션 제목 (선택)', value: sec.title || '',
            }).on('input', function () {
                if (widgets[idx]) {
                    widgets[idx].title = $(this).val();
                    var display = $(this).val() || ('섹션 그룹 ' + (idx + 1));
                    $('#lp-section-view-title').text(display);
                    // 메인 뷰 카드 레이블도 업데이트
                    $('#lp-main-list .lp-main-section-card[data-top="' + idx + '"] .lp-main-label').text(display);
                    saveWidgets();
                }
            })
        );

        var $colsRow = $('<div>', { class: 'lp-wi-panel-row', style: 'margin-top:6px' });
        $colsRow.append($('<span>', { class: 'lp-panel-row-label', text: '열 수' }));
        var $sCols = $('<select>', { class: 'lp-wi-cols' });
        [['1', '1열'], ['2', '2열'], ['3', '3열']].forEach(function (opt) {
            $('<option>', { value: opt[0], text: opt[1] })
                .prop('selected', String(sec.cols || '2') === opt[0])
                .appendTo($sCols);
        });
        $sCols.on('change', function () {
            if (widgets[idx]) { widgets[idx].cols = $(this).val(); saveWidgets(); }
        });
        $colsRow.append($sCols);
        $form.append($colsRow);
        $detail.append($form);

        /* 위젯 목록 */
        $detail.append($('<div>', { class: 'lp-section-divider', text: '위젯 목록' }));

        var $subList = $('<div>', { id: 'lp-sub-list' });
        var items = Array.isArray(sec.items) ? sec.items : [];

        if (items.length === 0) {
            $subList.append($('<p>', { class: 'lp-wi-empty', text: '위젯을 추가하세요.' }));
        } else {
            items.forEach(function (subW, j) {
                $subList.append(buildSubWidgetRow(subW, idx, j));
            });
        }
        $detail.append($subList);

        $subList.sortable({
            handle: '.lp-sort-inner',
            items: '> .lp-sub-item',
            placeholder: 'lp-wi-placeholder',
            axis: 'y',
            tolerance: 'pointer',
            update: function () {
                var snap = (widgets[idx].items || []).slice(), newItems = [];
                $subList.children('.lp-sub-item').each(function () {
                    var j = parseInt($(this).data('sub'), 10);
                    if (!isNaN(j) && snap[j]) newItems.push(snap[j]);
                });
                widgets[idx].items = newItems;
                saveWidgets();
                renderSectionView(idx);
            },
        });

        /* 위젯 추가 버튼 */
        var $addRow = $('<div>', { class: 'lp-section-add-row' });
        var $addBtn = $('<button>', { class: 'button button-secondary lp-section-add-btn', type: 'button', text: '＋ 위젯 추가' });
        var $picker = $('<div>', { class: 'lp-section-picker' }).hide();
        $('<button>', { type: 'button', class: 'button', 'data-type': 'list',    text: '📋 목록형' }).appendTo($picker);
        $('<button>', { type: 'button', class: 'button', 'data-type': 'gallery', text: '🖼 갤러리형' }).appendTo($picker);

        $addBtn.on('click', function (e) { e.preventDefault(); $picker.slideToggle(150); });
        $picker.find('[data-type]').on('click', function (e) {
            e.preventDefault();
            if (!Array.isArray(widgets[idx].items)) widgets[idx].items = [];
            widgets[idx].items.push({ type: $(this).data('type'), cat: '0', cols: '1', title: '', width: '' });
            saveWidgets();
            renderSectionView(idx);
        });
        $addRow.append($addBtn, $picker);
        $detail.append($addRow);
    }

    /* ══════════════════════════════════════════════════
       네비게이션
       ══════════════════════════════════════════════════ */

    function navigateToSection(idx) {
        renderSectionView(idx);
        $('#lp-layout-builder').addClass('lp-in-section');
    }

    function navigateBack() {
        activeSectionIdx = null;
        $('#lp-layout-builder').removeClass('lp-in-section');
    }

    /* ── 진입점 ───────────────────────────────────────── */

    $(function () {
        wp.customize(SETTING_ID, function (setting) {
            reloadWidgets();
            renderMainView();
            setting.bind(function () {
                if (!isUpdating) {
                    reloadWidgets();
                    // 외부 변경(undo 등)시 메인으로 복귀
                    navigateBack();
                    renderMainView();
                }
            });
        });

        /* 뒤로 버튼 */
        $(document).on('click', '#lp-back-btn', function (e) {
            e.preventDefault();
            navigateBack();
        });

        /* 외부 추가 버튼 */
        $(document).on('click', '#lp-add-item', function (e) {
            e.preventDefault();
            $('#lp-item-picker').slideToggle(150);
        });

        $(document).on('click', '#lp-item-picker [data-type]', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (type === 'section') {
                widgets.push({ type: 'section', cols: '2', title: '', items: [] });
            } else {
                widgets.push({ type: type, cat: '0', cols: '2', title: '' });
            }
            saveWidgets();
            renderMainView();
            $('#lp-item-picker').slideUp(150);
        });
    });

})(jQuery);
