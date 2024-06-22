<?php

namespace BrainMaestro\GitHooks\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

class HelpersTest extends PHPUnitTestCase
{
    /** @test  */
    #[Test]
    public function it_checks_os()
    {
        $this->assertIsBool(is_windows());
    }
}
