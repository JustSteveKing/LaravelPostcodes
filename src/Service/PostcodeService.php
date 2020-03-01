<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Service;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
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
     * Validate a Outcode against the RegEx
     *
     * @param string $postcode
     *
     * @return bool
     */
    public function validateOutcode(string $postcode): bool
    {
      if (preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW]))#', $postcode)) {
        return TRUE;
      }
      return FALSE;
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
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPostcodes(array $postcodes, array $filter = []): Collection
    {
        if (!empty($filter)) {
            $filter = build_query(['filter' => implode(',', $filter)]);
        }

        return collect($this->getResponse(
            'postcodes?' . $filter,
            'POST',
            ['postcodes' => array_values($postcodes)]
        ))->map(function ($item) {
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
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query(string $query): Collection
    {
        $queryString = http_build_query(['q' => $query]);

        return collect($this->getResponse("postcodes?$queryString"));
    }

    /**
     * Get data for the postcodes nearest to the passed postcode
     *
     * @param string $postcode
     *
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function nearest(string $postcode): Collection
    {
        return collect($this->getResponse("postcodes/$postcode/nearest"));
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
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function autocomplete(string $partialPostcode): Collection
    {
        return collect($this->getResponse("postcodes/$partialPostcode/autocomplete"));
    }

    /**
     * Get nearest outward codes for a given longitude & latitude
     *
     * @param float $longitude
     *
     * @param float $latitude
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function nearestOutwardCodesForGivenLngAndLat(float $longitude, float $latitude): Collection
    {
        return collect($this->getResponse(sprintf(
            'outcodes?lon=%s&lat=%s',
            $longitude,
            $latitude
        )));
    }

    /**
     * Get information about nearest outcodes based on outward code
     *
     * @param string $outwardcode
     *
     * @param int $limit Needs to be less than 100
     * @param int $radius Needs to be less than 25,000m
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNearestOutwardCode(
        string $outwardcode,
        int $limit = 10,
        int $radius = 5000
    ): Collection {
        $limit = ($limit > 100) ? 100 : $limit;
        $radius = ($radius > 100) ? 25000 : $radius;

        return collect($this->getResponse(
            "outcodes/$outwardcode/nearest?limit=$limit&radius=$radius"
        ));
    }

    /**
     * Get nearest postcodes for a given longitude & latitude
     *
     * @param float $longitude
     * @param float $latitude
     *
     * @return Collection
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function nearestPostcodesForGivenLngAndLat(float $longitude, float $latitude): Collection
    {
        return collect($this->getResponse(sprintf(
            'postcodes?lon=%s&lat=%s',
            $longitude,
            $latitude
        )));
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
    protected function getResponse(
        string $uri = null,
        string $method = 'GET',
        array $data = [],
        array $options = []
    ) {
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
