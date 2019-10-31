<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use JustSteveKing\LaravelPostcodes\Service\BulkReverseGeocoding\Geolocation;
use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodeServiceTest extends TestCase
{
    private const LONGITUDE = 0.629834723775309;
    private const LATITUDE = 51.7923246977375;

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

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultFound);
        $this->assertCount(1, $resultFound);
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes?q=N11 1QZ');

        $serviceNull = $this->service(200, json_encode(['result' => null]));
        $resultNull = $serviceNull->query('AA1 1AA');

        $this->assertTrue($resultNull->isEmpty());
        $this->assertRequest('GET', 'https://api.postcodes.io/postcodes?q=AA1 1AA');
    }

    public function testServiceCanGetTerminatedPostcode()
    {
        $service = $this->service(200, json_encode([
            'result' => [
                'postcode'         => $this->terminatedPostcode,
                'year_terminated'  => 1996,
                'month_terminated' => 6,
                'longitude'        => -2.242851,
                'latitude'         => 57.101474,
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

        $this->assertSame($data['result'], $actual->toArray());
        $this->assertRequest(
            'GET',
            'https://api.postcodes.io/postcodes/some-postcode-with-autocomplete-results/autocomplete'
        );
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

        $this->assertTrue($actual->isEmpty());
        $this->assertRequest(
            'GET',
            'https://api.postcodes.io/postcodes/some-postcode-without-autocomplete-results/autocomplete'
        );
    }

    public function testServiceCanGetNearestPostcodes()
    {
        $serviceFound = $this->service(
            200,
            json_encode(['result' => [['postcode' => $this->postcode]]])
        );

        $resultFound = $serviceFound->nearest($this->postcode);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $resultFound);
        $this->assertCount(1, $resultFound);
    }

    public function testServiceCanGetPostcodes()
    {
        $postcodes = ["PR3 0SG", "M45 6GN", "EX165BL"];
        $service = $this->service(200, json_encode([
            "status" => 200,
            "result" => [
                ["query" => "PR3 0SG", "result" => ["postcode" => "PR3 0SG",],],
                ["query" => "M45 6GN", "result" => ["postcode" => "M45 6GN",],],
                ["query" => "EX165BL", "result" => ["postcode" => "EX16 5BL"]]
            ]
        ]));
        $result = $service->getPostcodes($postcodes, ['postcode']);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertEquals($result->count(), 3);
        $this->assertEquals($result->first()->postcode, $postcodes[0]);
    }

    public function testServiceCanGetNearestOutwardCodesForGivenLongitudeAndLatitude()
    {
        $json = file_get_contents(
            __DIR__
            . '/../Fixtures/GetNearestOutwardCodesForGivenLongitudeAndLatitude.json'
        );
        $serviceFound = $this->service(200, $json);

        $expected = json_encode(json_decode($json)->result);
        $actual = json_encode($serviceFound->nearestOutwardCodesForGivenLngAndLat(
            self::LONGITUDE,
            self::LATITUDE
        ));

        $this->assertSame($expected, $actual);
        $this->assertRequest('GET', 'https://api.postcodes.io/outcodes?lon=0.62983472377531&lat=51.792324697737');
    }

    public function testServiceCanHandleEmptyResponseForNearestOutwardCodesForGivenLongitudeAndLatitude()
    {
        $serviceFound = $this->service(
            200,
            json_encode(['result' => null])
        );

        $actual = $serviceFound->nearestOutwardCodesForGivenLngAndLat(0, 0);

        $this->assertTrue($actual->isEmpty());
        $this->assertRequest('GET', 'https://api.postcodes.io/outcodes?lon=0&lat=0');
    }

    public function testServiceCanGetNearestOutwardCode()
    {
        $service = $this->service(200, json_encode(['result' => [['outcode' => substr($this->postcode, 0, 3)]]]));
        $result = $service->getNearestOutwardCode(substr($this->postcode, 0, 3));

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertEquals($result->first()->outcode, substr($this->postcode, 0, 3));
    }

    public function testServiceCanGetNearestPostcodesForGivenLngAndLat()
    {
        $json = file_get_contents(
            __DIR__
            . '/../Fixtures/GetNearestPostcodesForGivenLongitudeAndLatitude.json'
        );

        $serviceFound = $this->service(200, $json);

        $expected = json_encode(json_decode($json)->result);

        $actual = json_encode(
            $serviceFound->nearestPostcodesForGivenLngAndLat(0.629834723775309, 51.7923246977375)
                ->toArray()
        );

        $this->assertSame($expected, $actual);
    }

    public function testServiceCanHandleEmptyResponseForCanGetNearestPostcodesForGivenLngAndLat()
    {
        $serviceFound = $this->service(
            200,
            json_encode(['result' => null])
        );

        $actual = $serviceFound->nearestPostcodesForGivenLngAndLat(0, 0);

        $this->assertTrue($actual->isEmpty());
    }

    public function testServiceCanBulkReverseGeocoding(): void
    {
        $json = file_get_contents(__DIR__ . '/../Fixtures/BulkReverseGeocoding.json');
        $serviceFound = $this->service(200, $json);
        $geolocations = [
            new Geolocation(self::LONGITUDE, self::LATITUDE),
            new Geolocation(-2.49690382054704, 53.5351312861402, 1000, 5),
        ];

        $expected = json_encode(json_decode($json)->result);
        $actual = json_encode($serviceFound->bulkReverseGeocoding($geolocations, $geolocations));
        $expectedRequestBody = json_encode(array_map(function (Geolocation $geolocation) {
            return $geolocation->toArray();
        }, $geolocations));

        $this->assertSame($expected, $actual);
        $this->assertRequest('POST', 'https://api.postcodes.io/postcodes', $expectedRequestBody);
    }

    public function testServiceCanHandleEmptyResponseForBulkReverseGeocoding(): void
    {
        $serviceFound = $this->service(
            200,
            json_encode(['result' => null])
        );
        $geolocations = [
            new Geolocation(self::LONGITUDE, self::LATITUDE),
            new Geolocation(-2.49690382054704, 53.5351312861402, 1000, 5),
        ];

        $actual = $serviceFound->bulkReverseGeocoding($geolocations, $geolocations);
        $expectedRequestBody = json_encode(array_map(function (Geolocation $geolocation) {
            return $geolocation->toArray();
        }, $geolocations));

        $this->assertNull($actual);
        $this->assertRequest('POST', 'https://api.postcodes.io/postcodes', $expectedRequestBody);
    }

    private function service(int $status, string $body = null): PostcodeService
    {
        $this->handler = new MockHandler([new Response($status, [], $body)]);
        $stack = HandlerStack::create($this->handler);
        $client = new Client(['handler' => $stack]);

        return new PostcodeService($client);
    }

    private function assertRequest(
        string $method,
        string $uri,
        string $body = null
    ): void {
        $request = $this->handler->getLastRequest();

        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, urldecode((string)$request->getUri()));
        if ($body !== null) {
            $this->assertSame($body, $request->getBody()->getContents());
        }
    }
}
