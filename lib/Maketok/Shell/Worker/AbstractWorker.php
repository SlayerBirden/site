<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Worker;

use Maketok\Shell\Installer;

abstract class AbstractWorker implements WorkerInterface
{
    /**
     * @var Installer
     */
    protected $installer;

    /**
     * @param Installer $installer
     */
    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }
}
