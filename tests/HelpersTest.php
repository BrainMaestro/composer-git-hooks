<?php

namespace BrainMaestro\GitHooks\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class HelpersTest extends PHPUnitTestCase
{
    /**
     * @test
     */
    public function it_checks_os()
    {
        $this->assertInternalType('boolean', is_windows());
    }
}
