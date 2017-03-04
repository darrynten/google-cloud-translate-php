<?php
/**
 * GoogleCloudTranslate Validation
 *
 * @category Validation
 * @package  GoogleCloudTranslatePhp
 * @author   Darryn Ten <darrynten@github.com>
 * @license  MIT <https://github.com/darrynten/google-cloud-translate-php/LICENSE>
 * @link     https://github.com/darrynten/google-cloud-translate-php
 */

namespace DarrynTen\GoogleCloudTranslatePhp;

class Validation
{
    /**
     * The valid text types
     *
     * @var array $validTypes
     */
    private static $validTypes = [
        'text',
        'html',
    ];

    /**
     * The valid translation model types
     *
     * @var array $validModels
     */
    private static $validModels =[
        '',
        'nmt',
        'base',
    ];

    /**
     * A valid ISO language regex (en)
     *
     * @var string $validISOLanguageRegex Regex for 2 characters
     */
    private static $validISOLanguageRegex = '/^[a-z]{2}$/';

    /**
     * A valid BCP-47 language regex (en-ZA)
     *
     * @var string $validBCP47LanguageRegex Regex for two lowecase, dash, 2 uppercase
     */
    private static $validBCP47LanguageRegex = '/^[a-z]{2}\-[A-Z]{2}$/';

    /**
     * Check if a type is valid
     *
     * @param string $type The type to check
     *
     * @return boolean
     */
    public static function isValidType($type)
    {
        return in_array($type, self::$validTypes);
    }

    /**
     * Check if a model is valid
     *
     * @param string $type The model to check
     *
     * @return boolean
     */
    public static function isValidModel($model)
    {
        return in_array($model, self::$validModels);
    }

    /**
     * Check if a string is a possible language string.
     *
     * Not the best but better than nothing. Can be expanded
     * upon.
     *
     * Accepts ISO (en, es, de) and BCP-47 (en-ZA, en-GB)
     *
     * @param string $language The language to check
     *
     * @return boolean
     */
    public static function isValidLanguageRegex($language)
    {
        $match = false;

        if (preg_match(self::$validISOLanguageRegex, $language)) {
            $match = true;
        }

        if (preg_match(self::$validBCP47LanguageRegex, $language)) {
            $match = true;
        }

        return $match;
    }
}
