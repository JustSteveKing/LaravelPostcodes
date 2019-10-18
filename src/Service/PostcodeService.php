<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Service;

use GuzzleHttp\Client;

class PostcodeService
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var Client
     */
    protected $http;

    /**
     * Postcode Service constructor.
     *
     * @param Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->url = config('services.postcodes.url');

        $this->http = $client;
    }

    /**
     * Validate a postcode against the API
     *
     * @param string $postcode
     *
     * @return bool
     */
    public function validate(string $postcode): bool
    {
        return $this->getResponse("postcodes/$postcode/validate");
    }

    /**
     * Get the address details from a postcode
     *
     * @param string $postcode
     *
     * @return object
     */
    public function getPostcode(string $postcode): object
    {
        return $this->getResponse("postcodes/$postcode");
    }

    /**
     * Get information about nearest outcodes based on outward code
     *
     * @param string $outwardcode
     *
     * @param int $limit Needs to be less than 100
     * @param int $radius Needs to be less than 25,000m
     * @return object
     */
    public function getNearestOutwardCode(string $outwardcode, int $limit = 10, int $radius = 5000): object
    {
        $limit = ($limit > 100) ? 100 : $limit;
        $radius = ($radius > 100) ? 25000 : $radius;

        return collect($this->getResponse("outcodes/$outwardcode/nearest?limit=$limit&radius=$radius"));
    }

    /**
     * Get the address details from a random postcode
     *
     * @return object
     */
    public function getRandomPostcode()
    {
        return $this->getResponse("random/postcodes");
    }

    /**
     * Get the response and return the result object
     *
     * @param string $uri
     */
    protected function getResponse(string $uri = null)
    {
        $url = $this->url . $uri;

        $request = $this->http->request(
            'GET',
            $url
        );

        return json_decode($request->getBody()->getContents())->result;
    }
}
