<?php

declare(strict_types=1);

namespace App\Command;

use PhpMcp\Schema\ServerCapabilities;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
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
        private CacheInterface $cache,
        private ContainerInterface $container,
        #[Autowire('%kernel.project_dir%/src/Tools')]
        private readonly string $toolsDir,
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
            $server = Server::make()
                ->withServerInfo(
                    name: 'python',
                    version: '0.0.1'
                )
                ->withLogger($this->logger)
                ->withCache($this->cache)
                ->withContainer($this->container)
                ->withCapabilities(
                    ServerCapabilities::make(
                        tools: true,
                        toolsListChanged: false,
                        resources: false,
                        prompts: false,
                        logging: false, // TODO add somehow?
                    )
                )
                ->build();

            // Discover MCP elements via attributes
            $server->discover(
                basePath: $this->toolsDir,
            );

            // Start listening via stdio transport
            $transport = new StdioServerTransport();
            $transport->setLogger($this->logger);
            $server->listen($transport);
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
