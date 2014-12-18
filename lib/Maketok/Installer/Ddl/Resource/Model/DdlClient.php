<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl\Resource\Model;

use Maketok\Model\LazyObjectPropModel;

class DdlClient extends LazyObjectPropModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $code;
    /** @var string */
    public $version;
    /** @var array */
    public $config;
    /** @var array */
    public $dependencies;
}
