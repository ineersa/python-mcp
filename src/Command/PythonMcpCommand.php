<?php

declare(strict_types=1);

namespace App\Command;

use App\Tools\PythonTool;
use Mcp\Schema\ToolAnnotations;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'python-mcp',
    description: 'Add a short description for your command',
)]
class PythonMcpCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private ContainerInterface $container,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @param ConsoleOutput $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $description = <<<DESC
Use this tool to execute Python code in your chain of thought. The code will not be shown to the user. This tool should be used for internal reasoning, but not for code that is intended to be visible to the user (e.g. when creating plots, tables, or files).
When you send a message containing python code to python, it will be executed in a stateless docker container, and the stdout of that process will be returned to you.
DESC;
            // Build server configuration
            $server = Server::make()
                ->withServerInfo(
                    name: 'python',
                    version: '0.0.1'
                )
                ->withLogger($this->logger)
                ->withContainer($this->container)
                ->withTool(
                    handler: PythonTool::class,
                    name: 'python',
                    description: $description,
                    annotations: new ToolAnnotations(
                        title: 'Execute Python code',
                    )
                )
                ->build();

            $transport = new StdioTransport(
                logger: $this->logger,
            );

            $server->connect($transport);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), [
                'trace' => $e->getTrace(),
            ]);
            $output->getErrorOutput()->writeln(json_encode([
                'error' => $e->getMessage(),
            ]));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
