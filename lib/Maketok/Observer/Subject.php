<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
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
     * @param bool | int $flag
     * @return mixed
     */
    public function setShouldStopPropagation($flag)
    {
        $this->_shouldStopPropagation = (bool) $flag;
    }
}
