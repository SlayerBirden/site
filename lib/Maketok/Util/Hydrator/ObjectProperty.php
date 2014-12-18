<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Hydrator;

use Maketok\Model\LazyModelInterface;
use Zend\Stdlib\Hydrator\ObjectProperty as BaseObjectProperty;

/**
 * @codeCoverageIgnore
 */
class ObjectProperty extends BaseObjectProperty implements LazyHydratorInterface
{

    /**
     * {@inheritdoc}
     * add setter for origin data
     */
    public function hydrate(array $data, $object)
    {
        $object = parent::hydrate($data, $object);

        return $this->saveOriginState($data, $object);
    }

    /**
     * @param  array              $data
     * @param  \ArrayObject|mixed $object
     * @return mixed
     */
    public function saveOriginState(array $data, $object)
    {
        if ($object instanceof LazyModelInterface) {
            $object->processOrigin($data);
        }

        return $object;
    }
}
