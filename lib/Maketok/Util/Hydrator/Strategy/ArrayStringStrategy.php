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

class ArrayStringStrategy extends DefaultStrategy
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($value)
    {
        if (is_scalar($value)) {
            $value = explode(',', $value);
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        return $value;
    }
}
