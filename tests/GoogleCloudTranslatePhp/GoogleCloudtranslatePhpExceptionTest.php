<?php

namespace DarrynTen\GoogleCloudTranslatePhp\Tests\GoogleCloudTranslatePhp;

use Mockery as m;
use Google\Cloud\Exception\BadRequestException;
use DarrynTen\GoogleCloudTranslatePhp\Config;
use DarrynTen\GoogleCloudTranslatePhp\CustomException;
use DarrynTen\GoogleCloudTranslatePhp\GoogleCloudTranslate;
use PHPUnit_Framework_TestCase;

class GoogleCloudtranslatePhpExceptionTest extends PHPUnit_Framework_TestCase
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

    public function testNonTestConstructWithBadKey()
    {
        $this->expectException(BadRequestException::class);

        $config = [
            'key' => 'xxx',
            'projectId' => 'project-id',
        ];

        // Does a live request with a bad API key
        $instance = new GoogleCloudTranslate($config);
        $this->assertInstanceOf(GoogleCloudTranslate::class, $instance);
    }


    public function testApiException()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
        ];

        $this->expectException(CustomException::class);

        new GoogleCloudTranslate($config, 'xxx');
    }

    public function testApiJsonException()
    {
        $this->expectException(CustomException::class);

        throw new CustomException(
            json_encode(
                [
                    'errors' => [
                        'code' => 1,
                    ],
                    'status' => 404,
                    'title' => 'Not Found',
                    'detail' => 'Details',
                ]
            )
        );
    }

    public function testApiKeyException()
    {
        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'key' => '123',
            'format' => 'text',
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
    }

    public function testCheapskateException()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'cheapskate' => true,
            'cheapskateCount' => 10,
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->translate('yo yo yo yo yo yo yo yo yo');
    }

    public function testSameLanguageException()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->translate('true');
    }

    public function testSetBadPossibleSourceForTarge()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->isValidPossibleSourceForTarget('xxx');
    }


    public function testSetBadPossibleTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->isValidPossibleTarget('xxx');
    }


    public function testSetBadLanguageExcepton()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->getPossibleSourceLanguagesForTarget('xxx');
    }


    public function testSetLanguageExcepton()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setSourceLanguage('xxx');
    }

    public function testSetLanguageExceptonTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setTargetLanguage('xxx');
    }

    public function testSetTypeExceptonTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
            'format' => 'text',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setFormat('xxx');
    }

    public function testSetModelExceptonTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
            'is_test_runner' => true,
            'mock_client' => $this->getMockClient(),
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setModel('xxx');
    }
}
