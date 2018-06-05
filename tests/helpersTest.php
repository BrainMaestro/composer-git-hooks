<?php

namespace BrainMaestro\GitHooks\Tests;

use PHPUnit\Framework\TestCase;

class HelpersTester extends TestCase
{
    /**
     * @test
     */
    public function it_gets_git_tag_name()
    {
        $this->assertNotEmpty(git_version(), 'Failed to get the latest tag name from git');
    }

    /**
     * @test
     */
    public function it_checks_os()
    {
        $this->assertInternalType('boolean', is_windows());
    }
}
