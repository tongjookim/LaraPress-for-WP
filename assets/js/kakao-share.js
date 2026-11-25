/**
 * 카카오 JavaScript SDK 초기화 및 공유 헬퍼
 *
 * 커스터마이저(외모 → 사용자 정의하기 → 💬 카카오 SDK 설정)에서 저장한
 * JavaScript 키를 lpKakaoData 로 전달받아 SDK 를 초기화한다.
 * 키가 없거나 SDK 로드에 실패하면 URL 복사 폴백으로 동작한다.
 */
(function () {
    'use strict';

    var data = window.lpKakaoData || {};

    /** SDK 초기화 — 성공 시 true */
    function ready() {
        if (!window.Kakao || !data.jsKey) return false;
        try {
            if (!Kakao.isInitialized()) Kakao.init(data.jsKey);
            return Kakao.isInitialized();
        } catch (e) {
            return false;
        }
    }

    /** 공유 이미지 후보: 명시값 → og:image → 커스터마이저 기본 이미지 */
    function shareImage(explicit) {
        if (explicit) return explicit;
        var og = document.querySelector('meta[property="og:image"]');
        if (og && og.content) return og.content;
        return data.defaultImage || '';
    }

    /** URL 클립보드 복사 폴백 */
    function copyFallback(url, message) {
        function notify() { alert(message || 'URL이 클립보드에 복사되었습니다.'); }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(notify).catch(function () { alert(url); });
            return;
        }
        var ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        notify();
    }

    /**
     * 카카오톡 공유 — 실패 시 URL 복사로 폴백.
     * @param {Object} [opts] title · description · imageUrl · url · buttonTitle
     */
    window.lpKakaoShare = function (opts) {
        opts = opts || {};
        var url   = opts.url || location.href;
        var title = opts.title || document.title;
        var image = shareImage(opts.imageUrl);

        if (!ready()) {
            copyFallback(url, '카카오톡 공유를 사용할 수 없습니다.\nURL이 클립보드에 복사되었습니다.');
            return;
        }

        var content = {
            title:        title,
            description:  opts.description || '',
            link:         { webUrl: url, mobileWebUrl: url }
        };
        if (image) content.imageUrl = image;

        try {
            Kakao.Share.sendDefault({
                objectType: 'feed',
                content:    content,
                buttons:    [{ title: opts.buttonTitle || '기사 보기', link: { webUrl: url, mobileWebUrl: url } }]
            });
        } catch (e) {
            copyFallback(url, '카카오톡 공유에 실패했습니다.\nURL이 클립보드에 복사되었습니다.');
        }
    };

    /**
     * 공유 수 집계 — "많이 본 뉴스" 랭킹용 (서버에서 하루 1회로 제한)
     * @param {string} platform facebook · x · twitter · kakao
     */
    window.lpTrackShare = function (platform) {
        if (!data.ajaxUrl || !data.postId) return;
        var body = new FormData();
        body.append('action', 'lp_track_share');
        body.append('post_id', data.postId);
        body.append('platform', platform);
        try {
            if (navigator.sendBeacon && navigator.sendBeacon(data.ajaxUrl, body)) return;
            fetch(data.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true });
        } catch (e) {}
    };

    // 페이지 로드 시 미리 초기화 (SDK 가 있을 때만)
    ready();
})();
