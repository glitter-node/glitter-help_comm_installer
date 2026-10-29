<?php

namespace Modules\Glitter\HelpCommInstaller\Support;

use LogicException;

/**
 * HelpComm 및 Academy 설치에 필요한 게시판의 선언적 순서와 순수 검증 규칙입니다.
 *
 * 이 클래스는 DB나 Laravel 컨테이너를 사용하지 않으므로, 설치 전에 순서·멱등성·
 * 비활성 게시판 충돌을 비운영 테스트로 검증할 수 있습니다.
 */
final class RequiredBoardPlan
{
    public const TEMPLATE_IDENTIFIER = 'sirsoft-help_comm';

    public const ACADEMY_TEMPLATE_IDENTIFIER = 'glitter-learning_academy';

    public const BOARD_MODULE_IDENTIFIER = 'sirsoft-board';

    /**
     * @return array<string, array{name: array{ko: string, en: string}, description: array{ko: string, en: string}}>
     */
    public static function definitions(): array
    {
        return [
            'notice' => [
                'name' => ['ko' => '공지사항', 'en' => 'Notice'],
                'description' => ['ko' => '사이트 공지사항', 'en' => 'Site notices'],
            ],
            'free' => [
                'name' => ['ko' => '자유게시판', 'en' => 'Free Board'],
                'description' => ['ko' => '자유롭게 소통하는 게시판', 'en' => 'A board for open discussion'],
            ],
            'qna' => [
                'name' => ['ko' => '질문과 답변', 'en' => 'Q&A'],
                'description' => ['ko' => '공개 질문과 답변', 'en' => 'Public questions and answers'],
            ],
            'resources' => [
                'name' => ['ko' => '자료실', 'en' => 'Resources'],
                'description' => ['ko' => '자료를 공유하는 게시판', 'en' => 'A board for shared resources'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::definitions());
    }

    public static function handlesTemplate(string $templateIdentifier): bool
    {
        return in_array($templateIdentifier, [
            self::TEMPLATE_IDENTIFIER,
            self::ACADEMY_TEMPLATE_IDENTIFIER,
        ], true);
    }

    /**
     * @param  array<string, array{is_active: bool}|object|null>  $existingBoards
     * @return list<string>
     */
    public static function missingSlugs(array $existingBoards): array
    {
        self::assertExistingBoardsAreActive($existingBoards);

        return array_values(array_filter(
            self::slugs(),
            static fn (string $slug): bool => ! array_key_exists($slug, $existingBoards)
                || $existingBoards[$slug] === null,
        ));
    }

    /**
     * @param  array<string, array{is_active: bool}|object|null>  $existingBoards
     */
    public static function assertExistingBoardsAreActive(array $existingBoards): void
    {
        foreach (self::slugs() as $slug) {
            if (! array_key_exists($slug, $existingBoards) || $existingBoards[$slug] === null) {
                continue;
            }

            $board = $existingBoards[$slug];
            $active = is_array($board)
                ? ($board['is_active'] ?? false)
                : ($board->is_active ?? false);

            if (! (bool) $active) {
                throw new LogicException("Required board '{$slug}' exists but is inactive.");
            }
        }
    }
}
