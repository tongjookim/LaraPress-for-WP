<?php
/**
 * LaraPress Theme Footer
 * get_footer() 에 의해 index.php, search.php, archive.php 에서 공통 포함됩니다.
 * lp_skin_vars()로 스킨 변수를 가져와 푸터 출력에 사용합니다.
 */
extract( lp_skin_vars() );
$lp_hero_enable = get_theme_mod( 'lp_hero_enable', '1' );
?>

    <!-- 하단 Hero Section — 2D 일러스트 (메인 하단, 프론트 페이지만) -->
    <?php if ( $lp_hero_enable !== '0' && ( is_front_page() || is_home() ) ) : ?>
    <section class="lp-hero">
        <div class="lp-hero__text">
            <span class="lp-hero__eyebrow">LARAPRESS · NEWS ENGINE</span>
            <h2 class="lp-hero__title">5분만에 뚝딱!<br><span>나만의 인터넷신문을 만드세요</span></h2>
            <p class="lp-hero__sub">로고, 위젯, 색상까지 웹 UI로 손쉽게 만드세요</p>
        </div>
        <div class="lp-illust">
            <svg class="lp-illust__svg" viewBox="0 0 600 420" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="lp-ill-t"><title id="lp-ill-t">회벽에 웹사이트 벽화를 그리는 사람들: 사다리 위에서 롤러질하고, 발판에 올라 간판에 못을 박고, 무릎 꿇고 아랫단을 칠하고, 클립보드를 들고 확인한다</title><defs><linearGradient id="lg1" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ece3d5"/><stop offset=".45" stop-color="#e9dfcf"/><stop offset="1" stop-color="#d2c9ba"/></linearGradient><linearGradient id="lg2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#b1aa9e"/><stop offset=".45" stop-color="#a8a093"/><stop offset="1" stop-color="#868076"/></linearGradient><linearGradient id="lg3" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#bfb9af"/><stop offset=".45" stop-color="#b9b3a8"/><stop offset="1" stop-color="#98938a"/></linearGradient><clipPath id="cw"><rect x="172" y="46" width="280" height="256" rx="10"/></clipPath><linearGradient id="lg4" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#446fd5"/><stop offset=".45" stop-color="#2f5fd0"/><stop offset="1" stop-color="#2851b1"/></linearGradient><clipPath id="cb"><rect x="186" y="92" width="252" height="72" rx="6"/></clipPath><linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5b8de0"/><stop offset="1" stop-color="#f3b3a0"/></linearGradient><linearGradient id="lg5" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4e77d7"/><stop offset=".45" stop-color="#2f5fd0"/><stop offset="1" stop-color="#2a56bb"/></linearGradient><linearGradient id="lg6" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ae794e"/><stop offset=".45" stop-color="#a0612f"/><stop offset="1" stop-color="#804e26"/></linearGradient><linearGradient id="lg7" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#d2d5d9"/><stop offset=".45" stop-color="#b9bec5"/><stop offset="1" stop-color="#787c80"/></linearGradient><linearGradient id="lg8" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#d2d5d9"/><stop offset=".45" stop-color="#b9bec5"/><stop offset="1" stop-color="#787c80"/></linearGradient><linearGradient id="lg9" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#d2d5d9"/><stop offset=".45" stop-color="#b9bec5"/><stop offset="1" stop-color="#787c80"/></linearGradient><linearGradient id="wood" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#c58b4e"/><stop offset="1" stop-color="#8b5a2b"/></linearGradient><radialGradient id="rg10" cx=".38" cy=".32" r=".75"><stop offset="0" stop-color="#e7be9e"/><stop offset=".55" stop-color="#e2b089"/><stop offset="1" stop-color="#b0896b"/></radialGradient><linearGradient id="lg11" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#688fc6"/><stop offset=".45" stop-color="#3d6fb6"/><stop offset="1" stop-color="#2c5083"/></linearGradient><linearGradient id="lg12" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#5d616a"/><stop offset=".45" stop-color="#2f3440"/><stop offset="1" stop-color="#22252e"/></linearGradient><radialGradient id="rg13" cx=".38" cy=".32" r=".75"><stop offset="0" stop-color="#d09f7b"/><stop offset=".55" stop-color="#c68a5e"/><stop offset="1" stop-color="#9a6c49"/></radialGradient><linearGradient id="lg14" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#69a48b"/><stop offset=".45" stop-color="#3f8a6a"/><stop offset="1" stop-color="#2d634c"/></linearGradient><linearGradient id="lg15" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#656972"/><stop offset=".45" stop-color="#3a3f4a"/><stop offset="1" stop-color="#2a2d35"/></linearGradient><radialGradient id="rg16" cx=".38" cy=".32" r=".75"><stop offset="0" stop-color="#9f785f"/><stop offset=".55" stop-color="#8a5a3c"/><stop offset="1" stop-color="#6c462f"/></radialGradient><linearGradient id="lg17" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#9775b7"/><stop offset=".45" stop-color="#7a4ea3"/><stop offset="1" stop-color="#583875"/></linearGradient><linearGradient id="lg18" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#72767e"/><stop offset=".45" stop-color="#4a4f5a"/><stop offset="1" stop-color="#353941"/></linearGradient><radialGradient id="rg19" cx=".38" cy=".32" r=".75"><stop offset="0" stop-color="#f3d2b8"/><stop offset=".55" stop-color="#f0c8a8"/><stop offset="1" stop-color="#bb9c83"/></radialGradient><linearGradient id="lg20" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#e19e5a"/><stop offset=".45" stop-color="#d9822b"/><stop offset="1" stop-color="#9c5e1f"/></linearGradient><linearGradient id="lg21" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#5a657a"/><stop offset=".45" stop-color="#2b3a55"/><stop offset="1" stop-color="#1f2a3d"/></linearGradient><linearGradient id="lg22" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5a657a"/><stop offset=".45" stop-color="#2b3a55"/><stop offset="1" stop-color="#1f2a3d"/></linearGradient><radialGradient id="sun" cx="0" cy="0" r="1"><stop offset="0" stop-color="#fff6df" stop-opacity=".55"/><stop offset=".6" stop-color="#fff6df" stop-opacity="0"/></radialGradient><filter id="plaster" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency=".9" numOctaves="2" seed="7"/><feColorMatrix values="0 0 0 0 .45  0 0 0 0 .4  0 0 0 0 .33  0 0 0 -.9 .7"/><feComposite in2="SourceGraphic" operator="in"/></filter><filter id="grain" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency="1.4" numOctaves="1" seed="3"/><feColorMatrix values="0 0 0 0 .3  0 0 0 0 .3  0 0 0 0 .28  0 0 0 -1 .75"/><feComposite in2="SourceGraphic" operator="in"/></filter><filter id="blur3" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="2.5"/></filter></defs><rect width="600" height="420" fill="url(#lg1)"/><rect width="600" height="420" filter="url(#plaster)" opacity=".55"/><rect y="340" width="600" height="14" fill="url(#lg2)"/><rect y="354" width="600" height="66" fill="url(#lg3)"/><rect y="354" width="600" height="66" filter="url(#grain)" opacity=".5"/><path d="M0 386 H600 M120 354 L96 420 M300 354 L300 420 M480 354 L504 420" stroke="#8f887c" stroke-width="1" opacity=".55"/><rect x="172" y="46" width="280" height="256" rx="10" fill="#f4efe6" opacity=".9"/><rect x="172" y="46" width="280" height="256" rx="10" fill="none" stroke="#7d7466" stroke-width=".8" stroke-dasharray="3 2" opacity=".8"/><g fill="#e8d9a8" opacity=".9"><rect x="166" y="42" width="26" height="7" transform="rotate(-38 179 46)"/><rect x="432" y="42" width="26" height="7" transform="rotate(38 445 46)"/><rect x="166" y="299" width="26" height="7" transform="rotate(38 179 302)"/><rect x="432" y="299" width="26" height="7" transform="rotate(-38 445 302)"/></g><g clip-path="url(#cw)"><rect x="172" y="46" width="280" height="32" fill="url(#lg4)"/></g><circle cx="190" cy="62" r="5" fill="#e0706a"/><circle cx="206" cy="62" r="5" fill="#e3bd4f"/><circle cx="222" cy="62" r="5" fill="#5cb874"/><rect x="242" y="54" width="194" height="16" rx="8" fill="#f2f0ea" opacity=".95"/><rect x="252" y="60" width="70" height="4" rx="2" fill="#9fb4e0"/><g clip-path="url(#cb)"><path d="M186 160 Q240 116 300 138 T438 126" stroke="#6b6357" stroke-width=".9" fill="none" stroke-dasharray="2 2"/><path d="M186 164 Q260 140 330 150 T438 146" stroke="#6b6357" stroke-width=".9" fill="none" stroke-dasharray="2 2"/><circle cx="376" cy="112" r="12" stroke="#6b6357" stroke-width=".9" fill="none" stroke-dasharray="2 2"/><rect x="186" y="92" width="252" height="72" fill="none" stroke="#6b6357" stroke-width=".8" stroke-dasharray="3 2"/><path d="M186 92 H262 Q258 106 264 120 Q259 136 263 164 H186z" fill="url(#sky)"/><circle cx="230" cy="112" r="10" fill="#fbe7a1"/><path d="M186 164 V146 Q214 128 240 140 Q252 135 262 138 Q259 150 263 164z" fill="#3f6b4c"/><path d="M186 164 V154 Q224 146 263 156 V164z" fill="#2c513a"/></g><path d="M263 120 q2 8 0 14" stroke="#5b8de0" stroke-width="2" fill="none" stroke-linecap="round" opacity=".8"/><path d="M258 164 q1 5 0 9" stroke="#2c513a" stroke-width="2" stroke-linecap="round"/><rect x="186" y="178" width="80" height="72" rx="6" fill="#ecc75f"/><circle cx="226" cy="206" r="13" fill="#d99a2b"/><rect x="200" y="232" width="52" height="6" rx="3" fill="#fff3cf"/><rect x="272" y="178" width="80" height="72" rx="6" fill="none" stroke="#6b6357" stroke-width=".9" stroke-dasharray="3 2"/><path d="M272 178 h80 v40 q-20 4 -40 -2 q-20 -6 -40 4z" fill="#8cc69a"/><path d="M282 214 L300 190 L312 204 L322 194 L342 216 q-30 4 -60 -2z" fill="#3f8a57"/><path d="M284 240 L302 220 L314 232 L324 224 L340 240" stroke="#6b6357" stroke-width=".9" fill="none" stroke-dasharray="2 2"/><rect x="358" y="178" width="80" height="72" rx="6" fill="none" stroke="#6b6357" stroke-width=".9" stroke-dasharray="3 2"/><path d="M386 214 h24 M398 202 v24" stroke="#6b6357" stroke-width=".9" stroke-dasharray="2 2"/><rect x="186" y="262" width="150" height="7" rx="3.5" fill="#8f8a80"/><rect x="186" y="276" width="104" height="7" rx="3.5" fill="none" stroke="#6b6357" stroke-width=".8" stroke-dasharray="2 2"/><rect x="366" y="262" width="72" height="22" rx="11" fill="url(#lg5)"/><rect x="384" y="271" width="36" height="4" rx="2" fill="#dfe8fb"/><rect x="172" y="292" width="280" height="10" fill="none" stroke="#6b6357" stroke-width=".8" stroke-dasharray="3 2"/><path d="M172 292 H336 q-3 5 0 10 H172z" fill="#c8443f"/><rect x="172" y="46" width="280" height="256" rx="10" filter="url(#plaster)" opacity=".35" style="mix-blend-mode:multiply"/><rect x="476" y="96" width="108" height="54" rx="3" fill="#000" opacity=".25" filter="url(#blur3)" transform="translate(5 6)"/><rect x="476" y="96" width="108" height="54" rx="3" fill="url(#lg6)"/><path d="M480 108 q50 -3 100 1 M480 122 q46 3 100 -1 M480 138 q52 -2 100 2" stroke="#7a4520" stroke-width=".8" fill="none" opacity=".6"/><text x="530" y="132" text-anchor="middle" font-family="Georgia,'Times New Roman',serif" font-weight="700" font-size="24" letter-spacing="2" fill="#f7ecd6">OPEN</text><g fill="#5b5f66"><circle cx="483" cy="103" r="2"/><circle cx="577" cy="103" r="2"/><circle cx="483" cy="143" r="2"/></g><circle cx="575" cy="143" r="1.6" fill="#7c828a"/><path d="M118 398 L150 372 L360 370 L392 404 L380 416 L130 418z" fill="#d7dde3" opacity=".85"/><path d="M150 372 L170 410 M240 371 L250 416 M320 371 L330 412" stroke="#b7bfc8" stroke-width="1.2" opacity=".8"/><path d="M194 396 L242 76 M222 396 L270 76" stroke="#000" stroke-width="5" opacity=".16" filter="url(#blur3)"/><path d="M183.8 369.6 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M187.6 339.3 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M191.5 308.9 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M195.3 278.5 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M199.1 248.2 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M202.9 217.8 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M206.7 187.5 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M210.5 157.1 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M214.4 126.7 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M218.2 96.4 h28" stroke="#7d8691" stroke-width="4.2" stroke-linecap="round"/><path d="M180 400 L222 66 M208 400 L250 66" stroke="#9aa3ad" stroke-width="5.5" stroke-linecap="round"/><path d="M178.4 400 L220.4 66 M206.4 400 L248.4 66" stroke="#eef1f4" stroke-width="1.4" opacity=".8"/><path d="M177 402 h8 M205 402 h8" stroke="#222" stroke-width="4" stroke-linecap="round"/><ellipse cx="152" cy="404" rx="19" ry="3.5" fill="#000" opacity=".2" filter="url(#blur3)"/><path d="M135 384 V404 A11 3.5 0 0 0 157 404 V384z" fill="url(#lg7)"/><ellipse cx="146" cy="384" rx="11" ry="3.5" fill="#5b8de0"/><ellipse cx="146" cy="384" rx="11" ry="3.5" fill="none" stroke="#8a9098" stroke-width="1"/><path d="M135 385 q2 7 1 12" stroke="#5b8de0" stroke-width="2.4" fill="none" stroke-linecap="round"/><rect x="135" y="391" width="22" height="7" fill="#5b8de0" opacity=".85"/><ellipse cx="366" cy="402" rx="18" ry="3.5" fill="#000" opacity=".2" filter="url(#blur3)"/><path d="M350 385 V402 A10 3.5 0 0 0 370 402 V385z" fill="url(#lg8)"/><ellipse cx="360" cy="385" rx="10" ry="3.5" fill="#c8443f"/><ellipse cx="360" cy="385" rx="10" ry="3.5" fill="none" stroke="#8a9098" stroke-width="1"/><path d="M350 386 q2 7 1 12" stroke="#c8443f" stroke-width="2.4" fill="none" stroke-linecap="round"/><rect x="350" y="392" width="20" height="7" fill="#c8443f" opacity=".85"/><ellipse cx="464" cy="410" rx="19" ry="3.5" fill="#000" opacity=".2" filter="url(#blur3)"/><path d="M447 390 V410 A11 3.5 0 0 0 469 410 V390z" fill="url(#lg9)"/><ellipse cx="458" cy="390" rx="11" ry="3.5" fill="#3f8a57"/><ellipse cx="458" cy="390" rx="11" ry="3.5" fill="none" stroke="#8a9098" stroke-width="1"/><path d="M447 391 q2 7 1 12" stroke="#3f8a57" stroke-width="2.4" fill="none" stroke-linecap="round"/><rect x="447" y="397" width="22" height="7" fill="#3f8a57" opacity=".85"/><path d="M108 404 l30 -3 l8 7 l-34 3z" fill="#6c7480"/><path d="M114 404 l20 -2 l5 4 l-22 2z" fill="#5b8de0"/><path d="M534 324 L560 396" stroke="#6e4422" stroke-width="5" stroke-linecap="round"/><path d="M506 324 L494 398 M534 324 L546 398" stroke="url(#wood)" stroke-width="6" stroke-linecap="round"/><path d="M500 372 H540 M503 350 H537" stroke="#9c6633" stroke-width="5" stroke-linecap="round"/><rect x="498" y="316" width="44" height="9" rx="2" fill="#a56b35"/><rect x="498" y="316" width="44" height="2.5" fill="#d29b5f"/><ellipse cx="540" cy="400" rx="36" ry="5" fill="#000" opacity=".2" filter="url(#blur3)"/><g transform="translate(209 279) scale(1 1)"><path d="M-11 -120 L-16 -102" stroke="#325b95" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M-16 -102 L-4 -90" stroke="#c09674" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="-0.8" cy="-86.8" rx="4.6" ry="5.2" fill="#c09674"/><path d="M0 -80 L10 -56" stroke="#262a33" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M10 -56 L4 -30" stroke="#262a33" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M-2 -29 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M-2 -29 h19" stroke="#0b0f17" stroke-width="1.6"/><path d="M0 -80 L5 -40" stroke="url(#lg12)" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M5 -40 L2 0" stroke="url(#lg12)" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M-4 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M-4 1 h19" stroke="#0b0f17" stroke-width="1.6"/><rect x="-14" y="-86" width="28" height="12" rx="5" fill="url(#lg12)"/><path d="M-16 -120 Q-19 -99 -14 -80 L14 -80 Q21 -99 18 -120 Q1 -128 -16 -120z" fill="url(#lg11)"/><path d="M7 -115 Q15 -97 10 -82" stroke="#2e5388" stroke-width="1.4" fill="none" opacity=".6"/><rect x="-2.5" y="-133" width="7" height="10" fill="#c79b79"/><ellipse cx="-5.5" cy="-140" rx="2" ry="3" fill="#cb9e7b"/><ellipse cx="2" cy="-141" rx="8.6" ry="10.6" fill="url(#rg10)"/><path d="M-7 -142 Q-8 -154 2 -153 Q11 -153 11 -144 Q6 -149 0 -148 Q-3 -144 -5 -138z" fill="#2a221c"/><path d="M3 -144.6 q2 -1 4 0 M8.5 -144.4 q1.4 -.6 2.4 .2" stroke="#261f19" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="5" cy="-142" rx="1.05" ry="1.25" fill="#1b1410"/><ellipse cx="9.6" cy="-142" rx=".9" ry="1.15" fill="#1b1410"/><path d="M9 -141 q1.8 3 -.2 4" stroke="#aa8467" stroke-width="1" fill="none" stroke-linecap="round"/><path d="M5.2 -134.6 q2.4 1 4.2 -.2" stroke="#886a52" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="-0.5" cy="-138" rx="3.2" ry="2.4" fill="#d0a27e" opacity=".5"/><path d="M11 -120 L24 -138" stroke="#3d6fb6" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M24 -138 L30 -154" stroke="#e2b089" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="31.6" cy="-158.2" rx="4.6" ry="5.2" fill="#e2b089"/><path d="M30 -154 L36 -166 L48 -166" stroke="#3b4048" stroke-width="2.4" fill="none" stroke-linejoin="round"/><rect x="34" y="-176" width="26" height="10" rx="5" fill="#5b8de0"/><rect x="34" y="-176" width="26" height="3.5" rx="1.75" fill="#8fb0ea"/></g><g transform="translate(276 396) scale(1 1)"><ellipse cx="14" cy="1" rx="30" ry="5" fill="#000" opacity=".22" filter="url(#blur3)"/><path d="M-6 -90 L-4.0 -80.0" stroke="#347157" stroke-width="11" stroke-linecap="round" fill="none"/><path d="M-4.0 -80.0 L-2 -70" stroke="#a87550" stroke-width="7.4" stroke-linecap="round" fill="none"/><path d="M-2 -70 L14 -58" stroke="#a87550" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="17.6" cy="-55.3" rx="4.6" ry="5.2" fill="#a87550"/><path d="M0 -50 L-4 -6" stroke="#2e323b" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M-4 -6 L-32 -3" stroke="#2e323b" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M-38 -2 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M-38 -2 h19" stroke="#0b0f17" stroke-width="1.6"/><path d="M0 -50 L22 -36" stroke="url(#lg15)" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M22 -36 L20 0" stroke="url(#lg15)" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M14 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M14 1 h19" stroke="#0b0f17" stroke-width="1.6"/><rect x="-14" y="-56" width="28" height="12" rx="5" fill="url(#lg15)"/><path d="M-11 -91 Q-14 -70 -14 -50 L14 -50 Q26 -70 23 -91 Q6 -99 -11 -91z" fill="url(#lg14)"/><path d="M12 -86 Q20 -68 10 -52" stroke="#2f6850" stroke-width="1.4" fill="none" opacity=".6"/><rect x="2.5" y="-104" width="7" height="10" fill="#ae7953"/><ellipse cx="0.5" cy="-111" rx="2" ry="3" fill="#b27c55"/><ellipse cx="8" cy="-112" rx="8.6" ry="10.6" fill="url(#rg13)"/><path d="M-1 -110 Q-2 -124 8 -123.5 Q17 -123 17 -114 Q10 -120 4 -118 Q1 -113 2 -106z" fill="#3b2316"/><ellipse cx="1" cy="-123" rx="5" ry="4.5" fill="#3b2316"/><path d="M9 -115.6 q2 -1 4 0 M14.5 -115.4 q1.4 -.6 2.4 .2" stroke="#352014" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="11" cy="-113" rx="1.05" ry="1.25" fill="#1b1410"/><ellipse cx="15.6" cy="-113" rx=".9" ry="1.15" fill="#1b1410"/><path d="M15 -112 q1.8 3 -.2 4" stroke="#946846" stroke-width="1" fill="none" stroke-linecap="round"/><path d="M11.2 -105.6 q2.4 1 4.2 -.2" stroke="#775338" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="5.5" cy="-109" rx="3.2" ry="2.4" fill="#b67f56" opacity=".5"/><path d="M14 -90 L21.0 -86.0" stroke="#3f8a6a" stroke-width="11" stroke-linecap="round" fill="none"/><path d="M21.0 -86.0 L28 -82" stroke="#c68a5e" stroke-width="7.4" stroke-linecap="round" fill="none"/><path d="M28 -82 L40 -88" stroke="#c68a5e" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="44.0" cy="-90.0" rx="4.6" ry="5.2" fill="#c68a5e"/><path d="M42 -88 L58 -93" stroke="#8b5a2b" stroke-width="3.2" stroke-linecap="round"/><path d="M58 -93 l6 -2" stroke="#d9d3c4" stroke-width="5" stroke-linecap="round"/><path d="M63 -95 l4 -1" stroke="#c8443f" stroke-width="5" stroke-linecap="round"/></g><g transform="translate(424 402) scale(-1 1)"><ellipse cx="-14" cy="1" rx="30" ry="5" fill="#000" opacity=".22" filter="url(#blur3)"/><path d="M-11 -120 L-12 -98" stroke="#644086" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M-12 -98 L2 -92" stroke="#754c33" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="6.1" cy="-90.2" rx="4.6" ry="5.2" fill="#754c33"/><path d="M0 -80 L-4 -40" stroke="#3b3f48" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M-4 -40 L-6 0" stroke="#3b3f48" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M-12 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M-12 1 h19" stroke="#0b0f17" stroke-width="1.6"/><path d="M0 -80 L6 -40" stroke="url(#lg18)" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M6 -40 L8 0" stroke="url(#lg18)" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M2 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M2 1 h19" stroke="#0b0f17" stroke-width="1.6"/><rect x="-14" y="-86" width="28" height="12" rx="5" fill="url(#lg18)"/><path d="M-17 -120 Q-20 -99 -14 -80 L14 -80 Q20 -99 17 -120 Q0 -128 -17 -120z" fill="url(#lg17)"/><path d="M6 -115 Q14 -97 10 -82" stroke="#5c3a7a" stroke-width="1.4" fill="none" opacity=".6"/><rect x="-3.5" y="-133" width="7" height="10" fill="#794f35"/><ellipse cx="-6.5" cy="-140" rx="2" ry="3" fill="#7c5136"/><ellipse cx="1" cy="-141" rx="8.6" ry="10.6" fill="url(#rg16)"/><path d="M-8 -142 Q-9 -154 1 -153 Q10 -153 10 -144 Q5 -149 -1 -148 Q-4 -144 -6 -138z" fill="#141210"/><path d="M2 -144.6 q2 -1 4 0 M7.5 -144.4 q1.4 -.6 2.4 .2" stroke="#12100e" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="4" cy="-142" rx="1.05" ry="1.25" fill="#1b1410"/><ellipse cx="8.6" cy="-142" rx=".9" ry="1.15" fill="#1b1410"/><path d="M8 -141 q1.8 3 -.2 4" stroke="#68442d" stroke-width="1" fill="none" stroke-linecap="round"/><path d="M4.2 -134.6 q2.4 1 4.2 -.2" stroke="#533624" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="-1.5" cy="-138" rx="3.2" ry="2.4" fill="#7f5337" opacity=".5"/><path d="M11 -120 L22 -140" stroke="#7a4ea3" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M22 -140 L26 -162" stroke="#8a5a3c" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="26.8" cy="-166.4" rx="4.6" ry="5.2" fill="#8a5a3c"/><rect x="-4" y="-108" width="20" height="26" rx="2" fill="#9b7a52" transform="rotate(-8 6 -95)"/><rect x="-1" y="-104" width="15" height="19" fill="#f6f4ef" transform="rotate(-8 6 -95)"/><rect x="3" y="-110" width="7" height="4" rx="1" fill="#6c7480" transform="rotate(-8 6 -95)"/></g><g transform="translate(520 318) scale(1 1)"><ellipse cx="14" cy="1" rx="30" ry="5" fill="#000" opacity=".22" filter="url(#blur3)"/><path d="M-11 -120 L-8 -142" stroke="#b26b23" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M-8 -142 L6 -158" stroke="#ccaa8f" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="9.0" cy="-161.4" rx="4.6" ry="5.2" fill="#ccaa8f"/><path d="M0 -80 L-4 -40" stroke="#222e44" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M-4 -40 L-6 0" stroke="#222e44" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M-12 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M-12 1 h19" stroke="#0b0f17" stroke-width="1.6"/><path d="M0 -80 L6 -40" stroke="url(#lg21)" stroke-width="13.5" stroke-linecap="round" fill="none"/><path d="M6 -40 L8 0" stroke="url(#lg21)" stroke-width="10.5" stroke-linecap="round" fill="none"/><path d="M2 1 q0 -7 7 -7 q8 0 11 5 q1 3 -2 3z" fill="#1f2937"/><path d="M2 1 h19" stroke="#0b0f17" stroke-width="1.6"/><rect x="-14" y="-86" width="28" height="12" rx="5" fill="url(#lg21)"/><path d="M-16 -120 Q-19 -99 -14 -80 L14 -80 Q21 -99 18 -120 Q1 -128 -16 -120z" fill="url(#lg20)"/><path d="M7 -115 Q15 -97 10 -82" stroke="#a36220" stroke-width="1.4" fill="none" opacity=".6"/><rect x="-2.5" y="-133" width="7" height="10" fill="#d3b094"/><ellipse cx="-5.5" cy="-140" rx="2" ry="3" fill="#d8b497"/><ellipse cx="2" cy="-141" rx="8.6" ry="10.6" fill="url(#rg19)"/><path d="M-7 -143 Q-7 -154 2 -153.5 Q11 -153 11 -145z" fill="url(#lg22)"/><path d="M6 -146 q9 -1 13 2 q-6 2 -13 1z" fill="#222e44"/><path d="M-7 -143 q-1 6 2 8" stroke="#6b4b2a" stroke-width="2.4" fill="none"/><path d="M3 -144.6 q2 -1 4 0 M8.5 -144.4 q1.4 -.6 2.4 .2" stroke="#604426" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="5" cy="-142" rx="1.05" ry="1.25" fill="#1b1410"/><ellipse cx="9.6" cy="-142" rx=".9" ry="1.15" fill="#1b1410"/><path d="M9 -141 q1.8 3 -.2 4" stroke="#b4967e" stroke-width="1" fill="none" stroke-linecap="round"/><path d="M5.2 -134.6 q2.4 1 4.2 -.2" stroke="#907865" stroke-width="1.1" fill="none" stroke-linecap="round"/><ellipse cx="-0.5" cy="-138" rx="3.2" ry="2.4" fill="#ddb89b" opacity=".5"/><path d="M11 -120 L26 -136" stroke="#d9822b" stroke-width="10" stroke-linecap="round" fill="none"/><path d="M26 -136 L22 -160" stroke="#f0c8a8" stroke-width="6.6" stroke-linecap="round" fill="none"/><ellipse cx="21.3" cy="-164.4" rx="4.6" ry="5.2" fill="#f0c8a8"/><g class="lp-ill-hammer"><path d="M22 -162 L14 -182" stroke="#7a4a22" stroke-width="3.4" stroke-linecap="round"/><rect x="6" y="-188" width="16" height="7" rx="1.5" fill="#4b5058" transform="rotate(-22 14 -184)"/></g></g><rect width="600" height="420" fill="url(#sun)"/></svg>
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
                    <?php if ( get_theme_mod( 'lp_footer_logo_light', '' ) || get_theme_mod( 'lp_footer_logo_dark', '' ) ) :
                        echo lp_get_footer_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput — 내부에서 이스케이프 처리됨
                    elseif ( has_custom_logo() ) :
                        the_custom_logo();
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
                        <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김아무개' ) ); ?></span>
                        <?php else : ?>
                        <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김아무개' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '박아무개' ) ); ?></span>
                        <?php endif; ?>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김아무개' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김아무개' ) ); ?></span>
                    </p>
                    <p class="lp-cf-legal-row">
                        <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                        <span class="lp-cf-sep">|</span>
                        <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2026.11.25' ) ); ?></span>
                    </p>
                    <p class="lp-cf-legal-row">
                        <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '서울특별시 강남구 테헤란로 123' ) ); ?></span>
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
                        <?php echo lp_get_footer_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput — 내부에서 이스케이프 처리됨 ?>
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
                    <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김아무개' ) ); ?></span>
                    <?php else : ?>
                    <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김아무개' ) ); ?></span>
                    <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '박아무개' ) ); ?></span>
                    <?php endif; ?>
                    <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김아무개' ) ); ?></span>
                    <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김아무개' ) ); ?></span>
                </p>
                <p>
                    <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                    <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                    <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2026.11.25' ) ); ?></span>
                </p>
                <p>
                    <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '서울특별시 강남구 테헤란로 123' ) ); ?></span>
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
                        <?php echo lp_get_footer_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput — 내부에서 이스케이프 처리됨 ?>
                    </p>
                    <?php $aj_fdesc = get_theme_mod( 'lp_footer_company_desc', '' ); ?>
                    <?php if ( $aj_fdesc ) : ?>
                    <p class="aj-footer-desc"><?php echo esc_html( $aj_fdesc ); ?></p>
                    <?php endif; ?>
                    <div class="aj-footer-legal">
                        <p>
                            <?php if ( get_theme_mod( 'lp_pub_editor_same', '0' ) === '1' ) : ?>
                            <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김아무개' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <?php else : ?>
                            <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김아무개' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '박아무개' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <?php endif; ?>
                            <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김아무개' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김아무개' ) ); ?></span>
                        </p>
                        <p>
                            <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                            <span class="aj-fl-sep">|</span>
                            <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2026.11.25' ) ); ?></span>
                        </p>
                        <p>
                            <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '서울특별시 강남구 테헤란로 123' ) ); ?></span>
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
                    <h2 class="text-2xl font-black text-white mb-4 tracking-tighter lp-swn-footer-logo">
                        <?php echo lp_get_footer_logo_html(); // phpcs:ignore WordPress.Security.EscapeOutput — 내부에서 이스케이프 처리됨 ?>
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
                    <span><strong>발행·편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_pub_editor_name', '김아무개' ) ); ?></span>
                    <?php else : ?>
                    <span><strong>발행인:</strong> <?php echo esc_html( get_theme_mod( 'lp_publisher_name', '김아무개' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>편집인:</strong> <?php echo esc_html( get_theme_mod( 'lp_editor_name', '박아무개' ) ); ?></span>
                    <?php endif; ?>
                    <span class="hidden md:inline">|</span>
                    <span><strong>청소년보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_youth_officer', '김아무개' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>개인정보보호책임자:</strong> <?php echo esc_html( get_theme_mod( 'lp_privacy_officer', '김아무개' ) ); ?></span>
                </p>
                <p class="md:flex md:gap-4 md:flex-wrap">
                    <span><strong>정기간행물등록번호:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_num', '강원 아00000' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>등록일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_reg_date', '2025.06.25' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>창간일자:</strong> <?php echo esc_html( get_theme_mod( 'lp_est_date', '2026.11.25' ) ); ?></span>
                </p>
                <p class="md:flex md:gap-4 md:flex-wrap">
                    <span><strong>주소:</strong> <?php echo esc_html( get_theme_mod( 'lp_address', '서울특별시 강남구 테헤란로 123' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>대표전화:</strong> <?php echo esc_html( get_theme_mod( 'lp_phone', '1588-0000' ) ); ?></span>
                    <span class="hidden md:inline">|</span>
                    <span><strong>이메일:</strong> <?php echo esc_html( get_theme_mod( 'lp_email', 'support@larapress.io' ) ); ?></span>
                </p>
                <p class="mt-4 text-slate-600">
                    <?php echo esc_html( get_theme_mod( 'lp_copyright', 'Copyright © ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.' ) ); ?>
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

    <!-- TOP TOP 버튼 — 맨 위로 스크롤 (전 스킨 공통) -->
    <button type="button" id="lpTopTopBtn" class="lp-totop" aria-label="맨 위로 이동" title="맨 위로">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>

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

    <!-- TOP TOP 버튼 공통 JS -->
    <script>
    (function () {
        var btn = document.getElementById('lpTopTopBtn');
        if (!btn) return;

        var SHOW_AFTER = 400; // 이만큼 스크롤 내려가면 버튼 표시
        var ticking = false;

        function toggleVisibility() {
            btn.classList.toggle('is-visible', window.scrollY > SHOW_AFTER);
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(toggleVisibility);
                ticking = true;
            }
        }, { passive: true });

        toggleVisibility(); // 새로고침 등으로 이미 스크롤된 상태로 로드된 경우 대비

        btn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    })();
    </script>

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

        /* 다국어 전환 드롭다운 (햄버거와 동일한 토글 로직 재사용) */
        initHamburger('lpLangBtn', 'lpLangMenu');

        /* 유저 메뉴 드롭다운 (로그인/회원가입 · 내 프로필/내 기사 목록) */
        initHamburger('lpUserBtn', 'lpUserDropdown');

        /* 유저 메뉴 개인화 정보 — 풀페이지 캐시(WP Super Cache 등) 환경에서는
           로그인 사용자의 이름·링크를 서버에서 바로 출력하면 캐시된 정적 HTML에
           그대로 구워져 다른 방문자(다른 회원 포함)에게도 동일하게 노출된다.
           ("내 기사 목록"에 다른 회원 글이 뜨던 문제가 바로 이 캐시 문제였음)
           그래서 기본 마크업은 항상 "비로그인" 상태로만 렌더링해두고,
           캐시되지 않는 admin-ajax.php 요청으로 방문자 본인의 로그인 정보를
           매번 새로 받아와 이 스크립트가 클라이언트에서 채워 넣는다. */
        (function () {
            var userBtn  = document.getElementById('lpUserBtn');
            var userMenu = document.getElementById('lpUserDropdown');
            if (!userBtn || !userMenu) return;

            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=lp_user_menu'
            })
            .then(function (res) { return res.json(); })
            .then(function (res) {
                var data = res && res.success ? res.data : null;
                if (!data || !data.loggedIn) return; // 비로그인 → 기본 마크업 그대로 둠

                userBtn.title = data.name || '';
                userBtn.textContent = '';
                var avatar = document.createElement('span');
                avatar.className = 'lp-user-avatar';
                avatar.textContent = data.initial || '';
                userBtn.appendChild(avatar);

                userMenu.textContent = '';
                var nameLi = document.createElement('li');
                nameLi.className = 'lp-user-dropdown__name';
                nameLi.textContent = data.name || '';
                userMenu.appendChild(nameLi);

                [
                    { label: '내 프로필', url: data.profileUrl },
                    { label: '내 기사 목록', url: data.postsUrl }
                ].forEach(function (item) {
                    if (!item.url) return;
                    var li = document.createElement('li');
                    var a  = document.createElement('a');
                    a.href = item.url;
                    a.textContent = item.label;
                    li.appendChild(a);
                    userMenu.appendChild(li);
                });
            })
            .catch(function () { /* 네트워크 오류 시 로그인/회원가입 기본 상태 유지 */ });
        })();

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

    /* ── TRENDING 티커 세로 슬라이딩 (SWN 스킨) ── */
    (function () {
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        document.querySelectorAll('.lp-ticker-track').forEach(function (track) {
            var list  = track.querySelector('.lp-ticker-list');
            var items = list ? list.children : [];
            var n     = items.length;
            if (!list || n < 2) return;

            var h = items[0].getBoundingClientRect().height;
            var i = 0;

            function tick() {
                i++;
                list.style.transform = 'translateY(-' + (i * h) + 'px)';
                if (i >= n / 2) { // 절반(= 복제되기 전 목록)까지 오면 다음 틱에서 이음매 없이 리셋
                    setTimeout(function () {
                        list.style.transition = 'none';
                        i = 0;
                        list.style.transform = 'translateY(0)';
                        void list.offsetHeight; // 강제 리플로우
                        list.style.transition = '';
                    }, 520);
                }
            }

            var timer = setInterval(tick, 3200);
            track.addEventListener('mouseenter', function () { clearInterval(timer); });
            track.addEventListener('mouseleave', function () { timer = setInterval(tick, 3200); });
        });
    })();

    /* ── 위클리 추천 슬라이더 (SWN 스킨 · Home 27) ── */
    (function () {
        var track = document.getElementById('lpWeeklyTrack');
        if (!track) return;
        document.querySelectorAll('.lp-slider-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var dir = parseInt(btn.getAttribute('data-lp-slide'), 10) || 1;
                var card = track.querySelector('.lp-weekly-card');
                var step = card ? card.getBoundingClientRect().width + 20 : 220;
                track.scrollBy({ left: dir * step * 2, behavior: 'smooth' });
            });
        });

        /* 마우스로 잡고 옆으로 끄는(드래그) 스크롤 — 터치 기기는 네이티브 스크롤/탭이 이미
           정상 동작하므로 건너뜀. 터치를 흉내낸 synthetic mouse 이벤트가 여기 걸리면
           lp-dragging(→pointer-events:none)이 걸려 카드 클릭(기사 이동)이 막힘. */
        if (window.matchMedia && window.matchMedia('(pointer: coarse)').matches) {
            return;
        }

        var DRAG_THRESHOLD = 4; // 이 값(px) 이상 움직여야 "드래그"로 간주
        var isDown = false, startX = 0, startScrollLeft = 0, moved = 0;

        track.addEventListener('mousedown', function (e) {
            isDown = true;
            moved = 0;
            startX = e.pageX;
            startScrollLeft = track.scrollLeft;
            // 주의: lp-dragging(→ pointer-events:none)은 여기서 바로 추가하지 않는다.
            // mousedown 즉시 추가하면, 움직임 없는 "그냥 클릭"의 mouseup 히트테스트 시점에
            // 카드가 pointer-events:none 상태가 되어 링크 이동이 막혀버린다.
        });

        document.addEventListener('mousemove', function (e) {
            if (!isDown) return;
            var delta = e.pageX - startX;
            moved = Math.max(moved, Math.abs(delta));
            // 실제로 드래그로 인정될 만큼 움직였을 때만 스크롤 드래그 모드로 전환
            if (moved > DRAG_THRESHOLD) {
                if (!track.classList.contains('lp-dragging')) {
                    track.classList.add('lp-dragging');
                }
                e.preventDefault();
                track.scrollLeft = startScrollLeft - delta;
            }
        });

        document.addEventListener('mouseup', function () {
            if (!isDown) return;
            isDown = false;
            track.classList.remove('lp-dragging');
        });

        // 드래그로 끌었을 때는 카드 클릭(기사 이동)이 발생하지 않도록 차단
        track.addEventListener('click', function (e) {
            if (moved > 5) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
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
