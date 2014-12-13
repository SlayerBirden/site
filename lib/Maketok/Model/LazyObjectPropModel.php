<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Model;

/**
 * @codeCoverageIgnore
 */
class LazyObjectPropModel implements LazyModelInterface
{

    /**
     * @var array
     */
    protected $originalData;

    /**
     * {@inheritdoc}
     */
    public function setOrigin(array $data)
    {
        $this->originalData = $data;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrigin()
    {
        return $this->originalData;
    }

    /**
     * {@inheritdoc}
     */
    public function processOrigin(array $data = null)
    {
        if ($data) {
            return $this->setOrigin($data);
        } else {
            return $this->getOrigin();
        }
    }
}
