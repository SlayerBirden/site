<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Observer;

interface StateInterface
{

    /**
     * @param mixed $data
     * @return \Maketok\Observer\StateInterface
     */
    public function __construct($data = null);

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key);

    /**
     * @param SubjectInterface $subject
     * @return $this
     */
    public function setSubject(SubjectInterface $subject);

    /**
     * @return SubjectInterface
     */
    public function getSubject();
}
