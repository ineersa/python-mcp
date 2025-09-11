<?php

declare(strict_types=1);

namespace App\Tools;

use App\Service\PythonService;

final class PythonTool
{
    public function __construct(
        private readonly PythonService $pythonService,
    ) {
    }

    /**
     * Entry point for python tool.
     *
     * @return array{result: string}
     */
    public function __invoke(string $code): array
    {
        return ['result' => $this->pythonService->execute($code)];
    }
}
