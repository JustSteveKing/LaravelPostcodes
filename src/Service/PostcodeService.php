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
     * Get information based on outward code including geo data
     *
     * @param string $outwardcode
     *
     * @return object
     */
    public function getOutwardCode(string $outwardcode): object
    {
        return $this->getResponse("outcodes/$outwardcode");
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
     * Get data for the postcodes nearest to the passed postcode
     *
     * @param string $postcode
     *
     * return array|null
     */
    public function nearest(string $postcode): ?array
    {
        return $this->getResponse("postcodes/$postcode/nearest");
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
     * Autocomplete a postcode partial.
     *
     * @param string $partialPostcode
     *
     * @return array|null
     */
    public function autocomplete(string $partialPostcode): ?array
    {
        return $this->getResponse("postcodes/$partialPostcode/autocomplete");
    }

    /**
     * Get nearest outward codes for a given longitude & latitude
     *
     * @param float $latitude
     * @param float $longitude
     *
     * @return array|null
     */
    public function nearestOutwardCodesForGivenLngAndLat(float $longitude, float $latitude): ?array
    {
        return $this->getResponse(sprintf(
            'outcodes?lon=%s&lat=%s',
            $longitude,
            $latitude
        ));
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
