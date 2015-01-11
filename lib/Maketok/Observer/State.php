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

/**
 * @codeCoverageIgnore
 */
class State implements StateInterface, \IteratorAggregate
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var SubjectInterface
     */
    private $subject;

    /**
     * {@inheritdoc}
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject(SubjectInterface $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $data = $this->data;
        $data['subject'] = $this->getSubject();
        return new \ArrayIterator($data);
    }
}
