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
     * Get the address details from a random postcode
     *
     * @return object
     */
    public function getRandomPostcode()
    {
        return $this->getResponse("random/postcodes");
    }

    /**
     * Query the API for a given string
     *
     * @param  string  $query
     *
     * @return array|null
     */
    public function query(string $query): ?array
    {
        $queryString = http_build_query(['q' => $query]);

        return $this->getResponse("postcodes?$queryString");
    }

    /**
     * Lookup a terminated postcode. Returns the postcode, year and month of termination.
     *
     * @param string $postcode
     *
     * @return object
     */
    public function getTerminatedPostcode($postcode)
    {
        return $this->getResponse("terminated_postcodes/$postcode");
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
