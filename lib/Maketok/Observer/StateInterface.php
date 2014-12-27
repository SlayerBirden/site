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

interface StateInterface
{
    /**
     * @param  array $data
     * @return \Maketok\Observer\StateInterface
     */
    public function __construct($data = null);

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value);

    /**
     * @param  string $key
     * @return mixed
     */
    public function __get($key);

    /**
     * @param  SubjectInterface $subject
     * @return $this
     */
    public function setSubject(SubjectInterface $subject);

    /**
     * @return SubjectInterface
     */
    public function getSubject();
}
