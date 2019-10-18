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

    public function testServiceCanGetTerminatedPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->terminatedPostcode, "year_terminated" => 1996, "month_terminated" => 6, "longitude" => -2.242851, "latitude" => 57.101474]]));
        $result = $service->getTerminatedPostcode($this->terminatedPostcode);

        $this->assertNotNull($result->postcode);
    }

    public function testServiceCanGetPostcodes()
    {
        $postcodes = ["PR3 0SG", "M45 6GN", "EX165BL"];
        $service = $this->service(200, json_encode(["status" => 200, "result" => [["query" => "PR3 0SG", "result" => ["postcode" => "PR3 0SG",],], ["query" => "M45 6GN", "result" => ["postcode" => "M45 6GN",],], ["query" => "EX165BL", "result" => ["postcode" => "EX16 5BL"]]]]));
        $result = $service->getPostcodes($postcodes, ['postcode']);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);

        $this->assertEquals($result->count(), 3);
        $this->assertEquals($result->first()->postcode, $postcodes[0]);
    }

    private function service(int $status, string $body = null): PostcodeService
    {
        $mock = new MockHandler([new Response($status, [], $body)]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);

        return new PostcodeService($client);
    }
}
