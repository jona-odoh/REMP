<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class PageHelp
{
    /**
     * Resolve contextual help content for the active route.
     *
     * @return array{title: string, summary: string, steps: array<int, string>, tips: string}
     */
    public static function forCurrentRoute(): array
    {
        $routeName = Route::currentRouteName();
        $guides = config('page_help', []);

        if ($routeName && isset($guides[$routeName]) && is_array($guides[$routeName])) {
            return $guides[$routeName];
        }

        return $guides['default'] ?? [
            'title' => 'Page Guide',
            'summary' => 'Kogi State Ministry of Culture and Tourism internal back-office.',
            'steps' => [],
            'tips' => '',
        ];
    }
}
