<?php

declare(strict_types=1);

namespace App\Service;

/*
 * PHP service class to execute Python code inside a Docker container, using Symfony Process (sync).
 * Mirrors gpt_oss/tools/python_docker/docker_tool.py behavior.
 * @see https://github.com/openai/gpt-oss/blob/main/gpt_oss/tools/python_docker/docker_tool.py
 *
 * Requirements:
 * - Docker must be installed and the current user must have permission to run it.
 * - The image python:3.11 will be pulled automatically if missing.
 */

use Symfony\Component\Process\Process;

final class PythonService
{
    /**
     * Execute Python code using docker image python:3.11 and return combined output.
     * Mirrors the Python implementation by returning whatever the process outputs (stdout + stderr).
     * Each call is stateless. Non-zero exit does NOT throw; we return traceback text instead.
     *
     * @param string $script Python code to execute
     *
     * @return string Combined output from the containerized execution
     *
     * @throws \RuntimeException when docker is not available or pre-checks fail
     */
    public function execute(string $script): string
    {
        if (!$this->isDockerAvailable()) {
            throw new \RuntimeException('Docker is not installed or not available in PATH.');
        }

        $this->ensureImageExists();

        // Use shell to combine stderr => stdout for parity with original tool.
        $process = Process::fromShellCommandline('docker run -i --rm python:3.11 python - 2>&1');
        $process->setInput($script);
        // Optional timeouts can be adjusted if needed
        $process->setTimeout(300);
        $process->run();

        // Do not throw on non-zero exit: return combined outputs like the Python tool
        $combined = $process->getOutput().$process->getErrorOutput();

        // Normalize trailing newline/carriage return added by Python print/terminal.
        // We intentionally trim only CR/LF to keep spaces and other whitespace intact.
        return rtrim($combined, "\r\n");
    }

    private function isDockerAvailable(): bool
    {
        $process = Process::fromShellCommandline('command -v '.escapeshellarg('docker'));
        $process->run();

        return $process->isSuccessful();
    }

    private function ensureImageExists(): void
    {
        $cmd = 'docker image inspect '.escapeshellarg('python:3.11').' >/dev/null 2>&1 || docker image pull '.escapeshellarg('python:3.11');
        $process = Process::fromShellCommandline($cmd);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Failed ensuring docker image python:3.11:\n".$process->getOutput().$process->getErrorOutput());
        }
    }
}
