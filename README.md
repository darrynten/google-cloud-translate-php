## google-cloud-translate-php

This is pretty much based on our [Google Natural Language](https://github.com/darrynten/google-natural-language-php)
library and is quite similar.

![Travis Build Status](https://travis-ci.org/darrynten/google-cloud-translate-php.svg?branch=master)
![StyleCI Status](https://styleci.io/repos/81687310/shield?branch=master)
[![codecov](https://codecov.io/gh/darrynten/google-cloud-translate-php/branch/master/graph/badge.svg)](https://codecov.io/gh/darrynten/google-cloud-translate-php)
![Packagist Version](https://img.shields.io/packagist/v/darrynten/google-cloud-translate-php.svg)
![MIT License](https://img.shields.io/github/license/darrynten/google-cloud-translate-php.svg)

An unofficial, fully unit tested Google Cloud Translate PHP client with 
extra sugar.

## Why not just use the official one?

The official one is great, and we actually use it in this package, it
just doesn't quite have the sort of features we needed, so we wrapped
it up with some extra bits.

## What extra features?

### Cost Cutters

The Google Cloud Translate API costs money. If you're doing anything
with it at scale, you're going to have to keep an eye on your calls to
make sure things aren't running away with you. I learnt this the hard
way. It can get expensive.

That's why we introduced some cost cutting features.

#### Caching Requests

By default this package caches your requests, something you would have
to usually do yourself.

It caches using a framework-agnostic approach, whereby it leverages any
host frameworks caching mechanism, and falls back to a temporary cache
if there is no cache available.

The supported frameworks are detailed in the [AnyCache](https://github.com/darrynten/any-cache) project.

Examples include Laravel, Symfony, Doctrine, Psr6 and more.

This feature is on by default and can easily be disabled.

#### Cheapskate Mode

While not immediately clear, the Google Cloud Translate API charges per 1000 characters.

We've added "cheapskate mode" that, if set, automatically truncates the 
desired text, saving you another credit.

This feature is on by default and can easily be disabled.

### Conveniences

There are a few other conveniences like being able to set the type,
language, encoding etc.

One use case would be running a single instance of text through
multiple language attempts.

## Usage

```php
use DarrynTen\GoogleCloudTranslatePhp\GoogleCloudTranslate;

// Config options
$config = [
  'projectId' => 'my-awesome-project'  // At the very least
];

// Make a translator
$translate = new GoogleCloudTranslate($config);

// Get information
$translate->languages();
$translate->localizedLanguages();

// Detect languages
$translate->detectLanguage($string);
$translate->detectLanguageBatch([$strings]);

// Translate
$translate->translate($string);
$translate->translateBatch([$strings]);

// Set optional things
$language->setType('html');
$language->setTargetLanguage('en');

// Extra features
$language->setCaching(false);
$language->setCheapskate(false);

// Full config options
$config = [
  'projectId' => 'my-awesome-project',     // required
  'key' => 'api-key',                      // optional see note below
  'target' => 'en',                        // optional default is en
  'source' => 'en',                        // optional default is en
  'model' => 'blank',                      // optional default is en
  'authCache' => \CacheItemPoolInterface,  // stores access tokens
  'authCacheOptions' => $array,            // cache config
  'authHttpHandler' => callable(),         // psr-7 auth handler
  'httpHandler' => callable(),             // psr-7 rest handler
  'keyFile' => $json,                      // content
  'keyFilePath' => $string,                // path
  'retries' => 3,                          // default is 3
  'scopes' => $array,                      // app scopes
  'cache' => $boolean,                     // cache
  'cheapskate' => $boolean,                // cheaper detection calls
  'cheapskateCount' => 100,                // how cheap?
];

// authCache, authCacheOptions, authHttpHandler and httpHandler are not
// currently implemented.
```

See [The Google Cloud Docs](https://googlecloudplatform.github.io/google-cloud-php/#/docs/v0.22.0/translate/translateclient)
for more on these options and their usage.

Please note that while the Google Cloud Translation API supports authentication
via service account and application default credentials like other Cloud
Platform APIs, it also supports authentication via a public API access key.

If you wish to authenticate using an API key, follow the before you begin
[instructions](https://cloud.google.com/translate/docs/translating-text#before-you-begin)
to learn how to generate a key.

## Options

* `setType($type)` - Either `html` (default) or `text`
* `setSourceLanguage($language)` - Either ISO (`en`, `es`) or BCP-47 (`en-ZA`, `en-GB`).
* `setTargetLanguage($language)` - Either ISO (`en`, `es`) or BCP-47 (`en-ZA`, `en-GB`).

If no language is provided then it is autodetected from the text and
is returned with the response.

## Missing Features

Feel free to open a PR!

Usage of Google\Cloud\Storage\StorageObject is presently not possible.

* Custom `authCache` and `authCacheOptions`
* Custom `httpHandler` and `authHttpHandler`

## Roadmap

## Acknowledgements

* Open a PR and put yourself here :)
