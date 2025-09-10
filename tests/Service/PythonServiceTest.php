<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\PythonService;
use PHPUnit\Framework\TestCase;

final class PythonServiceTest extends TestCase
{
    public function testExecutePrintsCalculatedValue(): void
    {
        $service = new PythonService();

        $output = $service->execute('print(123*3)');

        // The service returns combined stdout/stderr text but trims trailing newlines; assert exact value.
        $this->assertSame('369', $output, 'PythonService should output the result of the print statement without trailing newline.');
    }

}
