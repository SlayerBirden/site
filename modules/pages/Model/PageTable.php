<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages\Model;

use Maketok\Model\TableMapper;

class PageTable extends TableMapper
{
    /**
     * @param string $code
     * @return Page
     */
    public function findByCode($code)
    {
        $result = $this->fetchFilter(['code' => $code]);
        return $result->current();
    }

    /**
     * @return \Zend\Db\ResultSet\AbstractResultSet|Page[]
     */
    public function fetchActive()
    {
        $result = $this->fetchFilter(['active' => true]);
        return $result;
    }
}
