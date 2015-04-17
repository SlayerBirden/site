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

use Maketok\Installer\ManagerInterface;

class Db extends AbstractWorker
{
    /**
     * @var ManagerInterface
     */
    private $ddlInstaller;

    /**
     * @param ManagerInterface $ddlInstaller
     */
    public function __construct(ManagerInterface $ddlInstaller)
    {
        $this->ddlInstaller = $ddlInstaller;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        do {
            $res = $this->ddlInstaller->process();
        } while ($res != 0);
    }

    /**
     * @return string representation
     */
    public function __toString()
    {
        return 'db';
    }
}
