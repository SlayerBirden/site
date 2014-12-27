<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param  string    $value
     * @return \DateTime
     */
    public function transform($value)
    {
        return parent::reverseTransform($value);
    }

    /**
     * @param  \DateTime $value
     * @return string
     */
    public function reverseTransform($value)
    {
        return parent::transform($value);
    }
}
