<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodeServiceTest extends TestCase
{
    protected $postcode = 'N11 1QZ';
    protected $terminatedPostcode = 'AB1 0AA';

    public function testServiceIsCorrectType()
    {
        $this->assertInstanceOf(PostcodeService::class, $this->service(200));
    }

    public function testServiceCanValidatePostcode()
    {
        $serviceFail = $this->service(200, json_encode(['result' => false]));
        $this->assertEquals(false, $serviceFail->validate('test'));

        $serviceSuccess = $this->service(200, json_encode(['result' => true]));
        $this->assertTrue($serviceSuccess->validate($this->postcode));
    }

    public function testServiceCanGetPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getPostcode($this->postcode);

        $this->assertEquals($result->postcode, $this->postcode);
    }

    public function testServiceCanGetRandomPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getRandomPostcode();

        $this->assertNotNull($result->postcode);
    }

    public function testServiceCanQueryPostcode()
    {
        $serviceFound = $this->service(200, json_encode(['result' => [['postcode' => $this->postcode]]]));
        $resultFound = $serviceFound->query($this->postcode);

        $this->assertIsArray($resultFound);
        $this->assertCount(1, $resultFound);

        $serviceNull = $this->service(200, json_encode(['result' => null]));
        $resultNull = $serviceNull->query($this->postcode);

        $this->assertNull($resultNull);
    }

    public function testServiceCanGetTerminatedPostcode()
    {
        $service = $this->service(200, json_encode([
            'result' => [
                'postcode' => $this->terminatedPostcode,
                'year_terminated' => 1996,
                'month_terminated' => 6,
                'longitude' => -2.242851,
                'latitude' => 57.101474,
            ],
        ]));
        $result = $service->getTerminatedPostcode($this->terminatedPostcode);

        $this->assertNotNull($result->postcode);
    }

    public function testServiceCanAutocompletePostcode(): void
    {
        $data = [
            'status' => 200,
            'result' => [
                "AB10 1AB",
                "AB10 1AF",
                "AB10 1AG",
                "AB10 1AH",
                "AB10 1AL",
                "AB10 1AN",
                "AB10 1AP",
                "AB10 1AQ",
                "AB10 1AR",
                "AB10 1AS",
            ],
        ];
        $service = $this->service(200, json_encode($data));

        $actual = $service->autocomplete('A');

        $this->assertSame($data['result'], $actual);
    }

    public function testServiceCantAutocompletePostcode(): void
    {
        $data = [
            'status' => 200,
            'result' => null,
        ];
        $service = $this->service(200, json_encode($data));

        $actual = $service->autocomplete('XYZ');

        $this->assertNull($actual);
    }

    private function service(int $status, string $body = null): PostcodeService
    {
        $mock = new MockHandler([new Response($status, [], $body)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new PostcodeService($client);
    }
}
