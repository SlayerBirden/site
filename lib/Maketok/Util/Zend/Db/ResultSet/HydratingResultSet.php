<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Zend\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet as BasicHydrator;

/**
 * @codeCoverageIgnore
 */
class HydratingResultSet extends BasicHydrator
{
    /**
     * @return null|\ArrayObject|mixed
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }
}
