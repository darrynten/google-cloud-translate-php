<?php

namespace DarrynTen\GoogleCloudTranslatePhp\Tests\GoogleCloudTranslatePhp;

use DarrynTen\GoogleCloudTranslatePhp\CustomException;
use DarrynTen\GoogleCloudTranslatePhp\GoogleCloudTranslate;
use PHPUnit_Framework_TestCase;

class GoogleCloudTranslatePhpExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testApiException()
    {
        $this->expectException(CustomException::class);

        new GoogleCloudTranslate([], 'xxx');
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

    public function testCheapskateException()
    {
        $this->expectException(CustomException::class);

        $config = [
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
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->translate('true');
    }

    public function testSetLanguageExcepton()
    {
        $this->expectException(CustomException::class);

        $config = [
            'projectId' => 'project-id',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setSourceLanguage('xxx');
    }

    public function testSetLanguageExceptonTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
            'projectId' => 'project-id',
            'model' => 'blank',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setTargetLanguage('xxx');
    }

    public function testSetTypeExceptonTarget()
    {
        $this->expectException(CustomException::class);

        $config = [
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
            'projectId' => 'project-id',
            'model' => 'blank',
        ];

        $instance = new GoogleCloudTranslate($config);

        $instance->setModel('xxx');
    }
}
