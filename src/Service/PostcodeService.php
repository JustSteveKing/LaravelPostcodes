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
            $filter = ['filter' => implode(',', $filter)];
        }

        return collect($this->getPostResponse('postcodes', ['postcodes' => array_values($postcodes)], $filter))
        ->map(function ($item, $key){
            return $item->result;
        });
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

    /**
     * Get the response of POST request and return the result object
     *
     * @param string $uri
     * @param array $postData
     * @param array $query
     * @return
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getPostResponse(string $uri = null, array $postData = [], array $query = [])
    {
        $url = $this->url . $uri;
        $request = $this->http->request(
            'POST',
            $url,
            [
                'query' => $query,
                'json'  => $postData,
            ]
        );

        return json_decode($request->getBody()->getContents())->result;
    }
}
