<?php

declare(strict_types=1);

namespace JustSteveKing\LaravelPostcodes\Unit;

use JustSteveKing\LaravelPostcodes\TestCase;
use JustSteveKing\LaravelPostcodes\Service\PostcodeService;

class PostcodeServiceTest extends TestCase
{
    protected $postcode = 'N11 1QZ';

    protected $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(PostcodeService::class);
    }

    public function testServiceIsCorrectType()
    {
        $this->assertInstanceOf(PostcodeService::class, $this->service);
    }

    public function testServiceCanValidatePostcode()
    {
        $this->assertEquals(false, $this->service->validate('test'));
        $this->assertTrue($this->service->validate($this->postcode));
    }

    public function testServiceCanGetPostcode()
    {
        $result = $this->service->getPostcode($this->postcode);
        
        $this->assertEquals($result->postcode, $this->postcode);
    }

    public function testServiceCanGetRandomPostcode()
    {
        $result = $this->service->getRandomPostcode();

        $this->assertNotNull($result->postcode);
    }
}
