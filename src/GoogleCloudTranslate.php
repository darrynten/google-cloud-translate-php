<?php

namespace DarrynTen\GoogleCloudTranslatePhp;

use Google\Cloud\Translate\TranslateClient;
use DarrynTen\AnyCache\AnyCache;

/**
 * Google Cloud Translate Client
 *
 * @category Library
 * @package  GoogleCloudTranslatePhp
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/google-cloud-translate-php/LICENSE>
 * @link     https://github.com/darrynten/google-cloud-translate-php
 */
class GoogleCloudTranslate
{
    /**
     * Hold the config option
     *
     * @var Config $config
     */
    public $config;

    /**
     * Keeps a copy of the translation client
     *
     * @var object $translateClient
     */
    private $translateClient;

    /**
     * The local cache
     *
     * @var AnyCache $cache
     */
    private $cache;

    /**
     * Construct
     *
     * Bootstraps the config and the cache, then loads the client
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
        $this->cache = new AnyCache();
        $this->translateClient = new TranslateClient(
            $this->config->getCloudTranslateConfig()
        );
    }

    /**
     * Get a list of supported languages
     *
     * @return mixed
     */
    public function languages()
    {
        $cacheKey = '__google_cloud_translate__languages_';

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->languages();
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Get a list of supported targets for a language
     *
     * @return mixed
     */
    public function localizedLanguages()
    {
        $cacheKey = '__google_cloud_translate__localised_languages_';

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->localizedLanguages($this->target);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Detect the language
     *
     * @return mixed
     */
    public function detectLangauge(string $sampleText)
    {
        $cacheKey = '__google_cloud_translate__detect_language_' .
            md5($sampleText) . '_type_' . $this->config->type;

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->detectLangauge($sampleText, $this->config->type);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Detect the language by batch
     *
     * @return mixed
     */
    public function detectLangaugeBatch(array $sampleTexts)
    {
        $cacheKey = '__google_cloud_translate__detect_language_batch_' .
            md5(json_encode($sampleTexts)) . '_type_' . $this->config->type;

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->detectLangaugeBatch($sampleTexts, $this->config->type);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Translate
     *
     * @return mixed
     */
    public function translate(string $text)
    {
        $this->checkCheapskate($text);

        if ($this->config->source === $this->config->target) {
            throw new CustomException('Cannot translate to and from the same language.');
        }

        $cacheKey = '__google_cloud_translate__translate_' .
            md5($text) . '_';

        $options = [
          'source' => $this->config->source,
          'target' => $this->config->target,
          'format' => $this->config->format,
          'model' => $this->config->model,
        ];

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->translate($text, $options);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }


    /**
     * Get the syntax analysis
     *
     * @return mixed
     */
    public function translateBatch(array $texts)
    {
        foreach ($texts as $text) {
            $this->checkCheapskate($text);
        }

        $cacheKey = '__google_cloud_translate__translate_batch_' .
            md5(json_encode($texts)) . '_';

        $options = [
          'source' => $this->config->source,
          'target' => $this->config->target,
          'format' => $this->config->format,
          'model' => $this->config->model,
        ];

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->translateBatch($texts, $options);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * This is a rather expensive service, detecting the language is charged
     * per character, and if you wanted to check the language used in a piece
     * of text you'd end up spending a fair sum depending on the size of the
     * text when you could get away with analysing a lot less.
     *
     * This mode is only used with language detection and defaults to a tweets
     * worth of characters
     *
     * Set `cheapskate` in your config to false to turn this off
     *
     * Default is on to save cash
     *
     * @throws CustomException
     * @return void
     */
    private function checkCheapskate(string $text)
    {
        if ($this->config->cheapskate === false) {
            return;
        }

        if (strlen($text) > $this->config->cheapskateCount) {
            throw new CustomException(
                sprintf(
                    'Text too long, cheapskate mode on with max count %s',
                    $this->config->cheapskateCount
                )
            );
        }
    }

    /**
     * Sets the document type
     *
     * @param string $type Either `html` or `text`
     *
     * @return void
     */
    public function setType(string $type)
    {
        if (Validation::isValidType($type)) {
            $this->config->type = $type;
        } else {
            throw new CustomException('Invalid Type');
        }
    }

    /**
     * Sets the document type
     *
     * @param string $type Either `html` or `text`
     *
     * @return void
     */
    public function setModel(string $model)
    {
        if (Validation::isValidModel($model)) {
            $this->config->model = $model;
        } else {
            throw new CustomException('Invalid Model');
        }
    }

    /**
     * Set the source language. Either `en` `es` (ISO) or `en-ZA` `en-GB` format
     *
     * @param string $sourceLanguage The desired language
     *
     * @return void
     */
    public function setSourceLanguage(string $sourceLanguage)
    {
        if (Validation::isValidLanguageRegex($sourceLanguage)) {
            $this->config->source = $sourceLanguage;
        } else {
            throw new CustomException('Invalid source language');
        }
    }


    /**
     * Set the target language. Either `en` `es` (ISO) or `en-ZA` `en-GB` format
     *
     * @param string $targetLanguage The desired language
     *
     * @return void
     */
    public function setTargetLanguage(string $targetLanguage)
    {
        if (Validation::isValidLanguageRegex($targetLanguage)) {
            $this->config->target = $targetLanguage;
        } else {
            throw new CustomException('Invalid target language');
        }
    }

    /**
     * Enable and disable cheapskate mode (trimming @ 1000 chars)
     *
     * @param boolean $value The state
     *
     * @return void
     */
    public function setCheapskate(bool $value)
    {
        $this->config->cheapskate = $value;
    }

    /**
     * Enable and disable cheapskate mode (trimming @ 1000 chars)
     *
     * @param boolean $value The state
     *
     * @return void
     */
    public function setCheapskateCount(int $value)
    {
        $this->config->cheapskateCount = $value;
    }

    /**
     * Enable and disable internal cache
     *
     * @param boolean $value The state
     *
     * @return void
     */
    public function setCache($value)
    {
        $this->config->cache = (bool)$value;
    }
}
