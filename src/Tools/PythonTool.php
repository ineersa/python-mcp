<?php

declare(strict_types=1);

namespace App\Tools;

use App\Service\PythonService;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

#[McpTool(
    name: 'python',
    description: <<<DESC
Use this tool to execute Python code in your chain of thought. The code will not be shown to the user. This tool should be used for internal reasoning, but not for code that is intended to be visible to the user (e.g. when creating plots, tables, or files).
When you send a message containing python code to python, it will be executed in a stateless docker container, and the stdout of that process will be returned to you.
DESC,
    annotations: new ToolAnnotations(
        title: 'Execute Python code',
    ),
)]
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
