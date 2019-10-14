<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use Illuminate\Validation\Rule;
use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Rules\Postcode;

class PostcodeRuleTest extends TestCase
{
    /**
     * @var Postcode
     */
    protected $rule;

    public function setUp(): void
    {
        parent::setUp();

        $this->rule = new Postcode();
    }

    public function testPostcodeMacro()
    {
        $this->assertInstanceOf(Postcode::class, Rule::postcode());
    }

    public function testValidPostcodePasses()
    {
        $postcode = 'CF10 4UW';
        $this->assertTrue($this->rule->passes('postcode', $postcode));
    }

    public function testInvalidPostcodeFails()
    {
        $postcode = 'testing';
        $this->assertFalse($this->rule->passes('postcode', $postcode));
    }
}
