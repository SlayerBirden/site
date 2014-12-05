<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * Please do not use for your own profit.
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Observer;

interface StateInterface
{
    /**
     * @return void
     */
    public function __construct($data = null);

    /**
     * @return void
     */
    public function __set($key, $value);

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
