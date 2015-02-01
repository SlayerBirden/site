<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;

class DateIntervalStrategy extends DefaultStrategy
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($value)
    {
        if (is_scalar($value)) {
            if (strpos($value, 'P') !== 0) {
                // assuming we're storing seconds here
                $value = 'PT' . (int) $value . ('S');
            }
            $value = new \DateInterval($value);
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($value)
    {
        if (is_object($value) && $value instanceof \DateInterval) {
            $value = $value->s; // seconds
        }
        return $value;
    }
}
