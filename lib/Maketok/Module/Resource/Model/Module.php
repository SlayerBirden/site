<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module\Resource\Model;

use Maketok\Model\LazyObjectPropModel;

class Module extends LazyObjectPropModel
{
    /** @var string */
    public $module_code;
    /** @var string */
    public $version;
    /** @var int */
    public $active;
    /** @var \DateTime data */
    public $updated_at;
    /** @var \DateTime data */
    public $created_at;
    /** @var string $area */
    public $area;
}
