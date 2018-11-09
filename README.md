# API Service Bundle

[![Build Status](https://travis-ci.org/eleven-labs/api-service-bundle.svg?branch=master)](https://travis-ci.org/eleven-labs/api-service-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/eleven-labs/api-service-bundle/badges/coverage.png)](https://scrutinizer-ci.com/g/eleven-labs/api-service-bundle/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/eleven-labs/api-service-bundle/badges/quality-score.png)](https://scrutinizer-ci.com/g/eleven-labs/api-service-bundle/)


This bundle integrate the [API Service Component](https://github.com/eleven-labs/api-service) 
into Symfony.

## Installation

Open a command console, enter your project directory and execute the following 
command to download the latest stable version of this bundle:

```bash
composer require eleven-labs/api-service-bundle
```

Then, enable the bundle by adding the following line in the app/AppKernel.php 
file of your project:

```php
<?php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new ElevenLabs\ApiServiceBundle\ApiServiceBundle(),
        );
        // ...
    }
}
```

## Config

Full configuration provided by the bundle:

```yml
api_service:
  # (optional)
  default_services:
    client: httplug.client
    message_factory: httplug.message_factory
    uri_factory: httplug.uri_factory
  # (optional) cache schema files
  cache:
    # provide the Id of any PSR-6 cache service
    service: 'my_app.cache'
  # (optional) configure supported pagination providers
  pagination:
    # extract pagination from response headers
    header:
      page: X-Page
      perPage: X-Per-Page
      totalPages: X-Total-Pages
      totalItems: X-Total-Items
  # configure api services
  apis:
    my_service:
      # the schema describing your api
      schema: 'file://%kernel.root_dir%/config/schema/foo.yml'
      # (optional) use a specific http client implementation
      client: httplug.client.foo
      # (optional) fine tune your api service
      config:
        # provide a base url
        baseUri: https://bar.com
        # validate request
        validateRequest: true
        # validate response
        validateResponse: false
        # return a psr-7 response
        # by default it return a `Resource` class
        returnResponse: false
```

## Dependencies

### HTTP client

The Api Service component make use of the `Http\Client\HttpClient` interface 
provided by [HttPlug](http://httplug.io/) to send requests.

You can use your own HTTP client services within Symfony, 
but we strongly advice you to use the [HttplugBundle](https://github.com/php-http/HttplugBundle)

```bash
composer require php-http/httplug-bundle
```

You can then choose one the many HTTP client adapter supported by HTTPlug. A list 
is available [here](http://docs.php-http.org/en/latest/clients.html)

```bash
# for example, here we choose the Guzzle 6 adapter
composer require php-http/guzzle6-adapter
```

### Cache

This bundle have the ability to cache schema files used by you API services.  
It use the [PSR-6: Caching Interface](http://www.php-fig.org/psr/psr-6/) to do so.

For performance reasons, the cache **SHOULD** be enable in production.

#### From Symfony 3.1 and above

Symfony 3.1 provide a cache implementation of the PSR-6 Caching Interface. You don't need
additional components to integrate caching capabilities to the framework.

#### From Symfony 2.7 to 3.1

We recommend you to use the [PSR-6 Cache adapter Bundle](https://github.com/php-cache/adapter-bundle).

You can then choose one of the many [cache pool implementations](http://www.php-cache.com/en/latest/#cache-pool-implementations) 
provided by the php-cache organization.



