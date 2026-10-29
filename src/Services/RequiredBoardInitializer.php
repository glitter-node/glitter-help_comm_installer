<?php

namespace Modules\Glitter\HelpCommInstaller\Services;

use App\Contracts\Repositories\ModuleRepositoryInterface;
use LogicException;
use Modules\Glitter\HelpCommInstaller\Support\RequiredBoardPlan;
use Modules\Sirsoft\Board\Repositories\Contracts\BoardRepositoryInterface;
use Modules\Sirsoft\Board\Services\BoardService;

/**
 * HelpComm 템플릿에 필요한 게시판을 공식 BoardService 경로로 준비합니다.
 *
 * 호출 시점은 core.templates.installed 리스너이며, TemplateManager의 설치 DB
 * 트랜잭션 내부입니다. 이 클래스는 자체적으로 예외를 삼키거나 보상 삭제하지
 * 않습니다. 생성 실패는 그대로 전파되어 설치 트랜잭션의 롤백 대상이 됩니다.
 */
final class RequiredBoardInitializer
{
    public function __construct(
        private BoardRepositoryInterface $boardRepository,
        private BoardService $boardService,
        private ModuleRepositoryInterface $moduleRepository,
    ) {}

    public function initialize(): void
    {
        $this->assertBoardModuleIsActive();

        // 먼저 네 slug를 모두 검사하여 비활성 충돌을 생성 전에 발견합니다.
        $existingBoards = [];
        foreach (RequiredBoardPlan::slugs() as $slug) {
            $existingBoards[$slug] = $this->boardRepository->findBySlug($slug);
        }

        $missingSlugs = RequiredBoardPlan::missingSlugs($existingBoards);

        foreach ($missingSlugs as $slug) {
            $definition = RequiredBoardPlan::definitions()[$slug];
            $this->boardService->createBoard($this->creationData($slug, $definition));
        }

        $this->assertRequiredBoardsAreActive();
    }

    private function assertBoardModuleIsActive(): void
    {
        if ($this->moduleRepository->findActiveByIdentifier(RequiredBoardPlan::BOARD_MODULE_IDENTIFIER) === null) {
            throw new LogicException(
                "The required module '".RequiredBoardPlan::BOARD_MODULE_IDENTIFIER."' must be active before installing the HelpComm template."
            );
        }
    }

    private function assertRequiredBoardsAreActive(): void
    {
        $boards = [];
        foreach (RequiredBoardPlan::slugs() as $slug) {
            $boards[$slug] = $this->boardRepository->findBySlug($slug);
        }

        $missing = RequiredBoardPlan::missingSlugs($boards);
        if ($missing !== []) {
            throw new LogicException(
                'Required HelpComm boards were not created: '.implode(', ', $missing).'.'
            );
        }
    }

    /**
     * BoardService::createBoard()에 전달할 저장 계약입니다.
     *
     * HTTP FormRequest를 우회하기 위한 임의 SQL을 사용하지 않고, BoardService가
     * 저장하는 Board 모델의 공식 기본값과 동일한 명시적 데이터를 전달합니다.
     * board_manager_ids는 설치 컨텍스트에서 가짜 관리자를 만들지 않도록 생략합니다.
     *
     * @param  array{name: array{ko: string, en: string}, description: array{ko: string, en: string}}  $definition
     * @return array<string, mixed>
     */
    private function creationData(string $slug, array $definition): array
    {
        return [
            'name' => $definition['name'],
            'description' => $definition['description'],
            'slug' => $slug,
            'is_active' => true,
            'type' => 'basic',
            'per_page' => 20,
            'per_page_mobile' => 15,
            'order_by' => 'created_at',
            'order_direction' => 'DESC',
            'categories' => [],
            'show_view_count' => false,
            'secret_mode' => 'disabled',
            'use_comment' => true,
            'use_reply' => true,
            'comment_order' => 'ASC',
            'use_report' => false,
            'use_scrap' => true,
            'use_reaction' => true,
            'use_comment_reaction' => true,
            'active_reaction_types' => ['like'],
            'max_reply_depth' => 5,
            'reply_delete_policy' => 'cascade',
            'secret_reply_visibility' => 'thread_path',
            'max_comment_depth' => 10,
            'use_file_upload' => false,
            'notify_admin_on_post' => true,
            'notify_author' => true,
            'blocked_keywords' => [],
            'permissions' => [],
            'add_to_menu' => false,
        ];
    }
}
