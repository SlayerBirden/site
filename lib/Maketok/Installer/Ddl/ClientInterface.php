<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Ddl;

use Maketok\Installer\ClientInterface as BaseClientInterface;

interface ClientInterface extends BaseClientInterface
{
    /**
     * client register dependencies (parents)
     * it must register dependencies to change resources that were created by other clients
     *
     * @return string[]
     */
    public function getDependencies();

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDdlVersion();

    /**
     * get client config to install
     *
     * @param  string     $version
     * @return array|bool
     */
    public function getDdlConfig($version);

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDdlCode();
}
