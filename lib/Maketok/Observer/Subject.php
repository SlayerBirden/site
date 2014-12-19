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
class Subject implements SubjectInterface
{

    /**
     * @var bool
     */
    protected $shouldStopPropagation = false;

    /**
     * @var string
     */
    protected $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getShouldStopPropagation()
    {
        return $this->shouldStopPropagation;
    }

    /**
     * {@inheritdoc}
     */
    public function setShouldStopPropagation($flag)
    {
        $this->shouldStopPropagation = (bool) $flag;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }
}
