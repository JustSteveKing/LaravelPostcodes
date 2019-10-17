<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Validation\Rule;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;
use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Rules\Postcode;

class PostcodeRuleTest extends TestCase
{
    /**
     * @var Postcode
     */
    protected $rule;

    public function testPostcodeMacro()
    {
        $this->setupRule(200);

        $this->assertInstanceOf(Postcode::class, Rule::postcode());
    }

    public function testValidPostcodePasses()
    {
        $this->setupRule(200, json_encode(['result' => true]));

        $postcode = 'CF10 4UW';
        $this->assertTrue($this->rule->passes('postcode', $postcode));
    }

    public function testInvalidPostcodeFails()
    {
        $this->setupRule(200, json_encode(['result' => false]));

        $postcode = 'testing';
        $this->assertFalse($this->rule->passes('postcode', $postcode));
    }

    public function testValidationMessageIsCorrect()
    {
        $this->setupRule(200);

        $string = 'The submitted postcode is not a valid UK postcode';

        $this->assertEquals($this->rule->message(), $string);
    }

    private function setupRule(int $status, string $body = null): void
    {
        $mock = new MockHandler([new Response($status, [], $body)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        $this->rule = new Postcode(new PostcodeService($client));
    }
}
