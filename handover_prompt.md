# 🚀 Project Handover: LaraPress for WordPress

## 📌 1. 프로젝트 개요
* **Original Source:** 라라벨 기반 뉴스 솔루션 `LaraBoard` (11년 차 발행인 김동주 노하우 집약)
* **Goal:** 라라벨의 MVC 로직과 `swn-style` 디자인을 워드프레스 테마/플러그인 환경으로 이식
* **Core Value:**
    * 발행인의 실전 경험이 녹아있는 뉴스 운영 워크플로우 반영
    * AI 검색 엔진(SearchGPT, Perplexity) 최적화 및 구조화된 데이터 제공
    * 한국형 인터넷 신문사 법적 필수 정보 관리 기능 탑재

## 🛠 2. 기술 스택 (Current Stack)
* **Backend:** PHP (WordPress Core)
* **Frontend:** Tailwind CSS (CDN 연동), CSS 3D Transform
* **WP APIs:** Customizer API, Custom Post Types (CPT), Shortcodes

## 📂 3. 핵심 파일 구조 및 기능

### 🔌 플러그인: `larapress-board`
* **Path:** `/wp-content/plugins/larapress-board/larapress-board.php`
* **Features:**
    * **CPT:** `lara_post` / **Taxonomy:** `lara_board` 등록
    * **Shortcode:** `[laraboard slug="..."]`를 통한 게시판 출력
    * **Routing:** `mode` 파라미터(list, read, write)에 따라 하나의 페이지에서 작동하는 SPA 방식 메커니즘

### 🎨 테마: `larapress-theme`
* **`functions.php`**:
    * **Menu:** `top-menu`, `primary-menu`, `footer-menu-1`, `footer-menu-2` 위치 등록
    * **Customizer:** 한국 언론사 특화 설정 폼 구현 (발행인, 편집인, 청소년보호책임자, 개인정보보호책임자 분리 관리)
* **`index.php`**:
    * **Theme Cloud:** `?layout=` 파라미터 기반 실시간 스킨 전환 엔진 (SWN, NYT, Basic 스킨 지원)
    * **Hero Section:** 압도적 시각 효과를 위한 CSS 3D '글래스 큐브' 애니메이션 적용
    * **Main Layout:** * `Portal Home`: 데스크 선정 탑 기사 위젯(1+2 배열) 및 카테고리별 최신글 자동 로드
        * `Article View`: 캡션 포함 이미지, 기자 프로필 박스, SNS 공유 기능이 포함된 한국형 기사 레이아웃
        * `Footer`: Customizer 설정값이 동적으로 반영되는 법적 고시 영역

## ✅ 4. 주요 해결 이슈 (Bug Fixes)
* **Layout Integrity:** 메인 뉴스 위젯의 PHP 루프 내 `div` 태그 닫힘 불일치로 인한 사이드바 밀림 현상 수정 완료
* **Officer Info:** 발행인/편집인 필드 분리 및 청소년/개인정보보호책임자 정보 입력 폼 추가 완성

## 📝 5. 향후 작업 로드맵 (To-Do)
1. **Media Integration:** 라라벨 `MediaController` 로직을 워드프레스 미디어 라이브러리와 심화 연동
2. **Statistics:** `SiteVisit` 모델을 이식하여 자체 방문자 분석 대시보드 구축
3. **BBS Expansion:** 댓글 시스템 및 게시판 상세 권한 설정 (라라벨 `BbsController` 기능 이식)
4. **Layout Skins:** `newyorktimes-style` 등 서브 스킨의 세부 디자인 고도화

## 🤖 Claude 지시사항 (Prompting)
1. `/wp-content/themes/larapress-theme/`와 `/wp-content/plugins/larapress-board/` 코드를 먼저 분석할 것.
2. `functions.php`의 Customizer 설정값이 `index.php` 푸터와 기사 뷰에 어떻게 바인딩되는지 확인할 것.
3. 타 플러그인의 Deprecated 로그는 무시하고, `LaraPress` 전용 코드의 품질과 구조적 무결성에 집중할 것.
