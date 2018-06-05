<?php

namespace BrainMaestro\GitHooks\Tests;

use PHPUnit\Framework\TestCase;

class HelpersTester extends TestCase
{
    /**
     * @test
     */
    public function it_checks_os()
    {
        $this->assertInternalType('boolean', is_windows());
    }
}
