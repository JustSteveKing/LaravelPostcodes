<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Rules;

use Illuminate\Contracts\Validation\Rule;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class Postcode implements Rule
{
    protected $service;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(PostcodeService $service)
    {
        $this->service = $service;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->service->validate($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The submitted postcode is not a valid UK postcode';
    }
}
