<?php

declare(strict_types=1);

namespace RobertBoes\FilamentPasskeys\Tests;

use Filament\Panel;
use Filament\PanelProvider;
use RobertBoes\FilamentPasskeys\FilamentPasskeysPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test')
            ->plugin(FilamentPasskeysPlugin::make());
    }
}
