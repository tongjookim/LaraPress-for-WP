<?php
/**
 * LaraPress Theme Header
 * get_header() 에 의해 index.php, search.php, archive.php 에서 공통 포함됩니다.
 * lp_skin_vars()로 스킨 변수를 가져와 헤더 출력에 사용합니다.
 */
extract( lp_skin_vars() );
$lp_banner_top = get_theme_mod( 'lp_banner_top', '' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <script>
    (function(){
        try {
            var m = localStorage.getItem('lp-dark-mode');
            if (m === 'dark' || (!m && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch(e) {}
    })();
    </script>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        /* 네비게이션 메뉴 애니메이션 및 스타일 지정 */
        .primary-menu-container ul { display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center; }
        @media (min-width: 768px) { .primary-menu-container ul { gap: 2.5rem; } }

        .primary-menu-container ul a { position: relative; color: #1e293b; transition: color 0.3s; }
        .primary-menu-container ul a::after {
            content: ''; position: absolute; bottom: -4px; left: 0; width: 0%; height: 2px;
            background-color: #2563eb; transition: width 0.3s ease;
        }
        .primary-menu-container ul a:hover { color: #2563eb; }
        .primary-menu-container ul a:hover::after { width: 100%; }

        /* 푸터 메뉴 스타일 */
        .footer-menu-container ul { display: flex; flex-direction: column; gap: 0.5rem; }
        .footer-menu-container ul a { color: #94a3b8; font-size: 0.875rem; transition: color 0.3s; }
        .footer-menu-container ul a:hover { color: #ffffff; }

        /* ── 스티키 기사 정보 바 ────────────────────── */
        #lp-sticky-bar {
            position: fixed;
            left: 0; right: 0;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            visibility: hidden;
            opacity: 0;
            transform: translateY(-110%);
            transition: transform 0.28s cubic-bezier(0.4,0,0.2,1),
                        opacity     0.28s ease,
                        visibility  0s   linear 0.28s;
            z-index: 51;
            overflow: hidden;
        }
        #lp-sticky-bar.is-visible {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
            transition: transform 0.28s cubic-bezier(0.4,0,0.2,1),
                        opacity     0.28s ease,
                        visibility  0s   linear 0s;
        }

        /* ── 열독률 진행바 (스티키 바 하단) ─────────── */
        #lp-progress-bar {
            position: absolute; bottom: 0; left: 0;
            height: 2px; width: 0%;
            background: linear-gradient(90deg, #1d4ed8, #3b82f6, #93c5fd);
            transition: width 0.1s linear;
            pointer-events: none;
            z-index: 1;
        }

        /* 기사 스티키 바 활성 시 GNB 슬라이드업 */
        nav.lp-gnav-hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateY(-100%);
        }
        nav {
            transition: opacity 0.22s ease,
                        transform 0.28s cubic-bezier(0.4,0,0.2,1);
        }

        /* ── WordPress 관리자 바 중첩 방지 ─────────────────────────────
           .admin-bar 클래스는 로그인 시 WP가 <body>에 자동 추가. */
        .admin-bar nav { top: 32px !important; }
        @media screen and (max-width: 782px) {
            .admin-bar nav { top: 46px !important; }
        }
        #lp-sticky-bar .lp-sb-inner {
            display: flex; align-items: center; gap: 0.5rem;
            height: 64px; padding: 0 1rem;
            position: relative; z-index: 2;
        }
        #lp-sticky-bar .lp-sb-logo {
            font-size: 0.9rem; font-weight: 800; letter-spacing: -0.03em;
            color: #0f172a; text-decoration: none; white-space: nowrap;
            flex-shrink: 0; line-height: 1;
        }
        #lp-sticky-bar .lp-sb-logo:hover { color: #1d4ed8; }
        #lp-sticky-bar .lp-sb-sep {
            width: 1px; height: 18px; background: #e2e8f0; flex-shrink: 0; margin: 0 0.125rem;
        }
        #lp-sticky-bar .lp-sb-title {
            font-size: 0.8125rem; font-weight: 700;
            color: #0f172a; overflow: hidden;
            display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical;
            line-height: 1.35;
        }
        #lp-sticky-bar .lp-sb-author {
            font-size: 0.6875rem; color: #94a3b8; margin-top: 1px; white-space: nowrap; line-height: 1.2;
        }
        .lp-font-ctrl {
            display: flex; align-items: stretch;
            border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;
            flex-shrink: 0;
        }
        .lp-font-ctrl button {
            padding: 3px 6px; font-weight: 700; color: #64748b;
            background: #fff; cursor: pointer; border: none;
            transition: background 0.15s, color 0.15s; line-height: 1;
            display: flex; align-items: center; gap: 1px;
        }
        .lp-font-ctrl button:hover { background: #f1f5f9; color: #1d4ed8; }
        .lp-font-ctrl button.lp-fz-active { background: #eff6ff; color: #1d4ed8; }
        .lp-font-ctrl button + button { border-left: 1px solid #e2e8f0; }
        .lp-font-ctrl .lp-fz-sm { font-size: 0.6875rem; }
        .lp-font-ctrl .lp-fz-md { font-size: 0.8125rem; }
        .lp-font-ctrl .lp-fz-lg { font-size: 0.9375rem; }
        #lp-sticky-bar .lp-sb-share-sep {
            width: 1px; height: 16px; background: #e2e8f0; flex-shrink: 0; margin: 0 0.125rem;
        }

        /* ── 하단 Hero (2D 일러스트) ───────────────── */
        .lp-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #0f172a 100%);
            padding: 3.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4rem;
            overflow: hidden;
            position: relative;
        }
        .lp-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 60% 80% at 70% 50%, rgba(37,99,235,0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .lp-hero__text { position: relative; z-index: 1; max-width: 460px; word-break: keep-all; }
        .lp-hero__eyebrow {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            color: #60a5fa;
            background: rgba(37,99,235,0.15);
            border: 1px solid rgba(96,165,250,0.3);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            margin-bottom: 1rem;
        }
        .lp-hero__title {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 900;
            color: #f1f5f9;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-bottom: 0.75rem;
        }
        .lp-hero__title span { color: #60a5fa; }
        .lp-hero__sub {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
        }
        /* 일러스트(사실적 SVG): 자체 배경(회벽·보도)이 있어 어두운/밝은(클래식) 스킨 모두에서 같게 보인다 */
        .lp-illust {
            position: relative;
            z-index: 1;
            flex: 0 1 460px;
            width: min(460px, 100%);
            border-radius: 26px;
            box-shadow: 0 18px 44px rgba(2,6,23,0.35);
        }
        .lp-illust__svg { display: block; width: 100%; height: auto; border-radius: 26px; }
        @media (prefers-reduced-motion: no-preference) {
            .lp-ill-hammer { transform-origin: 22px -160px; animation: lp-ill-hammer 1.1s ease-in-out infinite; }
        }
        @keyframes lp-ill-hammer { 0%,100% { transform: rotate(-14deg); } 50% { transform: rotate(10deg); } }
        @media (max-width: 640px) {
            .lp-hero { gap: 2rem; flex-direction: column; padding: 2.5rem 1.5rem; text-align: center; }
            .lp-illust { flex-basis: auto; width: min(420px, 100%); }
        }

        /* ── TOP TOP 버튼 (맨 위로 스크롤) — 전 스킨 공통 ───── */
        .lp-totop {
            position: fixed;
            right: 1.5rem;
            bottom: 1.5rem;
            z-index: 55;
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 50%;
            background: #7400AD; /* 업로드 로고(logo_CRpurple.svg) 컬러 */
            color: #fff;
            box-shadow: 0 4px 14px rgba(116,0,173,0.35);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity 0.25s ease, transform 0.25s ease, background 0.2s ease, box-shadow 0.2s ease;
        }
        .lp-totop.is-visible { opacity: 1; visibility: visible; transform: translateY(0); }
        .lp-totop:hover { background: #5c008a; box-shadow: 0 6px 18px rgba(116,0,173,0.45); }
        .lp-totop svg { width: 20px; height: 20px; pointer-events: none; }
        html.dark .lp-totop { background: #9b33d1; box-shadow: 0 4px 14px rgba(0,0,0,0.4); }
        html.dark .lp-totop:hover { background: #b658e0; }
        @media (max-width: 640px) {
            .lp-totop { right: 1rem; bottom: 1rem; width: 42px; height: 42px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .lp-totop { transition: opacity 0.25s ease, visibility 0.25s ease; }
        }

        /* ══════════════════════════════════════════════════
           NYT 스킨 (newyorktimes-style) 오버라이드
           ══════════════════════════════════════════════════ */
        .nyt-skin {
            background: #ffffff;
            font-family: Georgia, 'Times New Roman', Times, serif;
            color: #121212;
        }

        /* ── NYT 마스트헤드 ──────────────────────────────── */
        .nyt-topbar {
            border-bottom: 1px solid #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            color: #333;
        }
        .nyt-topbar a { color: #333; text-decoration: none; }
        .nyt-topbar a:hover { text-decoration: underline; }

        .nyt-masthead {
            border-top: 3px solid #000;
            border-bottom: 3px solid #000;
            padding: 0.85rem 1rem;
        }
        .nyt-masthead-inner {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .nyt-masthead-slot {
            width: 160px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        .nyt-masthead-slot.slot-right { justify-content: flex-end; }
        .nyt-masthead-center {
            flex: 1;
            text-align: center;
        }
        .nyt-masthead-banner { display: block; max-width: 160px; overflow: hidden; }
        .nyt-masthead-banner-ph {
            width: 160px;
            height: 50px;
            background: #f5f5f5;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6rem;
            color: #aaa;
            letter-spacing: 0.05em;
        }
        .nyt-masthead-logo {
            font-family: 'Times New Roman', Times, serif;
            font-size: clamp(1.8rem, 5vw, 3.2rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            color: #000;
            line-height: 1;
            text-decoration: none;
            display: block;
        }
        .nyt-masthead-logo:hover { opacity: 0.85; }
        .nyt-masthead-sub {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.6rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #666;
            margin-top: 0.3rem;
        }
        .nyt-search-btn {
            width: 36px;
            height: 36px;
            border: 1.5px solid #000;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s, color 0.15s;
            flex-shrink: 0;
        }
        .nyt-search-btn:hover { background: #000; color: #fff; }
        .nyt-search-btn svg { pointer-events: none; }
        .nyt-search-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 80px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }
        .nyt-search-modal.is-open { opacity: 1; pointer-events: auto; }
        .nyt-search-modal-box {
            background: #fff;
            border-top: 4px solid #000;
            width: 100%;
            max-width: 640px;
            padding: 2rem 2rem 1.75rem;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .nyt-search-label {
            font-family: 'Times New Roman', Times, serif;
            font-size: 0.7rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #666;
            display: block;
            margin-bottom: 0.5rem;
        }
        .nyt-search-form { display: flex; gap: 0; border-bottom: 2px solid #000; }
        .nyt-search-input {
            flex: 1;
            font-family: 'Times New Roman', Times, serif;
            font-size: 1.6rem;
            border: none;
            outline: none;
            padding: 0.2rem 0;
            background: transparent;
            color: #000;
        }
        .nyt-search-submit {
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            padding: 0 1rem;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            font-family: Arial, sans-serif;
        }
        .nyt-search-submit:hover { background: #333; }
        .nyt-search-modal-close {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            width: 28px;
            height: 28px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #888;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .nyt-search-modal-close:hover { color: #000; }
        @media (max-width: 640px) {
            .nyt-masthead-slot.slot-left { display: none; }
            .nyt-masthead-slot.slot-right { width: auto; }
        }

        /* ── NYT 섹션 네비 ───────────────────────────────── */
        .nyt-skin nav.nyt-nav {
            background: #fff;
            border-top: 1px solid #ddd;
            border-bottom: 2px solid #000;
        }
        .nyt-nav .primary-menu-container ul {
            gap: 0;
            justify-content: center;
        }
        .nyt-nav .primary-menu-container ul li {
            border-right: 1px solid #ddd;
        }
        .nyt-nav .primary-menu-container ul li:first-child {
            border-left: 1px solid #ddd;
        }
        .nyt-nav .primary-menu-container ul a {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #121212;
            padding: 0.9rem 1rem;
            display: block;
            text-transform: uppercase;
        }
        .nyt-nav .primary-menu-container ul a::after { display: none; }
        .nyt-nav .primary-menu-container ul a:hover {
            background: #f4f4f4;
            color: #000;
        }

        /* ── NYT 메인 레이아웃 ───────────────────────────── */
        .nyt-skin main { background: #fff; }
        .nyt-content-area { padding: 0; }

        .nyt-sidebar .bg-white {
            border-radius: 0 !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: transparent !important;
        }

        /* ── NYT 프론트 페이지 — 탑 기사 ────────────────── */
        .nyt-content-area h2.text-2xl {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.15em !important;
            text-transform: uppercase !important;
            color: #121212 !important;
            border-bottom: 3px solid #000 !important;
        }
        .nyt-content-area h2.text-2xl .w-2 { display: none; }
        .nyt-content-area h3.text-3xl,
        .nyt-content-area h3.text-3xl a {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: 2rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            color: #121212 !important;
            letter-spacing: -0.02em !important;
        }
        .nyt-content-area h3.text-3xl a:hover { color: #326891 !important; }
        .nyt-content-area h3.text-lg,
        .nyt-content-area h3.text-lg a {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-weight: 700 !important;
            color: #121212 !important;
        }
        .nyt-content-area h3.text-lg a:hover { color: #326891 !important; }
        .nyt-content-area h2.text-xl {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.12em !important;
            text-transform: uppercase !important;
            color: #121212 !important;
            border-bottom-color: #000 !important;
        }
        .nyt-content-area h2.text-xl span { color: #121212 !important; }
        .nyt-content-area h2.text-xl a {
            font-family: Georgia, serif !important;
            font-size: 0.7rem !important;
            letter-spacing: 0 !important;
            text-transform: none !important;
            color: #326891 !important;
        }
        .nyt-content-area ul.space-y-3 a {
            font-family: Georgia, 'Times New Roman', serif !important;
            color: #121212 !important;
            font-size: 0.9rem !important;
        }
        .nyt-content-area ul.space-y-3 a:hover { color: #326891 !important; }
        .nyt-content-area ul.space-y-3 li {
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 0.75rem;
        }
        .nyt-content-area .text-slate-400 {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 0.7rem !important;
            color: #888 !important;
        }
        .nyt-skin img { border-radius: 0 !important; }

        /* ── NYT 기사 단일 뷰 ────────────────────────────── */
        .nyt-skin article.article-view header .text-blue-600 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #121212 !important;
        }
        .nyt-skin article.article-view h1 {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(1.9rem, 4vw, 3rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -0.025em;
            color: #121212;
        }
        .nyt-skin article.article-view header .text-slate-700 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            color: #121212 !important;
            letter-spacing: 0.01em;
        }
        .nyt-skin article.article-view header .text-slate-500 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.75rem;
            color: #666 !important;
        }
        .nyt-skin article.article-view header {
            border-bottom-color: #000 !important;
            border-bottom-width: 2px !important;
        }
        /* 본문 내 이미지 캡션(figcaption / 워드프레스 레거시 wp-caption) 공통 스타일 */
        #lp-article-body figure figcaption,
        #lp-article-body .wp-caption-text {
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        #lp-article-body .wp-caption { max-width: 100%; }
        .nyt-skin #lp-article-body {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: 1.125rem !important;
            line-height: 1.9 !important;
            color: #121212 !important;
        }
        .nyt-skin #lp-article-body p { margin-bottom: 2rem; }
        .nyt-skin #lp-article-body figure figcaption {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.75rem;
            color: #666;
            text-align: left;
            border-top: 2px solid #000;
            padding-top: 0.4rem;
            margin-top: 0.5rem;
        }
        .nyt-skin .bg-gradient-to-br {
            background: #f7f7f7 !important;
            border-radius: 0 !important;
            border: 1px solid #ddd !important;
        }
        /* nyt-skin .lp-rx-btn 오버라이드는 반응 버튼 CSS 블록에 통합됨 */

        /* ── NYT 사이드바 ────────────────────────────────── */
        .nyt-sidebar > div {
            border-top: 3px solid #000 !important;
            padding-top: 1rem !important;
            margin-bottom: 1.5rem;
        }
        .nyt-sidebar h3 {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.12em !important;
            text-transform: uppercase !important;
            color: #121212 !important;
            border-bottom: 1px solid #ccc !important;
            border-bottom-color: #ccc !important;
            padding-bottom: 0.5rem !important;
            margin-bottom: 0.75rem !important;
        }
        .nyt-sidebar h3 .text-slate-400,
        .nyt-sidebar h3 span { display: none; }
        .nyt-sidebar ol a {
            font-family: Georgia, 'Times New Roman', serif !important;
            color: #121212 !important;
            font-size: 0.875rem !important;
        }
        .nyt-sidebar ol a:hover { color: #326891 !important; text-decoration: underline; }
        .nyt-sidebar ol li { border-bottom: 1px solid #e5e5e5; padding-bottom: 0.75rem; }
        .nyt-sidebar .text-blue-600 { color: #000 !important; }
        .nyt-sidebar .text-slate-500,
        .nyt-sidebar .text-slate-400 { color: #666 !important; }
        .nyt-sidebar input[type="text"] {
            border-radius: 0 !important;
            border-color: #000 !important;
            font-family: Georgia, serif;
        }
        .nyt-sidebar button[type="submit"] {
            border-radius: 0 !important;
            background: #000 !important;
        }
        .nyt-sidebar .h-\[250px\] { border-radius: 0 !important; border-color: #ccc !important; }

        /* ── NYT 스티키 바 ───────────────────────────────── */
        .nyt-skin #lp-sticky-bar {
            background: rgba(255,255,255,0.98);
            border-bottom: 2px solid #000;
            box-shadow: none;
        }
        .nyt-skin #lp-sticky-bar .lp-sb-logo {
            font-family: 'Times New Roman', Georgia, serif;
            letter-spacing: 0.03em;
        }
        .nyt-skin #lp-sticky-bar .lp-sb-title {
            font-family: Georgia, 'Times New Roman', serif;
            font-weight: 700;
        }
        .nyt-skin #lp-sticky-bar .lp-sb-author {
            font-family: Arial, Helvetica, sans-serif;
        }
        .nyt-skin .lp-font-ctrl button { border-radius: 0; }
        .nyt-skin #lp-progress-bar { background: #000; }

        /* ── NYT 푸터 ─────────────────────────────── */
        .nyt-skin footer h2 {
            font-family: 'Times New Roman', Times, serif;
            letter-spacing: -0.02em;
        }

        /* ── Classic 스킨 — 히어로 오버라이드 ─────── */
        .nyt-skin .lp-hero {
            background: #fff;
            border-top: 4px solid #000;
            border-bottom: 3px double #000;
            padding: 3rem clamp(1.25rem, 5vw, 4rem);
            gap: clamp(2rem, 5vw, 5rem);
        }
        .nyt-skin .lp-hero::before { display: none; }
        .nyt-skin .lp-hero__text { max-width: 480px; }
        .nyt-skin .lp-hero__eyebrow {
            background: transparent;
            color: #111;
            border: 1.5px solid #111;
            border-radius: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 0.6rem;
            letter-spacing: 0.35em;
        }
        .nyt-skin .lp-hero__title {
            font-family: 'Times New Roman', Georgia, serif;
            color: #000;
            font-size: clamp(2rem, 3.5vw, 2.9rem);
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .nyt-skin .lp-hero__title span { color: #000; }
        .nyt-skin .lp-hero__sub {
            font-family: Arial, Helvetica, sans-serif;
            color: #555;
            font-size: 0.875rem;
            line-height: 1.75;
        }
        .nyt-skin .lp-illust { box-shadow: 4px 8px 24px rgba(0,0,0,0.22); }
        @media (max-width: 640px) {
            .nyt-skin .lp-hero {
                flex-direction: column-reverse;
                gap: 1.75rem;
                padding: 2.25rem 1.25rem;
                text-align: center;
            }
            .nyt-skin .lp-hero__eyebrow { margin-left: auto; margin-right: auto; }
            .nyt-skin .lp-hero__text { max-width: 100%; }
        }

        .nyt-skin .text-3xl.font-black { font-family: Georgia, 'Times New Roman', serif; }

        @media (max-width: 768px) {
            .nyt-masthead-logo { font-size: 2.2rem; }
            .nyt-nav .primary-menu-container ul { flex-wrap: nowrap; overflow-x: auto; justify-content: flex-start; }
            .nyt-nav .primary-menu-container ul li { flex-shrink: 0; }
        }

        /* ════════════════════════════════════════════════
           클래식 스킨 전용 메가 푸터 (.lp-classic-footer)
           ════════════════════════════════════════════════ */
        .lp-classic-footer {
            background: #fff; color: #222;
            border-top: 4px solid #000;
            font-family: Arial, Helvetica, sans-serif;
        }
        .lp-cf-head { border-bottom: 1px solid #ddd; padding: 0.875rem 0; }
        .lp-cf-head-inner { display: flex; align-items: center; justify-content: space-between; }
        .lp-cf-logo-link { text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .lp-cf-logo-link img { height: 36px; width: auto; }
        .basic-footer-logo img,
        .aj-footer-logo-text img,
        .lp-swn-footer-logo img { height: 32px; width: auto; }

        /* 문의(Contact) 페이지 — Fluent Forms "SUBMIT NOW" 버튼 색상 */
        .lp-contact-page button[type="submit"] {
            background: #1d4ed8 !important;
            color: #fff !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 0.75rem 2.25rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .lp-contact-page button[type="submit"]:hover {
            background: #1e40af !important;
            transform: translateY(-1px);
        }
        .swn-skin .lp-contact-page button[type="submit"] { background: var(--swn-primary) !important; }
        .swn-skin .lp-contact-page button[type="submit"]:hover { background: var(--swn-secondary) !important; }
        .lp-cf-sitename {
            font-family: 'Times New Roman', Georgia, serif;
            font-size: 1.5rem; font-weight: 700; color: #000; letter-spacing: -0.02em;
        }
        .lp-cf-socials { display: flex; align-items: center; gap: 1rem; }
        .lp-cf-social { color: #666; transition: color 0.15s; display: flex; }
        .lp-cf-social:hover { color: #000; }
        .lp-cf-body { padding: 2rem 0; border-bottom: 1px solid #ddd; }
        .lp-cf-body-grid { display: flex; gap: 2rem; align-items: flex-start; }
        .lp-cf-cats {
            flex: 1;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(112px, 1fr));
            gap: 1.5rem 1rem;
            align-items: start;
        }
        .lp-cf-cat-title {
            font-size: 0.8125rem; font-weight: 700; color: #000;
            margin-bottom: 0.45rem;
            border-bottom: 1px solid #ddd; padding-bottom: 0.25rem;
        }
        .lp-cf-cat-title a { color: inherit; text-decoration: none; }
        .lp-cf-cat-title a:hover { text-decoration: underline; }
        .lp-cf-cat-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.28rem; }
        .lp-cf-cat-list li a { font-size: 0.75rem; color: #555; text-decoration: none; line-height: 1.35; }
        .lp-cf-cat-list li a:hover { color: #000; text-decoration: underline; }
        .lp-cf-right { width: 224px; flex-shrink: 0; border-left: 1px solid #ddd; padding-left: 1.25rem; }
        .lp-cf-service-list { list-style: none; margin: 0; padding: 0; }
        .lp-cf-service-list > li { padding: 0.3rem 0; }
        .lp-cf-service-list > li > a { font-size: 0.8rem; color: #222; text-decoration: none; }
        .lp-cf-service-list > li > a:hover { text-decoration: underline; }
        .lp-cf-hr { border: none; border-top: 1px solid #ddd; margin: 0.75rem 0; }
        .lp-cf-company-info {
            display: grid; grid-template-columns: auto 1fr;
            gap: 0.18rem 0.45rem; font-size: 0.6875rem; color: #444;
        }
        .lp-cf-company-info dt { color: #888; white-space: nowrap; }
        .lp-cf-company-info dd { margin: 0; color: #222; word-break: break-all; }
        .lp-cf-legal { border-top: 1px solid #ddd; padding: 1.125rem 0 1rem; }
        .lp-cf-legal-rows { display: flex; flex-direction: column; gap: 0.35rem; }
        .lp-cf-legal-row {
            display: flex; flex-wrap: wrap; align-items: center;
            gap: 0.2rem 0; font-size: 0.75rem; color: #555;
        }
        .lp-cf-legal-row strong { color: #333; font-weight: 600; }
        .lp-cf-sep { color: #bbb; padding: 0 0.6rem; }
        .lp-cf-legal-copy { font-size: 0.6875rem; color: #888; margin-top: 0.25rem; }
        .lp-cf-foot { border-top: 1px solid #ccc; padding: 0.6rem 0; background: #f8f8f8; }
        .lp-cf-foot-inner {
            display: flex; align-items: center;
            justify-content: flex-start; flex-wrap: wrap; gap: 0.4rem 1rem;
        }
        .lp-cf-policy-list { list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: 0 0.1rem; }
        .lp-cf-policy-list > li { display: flex; align-items: center; }
        .lp-cf-policy-list > li + li::before { content: '|'; padding: 0 0.35rem; color: #ccc; font-size: 0.6875rem; }
        .lp-cf-policy-list > li > a { font-size: 0.6875rem; color: #555; text-decoration: none; }
        .lp-cf-policy-list > li > a:hover { color: #000; text-decoration: underline; }
        @media (max-width: 768px) {
            .lp-cf-body-grid { flex-direction: column; }
            .lp-cf-right { width: 100%; border-left: none; padding-left: 0; border-top: 1px solid #ddd; padding-top: 1.25rem; margin-top: 0.5rem; }
            .lp-cf-cats { grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); }
        }

        /* ── 카테고리 목록 사이드바 위젯 ──────────────────── */
        .lp-cat-list { list-style: none; margin: 0; padding: 0; }
        .lp-cat-item {
            display: flex;
            align-items: center;
            padding: 0.42rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .lp-cat-item:last-child { border-bottom: none; }
        .lp-cat-bullet {
            color: #94a3b8;
            font-size: 0.8rem;
            flex-shrink: 0;
            margin-right: 0.3rem;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            transition: color 0.18s ease;
        }
        .lp-cat-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
            color: #334155;
            text-decoration: none;
            font-size: 0.875rem;
            gap: 0.5rem;
            transition: color 0.18s ease, transform 0.18s ease;
        }
        .lp-cat-link:hover { color: #1a73e8; transform: translateX(3px); }
        .lp-cat-item:hover .lp-cat-bullet { color: #1a73e8; }
        .lp-cat-name { flex: 1; min-width: 0; }
        .lp-cat-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            height: 1.35rem;
            padding: 0 0.3rem;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #64748b;
            font-size: 0.65rem;
            font-weight: 600;
            flex-shrink: 0;
            transition: background 0.18s ease, color 0.18s ease;
        }
        .lp-cat-link:hover .lp-cat-count { background: #dbeafe; color: #1a73e8; }
        .nyt-skin .lp-cat-link:hover { color: #326891; }
        .nyt-skin .lp-cat-link:hover .lp-cat-count { background: #e8f0f6; color: #326891; }
        .nyt-skin .lp-cat-item:hover .lp-cat-bullet { color: #326891; }

        /* ══════════════════════════════════════════════════
           댓글 — Fresh · Classic · Minimal 공용 (lp-cmt-*)
           ══════════════════════════════════════════════════ */
        .lp-comments { margin-top: 0; }

        /* 댓글 수 헤딩 */
        .lp-cmts-heading {
            font-size: 1rem; font-weight: 800; color: #1e293b;
            display: flex; align-items: center; gap: 0.5rem;
            padding-bottom: 0.75rem; margin-bottom: 1.25rem;
            border-bottom: 2px solid #0f172a;
        }
        .lp-cmts-count {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.5rem; height: 1.5rem; padding: 0 0.4rem;
            background: #2563eb; color: #fff;
            border-radius: 9999px; font-size: 0.72rem; font-weight: 700;
        }

        /* ── 기사 반응 버튼 ─────────────────────────────── */
        .lp-reactions {
            margin: 1.75rem 0;
            padding: 1.25rem 1rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            text-align: center;
        }
        .lp-rx-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.875rem;
        }
        .lp-rx-btns {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        }
        .lp-rx-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
            padding: 0.625rem 0.875rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s, transform 0.12s, box-shadow 0.15s;
            min-width: 72px;
            flex: 0 1 auto;
            user-select: none;
        }
        .lp-rx-btn:hover {
            border-color: #93c5fd;
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.12);
        }
        .lp-rx-btn.is-reacted {
            border-color: #3b82f6;
            background: #eff6ff;
            box-shadow: 0 2px 8px rgba(59,130,246,0.15);
        }
        .lp-rx-btn:active { transform: translateY(0); }
        .lp-rx-emoji { font-size: 1.375rem; line-height: 1.2; }
        .lp-rx-name  { font-size: 0.68rem; font-weight: 600; color: #64748b; white-space: nowrap; }
        .lp-rx-count { font-size: 0.72rem; font-weight: 700; color: #cbd5e1; min-height: 1em; }
        .lp-rx-btn.is-reacted .lp-rx-name  { color: #2563eb; }
        .lp-rx-btn.is-reacted .lp-rx-count { color: #3b82f6; }
        /* NYT 스킨 — 반응 버튼 오버라이드 */
        .nyt-skin .lp-rx-btn { border-radius: 0 !important; }
        .nyt-skin .lp-rx-btn.is-reacted { border-color: #000 !important; background: #f5f5f5 !important; }
        .nyt-skin .lp-rx-btn.is-reacted .lp-rx-name  { color: #121212 !important; }
        .nyt-skin .lp-rx-btn.is-reacted .lp-rx-count { color: #121212 !important; }
        /* AJ 스킨 — 반응 버튼 */
        .aj-reactions {
            margin: 1.75rem 0;
            padding: 1.125rem 1rem 1rem;
            background: var(--aj-stone);
            border: 1px solid var(--aj-border);
            border-radius: 10px;
            text-align: center;
        }
        .aj-reactions .lp-rx-btn {
            background: #fff;
            border-color: var(--aj-border);
        }
        .aj-reactions .lp-rx-btn:hover {
            border-color: var(--aj-amber);
            background: var(--aj-amber-lite);
            box-shadow: 0 4px 12px rgba(217,119,6,0.12);
        }
        .aj-reactions .lp-rx-btn.is-reacted {
            border-color: var(--aj-amber);
            background: var(--aj-amber-lite);
            box-shadow: 0 2px 8px rgba(217,119,6,0.15);
        }
        .aj-reactions .lp-rx-btn.is-reacted .lp-rx-name  { color: var(--aj-amber-dark); }
        .aj-reactions .lp-rx-btn.is-reacted .lp-rx-count { color: var(--aj-amber); }
        /* 다크모드 — 반응 버튼 */
        html.dark .lp-reactions { background: #182030; border-color: #334155; }
        html.dark .lp-rx-btn { background: #1e293b; border-color: #334155; }
        html.dark .lp-rx-btn:hover { background: #1e3a5f; border-color: #60a5fa; box-shadow: 0 4px 12px rgba(96,165,250,0.12); }
        html.dark .lp-rx-btn.is-reacted { background: #1e3a5f; border-color: #3b82f6; }
        html.dark .lp-rx-name  { color: #94a3b8; }
        html.dark .lp-rx-count { color: #475569; }
        html.dark .lp-rx-btn.is-reacted .lp-rx-name  { color: #93c5fd; }
        html.dark .lp-rx-btn.is-reacted .lp-rx-count { color: #60a5fa; }
        html.dark .aj-reactions { background: #1c1410; border-color: #3d3028; }
        html.dark .aj-reactions .lp-rx-btn { background: #231a12; border-color: #3d3028; }
        html.dark .aj-reactions .lp-rx-btn:hover { background: rgba(217,119,6,0.1); border-color: var(--aj-amber); box-shadow: none; }
        html.dark .aj-reactions .lp-rx-btn.is-reacted { background: rgba(217,119,6,0.12); border-color: var(--aj-amber); }
        html.dark .aj-reactions .lp-rx-btn.is-reacted .lp-rx-name  { color: var(--aj-amber); }
        html.dark .aj-reactions .lp-rx-btn.is-reacted .lp-rx-count { color: var(--aj-amber); }

        /* 댓글 목록 */
        .lp-cmts-list { list-style: none !important; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.75rem; }
        .lp-cmt-item  { list-style: none !important; margin: 0 !important; padding: 0 !important; }

        /* 들여쓰기 (대댓글) */
        ol.children, .lp-cmts-list .children, .lp-cmts-list ol.children {
            list-style: none !important;
            margin: 0.75rem 0 0 0 !important;
            padding: 0 0 0 2rem !important;
            border-left: 2px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        html.dark ol.children,
        html.dark .lp-cmts-list .children { border-left-color: #334155; }

        /* 댓글 카드 */
        .lp-cmt-body {
            padding: 1rem 1.125rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        /* 헤더 행: 아바타 + 이름/시간 + 답글 */
        .lp-cmt-header {
            display: flex; align-items: center; gap: 0.75rem;
            margin-bottom: 0.625rem;
        }
        .lp-cmt-avatar { flex-shrink: 0; }
        .lp-cmt-av-img { width: 40px; height: 40px; border-radius: 50%; display: block; }
        .lp-cmt-info { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 0.1rem; }
        .lp-cmt-author { font-size: 0.875rem; font-weight: 700; color: #1e293b; }
        .lp-cmt-author a { color: inherit; text-decoration: none; }
        .lp-cmt-author a:hover { color: #2563eb; text-decoration: underline; }
        .lp-cmt-time { font-size: 0.72rem; color: #94a3b8; }
        .lp-cmt-reply { flex-shrink: 0; }
        .lp-cmt-reply a {
            font-size: 0.72rem; font-weight: 600; color: #64748b;
            padding: 0.2rem 0.6rem; border: 1px solid #e2e8f0; border-radius: 9999px;
            text-decoration: none; transition: border-color 0.15s, color 0.15s, background 0.15s;
            white-space: nowrap;
        }
        .lp-cmt-reply a:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; }

        /* 승인 대기 */
        .lp-cmt-moderation {
            font-size: 0.75rem; color: #b45309;
            background: #fef3c7; border-radius: 5px;
            padding: 0.3rem 0.75rem; margin-bottom: 0.5rem;
        }

        /* 댓글 본문 */
        .lp-cmt-text { font-size: 0.9rem; color: #334155; line-height: 1.75; }
        .lp-cmt-text p { margin: 0 0 0.5em; }
        .lp-cmt-text p:last-child { margin-bottom: 0; }
        .lp-cmt-text a { color: #2563eb; text-decoration: underline; }

        /* 댓글 페이저 */
        .lp-cmts-pager { display: flex; gap: 0.5rem; margin-top: 1rem; font-size: 0.8rem; }
        .lp-cmts-pager a { color: #2563eb; text-decoration: none; padding: 0.25rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px; }
        .lp-cmts-pager a:hover { background: #eff6ff; }

        /* 댓글 폼 래퍼 */
        #respond {
            margin-top: 2rem; padding: 1.25rem 1.375rem;
            border: 1px solid #e2e8f0; border-radius: 10px;
            background: #f8fafc;
        }
        #respond .comment-reply-title {
            font-size: 0.9rem; font-weight: 800; color: #1e293b;
            margin: 0 0 1.25rem; display: flex; align-items: center; gap: 0.5rem;
        }
        #respond .comment-reply-title small { margin-left: auto; }
        #respond .comment-reply-title small a { font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-decoration: none; }
        #respond .comment-reply-title small a:hover { color: #ef4444; }

        /* 이름·이메일 가로 배열 */
        .lp-cmt-meta-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
        @media (max-width: 480px) { .lp-cmt-meta-fields { grid-template-columns: 1fr; } }

        /* 각 입력 필드 */
        .lp-cmt-field { margin: 0 0 0.875rem; }
        .lp-cmt-field label { display: block; font-size: 0.75rem; font-weight: 700; color: #475569; margin-bottom: 0.3rem; }
        .lp-cmt-field .required { color: #ef4444; margin-left: 0.1em; }
        .lp-cmt-field input[type="text"],
        .lp-cmt-field input[type="email"],
        .lp-cmt-field textarea {
            width: 100%; box-sizing: border-box;
            padding: 0.55rem 0.875rem;
            font-size: 0.9rem; font-family: inherit;
            border: 1.5px solid #e2e8f0; border-radius: 7px;
            background: #fff; color: #1e293b;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .lp-cmt-field input:focus,
        .lp-cmt-field textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
        .lp-cmt-field textarea { resize: vertical; min-height: 110px; line-height: 1.65; }
        .lp-cmt-field--full { margin-top: 0.25rem; }

        /* 제출 행 */
        .lp-cmt-submit-row { margin: 0; display: flex; align-items: center; gap: 1rem; }
        .lp-cmt-submit {
            padding: 0.6rem 1.75rem;
            background: #2563eb; color: #fff;
            font-size: 0.875rem; font-weight: 700;
            border: none; border-radius: 7px; cursor: pointer;
            transition: background 0.15s;
        }
        .lp-cmt-submit:hover { background: #1d4ed8; }
        #cancel-comment-reply-link { font-size: 0.8rem; color: #94a3b8; text-decoration: none; }
        #cancel-comment-reply-link:hover { color: #ef4444; }
        .lp-cmt-password-notice { color: #64748b; font-size: 0.875rem; }

        /* NYT 스킨 오버라이드 */
        .nyt-skin .lp-cmts-heading { border-bottom-color: #000; }
        .nyt-skin .lp-cmts-count { background: #000; }
        .nyt-skin .lp-cmt-reply a:hover { border-color: #000; color: #000; background: #f4f4f4; }
        .nyt-skin #respond .comment-reply-title { border-bottom-color: #000; }
        .nyt-skin .lp-cmt-field input:focus,
        .nyt-skin .lp-cmt-field textarea:focus { border-color: #000; box-shadow: none; }
        .nyt-skin .lp-cmt-submit { background: #000; }
        .nyt-skin .lp-cmt-submit:hover { background: #222; }

        /* Basic 스킨 오버라이드 */
        .basic-skin .lp-cmts-heading { border-bottom-color: #212529; }
        .basic-skin .lp-cmts-count { background: #0d6efd; }
        .basic-skin .lp-cmt-reply a:hover { border-color: #0d6efd; color: #0d6efd; background: #eef3ff; }
        .basic-skin .lp-cmt-field input:focus,
        .basic-skin .lp-cmt-field textarea:focus { border-color: #0d6efd; box-shadow: 0 0 0 3px rgba(13,110,253,0.1); }
        .basic-skin .lp-cmt-submit { background: #0d6efd; }
        .basic-skin .lp-cmt-submit:hover { background: #0b5ed7; }

        /* 다크모드 */
        html.dark .lp-cmts-heading { color: #f1f5f9; border-bottom-color: #e2e8f0; }
        html.dark .lp-cmt-body { background: #243044; border-color: #334155; }
        html.dark .lp-cmt-author { color: #e2e8f0; }
        html.dark .lp-cmt-author a:hover { color: #93c5fd; }
        html.dark .lp-cmt-time { color: #64748b; }
        html.dark .lp-cmt-reply a { border-color: #334155; color: #94a3b8; }
        html.dark .lp-cmt-reply a:hover { border-color: #60a5fa; color: #60a5fa; background: #1e3a5f; }
        html.dark .lp-cmt-text { color: #cbd5e1; }
        html.dark .lp-cmt-text a { color: #60a5fa; }
        html.dark #respond { background: #182030; border-color: #334155; }
        html.dark #respond .comment-reply-title { color: #e2e8f0; }
        html.dark .lp-cmt-field label { color: #94a3b8; }
        html.dark .lp-cmt-field input[type="text"],
        html.dark .lp-cmt-field input[type="email"],
        html.dark .lp-cmt-field textarea { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        html.dark .lp-cmt-field input:focus,
        html.dark .lp-cmt-field textarea:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96,165,250,0.15); }

        /* ── 페이지네이션 (검색 + 아카이브 공통) ─────────── */
        .lp-pagination { display: flex; justify-content: center; gap: 0.25rem; flex-wrap: wrap; margin-top: 2.5rem; }
        .lp-pagination a, .lp-pagination span {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2.25rem; height: 2.25rem; padding: 0 0.5rem;
            border: 1px solid #e2e8f0; border-radius: 0.375rem;
            font-size: 0.875rem; color: #475569; text-decoration: none; transition: all 0.15s;
        }
        .lp-pagination a:hover { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
        .lp-pagination .current { background: #2563eb; border-color: #2563eb; color: #fff; font-weight: 700; }
        .nyt-skin .lp-pagination a:hover { background: #f4f4f4; border-color: #000; color: #000; }
        .nyt-skin .lp-pagination .current { background: #000; border-color: #000; }

        /* ══════════════════════════════════════════════════
           로고 공통 스타일
           ══════════════════════════════════════════════════ */
        .lp-logo-wrap { display: inline-block; line-height: 1; text-decoration: none; }
        .lp-logo-wrap img { display: block; }
        .lp-logo-wrap svg { display: block; width: 100%; height: auto; }
        .lp-logo-name-sub {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 500;
        }
        .lp-swn-logo { text-decoration: none; }

        /* ══════════════════════════════════════════════════
           모바일 햄버거 메뉴 (전 스킨 공통)
           ══════════════════════════════════════════════════ */
        .lp-hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
            width: 36px;
            height: 36px;
            padding: 6px;
            background: none;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
        }
        .lp-hamburger span {
            display: block;
            height: 2px;
            background: currentColor;
            border-radius: 2px;
            transition: transform 0.22s ease, opacity 0.22s ease;
        }
        .lp-hamburger.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .lp-hamburger.is-open span:nth-child(2) { opacity: 0; }
        .lp-hamburger.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* 모바일 드롭다운 — 전역 기본값 숨김 */
        .lp-mobile-nav { display: none; }
        .lp-gnav-swn { position: relative; }

        @media (max-width: 767px) {
            .lp-hamburger { display: flex; }
            /* SWN: 햄버거가 헤더 줄로 이동해 nav 줄엔 숨겨진 데스크톱 메뉴만 남음 — 패딩 접고 그림자 제거 */
            .lp-gnav-swn .primary-menu-container { padding-top: 0; padding-bottom: 0; gap: 0; }
            .lp-gnav-swn { box-shadow: none; }
            /* 다국어 버튼: 모바일에서는 아이콘만 (다크모드/햄버거와 동일한 36px 정사각形) */
            .lp-lang-btn { width: 36px; padding: 0; gap: 0; }
            .lp-lang-current { display: none; }
            .lp-mobile-nav {
                position: absolute;
                top: 100%;
                left: 0; right: 0;
                background: #fff;
                border-top: 1px solid #e2e8f0;
                box-shadow: 0 8px 24px rgba(0,0,0,0.1);
                z-index: 100;
                padding: 0.5rem 0;
            }
            .lp-mobile-nav.is-open { display: block; }
            .lp-mobile-nav ul {
                display: flex !important;
                flex-direction: column !important;
                gap: 0 !important;
                padding: 0;
                margin: 0;
                list-style: none;
            }
            .lp-mobile-nav ul li a {
                display: block;
                padding: 0.75rem 1.25rem;
                font-size: 0.9375rem;
                font-weight: 600;
                border-bottom: 1px solid #f1f5f9;
            }
            .lp-mobile-nav ul li a::after { display: none !important; }
            .lp-desktop-nav { display: none; }
        }
        @media (min-width: 768px) {
            .lp-desktop-nav { display: flex; align-items: center; flex: 1; }
        }

        /* ══════════════════════════════════════════════════
           드롭다운 메뉴 — SWN · NYT · Basic 공통
           ══════════════════════════════════════════════════ */

        /* 데스크탑 드롭다운 */
        .lp-desktop-nav .menu-item-has-children { position: relative; }
        .lp-desktop-nav .menu-item-has-children > a { padding-right: 1.25em; }
        .lp-desktop-nav ul.sub-menu {
            position: absolute; top: calc(100% + 4px); left: 0; z-index: 9999;
            display: none; flex-direction: column; gap: 0 !important;
            flex-wrap: nowrap !important; justify-content: flex-start !important;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            min-width: 168px; padding: 0.375rem 0; overflow: visible;
        }
        .lp-desktop-nav .menu-item-has-children:hover > ul.sub-menu { display: flex; }
        /* 서브메뉴 항목 — 모든 스킨별 상속 스타일 리셋 */
        .lp-desktop-nav ul.sub-menu li {
            width: 100%; position: relative;
            border: none !important; flex-shrink: 1 !important;
        }
        .lp-desktop-nav ul.sub-menu li a {
            display: block !important;
            padding: 0.5rem 1.125rem !important;
            font-size: 0.875rem !important; font-weight: 500 !important;
            color: #1e293b !important; white-space: nowrap;
            text-transform: none !important; letter-spacing: 0 !important;
            border-bottom: none !important; margin-bottom: 0 !important;
            transition: background 0.12s, color 0.12s;
        }
        .lp-desktop-nav ul.sub-menu li a::after { display: none !important; }
        .lp-desktop-nav ul.sub-menu li a:hover {
            background: #f1f5f9 !important; color: #2563eb !important;
        }
        /* 2단계 서브메뉴 — 오른쪽으로 */
        .lp-desktop-nav ul.sub-menu ul.sub-menu { top: -0.375rem; left: 100%; }
        /* 화살표 스팬 (JS 주입) */
        .lp-dd-caret {
            display: inline-flex; align-items: center; margin-left: 0.2em;
            vertical-align: middle; opacity: 0.5; pointer-events: none; line-height: 1;
        }

        /* NYT 스킨 드롭다운 색상 */
        .nyt-skin .lp-desktop-nav ul.sub-menu {
            background: #fff; border-color: #ccc;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
        }
        .nyt-skin .lp-desktop-nav ul.sub-menu li a { color: #121212 !important; }
        .nyt-skin .lp-desktop-nav ul.sub-menu li a:hover {
            background: #f4f4f4 !important; color: #000 !important;
        }
        /* Basic 스킨 드롭다운 색상 */
        .basic-skin .lp-desktop-nav ul.sub-menu { border-color: #dee2e6; }
        .basic-skin .lp-desktop-nav ul.sub-menu li a { color: #212529 !important; }
        .basic-skin .lp-desktop-nav ul.sub-menu li a:hover {
            background: #f8f9fa !important; color: #0d6efd !important;
        }

        /* 모바일 서브메뉴 토글 */
        .lp-mobile-nav .menu-item-has-children { position: relative; }
        .lp-mobile-nav .menu-item-has-children > a { padding-right: 2.75rem; }
        .lp-mobile-nav ul.sub-menu { display: none !important; }
        .lp-mobile-nav ul.sub-menu.is-open { display: block !important; }
        .lp-mobile-nav ul.sub-menu li a {
            padding-left: 2rem !important; font-size: 0.875rem !important;
            border-top: 1px solid rgba(0,0,0,.06); border-bottom: none !important;
        }
        .lp-mobile-nav ul.sub-menu li:last-child a { border-bottom: 1px solid #f1f5f9 !important; }
        /* 모바일 토글 버튼 (JS 주입) */
        .lp-mob-dd-caret {
            position: absolute; right: 0; top: 0; height: 100%; min-height: 2.75rem;
            background: none; border: none; cursor: pointer;
            padding: 0 0.875rem; color: inherit; opacity: 0.55;
            display: flex; align-items: center; justify-content: center;
        }
        .lp-mob-dd-caret svg { transition: transform 0.2s ease; }
        .lp-mob-dd-caret.is-open svg { transform: rotate(180deg); }
        /* NYT 모바일 서브메뉴 구분선 */
        .nyt-skin .lp-mobile-nav ul.sub-menu li a { border-top-color: rgba(0,0,0,.1); }
        /* Basic 모바일 서브메뉴 */
        .basic-skin .lp-mobile-nav ul.sub-menu li a { border-top-color: rgba(0,0,0,.08); }
        .basic-skin .lp-mobile-nav ul.sub-menu li a:hover { color: #0d6efd; }

        /* ══════════════════════════════════════════════════
           Basic 스킨 (basic-skin) 오버라이드
           ══════════════════════════════════════════════════ */
        .basic-skin { background: #f8f9fa; color: #212529; font-family: -apple-system, BlinkMacSystemFont, 'Noto Sans KR', sans-serif; }

        .basic-header-accent {
            height: 4px;
            background: linear-gradient(90deg, #0d6efd, #6ea8fe);
        }
        .basic-header-main {
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.85rem 0;
        }
        .basic-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .basic-logo {
            font-size: clamp(1.3rem, 3vw, 1.75rem);
            font-weight: 800;
            color: #212529;
            text-decoration: none;
            letter-spacing: -0.03em;
            line-height: 1;
            flex-shrink: 0;
        }
        .basic-logo:hover { color: #0d6efd; }
        .basic-logo .basic-logo-badge {
            font-size: 0.55em;
            font-weight: 600;
            color: #6c757d;
            vertical-align: super;
            margin-left: 0.15em;
        }
        .basic-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .basic-search-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border: 1.5px solid #ced4da;
            border-radius: 6px;
            background: none;
            color: #495057;
            cursor: pointer;
            transition: border-color 0.15s, color 0.15s, background 0.15s;
        }
        .basic-search-btn:hover { border-color: #0d6efd; color: #0d6efd; background: #eef3ff; }
        .basic-search-drop {
            display: none;
            position: absolute;
            top: 100%;
            left: 0; right: 0;
            background: #fff;
            border-top: 2px solid #0d6efd;
            border-bottom: 1px solid #dee2e6;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            z-index: 200;
            padding: 1rem;
        }
        .basic-search-drop.is-open { display: block; }
        .basic-search-drop form { display: flex; gap: 0.5rem; max-width: 640px; margin: 0 auto; }
        .basic-search-drop input {
            flex: 1;
            border: 1.5px solid #ced4da;
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            font-size: 0.9375rem;
            outline: none;
            transition: border-color 0.15s;
        }
        .basic-search-drop input:focus { border-color: #0d6efd; }
        .basic-search-drop button[type="submit"] {
            padding: 0.5rem 1.25rem;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.875rem;
            transition: background 0.15s;
        }
        .basic-search-drop button[type="submit"]:hover { background: #0b5ed7; }
        .basic-nav {
            background: #fff;
            border-bottom: 2px solid #212529;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .basic-nav .lp-desktop-nav {
            gap: 0;
            overflow-x: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .basic-nav .lp-desktop-nav::-webkit-scrollbar { display: none; }
        .basic-nav .primary-menu-container ul {
            display: flex;
            gap: 0 !important;
            flex-wrap: nowrap;
            padding: 0;
        }
        .basic-nav .primary-menu-container ul a {
            display: block;
            padding: 0.9rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #343a40;
            text-decoration: none;
            white-space: nowrap;
            letter-spacing: 0.01em;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color 0.15s, border-color 0.15s;
        }
        .basic-nav .primary-menu-container ul a::after { display: none !important; }
        .basic-nav .primary-menu-container ul a:hover { color: #0d6efd; border-bottom-color: #0d6efd; }
        .basic-nav .lp-hamburger { color: #212529; }
        .basic-nav .lp-mobile-nav ul li a { color: #343a40; }
        .basic-nav .lp-mobile-nav ul li a:hover { color: #0d6efd; }
        .admin-bar .basic-nav { top: 32px !important; }
        @media screen and (max-width: 782px) {
            .admin-bar .basic-nav { top: 46px !important; }
        }
        .basic-skin main { background: #f8f9fa; }
        .basic-content-area {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1.5rem;
        }
        @media (min-width: 768px) { .basic-content-area { padding: 2rem 2.5rem; } }
        .basic-skin .lp-section-heading {
            font-size: 1rem;
            font-weight: 700;
            color: #212529;
            border-left: 3px solid #0d6efd;
            padding-left: 0.625rem;
            margin-bottom: 1rem;
        }
        .basic-skin aside .bg-white { border-radius: 4px; border-color: #dee2e6; }
        .basic-skin .lp-cat-link:hover { color: #0d6efd; }
        .basic-skin .lp-cat-link:hover .lp-cat-count { background: #dbeafe; color: #0d6efd; }
        .basic-skin .lp-cat-item:hover .lp-cat-bullet { color: #0d6efd; }
        .basic-skin a:hover { color: #0d6efd; }
        .basic-skin aside button[type="submit"] { background: #212529; }
        .basic-skin aside button[type="submit"]:hover { background: #0d6efd; }
        .basic-skin #lp-progress-bar { background: linear-gradient(90deg, #0d6efd, #6ea8fe); }
        .basic-skin #lp-sticky-bar .lp-sb-logo:hover { color: #0d6efd; }
        .basic-skin #lp-sticky-bar .lp-sb-title { color: #212529; }
        .basic-skin .lp-font-ctrl button.lp-fz-active { background: #eef3ff; color: #0d6efd; }
        .basic-skin .lp-font-ctrl button:hover { color: #0d6efd; }
        .basic-footer {
            background: #212529;
            color: #adb5bd;
            padding: 2.5rem 0 1.5rem;
            margin-top: auto;
        }
        .basic-footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #343a40;
            margin-bottom: 1.5rem;
        }
        @media (min-width: 640px) {
            .basic-footer-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (min-width: 1024px) {
            .basic-footer-grid { grid-template-columns: 2fr 1fr 1fr; }
        }
        .basic-footer-logo { font-size: 1.375rem; font-weight: 800; color: #fff; letter-spacing: -0.03em; display: block; margin-bottom: 0.5rem; text-decoration: none; }
        .basic-footer-desc { font-size: 0.8125rem; color: #6c757d; line-height: 1.6; }
        .basic-footer-col h3 { font-size: 0.8125rem; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; }
        .basic-footer-col ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.375rem; }
        .basic-footer-col ul a { font-size: 0.8125rem; color: #6c757d; text-decoration: none; transition: color 0.15s; }
        .basic-footer-col ul a:hover { color: #fff; }
        .basic-footer-legal { font-size: 0.75rem; color: #6c757d; }
        .basic-footer-legal p { margin-bottom: 0.375rem; display: flex; flex-wrap: wrap; gap: 0.25rem 0.75rem; }
        .basic-footer-legal strong { color: #adb5bd; }
        .basic-footer-copy { margin-top: 1rem; font-size: 0.75rem; color: #495057; }

        /* ════════════════════════════════════════════════
           엠버 저널 스킨 (Amber Journal)
           ════════════════════════════════════════════════ */
        :root {
            --aj-amber:      #D97706;
            --aj-amber-dark: #92400E;
            --aj-amber-lite: #FEF3C7;
            --aj-stone:      #1C1917;
            --aj-border:     #E5E7EB;
            --aj-muted:      #6B7280;
        }

        /* — 탑 유틸리티 바 — */
        .aj-topbar {
            background: var(--aj-stone);
            color: #d4c8bb;
            font-size: 0.72rem;
            padding: 0.35rem 0;
            letter-spacing: 0.01em;
        }
        .aj-topbar a { color: #d4c8bb; text-decoration: none; }
        .aj-topbar a:hover { color: #fff; }
        .aj-topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .aj-topbar-date { opacity: 0.75; }
        .aj-topbar-util { display: flex; align-items: center; gap: 0.875rem; }
        .aj-topbar-util a { display: flex; align-items: center; gap: 0.3rem; }

        /* — 마스트헤드 (로고 + 배너) — */
        .aj-masthead {
            padding: 1.25rem 0 1rem;
            background: #fff;
            border-bottom: 1px solid var(--aj-border);
        }
        .aj-masthead-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }
        .aj-logo-link { text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        /* 이미지·SVG 로고 선택 시 lp-logo-wrap을 aj-masthead-inner 안에서 정렬 */
        .aj-masthead-inner .lp-logo-wrap { display: flex; align-items: center; text-decoration: none; }
        .aj-logo-mark {
            width: 2.25rem; height: 2.25rem;
            background: linear-gradient(135deg, var(--aj-amber) 0%, var(--aj-amber-dark) 100%);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 900; font-size: 1.1rem; letter-spacing: -0.05em;
            flex-shrink: 0;
        }
        .aj-logo-text {
            font-size: 1.6rem;
            font-weight: 900;
            color: #111;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .aj-logo-text span { color: var(--aj-amber); }
        .aj-masthead-actions { display: flex; align-items: center; gap: 0.75rem; }
        .aj-search-btn {
            background: none; border: 1px solid var(--aj-border);
            border-radius: 9999px; padding: 0.35rem 0.9rem;
            font-size: 0.8rem; color: var(--aj-muted);
            cursor: pointer; display: flex; align-items: center; gap: 0.4rem;
            transition: border-color 0.15s, color 0.15s;
        }
        .aj-search-btn:hover { border-color: var(--aj-amber); color: var(--aj-amber); }
        .aj-masthead-banner {
            flex: 1; max-width: 500px; height: 80px;
            background: #f9f5f0; border: 1px dashed #d4c8bb;
            display: flex; align-items: center; justify-content: center;
            color: #bbb; font-size: 0.8rem; border-radius: 6px;
        }

        /* — 메인 네비게이션 (GNB) — */
        .aj-gnav {
            background: #fff;
            border-bottom: 2px solid var(--aj-stone);
            position: sticky; top: 0; z-index: 50;
        }
        .aj-gnav-inner {
            display: flex;
            align-items: stretch;
            gap: 0;
            position: relative;
        }
        .aj-gnav ul {
            list-style: none; margin: 0; padding: 0;
            display: flex; align-items: stretch; gap: 0;
        }
        .aj-gnav ul li { position: relative; }
        .aj-gnav ul li a {
            display: block;
            padding: 0.875rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: #222;
            text-decoration: none;
            letter-spacing: -0.01em;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color 0.15s, border-color 0.15s;
        }
        .aj-gnav ul li a:hover,
        .aj-gnav ul li.current-menu-item > a,
        .aj-gnav ul li.current-menu-ancestor > a {
            color: var(--aj-amber);
            border-bottom-color: var(--aj-amber);
        }
        /* 드롭다운 */
        .aj-gnav ul li ul {
            display: none; position: absolute; top: 100%; left: 0;
            background: #fff; border: 1px solid var(--aj-border);
            border-top: 2px solid var(--aj-amber);
            min-width: 160px; flex-direction: column;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08); z-index: 100;
        }
        .aj-gnav ul li:hover > ul { display: flex; }
        .aj-gnav ul li ul li a {
            padding: 0.6rem 1rem; font-size: 0.85rem; font-weight: 500;
            border-bottom: 1px solid var(--aj-border); margin-bottom: 0; border-left: none;
        }
        .aj-gnav ul li ul li:last-child a { border-bottom: none; }
        /* 모바일 햄버거 */
        .aj-hamburger-btn {
            display: none; margin-left: auto;
            background: none; border: none; cursor: pointer;
            padding: 0.6rem; flex-direction: column; gap: 4px;
        }
        .aj-hamburger-btn span {
            display: block; width: 22px; height: 2px;
            background: #333; border-radius: 2px;
            transition: all 0.2s;
        }
        @media (max-width: 767px) {
            .aj-hamburger-btn { display: flex; }
            .aj-gnav-inner .aj-gnav-menu { display: none; }
            .aj-gnav-inner .aj-gnav-menu.is-open {
                display: flex; flex-direction: column; position: absolute;
                top: 100%; left: 0; right: 0; background: #fff;
                border-top: 1px solid var(--aj-border);
                box-shadow: 0 8px 20px rgba(0,0,0,0.1); z-index: 99;
            }
            .aj-gnav-inner .aj-gnav-menu.is-open ul { flex-direction: column; }
            .aj-gnav-inner .aj-gnav-menu.is-open ul li a { border-bottom: 1px solid var(--aj-border); }
            .aj-gnav ul li ul { display: none !important; }
        }

        /* — 속보 배너 — */
        .aj-breaking {
            background: var(--aj-stone);
            color: #f5ede5;
            padding: 0.55rem 0;
            overflow: hidden;
        }
        .aj-breaking-inner {
            display: flex; align-items: center; gap: 1rem;
        }
        .aj-breaking-label {
            flex-shrink: 0;
            background: var(--aj-amber);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            padding: 0.2rem 0.6rem;
            border-radius: 3px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .aj-breaking-items {
            display: flex; gap: 1.5rem; overflow: hidden;
            flex: 1;
        }
        .aj-breaking-item {
            display: flex; align-items: center; gap: 0.5rem;
            flex-shrink: 0;
            max-width: 220px;
        }
        .aj-breaking-thumb {
            width: 36px; height: 36px; border-radius: 4px;
            object-fit: cover; flex-shrink: 0;
            background: #3d3530;
        }
        .aj-breaking-title {
            font-size: 0.78rem; font-weight: 500;
            color: #f5ede5; line-height: 1.3;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
            text-decoration: none;
        }
        .aj-breaking-title:hover { color: var(--aj-amber); }

        /* — 메인 레이아웃 — */
        .aj-main { padding: 2rem 0; background: #fafaf9; }
        .aj-layout { display: grid; grid-template-columns: 1fr 300px; gap: 2rem; }
        @media (max-width: 1023px) { .aj-layout { grid-template-columns: 1fr; } }

        /* — 기사 카드 (메인 컬럼) — */
        .aj-article-list { background: #fff; border: 1px solid var(--aj-border); border-radius: 8px; overflow: hidden; }
        .aj-article-card {
            display: flex; gap: 1rem; padding: 1.125rem 1.25rem;
            border-bottom: 1px solid var(--aj-border);
            transition: background 0.1s;
        }
        .aj-article-card:last-child { border-bottom: none; }
        .aj-article-card:hover { background: #fffbf5; }
        .aj-card-body { flex: 1; min-width: 0; }
        .aj-card-meta { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.375rem; }
        .aj-cat-tag {
            display: inline-block;
            background: var(--aj-amber-lite);
            color: var(--aj-amber-dark);
            font-size: 0.68rem; font-weight: 700;
            padding: 0.15rem 0.5rem; border-radius: 3px;
            text-decoration: none; letter-spacing: 0.02em;
        }
        .aj-cat-tag:hover { background: var(--aj-amber); color: #fff; }
        .aj-card-byline { font-size: 0.72rem; color: var(--aj-muted); }
        .aj-card-title {
            font-size: 1.05rem; font-weight: 700;
            color: #111; line-height: 1.4; margin-bottom: 0.3rem;
            text-decoration: none; display: block;
            letter-spacing: -0.02em;
        }
        .aj-card-title:hover { color: var(--aj-amber); }
        .aj-card-excerpt {
            font-size: 0.8rem; color: var(--aj-muted);
            line-height: 1.55;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .aj-card-thumb {
            width: 100px; height: 100px; flex-shrink: 0;
            border-radius: 6px; object-fit: cover; background: #f3ede5;
        }
        @media (max-width: 479px) { .aj-card-thumb { width: 76px; height: 76px; } }

        /* — 전면 기사 그리드 — */
        .aj-featured-section {
            margin-bottom: 2rem; padding: 1.25rem 1.375rem 1.5rem;
            background: #fff;
            border: 1px solid var(--aj-border); border-radius: 8px;
        }
        .aj-featured-grid {
            display: grid;
            grid-template-columns: 1fr 2.25fr 1fr;
            gap: 0; align-items: start;
        }
        /* 좌우 사이드 */
        .aj-featured-side { display: flex; flex-direction: column; gap: 0; }
        .aj-feat-sm {
            padding: 0 1rem 1rem; border-bottom: 1px solid var(--aj-border);
        }
        .aj-feat-sm:last-child { border-bottom: none; padding-bottom: 0; padding-top: 1rem; }
        .aj-feat-sm:first-child { padding-top: 0; }
        .aj-feat-sm-thumb {
            width: 100%; aspect-ratio: 16/9; object-fit: cover;
            border-radius: 5px; display: block; margin-bottom: 0.5rem;
            background: var(--aj-stone);
        }
        .aj-feat-sm-cat {
            display: inline-block; font-size: 0.64rem; font-weight: 800;
            color: var(--aj-amber); letter-spacing: 0.04em;
            text-decoration: none; margin-bottom: 0.25rem;
        }
        .aj-feat-sm-title {
            display: -webkit-box; -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; overflow: hidden;
            font-size: 0.875rem; font-weight: 700; color: #111;
            line-height: 1.45; text-decoration: none;
            letter-spacing: -0.02em; margin-bottom: 0.25rem;
        }
        .aj-feat-sm-title:hover { color: var(--aj-amber); }
        .aj-feat-sm-date { font-size: 0.68rem; color: var(--aj-muted); }
        /* 센터 메인 카드 */
        .aj-feat-center {
            padding: 0 1.25rem;
            border-left: 1px solid var(--aj-border);
            border-right: 1px solid var(--aj-border);
        }
        .aj-feat-main-thumb {
            width: 100%; aspect-ratio: 3/2; object-fit: cover;
            border-radius: 6px; display: block; margin-bottom: 0.875rem;
            background: var(--aj-stone);
        }
        .aj-feat-main-cat {
            display: inline-block; font-size: 0.72rem; font-weight: 800;
            color: var(--aj-amber); letter-spacing: 0.04em;
            text-decoration: none; margin-bottom: 0.4rem;
        }
        .aj-feat-main-title {
            display: block; font-size: 1.45rem; font-weight: 900;
            color: #111; line-height: 1.3; text-decoration: none;
            letter-spacing: -0.03em; margin-bottom: 0.5rem;
        }
        .aj-feat-main-title:hover { color: var(--aj-amber); }
        .aj-feat-main-excerpt {
            font-size: 0.875rem; color: #555; line-height: 1.65;
            margin-bottom: 0.5rem;
            display: -webkit-box; -webkit-line-clamp: 3;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .aj-feat-main-date { font-size: 0.72rem; color: var(--aj-muted); }
        /* 태블릿 */
        @media (max-width: 900px) {
            .aj-featured-grid { grid-template-columns: 1fr 1.75fr 1fr; }
            .aj-feat-main-title { font-size: 1.2rem; }
        }
        /* 모바일 */
        @media (max-width: 639px) {
            .aj-featured-grid { grid-template-columns: 1fr 1fr; gap: 0.75rem; }
            .aj-feat-center {
                grid-column: 1 / -1; order: -1;
                border: none; padding: 0 0 1rem;
                border-bottom: 1px solid var(--aj-border);
            }
            .aj-feat-main-thumb { aspect-ratio: 16/9; }
            .aj-feat-main-title { font-size: 1.1rem; }
            .aj-featured-side { display: contents; }
            .aj-feat-sm { padding: 0; border: none; }
        }
        @media (max-width: 419px) {
            .aj-featured-grid { grid-template-columns: 1fr; }
            .aj-featured-side { display: flex; flex-direction: column; gap: 0.75rem; }
            .aj-feat-sm { padding: 0; border: none; }
        }

        /* ── AJ 홈 위젯 블록 */
        .aj-widget-block {
            background: #fff;
            border: 1px solid var(--aj-border);
            border-radius: 8px;
            padding: 1.25rem 1.375rem 1.5rem;
            margin-bottom: 1rem;
        }
        /* 색상 오버라이드 — amber-skin 전용 */
        .amber-skin .aj-widget-block .text-blue-700 { color: var(--aj-amber-dark) !important; }
        .amber-skin .aj-widget-block .text-blue-600,
        .amber-skin .aj-widget-block [class*="text-blue"] { color: var(--aj-amber) !important; }
        .amber-skin .aj-widget-block a.hover\:text-blue-600:hover,
        .amber-skin .aj-widget-block a.hover\:text-blue-500:hover { color: var(--aj-amber) !important; }
        .amber-skin .aj-widget-block .group:hover h3,
        .amber-skin .aj-widget-block .group:hover h3 a { color: var(--aj-amber) !important; }
        .amber-skin .aj-widget-block .border-slate-200 { border-color: var(--aj-border) !important; }
        .amber-skin .aj-widget-block .bg-slate-100 { background-color: #f3ede5 !important; }
        .amber-skin .aj-widget-block .text-slate-800 { color: #111 !important; }
        .amber-skin .aj-widget-block .text-slate-700 { color: #333 !important; }
        .amber-skin .aj-widget-block .text-slate-500,
        .amber-skin .aj-widget-block .text-slate-400 { color: var(--aj-muted) !important; }

        /* — 사이드바 (오피니언) — */
        .aj-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }
        .aj-widget {
            background: #fff; border: 1px solid var(--aj-border);
            border-radius: 8px; overflow: hidden;
        }
        .aj-widget-head {
            padding: 0.75rem 1rem;
            border-bottom: 2px solid var(--aj-stone);
            display: flex; align-items: center; gap: 0.5rem;
        }
        .aj-widget-title {
            font-size: 0.9rem; font-weight: 800;
            color: #111; letter-spacing: -0.02em;
        }
        .aj-widget-mark {
            width: 3px; height: 0.9rem;
            background: var(--aj-amber); border-radius: 2px;
        }
        /* 오피니언 리스트 */
        .aj-opinion-list { list-style: none; margin: 0; padding: 0; }
        .aj-opinion-item {
            display: flex; gap: 0.75rem; align-items: flex-start;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--aj-border);
        }
        .aj-opinion-item:last-child { border-bottom: none; }
        .aj-opinion-num {
            font-size: 0.85rem; font-weight: 900;
            color: var(--aj-amber); flex-shrink: 0; width: 1rem;
            line-height: 1.4;
        }
        .aj-opinion-text { min-width: 0; }
        .aj-opinion-link {
            font-size: 0.83rem; font-weight: 600; color: #222;
            text-decoration: none; line-height: 1.35;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .aj-opinion-link:hover { color: var(--aj-amber); }
        .aj-opinion-date { font-size: 0.7rem; color: var(--aj-muted); margin-top: 0.2rem; }
        /* 썸네일 위젯 */
        .aj-thumb-list { list-style: none; margin: 0; padding: 0; }
        .aj-thumb-item {
            display: flex; gap: 0.75rem; align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--aj-border);
        }
        .aj-thumb-item:last-child { border-bottom: none; }
        .aj-thumb-img-wrap {
            flex-shrink: 0; width: 52px; height: 52px;
            border-radius: 9999px; overflow: hidden;
            background: #f3ede5; display: block;
        }
        .aj-thumb-img-wrap.aj-thumb-no-img { background: #e8ddd3; }
        .aj-thumb-img {
            width: 52px; height: 52px; border-radius: 9999px;
            object-fit: cover; display: block;
        }
        /* AJ 카드 썸네일 링크 래퍼 */
        .aj-card-thumb-wrap { flex-shrink: 0; display: block; overflow: hidden; border-radius: 6px; }
        .aj-card-thumb-wrap .aj-card-thumb { display: block; width: 100px; height: 80px; object-fit: cover; }
        .aj-thumb-link {
            font-size: 0.82rem; font-weight: 600; color: #222;
            text-decoration: none; line-height: 1.35;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .aj-thumb-link:hover { color: var(--aj-amber); }

        /* — 엠버 저널 푸터 — */
        .aj-footer { background: var(--aj-stone); color: #a8998f; }
        .aj-footer-body {
            display: grid; grid-template-columns: 1.8fr 1fr 1fr;
            gap: 2rem; padding: 2.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        @media (max-width: 767px) { .aj-footer-body { grid-template-columns: 1fr; } }
        .aj-footer-logo-text {
            font-size: 1.3rem; font-weight: 900; color: #fff;
            letter-spacing: -0.03em; margin-bottom: 0.5rem;
        }
        .aj-footer-logo-text span { color: var(--aj-amber); }
        .aj-footer-desc { font-size: 0.78rem; line-height: 1.65; color: #9a8f87; margin-bottom: 1rem; }
        .aj-footer-legal { font-size: 0.72rem; color: #6b6059; line-height: 1.8; }
        .aj-footer-col-title {
            font-size: 0.8rem; font-weight: 800; color: #fff;
            text-transform: uppercase; letter-spacing: 0.08em;
            margin-bottom: 0.875rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .aj-footer-notice-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.5rem; }
        .aj-footer-notice-list a {
            font-size: 0.8rem; color: #a8998f; text-decoration: none;
            display: -webkit-box; -webkit-line-clamp: 1;
            -webkit-box-orient: vertical; overflow: hidden;
            transition: color 0.15s;
        }
        .aj-footer-notice-list a:hover { color: var(--aj-amber); }
        .aj-footer-notice-date { font-size: 0.68rem; color: #5e544d; margin-top: 0.1rem; }
        .aj-footer-bottom {
            padding: 1rem 0;
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
        }
        .aj-footer-copy { font-size: 0.72rem; color: #5e544d; }
        .aj-footer-socials { display: flex; gap: 0.6rem; }
        .aj-footer-social {
            width: 1.85rem; height: 1.85rem; border-radius: 9999px;
            background: rgba(255,255,255,0.07); color: #a8998f;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: background 0.15s, color 0.15s;
        }
        .aj-footer-social:hover { background: var(--aj-amber); color: #fff; }

        /* ── Amber Journal 스티키 바 오버라이드 ── */
        .amber-skin #lp-sticky-bar { border-bottom: 2px solid var(--aj-stone); }
        .amber-skin #lp-sticky-bar .lp-sb-logo { color: var(--aj-stone); }
        .amber-skin #lp-sticky-bar .lp-sb-logo:hover { color: var(--aj-amber); }
        .amber-skin #lp-sticky-bar .lp-sb-title { color: var(--aj-stone); }
        .amber-skin #lp-progress-bar { background: linear-gradient(90deg, var(--aj-amber), var(--aj-amber-dark)); }
        .amber-skin .lp-font-ctrl button.lp-fz-active { background: var(--aj-amber-lite); color: var(--aj-amber-dark); }
        .amber-skin .lp-font-ctrl button:hover { color: var(--aj-amber); }
        .amber-skin #lp-sticky-bar .lp-sb-sep,
        .amber-skin #lp-sticky-bar .lp-sb-share-sep { background: var(--aj-border); }

        /* ── Amber Journal 공유·유틸리티 툴바 ── */
        .aj-util-toolbar {
            display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;
        }
        .aj-util-sep { width: 1px; height: 16px; background: var(--aj-border); flex-shrink: 0; }
        .aj-util-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; border-radius: 6px; cursor: pointer;
            border: 1px solid var(--aj-border); background: #fff;
            color: var(--aj-muted); transition: border-color 0.15s, color 0.15s, background 0.15s;
            flex-shrink: 0; padding: 0;
        }
        .aj-util-btn:hover { border-color: var(--aj-amber); color: var(--aj-amber); background: var(--aj-amber-lite); }
        .aj-fz-ctrl {
            display: flex; align-items: stretch;
            border: 1px solid var(--aj-border); border-radius: 6px; overflow: hidden;
            flex-shrink: 0;
        }
        .aj-fz-btn {
            padding: 0 7px; font-weight: 700; background: #fff; border: none;
            color: var(--aj-muted); cursor: pointer; line-height: 2rem; height: 2rem;
            transition: background 0.15s, color 0.15s;
            font-size: 0.75rem;
        }
        .aj-fz-btn + .aj-fz-btn { border-left: 1px solid var(--aj-border); }
        .aj-fz-btn:hover { background: var(--aj-amber-lite); color: var(--aj-amber); }
        .aj-fz-btn.aj-fz-active { background: var(--aj-amber-lite); color: var(--aj-amber-dark); }

        /* ── Amber Journal 댓글 영역 ── */
        .aj-comments { margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--aj-stone); }
        .aj-comments-title { font-size: 1rem; font-weight: 800; color: #111; margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem; }
        .aj-comments-title .aj-widget-mark { flex-shrink: 0; }
        /* 댓글 목록 컨테이너 */
        .aj-comments .commentlist { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem; }
        /* lp_aj_comment_cb 콜백 전용 클래스 */
        .aj-comment-item { list-style: none; margin: 0; padding: 0; }
        .aj-comment-body {
            padding: 1rem 1.125rem; background: #fafaf9;
            border: 1px solid var(--aj-border); border-radius: 8px;
        }
        .aj-comment-item.bypostauthor > .aj-comment-body { border-left: 3px solid var(--aj-amber); }
        .aj-comment-author-row { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
        .aj-comment-avatar { border-radius: 9999px; width: 36px; height: 36px; flex-shrink: 0; }
        .aj-comment-meta-info { display: flex; flex-direction: column; gap: 0.1rem; flex: 1; min-width: 0; }
        .aj-comment-name { font-size: 0.875rem; font-weight: 700; color: #111; }
        .aj-comment-name a { color: inherit; text-decoration: none; }
        .aj-comment-time { font-size: 0.7rem; color: var(--aj-muted); }
        .aj-comment-reply { margin-left: auto; }
        .aj-comment-reply a {
            font-size: 0.72rem; font-weight: 700; color: var(--aj-amber);
            text-decoration: none; border: 1px solid var(--aj-amber-lite);
            padding: 0.15rem 0.5rem; border-radius: 4px; transition: background 0.15s;
        }
        .aj-comment-reply a:hover { background: var(--aj-amber-lite); }
        .aj-comment-text p { font-size: 0.875rem; line-height: 1.7; color: #333; margin: 0; }
        .aj-comment-moderation { font-size: 0.8rem; color: var(--aj-muted); font-style: italic; margin: 0.25rem 0; }
        /* 대댓글 */
        .aj-comments .children { list-style: none; padding-left: 1.25rem; margin-top: 0.625rem; display: flex; flex-direction: column; gap: 0.625rem; }
        /* 댓글 폼 — .aj-comment-form-wrap 이 comment_form() 출력의 외부 래퍼 */
        .aj-comment-form-wrap {
            margin-top: 1.5rem; padding: 1.25rem 1.375rem;
            border: 1px solid var(--aj-border); border-radius: 8px; background: #fff;
        }
        /* WP가 출력하는 #respond, h3#reply-title 리셋 */
        .aj-comment-form-wrap #respond { padding: 0; margin: 0; background: transparent; border: none; }
        .aj-comment-form-wrap #reply-title {
            font-size: 0.9rem; font-weight: 800; color: #111;
            margin: 0 0 1rem; padding: 0; border: none; background: none;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .aj-comment-form-wrap #reply-title small a {
            font-size: 0.72rem; font-weight: 600; color: var(--aj-muted);
            text-decoration: none; margin-left: 0.5rem;
        }
        /* 레이블 */
        .aj-comment-form-wrap .comment-form-comment label,
        .aj-comment-form-wrap .comment-form-author label,
        .aj-comment-form-wrap .comment-form-email label,
        .aj-comment-form-wrap .comment-form-url label {
            display: block; font-size: 0.75rem; font-weight: 700; color: #555; margin-bottom: 0.3rem;
        }
        /* 입력 필드 */
        .aj-comment-form-wrap input[type="text"],
        .aj-comment-form-wrap input[type="email"],
        .aj-comment-form-wrap input[type="url"],
        .aj-comment-form-wrap textarea {
            width: 100%; border: 1px solid var(--aj-border); border-radius: 6px;
            padding: 0.55rem 0.75rem; font-size: 0.875rem; color: #111;
            outline: none; font-family: inherit; transition: border-color 0.15s; background: #fff;
            box-sizing: border-box;
        }
        .aj-comment-form-wrap input[type="text"]:focus,
        .aj-comment-form-wrap input[type="email"]:focus,
        .aj-comment-form-wrap textarea:focus { border-color: var(--aj-amber); }
        .aj-comment-form-wrap textarea { min-height: 110px; resize: vertical; }
        /* 제출 버튼 */
        .aj-comment-submit {
            background: var(--aj-amber); color: #fff; border: none; border-radius: 6px;
            padding: 0.6rem 1.5rem; font-size: 0.875rem; font-weight: 700;
            cursor: pointer; margin-top: 0.625rem; transition: background 0.15s; display: inline-block;
        }
        .aj-comment-submit:hover { background: var(--aj-amber-dark); }
        /* 안내 문구 */
        .aj-comment-form-wrap p.comment-notes,
        .aj-comment-form-wrap .logged-in-as { font-size: 0.75rem; color: var(--aj-muted); margin-bottom: 0.875rem; }
        /* 이름/이메일 2열 그리드 */
        .aj-comment-form-wrap #comment-form-meta {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;
        }
        @media (max-width: 479px) { .aj-comment-form-wrap #comment-form-meta { grid-template-columns: 1fr; } }
        /* WP .comment-form-comment p 기본 마진 제거 */
        .aj-comment-form-wrap .comment-form-comment,
        .aj-comment-form-wrap .comment-form-author,
        .aj-comment-form-wrap .comment-form-email { margin: 0; }
        .aj-comment-form-wrap .form-submit { margin: 0; padding: 0; }

        /* ── 프린트 스타일 ── */
        @media print {
            .aj-single-layout { display: block !important; }
            .aj-sidebar, #lp-sticky-bar, .aj-single-nav, .aj-util-toolbar,
            .aj-gnav, .aj-masthead, .aj-topbar, .aj-breaking, .aj-footer,
            .aj-comments, nav, footer { display: none !important; }
            .aj-single-article { border: none !important; box-shadow: none !important; }
            .aj-article-content { font-size: 11pt !important; line-height: 1.75 !important; color: #000 !important; }
            .aj-single-title { font-size: 18pt !important; }
            .aj-single-hero { max-height: 300px !important; }
        }

        /* ── 엠버 저널 검색 모달 ── */
        .aj-search-modal {
            position: fixed; inset: 0; z-index: 2000;
            background: rgba(10,8,6,0.88);
            display: flex; align-items: flex-start;
            justify-content: center; padding-top: 14vh;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s;
        }
        .aj-search-modal.is-open { opacity: 1; pointer-events: auto; }
        .aj-search-modal-box {
            background: #fff; border-radius: 14px;
            padding: 2rem 2rem 1.75rem;
            width: 90%; max-width: 640px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
            position: relative;
        }
        .aj-search-modal-label {
            font-size: 0.8rem; font-weight: 700; color: var(--aj-muted);
            letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.875rem;
        }
        .aj-search-form { display: flex; gap: 0.5rem; }
        .aj-search-input {
            flex: 1; border: 2px solid var(--aj-border);
            border-radius: 8px; padding: 0.75rem 1rem;
            font-size: 1rem; color: #111; outline: none;
            transition: border-color 0.15s;
        }
        .aj-search-input:focus { border-color: var(--aj-amber); }
        .aj-search-submit {
            background: var(--aj-amber); color: #fff;
            border: none; border-radius: 8px;
            padding: 0 1.25rem; font-size: 0.9rem;
            font-weight: 700; cursor: pointer;
            transition: background 0.15s; white-space: nowrap;
        }
        .aj-search-submit:hover { background: var(--aj-amber-dark); }
        .aj-search-close-btn {
            position: absolute; top: 0.875rem; right: 0.875rem;
            background: none; border: none; cursor: pointer;
            color: #aaa; font-size: 1.25rem; line-height: 1;
            padding: 0.25rem; transition: color 0.15s; border-radius: 4px;
        }
        .aj-search-close-btn:hover { color: #111; }
        .aj-search-hint { margin-top: 0.875rem; font-size: 0.75rem; color: #bbb; }

        /* ── 엠버 저널 상세 페이지 ── */
        .aj-single { padding: 2rem 0; background: #fafaf9; }
        .aj-single-layout {
            display: grid; grid-template-columns: 1fr 300px; gap: 2rem;
        }
        @media (max-width: 1023px) { .aj-single-layout { grid-template-columns: 1fr; } }
        .aj-single-article {
            background: #fff; border: 1px solid var(--aj-border);
            border-radius: 8px; overflow: hidden;
        }
        .aj-single-hero { width: 100%; max-height: 420px; object-fit: cover; display: block; }
        .aj-single-hero-figure { margin: 0; }
        .aj-hero-caption { font-size: 0.8rem; color: var(--aj-muted); text-align: center; padding: 0.5rem 1.25rem 0; }
        .aj-single-body { padding: 1.75rem; }
        .aj-single-kicker { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.875rem; }
        .aj-single-title {
            font-size: 1.75rem; font-weight: 900; color: #111;
            line-height: 1.3; letter-spacing: -0.03em; margin-bottom: 1rem;
        }
        @media (max-width: 639px) { .aj-single-title { font-size: 1.375rem; } }
        .aj-single-meta {
            display: flex; align-items: center; gap: 0.875rem; flex-wrap: wrap;
            font-size: 0.78rem; color: var(--aj-muted);
            padding: 0.75rem 0;
            border-top: 1px solid var(--aj-border);
            border-bottom: 2px solid var(--aj-stone);
            margin-bottom: 1.75rem;
        }
        .aj-single-meta strong { color: #333; }
        .aj-article-content {
            font-size: 1.0rem; line-height: 1.9; color: #222;
        }
        .aj-article-content > * + * { margin-top: 1.7rem; }
        .aj-article-content h2 { font-size: 1.25rem; font-weight: 800; color: #111; margin-top: 2rem; }
        .aj-article-content h3 { font-size: 1.05rem; font-weight: 700; margin-top: 1.5rem; }
        .aj-article-content img { max-width: 100%; height: auto; border-radius: 6px; margin: 0.75rem 0; }
        .aj-article-content a { color: var(--aj-amber); text-decoration: underline; }
        .aj-article-content blockquote {
            border-left: 3px solid var(--aj-amber); padding: 0.75rem 1.25rem;
            background: var(--aj-amber-lite); border-radius: 0 6px 6px 0;
            font-style: italic; color: #555; margin: 1.5rem 0;
        }
        .aj-article-content ul, .aj-article-content ol { padding-left: 1.5rem; }
        .aj-article-content li { margin-bottom: 0.35rem; }
        .aj-article-content figure { margin: 1.25rem 0; }
        .aj-article-content figcaption { font-size: 0.8rem; color: var(--aj-muted); margin-top: 0.4rem; text-align: center; }
        /* About Us(753페이지) 보정 — 공용 .aj-article-content 스타일(링크 밑줄, 이미지 height:auto)이
           버튼형 링크·원형 프로필 사진에 그대로 적용되는 것을 막는다. */
        body.page-id-753 .lp-about-page a { text-decoration: none; }
        body.page-id-753 .lp-about-page .lp-team-card { text-align: center; }
        body.page-id-753 .lp-about-page .lp-team-card img {
            width: 6rem; height: 6rem; margin: 0 auto 0.75rem;
        }
        /* 이전/다음 네비 */
        .aj-single-nav {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;
            margin-top: 2.5rem; padding-top: 1.5rem;
            border-top: 1px solid var(--aj-border);
        }
        .aj-nav-btn {
            display: flex; flex-direction: column; gap: 0.25rem;
            padding: 0.875rem; border: 1px solid var(--aj-border);
            border-radius: 8px; text-decoration: none;
            transition: border-color 0.15s, background 0.15s;
        }
        .aj-nav-btn:hover { border-color: var(--aj-amber); background: var(--aj-amber-lite); }
        .aj-nav-next { text-align: right; }
        .aj-nav-label { font-size: 0.68rem; color: var(--aj-amber); font-weight: 700; letter-spacing: 0.05em; }
        .aj-nav-title { font-size: 0.83rem; color: #333; font-weight: 600; line-height: 1.35;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        /* ── 기자 프로필 서명 (AJ) ── */
        .aj-author-bio {
            display: flex; align-items: flex-start; gap: 1rem;
            margin: 2rem 0 1.25rem;
            padding: 1.125rem 1.25rem;
            background: var(--aj-stone);
            border: 1px solid var(--aj-border);
            border-radius: 10px;
        }
        .aj-author-bio-link { flex-shrink: 0; }
        .aj-author-avatar { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; display: block; }
        .aj-author-bio-body { flex: 1; min-width: 0; }
        .aj-author-bio-label { font-size: 0.65rem; font-weight: 700; color: var(--aj-amber); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.2rem; }
        .aj-author-bio-name { font-size: 1rem; font-weight: 800; color: #111; text-decoration: none; transition: color 0.15s; display: inline-block; }
        .aj-author-bio-name:hover { color: var(--aj-amber); }
        .aj-author-bio-desc { font-size: 0.8125rem; color: var(--aj-muted); line-height: 1.65; margin-top: 0.3rem; }
        .aj-author-link { color: inherit; text-decoration: none; font-weight: 700; transition: color 0.15s; }
        .aj-author-link:hover { color: var(--aj-amber); }
        /* AJ 다크모드 기자 프로필 */
        html.dark .aj-author-bio { background: #231a12; border-color: #3d3028; }
        html.dark .aj-author-bio-name { color: #e8ddd3; }

        /* ── 공통 SNS 소셜 링크 ── */
        .lp-footer-socials { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-top: 1rem; }
        .lp-footer-social { display: flex; align-items: center; justify-content: center;
            width: 2rem; height: 2rem; border-radius: 9999px;
            background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.65);
            transition: background 0.15s, color 0.15s; text-decoration: none; }
        .lp-footer-social:hover { background: rgba(255,255,255,0.2); color: #fff; }
        /* Basic 스킨 소셜 */
        .basic-footer .lp-footer-social { background: rgba(255,255,255,0.07); color: #6c757d; }
        .basic-footer .lp-footer-social:hover { background: rgba(255,255,255,0.15); color: #fff; }

        /* ── AJ 푸터 언론사 정보 구분자 */
        .aj-footer-legal p { margin: 0 0 0.15rem; }
        .aj-footer-legal strong { color: #9a8f87; font-weight: 600; }
        .aj-fl-sep { margin: 0 0.35rem; opacity: 0.35; }

        /* ══════════════════════════════════════════════════════
           다크모드 전환 버튼
           ══════════════════════════════════════════════════════ */
        .lp-dm-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; flex-shrink: 0;
            background: transparent; border: none; cursor: pointer; padding: 0;
            color: inherit; border-radius: 6px;
            transition: background 0.15s, color 0.15s;
        }
        .lp-dm-btn:hover { background: rgba(128,128,128,0.12); }
        .lp-dm-icon { pointer-events: none; display: block; }
        .lp-dm-sun { display: none; } /* 초기값: 라이트모드에서 태양 숨김 (다크모드 블록에서 토글) */

        /* NYT 스킨 버튼 — nyt-search-btn 스타일 일치 */
        .nyt-skin .lp-dm-btn {
            border: 1.5px solid #000; border-radius: 4px; color: #000;
        }
        .nyt-skin .lp-dm-btn:hover { background: #000; color: #fff; }

        /* Basic 스킨 버튼 — basic-search-btn 스타일 일치 */
        .basic-skin .lp-dm-btn {
            border: 1.5px solid #ced4da; border-radius: 6px; color: #495057;
        }
        .basic-skin .lp-dm-btn:hover { border-color: #0d6efd; color: #0d6efd; background: #eef3ff; }

        /* Amber Journal 버튼 — aj-search-btn 스타일 일치 */
        .amber-skin .lp-dm-btn {
            width: auto; height: auto;
            font-size: 0.8rem; font-weight: 600; color: #555;
            padding: 0.3rem 0.6rem;
            border: 1px solid var(--aj-border); border-radius: 6px;
            gap: 0.25rem;
        }
        .amber-skin .lp-dm-btn:hover { background: var(--aj-amber-lite); border-color: var(--aj-amber); color: var(--aj-amber); }

        /* SWN 다크버튼 (헤더 배너 옆) */
        .lp-swn-dm-wrap {
            display: flex; align-items: center;
        }
        .lp-swn-header-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
        .lp-swn-header-right .lp-hamburger { margin-left: 0; }
        .lp-swn-dm-wrap .lp-dm-btn { color: #475569; }
        .lp-swn-dm-wrap .lp-dm-btn:hover { background: #f1f5f9; }

        /* 다국어 전환 버튼 (SWN, 다크모드 버튼 옆) */
        .lp-lang-switcher { position: relative; }
        .lp-lang-btn {
            width: auto; padding: 0 0.5rem; gap: 0.3rem;
            color: #475569; font-weight: 700; font-size: 0.8125rem;
        }
        .lp-lang-btn:hover { background: #f1f5f9; }
        .lp-lang-current { letter-spacing: 0.02em; }
        .lp-lang-menu {
            display: none;
            position: absolute; top: calc(100% + 8px); right: 0;
            min-width: 150px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 0.375rem; margin: 0; list-style: none;
            z-index: 110;
        }
        .lp-lang-menu.is-open { display: block; }
        .lp-lang-menu li a {
            display: block; padding: 0.5rem 0.625rem; border-radius: 6px;
            font-size: 0.875rem; font-weight: 600; color: #334155; text-decoration: none;
            transition: background 0.12s;
        }
        .lp-lang-menu li a:hover { background: #f1f5f9; }
        .lp-lang-menu li a.is-current { color: var(--swn-primary, #7400ad); background: #f6ecfb; }
        html.dark .lp-lang-btn { color: #94a3b8; }
        html.dark .lp-lang-btn:hover { background: #334155; color: #e2e8f0; }
        html.dark .lp-lang-menu { background: #1e293b; border-color: #334155; }
        html.dark .lp-lang-menu li a { color: #cbd5e1; }
        html.dark .lp-lang-menu li a:hover { background: #334155; }
        html.dark .lp-lang-menu li a.is-current { color: #d8b4fe; background: #334155; }

        /* ══════════════════════════════════════════════════════
           유저 메뉴 (로그인/회원가입 · 내 프로필/내 기사 목록)
           — 다크모드 토글 버튼 옆에 위치. lp-dm-btn 클래스를 공유해
             스킨별 버튼 테두리/색상(위 다크모드 버튼 규칙)을 그대로 상속받는다.
           ══════════════════════════════════════════════════════ */
        .lp-user-menu { position: relative; }
        .lp-user-avatar {
            display: flex; align-items: center; justify-content: center;
            width: 24px; height: 24px; border-radius: 50%;
            background: var(--swn-primary, #2563eb); color: #fff;
            font-weight: 800; font-size: 0.875rem; line-height: 1;
            pointer-events: none;
        }
        .amber-skin .lp-user-avatar { width: 20px; height: 20px; font-size: 0.75rem; }
        .lp-user-dropdown {
            display: none;
            position: absolute; top: calc(100% + 8px); right: 0;
            min-width: 170px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 0.375rem; margin: 0; list-style: none;
            z-index: 110;
        }
        .lp-user-dropdown.is-open { display: block; }
        .lp-user-dropdown__name {
            padding: 0.5rem 0.625rem 0.25rem;
            font-size: 0.8125rem; font-weight: 700; color: #64748b;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .lp-user-dropdown li a {
            display: block; padding: 0.5rem 0.625rem; border-radius: 6px;
            font-size: 0.875rem; font-weight: 600; color: #334155; text-decoration: none;
            white-space: nowrap;
            transition: background 0.12s;
        }
        .lp-user-dropdown li a:hover { background: #f1f5f9; }
        html.dark .lp-user-dropdown { background: #1e293b; border-color: #334155; }
        html.dark .lp-user-dropdown__name { color: #94a3b8; }
        html.dark .lp-user-dropdown li a { color: #cbd5e1; }
        html.dark .lp-user-dropdown li a:hover { background: #334155; }
        /* SWN 헤더 우측 영역 — 다크모드 버튼과 동일한 색상 적용 */
        .lp-swn-header-right .lp-user-btn { color: #475569; }
        .lp-swn-header-right .lp-user-btn:hover { background: #f1f5f9; }
        html.dark .lp-swn-header-right .lp-user-btn { color: #94a3b8; }
        html.dark .lp-swn-header-right .lp-user-btn:hover { background: #334155; color: #e2e8f0; }

        /* ══════════════════════════════════════════════════════
           다크모드 오버라이드
           ══════════════════════════════════════════════════════ */

        /* ── 부드러운 전환 ── */
        body,
        .nyt-masthead, .nyt-masthead-logo, .nyt-nav,
        .basic-logo, .basic-header-main, .basic-nav, .basic-content-area, .basic-skin main,
        .aj-masthead, .aj-gnav, .aj-widget, .aj-widget-block, .aj-featured-section,
        .aj-single, .aj-single-article, .aj-comment-body, .aj-comment-form-wrap,
        .bg-white, .bg-slate-50, .bg-slate-100 {
            transition: background-color 0.25s ease, color 0.2s ease, border-color 0.2s ease;
        }

        /* ── 다크/라이트 아이콘 ── */
        .lp-dm-moon { display: block; }
        .lp-dm-sun  { display: none; }
        html.dark .lp-dm-moon { display: none; }
        html.dark .lp-dm-sun  { display: block; }

        /* ── 다크/라이트 로고 전환 (헤더·푸터 공통) — 다크모드 전용 로고가 업로드된 경우에만 두 개가 함께 출력됨 */
        .lp-logo-light-img { display: block !important; }
        .lp-logo-dark-img  { display: none !important; }
        html.dark .lp-logo-light-img { display: none !important; }
        html.dark .lp-logo-dark-img  { display: block !important; }

        /* ── 특성 이미지가 없는 글의 대체 썸네일(기본 로고) ──
           원본 사진용 object-fit:cover 인라인 스타일보다 우선 적용되도록 !important 사용.
           로고는 와이드 워드마크라 cover로 채우면 잘려 보이므로 contain + 여백 배경으로 표시. */
        .lp-thumb-fallback-logo {
            object-fit: contain !important;
            background: #f8fafc;
            padding: 12%;
            box-sizing: border-box;
        }
        html.dark .lp-thumb-fallback-logo { background: #1e293b; }

        /* ════════════════════════════════════════════════════
           SWN (Fresh) 다크모드
           ════════════════════════════════════════════════════ */
        html.dark body { background: #0f172a; color: #e2e8f0; }
        html.dark .bg-white        { background: #1e293b !important; }
        html.dark .bg-slate-50     { background: #182030 !important; }
        html.dark .bg-slate-100    { background: #243044 !important; }
        html.dark .bg-slate-900    { background: #020617 !important; }
        /* 텍스트 */
        html.dark .text-slate-900  { color: #f1f5f9  !important; }
        html.dark .text-slate-800  { color: #e2e8f0  !important; }
        html.dark .text-slate-700  { color: #cbd5e1  !important; }
        html.dark .text-slate-600  { color: #94a3b8  !important; }
        html.dark .text-slate-500  { color: #94a3b8  !important; }
        html.dark .text-slate-400  { color: #64748b  !important; }
        html.dark .text-blue-700   { color: #93c5fd  !important; }
        html.dark .text-blue-600   { color: #60a5fa  !important; }
        html.dark .text-blue-500   { color: #93c5fd  !important; }
        /* 경계선 */
        html.dark .border-slate-200 { border-color: #334155 !important; }
        html.dark .border-slate-100 { border-color: #1e293b !important; }
        html.dark .border-slate-800 { border-color: #1e293b !important; }
        html.dark .border-slate-900 { border-color: #e2e8f0 !important; }
        html.dark .shadow-sm        { box-shadow: none !important; }
        /* 호버 링크 */
        html.dark .group:hover .group-hover\:text-blue-600 { color: #60a5fa !important; }
        html.dark a.hover\:text-blue-600:hover { color: #60a5fa !important; }
        html.dark a.hover\:text-blue-500:hover { color: #93c5fd !important; }
        /* SWN 내비 */
        html.dark #lp-gnav { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .primary-menu-container ul a       { color: #cbd5e1; }
        html.dark .primary-menu-container ul a:hover { color: #93c5fd; }
        html.dark .lp-swn-dm-wrap .lp-dm-btn       { color: #94a3b8; }
        html.dark .lp-swn-dm-wrap .lp-dm-btn:hover { background: #334155; color: #e2e8f0; }
        /* Breaking News */
        html.dark .lp-section-head { color: #f1f5f9; }
        html.dark .lp-breaking-bar { background: #1e3a5f !important; border-color: #334155 !important; color: #e2e8f0 !important; }
        /* 기자 프로필 공통 */
        html.dark .lp-author-bio     { background: #182030 !important; border-color: #334155 !important; }
        html.dark .lp-author-bio img { border-color: #334155 !important; }

        /* ════════════════════════════════════════════════════
           NYT (Classic) 다크모드
           ════════════════════════════════════════════════════ */
        html.dark body.nyt-skin      { background: #1a1a1a; color: #d8d8d8; }
        /* 탑바 */
        html.dark .nyt-topbar        { background: #111; color: #888; border-color: #2a2a2a; }
        html.dark .nyt-topbar a      { color: #888; }
        html.dark .nyt-topbar a:hover { color: #ddd; }
        /* 마스트헤드 */
        html.dark .nyt-masthead      { background: #111; border-top-color: #444; border-bottom-color: #444; }
        html.dark .nyt-masthead-logo { color: #e2e8f0; }
        html.dark .nyt-masthead-sub  { color: #888; }
        html.dark .nyt-masthead-banner-ph { background: #222; color: #555; border-color: #333; }
        /* 내비 */
        html.dark .nyt-nav           { background: #111 !important; border-color: #2a2a2a !important; }
        html.dark .nyt-nav a         { color: #ccc !important; }
        html.dark .nyt-nav a:hover   { color: #fff !important; }
        /* 검색 */
        html.dark .nyt-search-btn         { border-color: #aaa; color: #aaa; }
        html.dark .nyt-search-btn:hover   { background: #aaa; color: #111; }
        html.dark .nyt-skin .lp-dm-btn    { border-color: #aaa; color: #aaa; }
        html.dark .nyt-skin .lp-dm-btn:hover { background: #aaa; color: #111; }
        html.dark .nyt-search-modal       { background: rgba(0,0,0,0.88); }
        html.dark .nyt-search-modal-box   { background: #1a1a1a; border-top-color: #aaa; }
        html.dark .nyt-search-label       { color: #888; }
        html.dark .nyt-search-form        { border-bottom-color: #aaa; }
        html.dark .nyt-search-input       { color: #e2e8f0; background: transparent; }
        /* 섹션 제목 */
        html.dark .nyt-section-heading    { color: #d8d8d8; border-color: #2a2a2a; }
        /* 홈 콘텐츠 */
        html.dark .nyt-content-area h2.text-2xl { color: #d8d8d8 !important; border-bottom-color: #555 !important; }
        html.dark .nyt-content-area h3.text-3xl,
        html.dark .nyt-content-area h3.text-3xl a { color: #e2e8f0 !important; }
        html.dark .nyt-content-area h3.text-3xl a:hover { color: #90cdf4 !important; }
        html.dark .nyt-content-area h3.text-lg,
        html.dark .nyt-content-area h3.text-lg a { color: #e2e8f0 !important; }
        html.dark .nyt-content-area h3.text-lg a:hover { color: #90cdf4 !important; }
        html.dark .nyt-content-area h2.text-xl { color: #d8d8d8 !important; border-bottom-color: #3a3a3a !important; }
        html.dark .nyt-content-area h2.text-xl span { color: #d8d8d8 !important; }
        html.dark .nyt-content-area h2.text-xl a { color: #90cdf4 !important; }
        html.dark .nyt-content-area ul.space-y-3 a { color: #d8d8d8 !important; }
        html.dark .nyt-content-area ul.space-y-3 a:hover { color: #90cdf4 !important; }
        html.dark .nyt-content-area ul.space-y-3 li { border-bottom-color: #2a2a2a; }
        html.dark .nyt-content-area .text-slate-400 { color: #888 !important; }
        html.dark .nyt-skin .bg-slate-100,
        html.dark .nyt-content-area .bg-slate-100 { background: #2a2a2a !important; }
        /* 사이드바 */
        html.dark .nyt-sidebar > div           { border-top-color: #555 !important; }
        html.dark .nyt-sidebar h3              { color: #ccc !important; border-bottom-color: #333 !important; }
        html.dark .nyt-sidebar ol a            { color: #ccc !important; }
        html.dark .nyt-sidebar ol a:hover      { color: #90cdf4 !important; }
        html.dark .nyt-sidebar ol li           { border-bottom-color: #2a2a2a; }
        html.dark .nyt-sidebar input[type="text"] { border-color: #555 !important; background: #222; color: #ddd; }
        html.dark .nyt-sidebar button[type="submit"] { background: #444 !important; }
        /* 기사 단일 뷰 */
        html.dark .nyt-skin article.article-view header .text-blue-600 { color: #aaa !important; }
        html.dark .nyt-skin article.article-view h1 { color: #e2e8f0; }
        html.dark .nyt-skin article.article-view header .text-slate-700 { color: #ccc !important; }
        html.dark .nyt-skin article.article-view header .text-slate-500 { color: #888 !important; }
        html.dark .nyt-skin article.article-view header { border-bottom-color: #444 !important; }
        html.dark #lp-article-body figure figcaption,
        html.dark #lp-article-body .wp-caption-text { color: #9ca3af; }
        html.dark .nyt-skin #lp-article-body { color: #d8d8d8 !important; }
        html.dark .nyt-skin #lp-article-body figure figcaption { color: #888; border-top-color: #444; }
        html.dark .nyt-skin .bg-gradient-to-br { background: #222 !important; border-color: #333 !important; }
        /* 스티키 바 */
        html.dark .nyt-skin #lp-sticky-bar { background: rgba(17,17,17,0.98) !important; border-bottom-color: #444 !important; }
        /* 푸터 */
        html.dark .nyt-skin footer h2 { color: #e2e8f0; }

        /* ════════════════════════════════════════════════════
           Basic (Minimal) 다크모드
           ════════════════════════════════════════════════════ */
        html.dark body.basic-skin    { background: #0f172a; color: #e2e8f0; }
        html.dark .basic-skin main   { background: #0f172a; }
        /* 로고 */
        html.dark .basic-logo        { color: #e2e8f0 !important; }
        html.dark .basic-logo .basic-logo-badge { color: #94a3b8; }
        /* 헤더/내비 */
        html.dark .basic-header-accent { background: #0b5ed7; }
        html.dark .basic-header-main { background: #1e293b; box-shadow: none; border-bottom: 1px solid #334155; }
        html.dark .basic-nav         { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .basic-nav a       { color: #e2e8f0 !important; }
        html.dark .basic-nav a:hover { color: #93c5fd !important; }
        html.dark .basic-nav .lp-hamburger { color: #e2e8f0; }
        html.dark .basic-nav .lp-mobile-nav ul li a       { color: #cbd5e1; }
        html.dark .basic-nav .lp-mobile-nav ul li a:hover { color: #93c5fd; }
        /* 검색 */
        html.dark .basic-search-btn       { border-color: #4a5568; color: #e2e8f0; }
        html.dark .basic-search-btn:hover { border-color: #93c5fd; color: #93c5fd; background: #1e3a5f; }
        html.dark .basic-skin .lp-dm-btn       { border-color: #4a5568; color: #e2e8f0; }
        html.dark .basic-skin .lp-dm-btn:hover { border-color: #93c5fd; color: #93c5fd; background: #1e3a5f; }
        html.dark .basic-search-drop       { background: #1e293b; border-color: #334155; }
        html.dark .basic-search-drop input { background: #0f172a; border-color: #4a5568; color: #e2e8f0; }
        html.dark .basic-search-drop button[type="submit"]       { background: #0b5ed7; }
        html.dark .basic-search-drop button[type="submit"]:hover { background: #1d4ed8; }
        /* 콘텐츠 영역 */
        html.dark .basic-content-area      { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .basic-skin .lp-section-heading { color: #e2e8f0; border-left-color: #60a5fa; }
        html.dark .basic-skin aside .bg-white { background: #1e293b !important; border-color: #334155 !important; }
        html.dark .basic-skin aside button[type="submit"]       { background: #334155 !important; }
        html.dark .basic-skin aside button[type="submit"]:hover { background: #60a5fa !important; }
        html.dark .basic-skin .bg-slate-100 { background: #243044 !important; }
        /* 스티키 바 */
        html.dark .basic-skin #lp-sticky-bar { background: rgba(15,23,42,0.97) !important; border-bottom-color: #334155 !important; }
        html.dark .basic-skin #lp-sticky-bar .lp-sb-title { color: #e2e8f0; }
        /* 푸터 */
        html.dark .basic-footer          { background: #020617; color: #64748b; }
        html.dark .basic-footer-grid     { border-color: #1e293b; }
        html.dark .basic-footer-logo     { color: #e2e8f0 !important; }
        html.dark .basic-footer-desc     { color: #4b5563; }
        html.dark .basic-footer-col h3   { color: #cbd5e1; }
        html.dark .basic-footer-col ul a       { color: #64748b; }
        html.dark .basic-footer-col ul a:hover { color: #e2e8f0; }
        html.dark .basic-footer-legal    { border-color: #1e293b; color: #64748b; }
        html.dark .basic-footer-legal strong { color: #94a3b8; }
        html.dark .basic-footer-copy     { color: #334155; }

        /* ════════════════════════════════════════════════════
           Amber Journal 다크모드
           ════════════════════════════════════════════════════ */
        html.dark body.amber-skin {
            --aj-bg: #1c1410; --aj-border: #3d3028; --aj-stone: #231a12; --aj-muted: #7a6e68;
            background: #1c1410; color: #e8ddd3;
        }
        /* 탑바 */
        html.dark .aj-topbar        { background: #100c09; color: #6b5e56; border-color: #2a2018; }
        html.dark .aj-topbar a      { color: #6b5e56; }
        html.dark .aj-topbar a:hover { color: #e8ddd3; }
        /* 마스트헤드 + 로고 */
        html.dark .aj-masthead      { background: #1c1410; border-color: #3d3028; }
        html.dark .aj-masthead-banner { background: #2a1f16; border-color: #3d3028; color: #6b5e56; }
        html.dark .aj-logo-text     { color: #e8ddd3 !important; }
        html.dark .aj-logo-mark     { filter: brightness(1.3); }
        /* 내비 */
        html.dark .aj-gnav          { background: #231a12; border-color: #3d3028; }
        html.dark .aj-gnav-ul > li > a       { color: #d8ccc4; }
        html.dark .aj-gnav-ul > li > a:hover { color: #fff; }
        html.dark .aj-gnav-sub      { background: #231a12; border-color: #3d3028; }
        /* 검색 */
        html.dark .aj-search-btn    { border-color: #3d3028; color: #a09080; }
        html.dark .amber-skin .lp-dm-btn       { border-color: #3d3028; color: #a09080; }
        html.dark .amber-skin .lp-dm-btn:hover { background: #2a1f16; border-color: var(--aj-amber); color: var(--aj-amber); }
        html.dark .aj-search-modal-box   { background: #231a12; }
        html.dark .aj-search-modal-label { color: #a09080; }
        html.dark .aj-search-input  { background: #1c1410; border-color: #3d3028; color: #e8ddd3; }
        html.dark .aj-search-close-btn        { color: #7a6e68; }
        html.dark .aj-search-close-btn:hover  { color: #e8ddd3; }
        html.dark .aj-search-hint   { color: #7a6e68; }
        /* 전면 기사 */
        html.dark .aj-featured-section { background: #231a12; border-color: #3d3028; }
        html.dark .aj-feat-sm       { border-color: #3d3028; }
        html.dark .aj-feat-center   { border-color: #3d3028; }
        html.dark .aj-feat-sm-title       { color: #e8ddd3; }
        html.dark .aj-feat-sm-title:hover { color: var(--aj-amber); }
        html.dark .aj-feat-main-title       { color: #e8ddd3; }
        html.dark .aj-feat-main-title:hover { color: var(--aj-amber); }
        html.dark .aj-feat-main-excerpt { color: #b0a090; }
        /* 카테고리/아카이브 목록 기사 제목 */
        html.dark .lp-arc-list-title,
        html.dark .lp-arc-grid-title,
        html.dark .lp-arc-webzine-title { color: #ffffff; }
        html.dark .lp-arc-list-item:hover .lp-arc-list-title { color: var(--aj-amber); }
        html.dark .lp-arc-grid article:hover .lp-arc-grid-title { color: var(--aj-amber); }
        html.dark .lp-arc-webzine article:hover .lp-arc-webzine-title { color: var(--aj-amber); }
        /* 홈 위젯 블록 */
        html.dark .aj-widget-block  { background: #231a12; border-color: #3d3028; }
        html.dark .aj-widget-block .bg-slate-100 { background: #3d3028 !important; }
        html.dark .aj-article-list .aj-card { background: #231a12; border-color: #3d3028; }
        html.dark .aj-card-title a        { color: #e8ddd3; }
        html.dark .aj-card-title a:hover  { color: var(--aj-amber); }
        html.dark .aj-card-meta    { color: #7a6e68; }
        /* 사이드바 위젯 */
        html.dark .aj-widget       { background: #231a12; border-color: #3d3028; }
        html.dark .aj-widget-head  { border-color: #3d3028; background: #1c1410; }
        html.dark .aj-widget-title { color: #e8ddd3; }
        html.dark .lp-cat-link     { color: #ffffff; }
        html.dark .lp-cat-link:hover { color: var(--aj-amber); }
        html.dark .lp-cat-item:hover .lp-cat-bullet { color: var(--aj-amber); }
        html.dark .aj-opinion-item      { border-color: #3d3028; }
        html.dark .aj-opinion-link        { color: #d8ccc4; }
        html.dark .aj-opinion-link:hover  { color: var(--aj-amber); }
        html.dark .aj-opinion-title       { color: #d8ccc4; }
        html.dark .aj-opinion-title:hover { color: var(--aj-amber); }
        /* 썸네일 위젯 */
        html.dark .aj-thumb-item    { border-color: #3d3028; }
        html.dark .aj-thumb-img-wrap { background: #3d3028; }
        html.dark .aj-thumb-img-wrap.aj-thumb-no-img { background: #2a1f16; }
        html.dark .aj-thumb-link        { color: #d8ccc4; }
        html.dark .aj-thumb-link:hover  { color: var(--aj-amber); }
        /* 유틸리티 버튼 */
        html.dark .aj-util-btn        { background: #231a12; border-color: #3d3028; color: #a09080; }
        html.dark .aj-util-btn:hover  { border-color: var(--aj-amber); color: var(--aj-amber); background: rgba(217,119,6,0.1); }
        html.dark .aj-fz-ctrl         { border-color: #3d3028; }
        html.dark .aj-fz-btn          { background: #231a12; color: #a09080; }
        html.dark .aj-fz-btn + .aj-fz-btn { border-left-color: #3d3028; }
        html.dark .aj-fz-btn:hover         { background: rgba(217,119,6,0.1); color: var(--aj-amber); }
        html.dark .aj-fz-btn.aj-fz-active  { background: rgba(217,119,6,0.15); color: var(--aj-amber); }
        /* 기사 단일 뷰 */
        html.dark .aj-single          { background: #1c1410; }
        html.dark .aj-single-article  { background: #231a12; border-color: #3d3028; }
        html.dark .aj-single-title    { color: #ffffff; }
        html.dark .aj-single-meta     { color: #7a6e68; border-top-color: #3d3028; border-bottom-color: #3d3028; }
        html.dark .aj-single-meta strong { color: #d8ccc4; }
        html.dark .aj-article-content { color: #d8ccc4; }
        html.dark .aj-article-content h2,
        html.dark .aj-article-content h3 { color: #e8ddd3; }
        html.dark .aj-article-content blockquote { background: rgba(217,119,6,0.1); color: #b0a090; }
        html.dark .aj-nav-btn         { border-color: #3d3028; }
        html.dark .aj-nav-btn:hover   { border-color: var(--aj-amber); background: rgba(217,119,6,0.08); }
        html.dark .aj-nav-title       { color: #d8ccc4; }
        /* 댓글 */
        html.dark .aj-comments             { border-top-color: #3d3028; }
        html.dark .aj-comments-title       { color: #e8ddd3; }
        html.dark .aj-comment-body         { background: #2a1f16; border-color: #3d3028; }
        html.dark .aj-comment-name         { color: #e8ddd3; }
        html.dark .aj-comment-text p       { color: #d8ccc4; }
        html.dark .aj-comment-form-wrap    { background: #231a12; border-color: #3d3028; }
        html.dark .aj-comment-form-wrap #respond { background: transparent; border: none; }
        html.dark .aj-comment-form-wrap #reply-title { color: #e8ddd3; }
        html.dark .aj-comment-form-wrap .comment-form-comment label,
        html.dark .aj-comment-form-wrap .comment-form-author label,
        html.dark .aj-comment-form-wrap .comment-form-email label { color: #a09080; }
        html.dark .aj-comment-form-wrap input[type="text"],
        html.dark .aj-comment-form-wrap input[type="email"],
        html.dark .aj-comment-form-wrap input[type="url"],
        html.dark .aj-comment-form-wrap textarea { background: #1c1410; border-color: #3d3028; color: #e8ddd3; }
        html.dark .aj-comments ol.children { border-left-color: #3d3028; }
        /* 기자 프로필 */
        html.dark .aj-author-bio      { background: #231a12; border-color: #3d3028; }
        html.dark .aj-author-bio-name { color: #e8ddd3; }
        /* 스티키 바 */
        html.dark .amber-skin #lp-sticky-bar { background: rgba(28,20,16,0.97) !important; border-color: #3d3028 !important; }
        /* 푸터 */
        html.dark .aj-footer               { background: #100c09; border-color: #2a2018; }
        html.dark .aj-footer-body          { border-color: #2a2018; }
        html.dark .aj-footer-logo-text     { color: #e8ddd3; }
        html.dark .aj-footer-desc          { color: #6b5e56; }
        html.dark .aj-footer-legal         { color: #5a5048; }
        html.dark .aj-footer-legal strong  { color: #7a6e68; }
        html.dark .aj-footer-col-title     { color: #e8ddd3; border-color: #3d3028; }
        html.dark .aj-footer-notice-list a       { color: #b0a090; }
        html.dark .aj-footer-notice-list a:hover { color: var(--aj-amber); }
        html.dark .aj-footer-notice-date   { color: #5a5048; }
        html.dark .aj-footer-bottom        { border-color: #2a2018; }
        html.dark .aj-footer-copy          { color: #3d3028; }

        /* ══════════════════════════════════════════════════════
           SWN 스킨 — neeon(청라온) 브랜드 리스킨
           청라온 실사이트 Customizer 값 기준: primary #7400ad,
           top bar #8100c6, footer #0f1012, ticker accent #81d742
           ══════════════════════════════════════════════════════ */
        .swn-skin { --swn-primary: #7400ad; --swn-secondary: #4f0077; --swn-topbar: #8100c6; --swn-accent: #81d742; }

        /* 상단 티커 바 */
        .swn-skin .lp-topbar { background: var(--swn-topbar); color: #f1e6fa; }
        .swn-skin .lp-topbar-inner { display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; padding: 0.5rem 0; font-size: 0.8125rem; }
        .swn-skin .lp-ticker-wrap { display: flex; align-items: center; gap: 0.65rem; min-width: 0; flex: 1; }
        /* 본문 영역 트렌딩 티커 — "주요 뉴스" 섹션 상단 (연보라 배경으로 별도 스타일) */
        .swn-skin .lp-content-ticker {
            background: #f6ecfb; border: 1px solid #ecd9f5; border-radius: 9999px;
            padding: 0.5rem 1rem 0.5rem 0.5rem; margin-bottom: 1.5rem;
        }
        .swn-skin .lp-content-ticker .lp-ticker-badge { background: var(--swn-primary); color: #fff; }
        .swn-skin .lp-content-ticker .lp-ticker-list a { color: var(--swn-secondary); }
        .swn-skin .lp-content-ticker .lp-ticker-list a:hover { color: var(--swn-primary); text-decoration: underline; }
        html.dark .swn-skin .lp-content-ticker { background: #2a1a33; border-color: #3d2649; }
        html.dark .swn-skin .lp-content-ticker .lp-ticker-list a { color: #e9d5f5; }
        html.dark .swn-skin .lp-content-ticker .lp-ticker-list a:hover { color: #fff; }
        .swn-skin .lp-ticker-badge {
            display: inline-flex; align-items: center; gap: 0.3rem; flex-shrink: 0;
            background: rgba(0,0,0,0.22); color: #fff; font-weight: 800; font-size: 0.6875rem;
            letter-spacing: 0.06em; padding: 0.22rem 0.65rem; border-radius: 9999px; text-transform: uppercase;
        }
        .swn-skin .lp-ticker-badge svg { color: var(--swn-accent); }
        /* 세로 슬라이딩(위→아래 한 줄씩) 티커 — JS(lpTickerSlide)가 transform을 제어 */
        .swn-skin .lp-ticker-track { flex: 1; min-width: 0; overflow: hidden; height: 1.375rem; position: relative; }
        .swn-skin .lp-ticker-list {
            list-style: none; margin: 0; padding: 0;
            transition: transform 0.5s cubic-bezier(.4,0,.2,1);
        }
        .swn-skin .lp-ticker-list li {
            height: 1.375rem; line-height: 1.375rem; overflow: hidden;
            white-space: nowrap; text-overflow: ellipsis;
        }
        .swn-skin .lp-ticker-list a { color: #f1e6fa; text-decoration: none; font-weight: 500; }
        .swn-skin .lp-ticker-list a:hover { color: #fff; }
        @media (prefers-reduced-motion: reduce) { .swn-skin .lp-ticker-list { transition: none; } }
        .swn-skin .lp-topbar-right { display: flex; align-items: center; gap: 0.9rem; flex-shrink: 0; }
        .swn-skin .lp-topbar-date { opacity: 0.85; white-space: nowrap; }
        .swn-skin .lp-topbar-right nav ul { display: flex; gap: 1rem; list-style: none; margin: 0; padding: 0; }
        .swn-skin .lp-topbar-right nav a { color: #e7d6f2; text-decoration: none; transition: color 0.15s; }
        .swn-skin .lp-topbar-right nav a:hover { color: #fff; }
        @media (max-width: 640px) { .swn-skin .lp-topbar-right nav { display: none; } }

        /* 헤더/내비게이션 포인트 컬러 */
        .swn-skin .lp-dm-btn:hover { background: #f6ecfb; color: var(--swn-primary); }
        .swn-skin .primary-menu-container ul a:hover { color: var(--swn-primary); }
        .swn-skin .primary-menu-container ul a::after { background-color: var(--swn-primary); }
        .swn-skin .lp-desktop-nav ul.sub-menu li a:hover { background: #f6ecfb !important; color: var(--swn-primary) !important; }

        /* 카테고리 배지 (neeon rt_category_color 재사용) */
        .swn-skin .lp-cat-pill {
            display: inline-flex; align-items: center; font-size: 0.6875rem; font-weight: 800;
            letter-spacing: 0.02em; color: #fff; padding: 0.2rem 0.65rem; border-radius: 9999px;
            text-decoration: none; text-transform: uppercase; line-height: 1.5;
        }
        .swn-skin .lp-cat-pill:hover { opacity: 0.88; }

        /* 인기 카테고리 리스트(HOT CATEGORIES) 포인트 컬러 */
        .swn-skin .lp-cat-link:hover { color: var(--swn-primary); }
        .swn-skin .lp-cat-link:hover .lp-cat-count { background: #f6ecfb; color: var(--swn-primary); }
        .swn-skin .lp-cat-item:hover .lp-cat-bullet { color: var(--swn-primary); }

        /* 위클리 추천 — 가로 스크롤 슬라이더 (스크롤바 숨김 + 마우스 드래그 스크롤) */
        .swn-skin .lp-weekly-track {
            display: flex; gap: 1.25rem; overflow-x: auto; scroll-snap-type: x mandatory;
            padding-bottom: 0.5rem; scroll-behavior: smooth;
            cursor: grab;
            scrollbar-width: none;    /* Firefox */
            -ms-overflow-style: none; /* 구형 Edge/IE */
        }
        .swn-skin .lp-weekly-track::-webkit-scrollbar { display: none; } /* Chrome · Safari */
        .swn-skin .lp-weekly-track.lp-dragging {
            cursor: grabbing; scroll-snap-type: none; scroll-behavior: auto;
            user-select: none; -webkit-user-select: none;
        }
        .swn-skin .lp-weekly-card { flex: 0 0 200px; scroll-snap-align: start; display: block; }
        .swn-skin .lp-weekly-track.lp-dragging .lp-weekly-card { pointer-events: none; }
        .swn-skin .lp-slider-nav { display: flex; gap: 0.5rem; flex-shrink: 0; }
        .swn-skin .lp-slider-btn {
            width: 2rem; height: 2rem; border-radius: 9999px; border: 1px solid #e2e8f0;
            background: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; line-height: 1; cursor: pointer; color: #475569;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .swn-skin .lp-slider-btn:hover { background: #f6ecfb; border-color: var(--swn-primary); color: var(--swn-primary); }
        html.dark .swn-skin .lp-slider-btn { background: #1e293b; border-color: #334155; color: #cbd5e1; }

        /* 댓글 · 반응 · 페이지네이션 · 본문 글자크기 컨트롤 포인트 컬러 */
        .swn-skin .lp-rx-btn.is-reacted            { border-color: var(--swn-primary); }
        .swn-skin .lp-rx-btn.is-reacted .lp-rx-name { color: var(--swn-primary); }
        .swn-skin .lp-rx-btn.is-reacted .lp-rx-count{ color: var(--swn-secondary); }
        .swn-skin .lp-cmt-author a:hover { color: var(--swn-primary); }
        .swn-skin .lp-cmt-reply a:hover { border-color: var(--swn-primary); color: var(--swn-primary); background: #f6ecfb; }
        .swn-skin .lp-cmt-text a { color: var(--swn-primary); }
        .swn-skin .lp-cmts-pager a { color: var(--swn-primary); }
        .swn-skin .lp-cmt-field input:focus,
        .swn-skin .lp-cmt-field textarea:focus { border-color: var(--swn-primary); box-shadow: 0 0 0 3px rgba(116,0,173,0.1); }
        .swn-skin .lp-cmt-submit { background: var(--swn-primary); }
        .swn-skin .lp-cmt-submit:hover { background: var(--swn-secondary); }
        .swn-skin .lp-pagination a:hover { background: #f6ecfb; border-color: #d3aee3; color: var(--swn-secondary); }
        .swn-skin .lp-pagination .current { background: var(--swn-primary); border-color: var(--swn-primary); }
        .swn-skin .lp-font-ctrl button:hover { background: #f6ecfb; color: var(--swn-secondary); }
        .swn-skin .lp-font-ctrl button.lp-fz-active { background: #f0e0f7; color: var(--swn-secondary); }

        /* 메인 콘텐츠 — 위젯 섹션 헤딩/링크 포인트 컬러(파랑→보라) */
        .swn-skin .text-blue-600, .swn-skin .text-blue-700 { color: var(--swn-primary) !important; }
        .swn-skin a.hover\:text-blue-600:hover,
        .swn-skin a.hover\:text-blue-500:hover,
        .swn-skin .group:hover .group-hover\:text-blue-600 { color: var(--swn-primary) !important; }
        .swn-skin .aj-widget-block .text-blue-700 { color: var(--swn-secondary) !important; }

        /* 카드 호버 살짝 들어올리는 효과 */
        .swn-skin .lp-content-area article.group,
        .swn-skin article.group { transition: transform 0.2s ease; }

        /* 다크모드에서도 포인트 컬러 유지 */
        html.dark .swn-skin .primary-menu-container ul a:hover { color: #c084fc !important; }
        html.dark .swn-skin .text-blue-600,
        html.dark .swn-skin .text-blue-700 { color: #c084fc !important; }
        html.dark .swn-skin a.hover\:text-blue-600:hover,
        html.dark .swn-skin a.hover\:text-blue-500:hover { color: #c084fc !important; }

        /* 푸터 — neeon footer-style-3 (다크, #0f1012) */
        .swn-skin footer.bg-slate-900 { background-color: #0f1012 !important; }
        .swn-skin footer.bg-slate-900 .text-slate-500,
        .swn-skin footer.bg-slate-900 .text-slate-400 { color: #d0d0d0 !important; }
        .swn-skin footer.bg-slate-900 .border-slate-800 { border-color: #232326 !important; }
        .swn-skin footer.bg-slate-900 a.text-blue-400 { color: #cfa3e6 !important; }
        .swn-skin footer.bg-slate-900 a.text-blue-400:hover,
        .swn-skin footer.bg-slate-900 .footer-menu-container a:hover { color: #ffffff !important; }
        .swn-skin footer.bg-slate-900 .lp-footer-social { background: rgba(255,255,255,0.08); color: #d0d0d0; }
        .swn-skin footer.bg-slate-900 .lp-footer-social:hover { background: var(--swn-primary); color: #fff; }
    </style>
</head>
<body <?php body_class( $body_class . ' flex flex-col min-h-screen antialiased' ); ?>>
    <?php wp_body_open(); // 사이트 아이콘 스플래시 화면 등 훅에서 <body> 시작 직후 출력 ?>

    <?php if ( $current_theme_style === 'swn-style' ) : ?>
        <!-- 상단 유틸리티 바 (날짜 · 메뉴) — TRENDING 티커는 프론트 페이지 "주요 뉴스" 상단으로 이동 -->
        <div class="lp-topbar">
            <div class="<?php echo $container_class; ?> lp-topbar-inner">
                <div class="lp-topbar-date"><?php echo lp_header_date_html(); ?></div>
                <div class="lp-topbar-right">
                    <nav>
                        <?php
                        if ( has_nav_menu( 'top-menu' ) ) {
                            wp_nav_menu( [ 'theme_location' => 'top-menu', 'container' => false, 'fallback_cb' => false ] );
                        } else {
                            echo '<a href="' . admin_url( 'nav-menus.php' ) . '" class="text-white/80 hover:text-white transition">메뉴를 설정해 주세요. [메뉴 설정하러 가기]</a>';
                        }
                        ?>
                    </nav>
                </div>
            </div>
        </div>

        <!-- 매체 로고 및 배너 -->
        <header class="py-8 md:pb-4 bg-white">
            <div class="<?php echo $container_class; ?> flex items-center justify-between gap-4 md:gap-6">
                <div class="shrink-0">
                    <?php echo lp_get_logo_html( 'swn' ); ?>
                </div>
                <?php if ( ! empty( $lp_banner_top ) ) : ?>
                <div class="hidden md:flex w-full md:w-[728px] h-[90px] items-center justify-center overflow-hidden">
                    <?php echo $lp_banner_top; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <?php else : ?>
                <div class="hidden md:flex w-full md:w-[728px] h-[90px] bg-slate-100 border border-slate-200 items-center justify-center text-slate-400 text-sm rounded">
                    상단 메인 배너 영역 (728×90)
                </div>
                <?php endif; ?>
                <div class="lp-swn-header-right">
                    <div class="lp-swn-dm-wrap">
                        <button class="lp-dm-btn" id="lpDarkModeBtn" aria-label="다크/라이트 모드 전환" title="다크/라이트 모드">
                            <svg class="lp-dm-icon lp-dm-moon" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                            <svg class="lp-dm-icon lp-dm-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                        </button>
                    </div>
                    <?php echo lp_get_user_menu_html( 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                    <?php
                    $lp_langs = function_exists( 'pll_the_languages' ) ? pll_the_languages( [ 'raw' => 1, 'hide_if_empty' => 0 ] ) : [];
                    if ( ! empty( $lp_langs ) ) :
                        $lp_cur_lang = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
                    ?>
                    <div class="lp-lang-switcher" id="lpLangSwitcher">
                        <button class="lp-dm-btn lp-lang-btn" id="lpLangBtn" aria-haspopup="true" aria-expanded="false" title="언어 선택 / Language">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            <span class="lp-lang-current"><?php echo esc_html( strtoupper( $lp_cur_lang ) ); ?></span>
                        </button>
                        <ul class="lp-lang-menu" id="lpLangMenu">
                            <?php foreach ( $lp_langs as $lp_lang ) : ?>
                            <li>
                                <a href="<?php echo esc_url( $lp_lang['url'] ); ?>"<?php echo $lp_lang['current_lang'] ? ' class="is-current" aria-current="true"' : ''; ?>>
                                    <?php echo esc_html( strtoupper( $lp_lang['slug'] ) ); ?> · <?php echo esc_html( $lp_lang['name'] ); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <button class="lp-hamburger text-slate-800" id="lpSwn-hamburger" aria-label="메뉴 열기" aria-expanded="false" aria-controls="lpSwn-mobile-nav">
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- 메인 네비게이션 (GNB) -->
        <nav class="lp-gnav-swn border-y border-slate-200 bg-white shadow-sm" id="lp-gnav">
            <div class="<?php echo $container_class; ?> flex items-center gap-2 py-3 md:py-4 text-base md:text-lg font-bold primary-menu-container">
                <div class="lp-desktop-nav">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                } else {
                    echo '<a href="' . admin_url( 'nav-menus.php' ) . '" class="text-blue-600 text-sm">메뉴를 설정해 주세요.</a>';
                }
                ?>
                </div>
            </div>
            <div class="lp-mobile-nav" id="lpSwn-mobile-nav">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                }
                ?>
            </div>
        </nav>

    <?php elseif ( $current_theme_style === 'newyorktimes-style' ) : ?>
        <!-- ═══════════════════════════════════════════════════
             NYT 스타일 헤더 — 탑바 + 마스트헤드 + 섹션 네비
             ═══════════════════════════════════════════════════ -->

        <!-- 탑 유틸리티 바 -->
        <div class="nyt-topbar">
            <div class="<?php echo $container_class; ?> flex justify-between items-center py-1.5">
                <span><?php echo lp_header_date_html(); ?></span>
                <div class="flex items-center gap-4">
                    <?php
                    if ( has_nav_menu( 'top-menu' ) ) {
                        wp_nav_menu( [ 'theme_location' => 'top-menu', 'container' => false, 'menu_class' => 'flex gap-4', 'fallback_cb' => false ] );
                    } else {
                        echo '<a href="' . admin_url( 'nav-menus.php' ) . '">메뉴 설정</a>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- 마스트헤드 -->
        <header class="nyt-masthead">
            <div class="<?php echo $container_class; ?> nyt-masthead-inner">

                <!-- 슬롯 1: 작은 배너 (좌측) -->
                <div class="nyt-masthead-slot slot-left">
                    <?php if ( ! empty( $lp_banner_top ) ) : ?>
                        <div class="nyt-masthead-banner"><?php echo $lp_banner_top; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
                    <?php else : ?>
                        <div class="nyt-masthead-banner-ph">AD 160×50</div>
                    <?php endif; ?>
                </div>

                <!-- 슬롯 2: 로고 중앙 -->
                <div class="nyt-masthead-center">
                    <?php echo lp_get_logo_html( 'nyt' ); ?>
                    <?php $lp_tag = lp_get_tagline(); ?>
                    <?php if ( $lp_tag ) : ?>
                    <p class="nyt-masthead-sub"><?php echo esc_html( $lp_tag ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- 슬롯 3: 검색 + 다크모드 (우측) -->
                <div class="nyt-masthead-slot slot-right" style="gap:0.5rem;">
                    <button class="nyt-search-btn" id="nytSearchOpen" aria-label="검색">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                    <button class="lp-dm-btn" id="lpDarkModeBtn" aria-label="다크/라이트 모드 전환" title="다크/라이트 모드">
                        <svg class="lp-dm-icon lp-dm-moon" width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg class="lp-dm-icon lp-dm-sun" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <?php echo lp_get_user_menu_html( 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>

            </div>
        </header>

        <!-- 검색 모달 -->
        <div class="nyt-search-modal" id="nytSearchModal" role="dialog" aria-modal="true" aria-label="검색">
            <div class="nyt-search-modal-box">
                <button class="nyt-search-modal-close" id="nytSearchClose" aria-label="닫기">&#x2715;</button>
                <label class="nyt-search-label" for="nytSearchInput">검색</label>
                <form class="nyt-search-form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                    <input class="nyt-search-input" id="nytSearchInput" type="search" name="s"
                           placeholder="검색어를 입력하세요…"
                           value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off">
                    <button class="nyt-search-submit" type="submit">검색</button>
                </form>
            </div>
        </div>

        <!-- 섹션 네비게이션 (GNB) -->
        <nav class="nyt-nav sticky top-0 z-50" id="lp-gnav">
            <div class="<?php echo $container_class; ?> flex items-center primary-menu-container">
                <div class="lp-desktop-nav">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                } else {
                    echo '<a href="' . admin_url( 'nav-menus.php' ) . '" style="font-size:0.75rem;padding:0.5rem 0;">메뉴를 설정해 주세요.</a>';
                }
                ?>
                </div>
                <button class="lp-hamburger ml-auto text-black" id="lpNyt-hamburger" aria-label="메뉴 열기" aria-expanded="false" aria-controls="lpNyt-mobile-nav">
                    <span></span><span></span><span></span>
                </button>
            </div>
            <div class="lp-mobile-nav" id="lpNyt-mobile-nav" style="border-top:1px solid #000;">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                }
                ?>
            </div>
        </nav>

    <?php elseif ( $current_theme_style === 'basic' ) : ?>
        <!-- ═══════════════════════════════════════════════════
             Basic 스킨 헤더 — 액센트 바 + 로고 + GNB
             ═══════════════════════════════════════════════════ -->

        <!-- 상단 4px 액센트 바 -->
        <div class="basic-header-accent"></div>

        <!-- 헤더 메인 로우 -->
        <header class="basic-header-main" style="position:relative;">
            <div class="<?php echo $container_class; ?> basic-header-inner">
                <?php echo lp_get_logo_html( 'basic' ); ?>
                <div class="basic-header-actions">
                    <span style="font-size:0.75rem;color:#6c757d;display:none;" class="md:inline-block">
                        <?php echo lp_header_date_html(); ?>
                    </span>
                    <button class="basic-search-btn" id="basicSearchOpen" aria-label="검색">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>
                    <button class="lp-dm-btn" id="lpDarkModeBtn" aria-label="다크/라이트 모드 전환" title="다크/라이트 모드">
                        <svg class="lp-dm-icon lp-dm-moon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg class="lp-dm-icon lp-dm-sun" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <?php echo lp_get_user_menu_html( 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
            </div>

            <!-- 검색 드롭다운 -->
            <div class="basic-search-drop" id="basicSearchDrop">
                <form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                    <input type="search" name="s" placeholder="검색어를 입력하세요…"
                           value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off" id="basicSearchInput">
                    <button type="submit">검색</button>
                </form>
            </div>
        </header>

        <!-- 상단 배너 슬롯 (선택) -->
        <?php if ( ! empty( $lp_banner_top ) ) : ?>
        <div style="background:#f8f9fa;border-bottom:1px solid #dee2e6;padding:0.5rem 0;">
            <div class="<?php echo $container_class; ?> flex justify-center">
                <?php echo $lp_banner_top; // phpcs:ignore WordPress.Security.EscapeOutput ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- GNB -->
        <nav class="basic-nav" id="lp-gnav">
            <div class="<?php echo $container_class; ?> flex items-center primary-menu-container">
                <div class="lp-desktop-nav">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                } else {
                    echo '<a href="' . admin_url( 'nav-menus.php' ) . '" style="font-size:0.8125rem;padding:0.6rem 0;color:#0d6efd;">메뉴를 설정해 주세요.</a>';
                }
                ?>
                </div>
                <button class="lp-hamburger ml-auto" id="lpBasic-hamburger" aria-label="메뉴 열기" aria-expanded="false" aria-controls="lpBasic-mobile-nav">
                    <span></span><span></span><span></span>
                </button>
            </div>
            <div class="lp-mobile-nav" id="lpBasic-mobile-nav">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'fallback_cb' => false ] );
                }
                ?>
            </div>
        </nav>

    <?php elseif ( $current_theme_style === 'amber-journal' ) : ?>
        <!-- ═══════════════════════════════════════════════════
             엠버 저널 (Amber Journal) 헤더
             ═══════════════════════════════════════════════════ -->

        <!-- ① 상단 유틸리티 바 -->
        <div class="aj-topbar">
            <div class="<?php echo $container_class; ?> aj-topbar-inner">
                <span class="aj-topbar-date"><?php echo lp_header_date_html(); ?></span>
                <div class="aj-topbar-util">
                    <?php
                    if ( has_nav_menu( 'top-menu' ) ) {
                        wp_nav_menu( [ 'theme_location' => 'top-menu', 'container' => false, 'menu_class' => 'aj-topbar-util', 'fallback_cb' => false ] );
                    } else {
                        echo '<a href="' . esc_url( wp_login_url() ) . '">로그인</a>';
                        echo '<a href="' . esc_url( wp_registration_url() ) . '">회원가입</a>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- ② 마스트헤드 (로고 + 배너) -->
        <div class="aj-masthead">
            <div class="<?php echo $container_class; ?> aj-masthead-inner">
                <?php echo lp_get_logo_html( 'amber-journal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>

                <?php if ( ! empty( $lp_banner_top ) ) : ?>
                <div class="hidden md:flex flex-1 max-w-[500px] h-20 items-center justify-center overflow-hidden">
                    <?php echo $lp_banner_top; // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
                <?php else : ?>
                <div class="aj-masthead-banner hidden md:flex">배너 광고 영역</div>
                <?php endif; ?>

                <div class="aj-masthead-actions">
                    <button class="aj-search-btn" id="ajSearchOpen" aria-label="검색">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        검색
                    </button>
                    <button class="lp-dm-btn" id="lpDarkModeBtn" aria-label="다크/라이트 모드 전환" title="다크/라이트 모드">
                        <svg class="lp-dm-icon lp-dm-moon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg class="lp-dm-icon lp-dm-sun" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <?php echo lp_get_user_menu_html( 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
                </div>
            </div>
        </div>

        <!-- ③ 메인 네비게이션 -->
        <nav class="aj-gnav" id="aj-gnav">
            <div class="<?php echo $container_class; ?> aj-gnav-inner">
                <div class="aj-gnav-menu" id="ajGnavMenu">
                <?php
                if ( has_nav_menu( 'primary-menu' ) ) {
                    wp_nav_menu( [ 'theme_location' => 'primary-menu', 'container' => false, 'menu_class' => 'aj-gnav-ul', 'fallback_cb' => false ] );
                } else {
                    echo '<ul><li><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">메뉴를 설정해 주세요.</a></li></ul>';
                }
                ?>
                </div>
                <button class="aj-hamburger-btn" id="ajHamburger" aria-label="메뉴 열기" aria-expanded="false" aria-controls="ajGnavMenu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </nav>

        <!-- ④ 속보 바 -->
        <?php
        $aj_breaking = new WP_Query( [ 'posts_per_page' => 4, 'ignore_sticky_posts' => 1, 'no_found_rows' => true ] );
        if ( $aj_breaking->have_posts() ) :
        ?>
        <div class="aj-breaking">
            <div class="<?php echo $container_class; ?> aj-breaking-inner">
                <span class="aj-breaking-label">속보</span>
                <div class="aj-breaking-items">
                    <?php while ( $aj_breaking->have_posts() ) : $aj_breaking->the_post(); ?>
                    <div class="aj-breaking-item">
                        <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( [ 36, 36 ], [ 'class' => 'aj-breaking-thumb' ] ); ?>
                        <?php else : ?>
                        <span class="aj-breaking-thumb"></span>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>" class="aj-breaking-title"><?php the_title(); ?></a>
                    </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
