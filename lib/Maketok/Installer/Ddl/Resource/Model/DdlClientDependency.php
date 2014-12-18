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

class DdlClientDependency
{

    /** @var int */
    public $id;
    /** @var int */
    public $client_id;
    /** @var int */
    public $dependency_code;
}
