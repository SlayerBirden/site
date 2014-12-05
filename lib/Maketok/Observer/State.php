<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Observer;

class State implements StateInterface
{
    private $_data = array();

    /**
     * @var SubjectInterface
     */
    private $_subject;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function __get($key)
    {
        return $this->_data[$key];
    }

    /**
     * @param SubjectInterface $subject
     * @return $this
     */
    public function setSubject(SubjectInterface $subject)
    {
        $this->_subject = $subject;
        return $this;
    }

    /**
     * @return SubjectInterface
     */
    public function getSubject()
    {
        return $this->_subject;
    }
}
