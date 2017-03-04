<?php

namespace DarrynTen\GoogleCloudTranslatePhp\Tests;

use PHPUnit_Framework_TestCase;
use DarrynTen\GoogleCloudTranslatePhp\Validation;

class ValidationTest extends PHPUnit_Framework_TestCase
{
    public function testValidType()
    {
        $this->assertTrue(Validation::isValidFormat('html'));
    }

    public function testValidLanguage()
    {
        $this->assertTrue(Validation::isValidLanguageRegex('en'));
        $this->assertTrue(Validation::isValidLanguageRegex('en-ZA'));
    }

    public function testInvalidType()
    {
        $this->assertFalse(Validation::isValidFormat('BAR'));
    }

    public function testInvalidLanguage()
    {
        $this->assertFalse(Validation::isValidLanguageRegex('BAR'));
    }
}
