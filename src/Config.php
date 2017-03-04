<?php

namespace DarrynTen\GoogleCloudTranslatePhp;

use Psr\Cache\CacheItemPoolInterface;

/**
 * GoogleCloudTranslate Config
 *
 * @category Configuration
 * @package  GoogleCloudTranslatePhp
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/google-cloud-translate-php/LICENSE>
 * @link     https://github.com/darrynten/google-cloud-translate-php
 */
class Config
{
    /**
     * The api key
     *
     * @var string $key
     */
    private $key;

    /**
     * The type of text.
     *
     * Options are `text` and `html`
     *
     * @var string $type
     */
    public $type = 'html';

    /**
     * The encoding
     *
     * Options are `UTF8`, `UTF16`, `UTF32` and `NONE`
     *
     * @var string $encoding
     */
    public $encoding = 'UTF8';

    /**
     * The source translation language
     *
     * Must be a valid ISO 639-1 language code. Defaults to "en"
     *
     * @var string $source
     */
    public $source;

    /**
     * The target translation language
     *
     * Must be a valid ISO 639-1 language code. Defaults to "en"
     *
     * @var string $target
     */
    public $target;

    /**
     * The model
     *
     * Can either be nmt or base. Defaults to empty string
     *
     * @var string $model
     */
    public $model;

    /**
     * The project ID
     *
     * @var string $projectId
     */
    private $projectId;

    /**
     * Whether or not to use caching.
     *
     * The default is true as this is a good idea.
     *
     * @var boolean $cache
     */
    public $cache = true;

    /**
     * Cheapskate mode - Limit language detection characters
     *
     * @var boolean $cheapskate
     */
    public $cheapskate = true;

    /**
     * Cheapskate count - The number to limit to
     *
     * @var integer $cheapskateCount
     */
    public $cheapskateCount = 160;

    /**
     * Custom Auth Cache
     *
     * @var CacheItemPoolInterface $authCache
     */
    private $authCache;

    /**
     * Custom Auth Cache options
     *
     * @var array $authCacheOptions
     */
    private $authCacheOptions;

    /**
     * Custom Auth HTTP Handler
     *
     * @var callable $authHttpHandler
     */
    private $authHttpHandler;

    /**
     * Custom REST HTTP Handler
     *
     * @var callable $httpHandler
     */
    private $httpHandler;

    /**
     * A custom key file for auth
     *
     * @var string $keyFile
     */
    private $keyFile;

    /**
     * A path on disk to the key file
     *
     * @var string $keyFilePath
     */
    private $keyFilePath;

    /**
     * The number of times to retry failed calls
     *
     * @var integer $retries
     */
    private $retries = 3;

    /**
     * The scopes
     *
     * @var array $scopes
     */
    private $scopes;

    /**
     * Construct the config object
     *
     * @param array $config An array of configuration options
     */
    public function __construct($config)
    {
        // Throw exceptions on essentials
        if (!isset($config['projectId']) || empty($config['projectId'])) {
            throw new CustomException('Missing Google Cloud Translate Project ID');
        } else {
            $this->projectId = (string)$config['projectId'];
        }

        if (!isset($config['key']) || empty($config['key'])) {
            $this->key = null;
        } else {
            $this->key = (string)$config['key'];
        }

        // optionals
        if (isset($config['cheapskate'])) {
            $this->cheapskate = (bool)$config['cheapskate'];
        }

        if (isset($config['cheapskateCount'])) {
            $this->cheapskateCount = $config['cheapskateCount'];
        } else {
            $this->cheapskateCount = 160;
        }

        if (isset($config['cache'])) {
            $this->cache = (bool)$config['cache'];
        }

        if (isset($config['source'])) {
            if (Validation::isValidLanguageRegex($config['source'])) {
                $this->source = $config['source'];
            } else {
                throw new CustomException('Invalid source');
            }
        } else {
            $this->source = 'en';
        }

        if (isset($config['target'])) {
            if (Validation::isValidLanguageRegex($config['target'])) {
                $this->target = $config['target'];
            } else {
                throw new CustomException('Invalid target');
            }
        } else {
            $this->target = 'en';
        }

        if (isset($config['type']) && !empty($config['type'])) {
            if (Validation::isValidType($config['type'])) {
                $this->type = $config['type'];
            } else {
                throw new CustomException('Invalid type');
            }
        } else {
            $this->type = 'text';
        }

        /**
         * TODO

        if (isset($config['authCache']) && !empty($config['authCache'])) {
            $this->authCache = (bool)$config['cache'];
        }

        if (isset($config['authCacheOptions']) && !empty($config['authCacheOptions'])) {
            $this->authCacheOptions = $config['authCacheOptions'];
        }

        if (isset($config['authHttpHandler']) && !empty($config['authHttpHandler'])) {
            $this->authHttpHandler = $config['authHttpHandler'];
        }

        if (isset($config['httpHandler']) && !empty($config['httpHandler'])) {
            $this->httpHandler = $config['httpHandler'];
        }
        */
        if (isset($config['keyFile']) && !empty($config['keyFile'])) {
            $this->keyFile = $config['keyFile'];
        }

        if (isset($config['keyFilePath']) && !empty($config['keyFilePath'])) {
            $this->keyFilePath = $config['keyFilePath'];
        }

        if (isset($config['retries']) && !empty($config['retries'])) {
            $this->retries = $config['retries'];
        }

        if (isset($config['scopes']) && !empty($config['scopes'])) {
            $this->scopes = $config['scopes'];
        }
    }

    /**
     * Retrieves the expected config for the Cloud Translate API
     *
     * @return array
     */
    public function getCloudTranslateConfig()
    {
        $config = [
            'key' => $this->key,
            'projectId' => $this->projectId,
        ];

        /**
         * TODO
         *
        if ($this->authCache) {
            $config['authCache'] = $this->authCache;
        }

        if ($this->authCache && $this->authCacheOptions) {
            $config['authCacheOptions'] = $this->authCacheOptions;
        }

        if ($this->authHttpHandler) {
            $config['authHttpHandler'] = $this->authHttpHandler;
        }

        if ($this->httpHandler) {
            $config['httpHandler'] = $this->httpHandler;
        }
        */

        if ($this->keyFile) {
            $config['keyFile'] = $this->keyFile;
        }

        if ($this->keyFilePath) {
            $config['keyFilePath'] = $this->keyFilePath;
        }

        if ($this->retries) {
            $config['retries'] = $this->retries;
        }

        if ($this->scopes) {
            $config['scopes'] = $this->scopes;
        }

        return $config;
    }
}
