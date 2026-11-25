<div align="center">

# 🚀 LaraPress for WordPress
**한국형 인터넷신문 구축을 위한 AI 검색 최적화 뉴스 포털 테마**

<p align="center">
  <img src="https://img.shields.io/badge/version-1.0.0-007bc1?style=for-the-badge" alt="version">
  <img src="https://img.shields.io/badge/PHP-8.0+-777bb4?style=for-the-badge&logo=php&logoColor=white" alt="php">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38bdf8?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="tailwind">
  <img src="https://img.shields.io/badge/License-GPLv2-97ca00?style=for-the-badge" alt="license">
</p>

LaraPress는 라라벨 기반 뉴스 솔루션의 로직을 워드프레스로 이식하여 <br> SearchGPT와 Perplexity에 최적화된 구조화된 데이터를 제공합니다.

---
</div>

# LaraPress for WordPress
LaraPress는 라라벨 기반 뉴스 솔루션 LaraPress를 워드프레스 환경에 맞게 이식한 뉴스 포털 테마입니다.
인터넷신문을 새로 만들거나 기존 블로그를 언론사형 사이트로 바꾸려는 운영자가, 코드를 고치지 않고 알림판에서 디자인·법정 고지 정보·홈 화면 구성을 직접 설정할 수 있도록 만들었습니다.

# 이런 필요에 맞춰 만들었습니다 (Who It's For)
* 인터넷신문 사이트에 필요한 고지 정보(발행인, 편집인, 청소년보호책임자, 개인정보보호책임자, 정기간행물등록번호, 등록일자, 창간일자, 주소, 대표전화, 이메일)를 한곳에서 입력하고 푸터에 빠짐없이 표시하고 싶은 경우.
* 개발자 없이 포털·신문사형 메인 화면(주요 뉴스, 최신 뉴스, 섹션별 목록, 갤러리)을 직접 구성하고 순서를 바꾸고 싶은 경우.
* 기사가 검색 엔진과 AI 검색(SearchGPT, Perplexity 등)에서 구조를 정확히 이해되도록 하고 싶은 경우.
* 로고·파비콘·스플래시, 배너 광고 슬롯, 소셜 공유(카카오톡 등)를 별도 플러그인 없이 갖추고 싶은 경우.
* 신문사 성격에 맞게 디자인 스킨(스타일)을 바꿔 가며 운영하고 싶은 경우.

# 주요 특징 (Core Features)
* 다양한 레이아웃 제공: 프레시(Fresh), 클래식(Classic), 미니멀(Minimal), 엠버 저널(Amber Journal) 스킨을 선택할 수 있으며, 아카이브(카테고리·태그) 목록은 리스트, 2단·3단 그리드, 웹진형을 지원합니다.
* AI 검색 엔진 최적화: SearchGPT, Perplexity 등 차세대 AI 검색 엔진이 기사 구조를 이해할 수 있도록 구조화된 데이터를 제공합니다.
* 한국형 언론사 특화 설정: 발행인, 편집인, 청소년 보호 책임자 등 국내 인터넷 신문사 필수 고지 정보를 입력하면 푸터에 자동 노출합니다. 발행인과 편집인이 같은 경우 통합 표시도 지원합니다.
* 알림판 "테마 설정" 페이지: 일반·브랜딩, 레이아웃, 언론사 정보, 배너 광고, 엠버 저널, 외부 연동을 탭별 입력 폼으로 관리합니다. 엠버 저널 탭은 해당 레이아웃을 선택했을 때만 나타납니다.
* 인터랙티브 기사 뷰
  * 스티키 바(Sticky Bar): 스크롤 시 기사 제목과 진행 상태를 보여주는 상단 고정 바 제공.
  * 열독률 진행바: 독자의 기사 읽기 진행도를 실시간 시각화.
  * 반응형 위젯: 드래그 앤 드롭 방식의 홈 화면 위젯 빌더로 포털 사이트형 메인 화면 구성.
  * 기사 반응 시스템: '유용해요', '흥미진진해요' 등 독자 반응 수집 기능.
* PRO 라이선스: 프리미엄 스킨, 고급 아카이브 레이아웃, 배너 광고 슬롯, 홈 위젯 빌더의 섹션 그룹·갤러리 위젯은 라이선스 서버 인증 후 사용할 수 있습니다. 라이선스가 없으면 저장된 설정은 보존하되 무료 기능으로 표시합니다.

# 기술 스택 (Technical Stack)
* Backend: PHP 8.0+ (WordPress Core API)
* Frontend: Tailwind CSS (UI), Vanilla JS, 인라인 SVG
* Database: WordPress WP_Query & Meta API, `theme_mod` 기반 설정 저장
* Admin: Customizer API의 설정 정의를 그대로 읽어 만드는 "테마 설정" 페이지(탭 폼, 서버 측 검증·sanitize), JSON 기반 위젯 빌더
* 라이선스: 외부 라이선스 서버 REST API 인증, wp-cron 일 1회 재검증, 방문자 요청에서는 원격 호출을 하지 않음

# 프로젝트 구조 (Project Structure)
* functions.php: 테마 핵심 로직, Customizer 설정 정의, 조회수 추적 및 반응 시스템 핸들러.
* index.php: 스킨 엔진 기반의 메인 레이아웃 및 Breaking News 위젯 출력.
* header.php / footer.php: 공통 헤더/푸터 및 스킨별 조건부 렌더링 로직, 하단 히어로.
* single.php: 기사 상세 페이지, 기자 프로필 박스 및 SNS 공유 기능.
* archive.php: 카테고리/태그별 리스트, 그리드, 웹진형 레이아웃 지원.
* inc/theme-settings-page.php: 알림판 "테마 설정" 페이지(메뉴, 탭, 폼 렌더, 저장).
* inc/customize-controls.php: 홈 위젯 빌더·아카이브 스타일 선택 등 커스텀 컨트롤.
* inc/license.php: PRO 라이선스 인증 및 기능 잠금.
* assets/: 위젯 빌더·테마 설정 페이지 전용 JS/CSS 및 테마 자산.

# 설치 및 설정 (Setup)
* 워드프레스 관리자 화면에서 외모 > 테마 > 새로 추가를 통해 테마를 업로드하고 활성화합니다.
* 알림판 왼쪽의 "테마 설정" 메뉴로 이동합니다.
* 일반·브랜딩: 파비콘, 로고, 헤더, 스플래시를 설정합니다.
* 레이아웃: 디자인 스킨, 홈 화면 위젯, 사이드바·아카이브 스타일을 고릅니다. 위젯을 추가하고 순서를 조정해 메인 페이지를 구성합니다.
* 언론사 정보: 발행인·편집인 등 법적 고시 정보를 입력합니다.
* 라이선스: PRO 기능을 쓰려면 "라이선스" 탭에서 키를 입력해 인증합니다.

# 향후 로드맵 (Roadmap)
* [ ] 미디어 라이브러리 심화 연동: 라라벨 MediaController 로직 이식.
* [ ] 자체 방문자 분석 대시보드: SiteVisit 모델 기반 통계 시스템 구축.
* [ ] 게시판 권한 고도화: 라라벨 BbsController의 상세 권한 설정 기능 이식.
* [ ] 서브 스킨 고도화: 뉴욕타임즈 스타일 등 클래식 스킨의 디자인 디테일 강화.

# 라이선스 (License)
이 테마는 GPL v2에 따라 배포됩니다.
