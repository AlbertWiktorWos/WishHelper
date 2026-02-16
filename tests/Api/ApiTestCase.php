<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Test\HasBrowser;

/**
 * We create a base ApiTestCase class to set default HTTP headers for all API tests.
 */
abstract class ApiTestCase extends BaseApiTestCase
{
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    protected function browser(array $options = [], array $server = [])
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()
                    ->withHeader('Accept', 'application/ld+json') // default header for all requests
            );
    }
}
