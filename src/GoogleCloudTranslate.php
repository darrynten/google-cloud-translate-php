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
     * List of possible translation target languages
     *
     * Used to pre-check that a language is supported as a target
     * or a source, so as to ensure no unneeded translation attempts
     * are made, which are charged per character.
     *
     * @var array $possibleTargetLanguages
     */
    private $possibleTargetLanguages = [];

    /**
     * List of possible source languages for targets.
     *
     * Used to pre-check that a language is supported as a target
     * or a source, so as to ensure no unneeded translation attempts
     * are made, which are charged per character.
     *
     * Stored with the language code as the key and the valid
     * sources as an array on that key.
     *
     * @var array $possibleSourceLanguages
     */
    private $possibleSourceLanguages = [];

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

        if (!isset($config['is_test_runner'])) {
            $this->translateClient = new TranslateClient(
                $this->config->getCloudTranslateConfig()
            );
        } else {
            $this->translateClient = $config['mock_client'];
        }

        // var_dump($this->translateClient->languages());

        // Get and store a list of possible translate languages
        $this->getPossibleTargetLanguages();
        $this->getPossibleSourceLanguagesForTarget($this->config->target);
    }

    /**
     * Get and store a list of possible languages that translation
     * can be performed against
     *
     * @return void
     */
    public function getPossibleTargetLanguages()
    {
        $possibleLanguages = $this->languages();

        foreach ($possibleLanguages as $language) {
            $this->possibleTargetLanguages[] = $language;
        }
    }

    /**
     * Get and store a list of possible languages that translation
     * can be performed against for a specified target
     *
     * @return void
     */
    public function getPossibleSourceLanguagesForTarget($target)
    {
        if (!Validation::isValidLanguageRegex($target)) {
            throw new CustomException(sprintf(
                'Invalid target language %s',
                $target
            ));
        }

        $possibleSourceLanguages = $this->localizedLanguages($target);

        foreach ($possibleSourceLanguages as $language) {
            $this->possibleSourceLanguages[$target][$language['code']] = $language['name'];
        }
    }

    /**
     * Check if a desired language is a valid target
     *
     * @param string $target
     *
     * @return boolean
     *
     * @throws CustomException
     */
    public function isValidPossibleTarget($target)
    {
        if (!in_array($target, $this->possibleTargetLanguages)) {
            throw new CustomException(sprintf(
                'Invalid target language %s',
                $target
            ));
        }

        return true;
    }

    /**
     * Check if a particular language is valid as a source for a
     * given target
     *
     * @param string $target
     *
     * @return boolean
     *
     * @throws CustomException
     */
    public function isValidPossibleSourceForTarget($source)
    {
        if (!isset($this->possibleSourceLanguages[$this->config->target])) {
            $this->getPossibleSourceLanguagesForTarget($this->config->target);
        }

        if (!array_key_exists($source, $this->possibleSourceLanguages[$this->config->target])) {
            throw new CustomException(sprintf(
                'Invalid source language %s for target %s',
                $source,
                $this->config->target
            ));
        }

        return true;
    }

    /**
     * Get a list of supported languages
     *
     * @return array
     */
    public function languages()
    {
        $cacheKey = '__google_cloud_translate__languages_';

        if (!$this->config->cache || !$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->languages();
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Get a list of supported targets for a language
     *
     * @return array
     */
    public function localizedLanguages($target)
    {
        $cacheKey = '__google_cloud_translate__localised_languages_' . $target;

        if (!$this->config->cache || !$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->localizedLanguages([
                'target' => $target
            ]);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Detect the language
     *
     * @return mixed
     */
    public function detectLanguage($sample)
    {
        $cacheKey = '__google_cloud_translate__detect_language_' .
            md5($sample) . '_type_' . $this->config->format;

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->detectLanguage($sample, [
                'format' => $this->config->format
            ]);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }

        return $result;
    }

    /**
     * Detect the language by batch
     *
     * @return mixed
     */
    public function detectLanguageBatch(array $sampleTexts)
    {
        $cacheKey = '__google_cloud_translate__detect_language_batch_' .
            md5(json_encode($sampleTexts)) . '_type_' . $this->config->format;

        if (!$result = unserialize($this->cache->get($cacheKey))) {
            $result = $this->translateClient->detectLanguageBatch($sampleTexts, [
                'format' => $this->config->format
            ]);
            $this->cache->put($cacheKey, serialize($result), 9999999);
        }


        return $result;
    }

    /**
     * Translate
     *
     * @return mixed
     */
    public function translate($text)
    {
        $this->checkCheapskate($text);

        if ($this->config->source === $this->config->target) {
            throw new CustomException('Cannot translate to and from the same language.');
        }

        $options = [
          'source' => $this->config->source,
          'target' => $this->config->target,
          'format' => $this->config->format,
          'model' => (string) $this->config->model,
        ];

        $cacheKey = '__google_cloud_translate__translate_' .
            md5(serialize([$text, $options])) . '_';

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

        $options = [
          'source' => $this->config->source,
          'target' => $this->config->target,
          'format' => $this->config->format,
          // Default model is null for batch, '' for single
          'model' => ($this->config->model !== '') ? $this->config->model : null,
        ];

        $cacheKey = '__google_cloud_translate__translate_batch_' .
            md5(serialize([$texts, $options])) . '_';

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
    private function checkCheapskate($text)
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
     * Sets the document format
     *
     * @param string $format Either `html` or `text`
     *
     * @return void
     */
    public function setFormat($format)
    {
        if (Validation::isValidFormat($format)) {
            $this->config->format = $format;
        } else {
            throw new CustomException('Invalid format');
        }
    }

    /**
     * Sets the model type
     *
     * @param string $model
     *
     * @return void
     */
    public function setModel($model)
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
    public function setSourceLanguage($sourceLanguage)
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
    public function setTargetLanguage($targetLanguage)
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
    public function setCheapskate($value)
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
    public function setCheapskateCount($value)
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
