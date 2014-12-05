<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Zend\Db\ResultSet;

use Zend\Db\ResultSet\HydratingResultSet as BasicHydrator;

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
