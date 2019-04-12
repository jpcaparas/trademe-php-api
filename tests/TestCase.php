<?php

namespace Tests;

use PHPUnit\Framework\TestCase as TestCaseBase;
use Prophecy\Prophet;

class TestCase extends TestCaseBase
{
    /** @var Prophet */
    protected $prophet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prophet = new Prophet();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        $this->prophet->checkPredictions();
    }
}
