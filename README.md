# HelpComm Board Installer

`glitter-help_comm_installer`는 `sirsoft-help_comm` 또는
`glitter-learning_academy` 템플릿 설치 시 필요한 `notice`, `free`, `qna`,
`resources` 게시판을 준비하는 보조 모듈입니다.

## 관리자 목록 및 의존성

- `hidden: false`로 일반 관리자 모듈 목록에 표시됩니다.
- `sirsoft-board >= 1.2.1`이 활성 상태여야 합니다.
- 새 템플릿 설치 시 게시판 생성은 `core.templates.installed` 이벤트에서 수행됩니다.
- 게시판 생성은 `sirsoft-board`의 `BoardService::createBoard()`를 사용합니다.

## 생명주기와 데이터 보존

이 모듈은 자체 migration, seeder, 권한, 역할, 관리자 메뉴를 제공하지 않습니다.

- `module:install`: 모듈만 설치합니다. 게시판은 생성하지 않습니다.
- `module:activate`: 리스너를 활성화합니다. 게시판은 변경하지 않습니다.
- `template:install sirsoft-help_comm` 또는 `template:install glitter-learning_academy`: 누락된 필수 게시판만 순서대로 생성합니다.
- `module:deactivate`: 리스너가 동작하지 않게 되지만 게시판과 게시물 데이터는 보존됩니다.
- `module:uninstall`: 이 모듈의 등록 정보만 제거하며 `sirsoft-board`가 소유한 게시판, 게시글, 댓글, 역할, 권한은 삭제하지 않습니다.
- 0.1.1 업그레이드는 현재 활성 사용자 템플릿이 `glitter-learning_academy` 또는 `sirsoft-help_comm`인 경우 같은 멱등 초기화를 한 번 수행합니다.
- `template:update`, `template:activate`, `template:uninstall`: 이 모듈의 설치 이벤트 리스너는 호출되지 않습니다.

모듈을 비활성화하거나 제거하면 이후 `template:install sirsoft-help_comm`에서
필수 게시판 자동 초기화가 실행되지 않을 수 있습니다. 운영 전환 시에는
`sirsoft-board`가 활성이고 이 모듈도 활성 상태인지 확인해야 합니다.
