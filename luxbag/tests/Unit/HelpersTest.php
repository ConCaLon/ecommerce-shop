<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Unit tests for global helper functions defined in app/helpers.php.
 */
class HelpersTest extends TestCase
{
    // ─── vnd() ────────────────────────────────────────────────────

    public function test_vnd_formats_integer_correctly(): void
    {
        $this->assertEquals('1.000.000đ', vnd(1000000));
    }

    public function test_vnd_formats_float_correctly(): void
    {
        $this->assertEquals('1.850.000đ', vnd(1850000.00));
    }

    public function test_vnd_formats_zero(): void
    {
        $this->assertEquals('0đ', vnd(0));
    }

    public function test_vnd_formats_string_number(): void
    {
        $this->assertEquals('500.000đ', vnd('500000'));
    }

    public function test_vnd_returns_string(): void
    {
        $result = vnd(100000);
        $this->assertIsString($result);
        $this->assertStringEndsWith('đ', $result);
    }

    public function test_vnd_contains_dot_separators_for_thousands(): void
    {
        $result = vnd(1000000);
        $this->assertStringContainsString('.', $result);
    }
}
