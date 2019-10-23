<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Service;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;

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
     * Get the address details from a multiple postcodes at once
     *
     * @param array $postcodes
     *
     * @param array $filter - optional array of fields to return
     * @return \Illuminate\Support\Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPostcodes(array $postcodes, array $filter = []): object
    {
        if (!empty($filter)) {
            $filter = build_query(['filter' => implode(',', $filter)]);
        }

        return collect($this->getResponse('postcodes?' . $filter, 'POST', ['postcodes' => array_values($postcodes)]))
            ->map(function ($item, $key) {
                return $item->result;
            });
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
     * @param string $query
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
     * Get the response and return the result object
     *
     * @param string $uri
     */
    protected function getOldResponse(string $uri = null)
    {
        $url = $this->url . $uri;

        $request = $this->http->request(
            'GET',
            $url
        );

        return json_decode($request->getBody()->getContents())->result;
    }

    /**
     * Get the response and return the result object
     *
     * @param string|null $uri
     * @param string $method
     * @param array $data - data to be sent in post/patch/put request
     * @param array $options - array of options to be passed to curl, if $data is passed 'json' will be overwritten
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getResponse(string $uri = null, string $method = 'GET', array $data = [], array $options = [])
    {
        $url = $this->url . $uri;

        if (!empty($data)) {
            $options['json'] = $data;
        }

        $request = $this->http->request(
            $method,
            $url,
            $options
        );

        return json_decode($request->getBody()->getContents())->result;
    }
}
