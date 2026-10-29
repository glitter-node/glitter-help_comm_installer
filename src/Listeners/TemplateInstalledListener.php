<?php

namespace Modules\Glitter\HelpCommInstaller\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Modules\Glitter\HelpCommInstaller\Services\RequiredBoardInitializer;
use Modules\Glitter\HelpCommInstaller\Support\RequiredBoardPlan;

final class TemplateInstalledListener implements HookListenerInterface
{
    public function __construct(private RequiredBoardInitializer $initializer) {}

    public static function getSubscribedHooks(): array
    {
        return [
            'core.templates.installed' => [
                'method' => 'handleTemplateInstalled',
                'priority' => 10,
                'sync' => true,
            ],
        ];
    }

    public function handle(...$args): void
    {
        $this->handleTemplateInstalled(...$args);
    }

    public function handleTemplateInstalled(string $templateIdentifier): void
    {
        if (! RequiredBoardPlan::handlesTemplate($templateIdentifier)) {
            return;
        }

        $this->initializer->initialize();
    }
}
