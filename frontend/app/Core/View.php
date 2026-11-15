<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewsRoot = dirname(__DIR__) . '/Views';
        $viewFile = $viewsRoot . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            return;
        }

        extract($data, EXTR_SKIP);

        if ($layout === null) {
            require $viewFile;
            return;
        }

        $contentView = $viewFile;
        $layoutFile = $viewsRoot . '/' . str_replace('.', '/', $layout) . '.php';

        if (!is_file($layoutFile)) {
            http_response_code(500);
            echo 'Layout not found: ' . htmlspecialchars($layout, ENT_QUOTES, 'UTF-8');
            return;
        }

        require $layoutFile;
    }

    public static function partial(string $partial, array $data = []): void
    {
        $file = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $partial) . '.php';
        if (!is_file($file)) {
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }
}
