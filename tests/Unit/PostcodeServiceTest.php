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
    /** @var MockHandler */
    protected $handler;

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
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes/test/validate');

        $serviceSuccess = $this->service(200, json_encode(['result' => true]));
        $this->assertTrue($serviceSuccess->validate($this->postcode));
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes/N11 1QZ/validate');
    }

    public function testServiceCanGetPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getPostcode($this->postcode);

        $this->assertEquals($result->postcode, $this->postcode);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes/N11 1QZ');
    }

    public function testServiceCanGetOutwardCode()
    {
        $service = $this->service(200, json_encode(['result' => ['outcode' => substr($this->postcode, 0, 3)]]));
        $result = $service->getOutwardCode(substr($this->postcode, 0, 3));

        $this->assertEquals($result->outcode, substr($this->postcode, 0, 3));
    }

    public function testServiceCanGetRandomPostcode()
    {
        $service = $this->service(200, json_encode(['result' => ['postcode' => $this->postcode]]));
        $result = $service->getRandomPostcode();

        $this->assertNotNull($result->postcode);
        $this->assertRequest('GET', 'https://api.postcodes.io/random/postcodes');
    }

    public function testServiceCanQueryPostcode()
    {
        $serviceFound = $this->service(200, json_encode(['result' => [['postcode' => $this->postcode]]]));
        $resultFound = $serviceFound->query($this->postcode);

        $this->assertIsArray($resultFound);
        $this->assertCount(1, $resultFound);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes?q=N11 1QZ');

        $serviceNull = $this->service(200, json_encode(['result' => null]));
        $resultNull = $serviceNull->query('AA1 1AA');

        $this->assertNull($resultNull);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes?q=AA1 1AA');
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
        $this->assertRequest('GET', 'https://api.postcodes.io/terminated_postcodes/AB1 0AA');
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
        $partialPostcode = 'some-postcode-with-autocomplete-results';

        $actual = $service->autocomplete($partialPostcode);

        $this->assertSame($data['result'], $actual);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes/some-postcode-with-autocomplete-results/autocomplete');
    }

    public function testServiceCantAutocompletePostcode(): void
    {
        $data = [
            'status' => 200,
            'result' => null,
        ];
        $service = $this->service(200, json_encode($data));
        $partialPostcode = 'some-postcode-without-autocomplete-results';

        $actual = $service->autocomplete($partialPostcode);

        $this->assertNull($actual);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes/some-postcode-without-autocomplete-results/autocomplete');
    }

    public function testServiceCanGetNearestPostcodes()
    {
        $serviceFound = $this->service(
            200,
            json_encode(['result' => [['postcode' => $this->postcode]]])
        );

        $resultFound = $serviceFound->nearest($this->postcode);

        $this->assertIsArray($resultFound);
        $this->assertCount(1, $resultFound);
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
        $this->handler = new MockHandler([new Response($status, [], $body)]);
        $stack = HandlerStack::create($this->handler);
        $client = new Client(['handler' => $stack]);

        return new PostcodeService($client);
    }

    private function assertRequest(string $method, string $uri): void
    {
        $request = $this->handler->getLastRequest();

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, urldecode((string) $request->getUri()));
    }
}
