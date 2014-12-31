<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Resource\Model\Hydrator\Filter;

use Zend\Stdlib\Hydrator\Filter\FilterInterface;

class ClientFilter implements FilterInterface
{
    /**
     * Private object properties
     * @var string[]
     */
    protected $privateProperties = [
        'config',
        'dependencies',
        'updated_at',
        'dependents',
        'is_max_version',
        'got_update',
        'max_version',
    ];

    /**
     * {@inheritdoc}
     */
    public function filter($property)
    {
        if (in_array($property, $this->privateProperties)) {
            return false;
        }
        return true;
    }
}
