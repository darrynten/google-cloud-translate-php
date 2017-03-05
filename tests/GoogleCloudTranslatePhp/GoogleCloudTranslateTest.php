<?php

namespace DarrynTen\GoogleCloudTranslatePhp\Tests\GoogleCloudTranslatePhp;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use ReflectionClass;

use DarrynTen\GoogleCloudTranslatePhp\Config;
use DarrynTen\GoogleCloudTranslatePhp\GoogleCloudTranslate;

class GoogleCloudTranslateTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function getMockClient()
    {
        $config = [
            'projectId' => 'project-id',
        ];

        $config = new Config($config);

        $mock = m::mock(TranslateClient::class);

        $mock->shouldReceive('__construct')
          ->with($config)
          ->zeroOrMoreTimes()
          ->andReturn();

        $mock->shouldReceive('languages')
          ->zeroOrMoreTimes()
          ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/languages_response.json')));

        $mock->shouldReceive('localizedLanguages')
          ->zeroOrMoreTimes()
          ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/source_languages_for_en.json'), true));

        $mock->shouldReceive('localizedLanguages')
          ->zeroOrMoreTimes()
          ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/source_languages_for_en.json'), true));

        return $mock;
    }

    public function testConstruct()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);
        $this->assertInstanceOf(GoogleCloudTranslate::class, $instance);
    }

    public function testSet()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'key' => 'xxx',
            'format' => 'text',
            'model' => 'base',
            'cheapskate' => true,
            'cache' => true,
            // 'authCache' => null,
            // 'authCacheOptions' => ['options'],
            // 'authHttpHandler' => null,
            // 'httpHandler' => null,
            'keyFile' => '{key:1}',
            'keyFilePath' => '.',
            'retries' => 3,
            'scopes' => ['scope'],
        ];

        $instance = new GoogleCloudTranslate($config);

        $config = $instance->config->getCloudTranslateConfig();
        $this->assertArrayHasKey('projectId', $config);
        $this->assertArrayHasKey('key', $config);
        $this->assertArrayHasKey('target', $config);
        $this->assertArrayHasKey('keyFile', $config);
        $this->assertArrayHasKey('keyFilePath', $config);
        $this->assertArrayHasKey('scopes', $config);

        $this->assertEquals('base', $instance->config->model);
        $instance->setModel('');
        $this->assertEquals('', $instance->config->model);
        $instance->setModel('base');
        $this->assertEquals('base', $instance->config->model);

        $this->assertEquals('text', $instance->config->format);
        $instance->setFormat('html');
        $this->assertEquals('html', $instance->config->format);

        $this->assertEquals('160', $instance->config->cheapskateCount);
        $instance->setCheapskateCount(12);
        $this->assertEquals(12, $instance->config->cheapskateCount);

        $this->assertEquals('en', $instance->config->target);
        $instance->setTargetLanguage('de');
        $this->assertEquals('de', $instance->config->target);

        $this->assertEquals('en', $instance->config->source);
        $instance->setSourceLanguage('es');
        $this->assertEquals('es', $instance->config->source);

        $this->assertEquals(true, $instance->config->cheapskate);
        $instance->setCheapskate(false);
        $this->assertEquals(false, $instance->config->cheapskate);
        $instance->setCheapskate(true);

        $this->assertEquals(true, $instance->config->cache);
        $instance->setCache(false);
        $this->assertEquals(false, $instance->config->cache);
        $instance->setCache(true);

        $this->assertEquals(true, $instance->isValidPossibleTarget('es'));
        $this->assertEquals(true, $instance->isValidPossibleSourceForTarget('en'));
    }

    public function testLanguages()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => false,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('languages')
            ->once()
            ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/languages_response.json')));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $languages = $instance->languages();
    }

    public function testLocalizedLanguages()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => false,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('localizedLanguages')
            ->with(['target' => 'en'])
            ->once()
            ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/source_languages_for_en.json')));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $languages = $instance->localizedLanguages('en');
    }

    public function testDetect()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => false,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('detectLanguage')
            ->with(file_get_contents(__DIR__ . '/mocks/test_detect_sample.txt'), [ 'format' => 'text'])
            ->once()
            ->andReturn(file_get_contents(__DIR__ . '/mocks/detect_language_response_en.json'));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->detectLanguage(file_get_contents(__DIR__ . '/mocks/test_detect_sample.txt'));
    }

    public function testDetectBatch()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('detectLanguageBatch')
            ->with(json_decode(file_get_contents(__DIR__ . '/mocks/batch_language_request.json')), [ 'format' => 'text'])
            ->once()
            ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/batch_language_response.json')));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->detectLanguageBatch(json_decode(file_get_contents(__DIR__ . '/mocks/batch_language_request.json')));
    }

    public function testTranslate()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'target' => 'de',
            'source' => 'en',
            'cache' => true,
            'cheapskate' => false,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('translate')
            ->with('A super awesome thing to translate.', ['source' => 'en', 'target' => 'de', 'format' => 'text', 'model' => ''])
            ->once()
            ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/test_translate_result_en_to_de.json')));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->translate('A super awesome thing to translate.');
    }

    public function testTranslateBatch()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'source' => 'de',
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('translateBatch')
            ->with(json_decode(file_get_contents(__DIR__ . '/mocks/test_batch_translate_request.json')), ['source' => 'de', 'target' => 'en', 'format' => 'text', 'model' => null])
            ->once()
            ->andReturn(json_decode(file_get_contents(__DIR__ . '/mocks/test_batch_translate_response.json')));

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->translateBatch(json_decode(file_get_contents(__DIR__ . '/mocks/test_batch_translate_request.json')));
    }
}
