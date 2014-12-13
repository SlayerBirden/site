<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 * @project site
 * @developer Oleg Kulik slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Hydrator;

use Zend\Stdlib\Hydrator\HydratorInterface;

interface LazyHydratorInterface extends HydratorInterface
{

    /**
     * @param array $data
     * @param \ArrayObject|mixed $object
     * @return mixed
     */
    public function saveOriginState(array $data, $object);
}
