<div align="center">

# 🚀 LaraPress for WordPress
**11년 차 발행인의 노하우가 집약된 AI 최적화 뉴스 플랫폼**

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
LaraPress는 11년 차 발행인의 실전 노하우가 집약된 라라벨 기반 뉴스 솔루션 LaraPress를 워드프레스 환경에 맞게 이식한 고성능 뉴스 포털 테마입니다.

# 주요 특징 (Core Features)
* 다양한 레이아웃 제공: Customizer 설정을 통해 프레시(Fresh), 클래식(Classic), 미니멀(Minimal), 엠버 저널(Amber Journal) 등 다양한 디자인 스킨을 변경할 수 있습니다.
* AI 검색 엔진 최적화: SearchGPT, Perplexity 등 차세대 AI 검색 엔진이 기사 구조를 완벽히 이해할 수 있도록 구조화된 데이터를 제공합니다.
* 한국형 언론사 특화 설정: 발행인, 편집인, 청소년 보호 책임자 등 국내 인터넷 신문사 법적 필수 정보를 손쉽게 관리하고 푸터에 자동 노출합니다.
* 인터랙티브 기사 뷰
  * 스티키 바(Sticky Bar): 스크롤 시 기사 제목과 진행 상태를 보여주는 상단 고정 바 제공.
  * 열독률 진행바: 독자의 기사 읽기 진행도를 실시간 시각화.
  * 반응형 위젯: 드래그 앤 드롭 방식의 홈 화면 위젯 빌더로 포털 사이트형 메인 화면 구성.
  * 기사 반응 시스템: '유용해요', '흥미진진해요' 등 독자 반응 수집 기능.
 
# 기술 스택 (Technical Stack)
* ackend: PHP (WordPress Core API)
* Frontend: Tailwind CSS (UI), Vanilla JS, CSS 3D Transform
* Database: WordPress WP_Query & Meta API
* Special Controls: Customizer API 기반의 JSON 위젯 빌더

# 프로젝트 구조 (Project Structure)
* functions.php: 테마 핵심 로직, Customizer 설정 폼, 조회수 추적 및 반응 시스템 핸들러.
* index.php: 스킨 엔진 기반의 메인 레이아웃 및 Breaking News 위젯 출력.
* header.php / footer.php: 공통 헤더/푸터 및 스킨별 조건부 렌더링 로직.
* single.php: 기사 상세 페이지, 기자 프로필 박스 및 SNS 공유 기능.
* archive.php: 카테고리/태그별 리스트, 그리드, 웹진형 레이아웃 지원.
* assets/: 위젯 빌더 전용 JS/CSS 및 테마 자산.

# 설치 및 설정 (Setup)
* 워드프레스 관리자 화면에서 외모 > 테마 > 새로 추가를 통해 테마를 업로드합니다.
* 외모 > 사용자 정의하기(Customizer) 메뉴로 이동합니다.
* 레이아웃 스킨 설정: 원하는 디자인 스타일을 선택합니다.
* 언론사 푸터 정보 설정: 법적 고시 정보를 입력합니다.
* 홈 화면 레이아웃: 위젯을 추가하고 순서를 조정하여 메인 페이지를 구성합니다.

# 향후 로드맵 (Roadmap)
* [ ] 미디어 라이브러리 심화 연동: 라라벨 MediaController 로직 이식.
* [ ] [ ] 자체 방문자 분석 대시보드: SiteVisit 모델 기반 통계 시스템 구축.
* [ ] [ ] 게시판 권한 고도화: 라라벨 BbsController의 상세 권한 설정 기능 이식.
* [ ] [ ] 서브 스킨 고도화: 뉴욕타임즈 스타일 등 클래식 스킨의 디자인 디테일 강화.

# 라이선스 (License)
이 테마는 GPL v2에 따라 배포됩니다.
