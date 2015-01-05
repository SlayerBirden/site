<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Authentication\Resource\Model\Hydrator\Filter;

use Zend\Stdlib\Hydrator\Filter\FilterInterface;

class UserFilter implements FilterInterface
{
    /**
     * Private object properties
     * @var string[]
     */
    protected $privateProperties = [
        'roles',
        'firstname',
        'lastname',
        'password_hash',
    ];

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function filter($property)
    {
        if (in_array($property, $this->privateProperties)) {
            return false;
        }
        return true;
    }
}
