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
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'python-mcp',
    description: 'Add a short description for your command',
)]
class PythonMcpCommand extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private ContainerInterface $container,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
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
            // Build server configuration
            $server = Server::builder()
                ->setServerInfo(
                    name: 'python',
                    version: '0.0.1'
                )
                ->setLogger($this->logger)
                ->setContainer($this->container)
                ->setDiscovery($this->projectDir.'/src/Tools')
                ->build();

            $transport = new StdioTransport(
                logger: $this->logger,
            );

            $server->connect($transport);
            $transport->listen();
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
