<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

use Zend\Db\Adapter\Adapter;

class MockAdapter extends Adapter
{
    /**
     * This class is solely used to get hold of adapter inheritance without constructor initiation
     */
    public function __construct()
    {
        #pass
    }
}
