<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Observer;

class Subject implements SubjectInterface
{

    protected $_shouldStopPropagation = false;

    protected $_code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->_code = $code;
    }

    /**
     * @return bool
     */
    public function getShouldStopPropagation()
    {
        return $this->_shouldStopPropagation;
    }

    /**
     * @param  bool | int $flag
     * @return mixed
     */
    public function setShouldStopPropagation($flag)
    {
        $this->_shouldStopPropagation = (bool) $flag;
    }
}
