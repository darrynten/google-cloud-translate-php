<?php

namespace DarrynTen\GoogleCloudTranslatePhp\Tests\GoogleCloudTranslatePhp;

use PHPUnit_Framework_TestCase;
use Mockery as m;
use ReflectionClass;

use DarrynTen\GoogleCloudTranslatePhp\GoogleCloudTranslate;

class GoogleCloudTranslateTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testConstruct()
    {
        $config = [
            'projectId' => 'project-id'
        ];

        $instance = new GoogleCloudTranslate($config);
        $this->assertInstanceOf(GoogleCloudTranslate::class, $instance);
    }

    public function testSet()
    {
        $config = [
            'projectId' => 'project-id',
            'key' => '123',
            'format' => 'html',
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

        $this->assertEquals('', $instance->config->model);
        $instance->setModel('base');
        $this->assertEquals('base', $instance->config->model);

        $this->assertEquals('html', $instance->config->format);
        $instance->setFormat('text');
        $this->assertEquals('text', $instance->config->format);

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
    }

    public function testLanguages()
    {
        $config = [
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('languages')
            ->once()
            ->andReturn();

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
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('localizedLanguages')
            ->once()
            ->andReturn();

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $languages = $instance->localizedLanguages();
    }

    public function testDetect()
    {
        $config = [
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('detectLangauge')
            ->once()
            ->andReturn();

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->detectLangauge('see spot. see spot run. good spot.');
    }

    public function testDetectBatch()
    {
        $config = [
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('detectLangaugeBatch')
            ->once()
            ->andReturn();

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->detectLangaugeBatch([
            'This is the first one',
            'Ich liebe dich'
        ]);
    }

    public function testTranslate()
    {
        $config = [
            'projectId' => 'project-id',
            'cheapskate' => true,
            'target' => 'es',
            'source' => 'de',
            'cache' => true,
            'cheapskate' => false,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('translate')
            ->once()
            ->andReturn();

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
            'projectId' => 'project-id',
            'cheapskate' => true,
            'source' => 'de',
            'cache' => true,
        ];

        $client = m::mock(TranslateClient::class);

        $client->shouldReceive('translateBatch')
            ->once()
            ->andReturn();

        $instance = new GoogleCloudTranslate($config);

        // Need to inject mock to a private property
        $reflection = new ReflectionClass($instance);
        $reflectedClient = $reflection->getProperty('translateClient');
        $reflectedClient->setAccessible(true);
        $reflectedClient->setValue($instance, $client);

        $result = $instance->translateBatch([
            'Multiple translations',
            'Konnen sie vielen haben?'
        ]);
    }
}
