<?php

namespace Orchestra\Testbench;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Non-autoloaded editor stub for Testbench's base test case.
 *
 * The real runtime implementation is provided by the installed
 * orchestra/testbench-core package.
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @param  string|array<int, string>  $paths
     */
    protected function loadMigrationsFrom(string|array $paths): void {}
}
