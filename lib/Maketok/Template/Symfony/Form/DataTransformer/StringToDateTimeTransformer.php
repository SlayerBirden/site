<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Template\Symfony\Form\DataTransformer;


use Symfony\Component\Form\Extension\Core\DataTransformer\DateTimeToStringTransformer as BaseDateTimeToStringTransformer;

/**
 * @codeCoverageIgnore
 * This is the opposite of Symfony's DateTimeToStringTransformer
 */
class StringToDateTimeTransformer extends BaseDateTimeToStringTransformer
{

    /**
     * @param string $value
     * @return \DateTime
     */
    public function transform($value)
    {
        return parent::reverseTransform($value);
    }

    /**
     * @param \DateTime $value
     * @return string
     */
    public function reverseTransform($value)
    {
        return parent::transform($value);
    }
}
