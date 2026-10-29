<?php

namespace App\Upgrades\Data\Ext\Modules\GlitterHelpCommInstaller\V0_1_1\Migrations;

use App\Contracts\Repositories\TemplateRepositoryInterface;
use App\Extension\Upgrade\DataMigration;
use App\Extension\UpgradeContext;
use Modules\Glitter\HelpCommInstaller\Services\RequiredBoardInitializer;
use Modules\Glitter\HelpCommInstaller\Support\RequiredBoardPlan;

/**
 * 이미 설치된 활성 사용자 템플릿에도 누락 게시판만 보완합니다.
 *
 * 게시판 조회와 생성은 기존 RequiredBoardInitializer/BoardService 계약을
 * 재사용하며, 기존 게시판·게시글·댓글은 변경하지 않습니다.
 */
class InitializeActiveAcademyBoards implements DataMigration
{
    public function name(): string
    {
        return 'InitializeActiveAcademyBoards';
    }

    public function run(UpgradeContext $context): void
    {
        $template = app(TemplateRepositoryInterface::class)->findActiveByType('user');

        if ($template === null || ! RequiredBoardPlan::handlesTemplate((string) $template->identifier)) {
            $context->logger->info('[help-comm:0.1.1] 지원 대상 활성 사용자 템플릿 없음 — 스킵');

            return;
        }

        app(RequiredBoardInitializer::class)->initialize();
        $context->logger->info('[help-comm:0.1.1] 활성 템플릿 게시판 멱등 초기화 완료', [
            'template' => $template->identifier,
        ]);
    }
}
