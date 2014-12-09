<?php
/**
 * This is a part of Maketok Site. Licensed under GPL 3.0
 *
 * @project site
 * @developer Slayer slayer.birden@gmail.com maketok.com
 */

namespace Maketok\Util\Hydrator;


use Zend\Stdlib\Hydrator\ObjectProperty;

class Hybrid extends ObjectProperty
{

    /**
     * {@inheritdoc}
     * add setter for origin data
     */
    public function hydrate(array $data, $object)
    {
        $object = parent::hydrate($data, $object);

        if (method_exists($object, 'setOrigin')) {
            $object->setOrigin($data);
        }
        return $object;
    }
}
