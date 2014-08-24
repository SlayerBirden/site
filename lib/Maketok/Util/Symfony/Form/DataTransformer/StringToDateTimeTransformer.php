<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Symfony\Form\DataTransformer;


use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\DataTransformer\BaseDateTimeTransformer;

class StringToDateTimeTransformer extends BaseDateTimeTransformer
{

    /**
     * Format used for generating strings
     * @var string
     */
    private $generateFormat;

    /**
     * Format used for parsing strings
     *
     * Different than the {@link $generateFormat} because formats for parsing
     * support additional characters in PHP that are not supported for
     * generating strings.
     *
     * @var string
     */
    private $parseFormat;

    /**
     * Whether to parse by appending a pipe "|" to the parse format.
     *
     * This only works as of PHP 5.3.7.
     *
     * @var bool
     */
    private $parseUsingPipe;

    /**
     * Transforms a \DateTime instance to a string
     *
     * @see \DateTime::format() for supported formats
     *
     * @param string  $inputTimezone  The name of the input timezone
     * @param string  $outputTimezone The name of the output timezone
     * @param string  $format         The date format
     * @param bool    $parseUsingPipe Whether to parse by appending a pipe "|" to the parse format
     *
     * @throws UnexpectedTypeException if a timezone is not a string
     */
    public function __construct($inputTimezone = null, $outputTimezone = null, $format = 'Y-m-d H:i:s', $parseUsingPipe = null)
    {
        parent::__construct($inputTimezone, $outputTimezone);

        $this->generateFormat = $this->parseFormat = $format;

        // The pipe in the parser pattern only works as of PHP 5.3.7
        // See http://bugs.php.net/54316
        $this->parseUsingPipe = null === $parseUsingPipe
            ? version_compare(phpversion(), '5.3.7', '>=')
            : $parseUsingPipe;

        // See http://php.net/manual/en/datetime.createfromformat.php
        // The character "|" in the format makes sure that the parts of a date
        // that are *not* specified in the format are reset to the corresponding
        // values from 1970-01-01 00:00:00 instead of the current time.
        // Without "|" and "Y-m-d", "2010-02-03" becomes "2010-02-03 12:32:47",
        // where the time corresponds to the current server time.
        // With "|" and "Y-m-d", "2010-02-03" becomes "2010-02-03 00:00:00",
        // which is at least deterministic and thus used here.
        if ($this->parseUsingPipe && false === strpos($this->parseFormat, '|')) {
            $this->parseFormat .= '|';
        }
    }

    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called on two occasions inside a form field:
     *
     * 1. When the form field is initialized with the data attached from the datasource (object or array).
     * 2. When data from a request is submitted using {@link Form::submit()} to transform the new input data
     *    back into the renderable format. For example if you have a date field and submit '2009-10-10'
     *    you might accept this value because its easily parsed, but the transformer still writes back
     *    "2009/10/10" onto the form field (for further displaying or other purposes).
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that value transformers must be chainable. If the transform() method
     * of the first value transformer outputs NULL, the second value transformer
     * must be able to process that value.
     *
     * By convention, transform() should return an empty string if NULL is
     * passed.
     *
     * @param string $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        if (empty($value)) {
            return;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        try {
            $outputTz = new \DateTimeZone($this->outputTimezone);
            $dateTime = \DateTime::createFromFormat($this->parseFormat, $value, $outputTz);

            $lastErrors = \DateTime::getLastErrors();

            if (0 < $lastErrors['warning_count'] || 0 < $lastErrors['error_count']) {
                throw new TransformationFailedException(
                    implode(', ', array_merge(
                        array_values($lastErrors['warnings']),
                        array_values($lastErrors['errors'])
                    ))
                );
            }

            // On PHP versions < 5.3.7 we need to emulate the pipe operator
            // and reset parts not given in the format to their equivalent
            // of the UNIX base timestamp.
            if (!$this->parseUsingPipe) {
                list($year, $month, $day, $hour, $minute, $second) = explode('-', $dateTime->format('Y-m-d-H-i-s'));

                // Check which of the date parts are present in the pattern
                preg_match(
                    '/(' .
                    '(?P<day>[djDl])|' .
                    '(?P<month>[FMmn])|' .
                    '(?P<year>[Yy])|' .
                    '(?P<hour>[ghGH])|' .
                    '(?P<minute>i)|' .
                    '(?P<second>s)|' .
                    '(?P<dayofyear>z)|' .
                    '(?P<timestamp>U)|' .
                    '[^djDlFMmnYyghGHiszU]' .
                    ')*/',
                    $this->parseFormat,
                    $matches
                );

                // preg_match() does not guarantee to set all indices, so
                // set them unless given
                $matches = array_merge(array(
                    'day' => false,
                    'month' => false,
                    'year' => false,
                    'hour' => false,
                    'minute' => false,
                    'second' => false,
                    'dayofyear' => false,
                    'timestamp' => false,
                ), $matches);

                // Reset all parts that don't exist in the format to the
                // corresponding part of the UNIX base timestamp
                if (!$matches['timestamp']) {
                    if (!$matches['dayofyear']) {
                        if (!$matches['day']) {
                            $day = 1;
                        }
                        if (!$matches['month']) {
                            $month = 1;
                        }
                    }
                    if (!$matches['year']) {
                        $year = 1970;
                    }
                    if (!$matches['hour']) {
                        $hour = 0;
                    }
                    if (!$matches['minute']) {
                        $minute = 0;
                    }
                    if (!$matches['second']) {
                        $second = 0;
                    }
                    $dateTime->setDate($year, $month, $day);
                    $dateTime->setTime($hour, $minute, $second);
                }
            }

            if ($this->inputTimezone !== $this->outputTimezone) {
                $dateTime->setTimeZone(new \DateTimeZone($this->inputTimezone));
            }
        } catch (TransformationFailedException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $dateTime;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format for your data processing/model layer.
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as empty strings). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param \DateTime $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!$value instanceof \DateTime) {
            throw new TransformationFailedException('Expected a \DateTime.');
        }

        $value = clone $value;
        try {
            $value->setTimezone(new \DateTimeZone($this->outputTimezone));
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }

        return $value->format($this->generateFormat);
    }
}
