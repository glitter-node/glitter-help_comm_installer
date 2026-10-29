<?php

namespace Modules\Glitter\HelpCommInstaller;

use App\Extension\AbstractModule;
use Modules\Glitter\HelpCommInstaller\Listeners\TemplateInstalledListener;

class Module extends AbstractModule
{
    /**
     * HelpComm 템플릿 설치 시 필수 게시판을 준비하는 리스너를 등록합니다.
     *
     * core.templates.installed 는 템플릿 설치 트랜잭션 내부에서 발화하므로,
     * 리스너는 반드시 동기로 실행되어 생성 실패 예외가 설치 호출자에게 전파되어야 합니다.
     */
    public function getHookListeners(): array
    {
        return [
            TemplateInstalledListener::class,
        ];
    }

    /**
     * 이 모듈은 권한·역할·메뉴·시더를 제공하지 않습니다.
     */
    public function getPermissions(): array
    {
        return [];
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getAdminMenus(): array
    {
        return [];
    }

    public function getSeeders(): array
    {
        return [];
    }

    public function getMetadata(): array
    {
        return [
            'author' => 'Glitter.kr',
            'brand' => 'Glitter.kr',
        ];
    }
}
