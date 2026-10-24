<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    public function test_formula_cells_are_neutralised(): void
    {
        foreach (['=HYPERLINK("http://x")', '+1+1', '-2+3', '@SUM(A1)', "\tcmd", "\rcmd"] as $value) {
            $this->assertSame("'".$value, Csv::cell($value));
        }
    }

    public function test_plain_cells_are_unchanged(): void
    {
        $this->assertSame('Jane Doe', Csv::cell('Jane Doe'));
        $this->assertSame('jane@example.com', Csv::cell('jane@example.com'));
        $this->assertSame('', Csv::cell(null));
        $this->assertSame(['a', "'=b"], Csv::row(['a', '=b']));
    }
}
