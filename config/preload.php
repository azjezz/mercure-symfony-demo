<?php

declare(strict_types=1);

(static function (): void {
    require_once dirname(__DIR__) . '/vendor/azjezz/psl/src/preload.php';

    $symfony_preload_file = dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';

    if (file_exists($symfony_preload_file)) {
        require_once $symfony_preload_file;

        return;
    }

    $symfony_preload_file = dirname(__DIR__) . '/var/cache/dev/App_KernelDevDebugContainer.preload.php';

    if (file_exists($symfony_preload_file)) {
        require_once $symfony_preload_file;

        return;
    }
})();
