<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Installer\Data;

use Maketok\Installer\ClientInterface as BaseClientInterface;

interface ClientInterface extends BaseClientInterface
{

    /**
     * get client version to install
     *
     * @return string
     */
    public function getDataVersion();

    /**
     * get client config to install
     *
     * @param  string     $version
     * @return array|bool
     */
    public function getDataConfig($version);

    /**
     * get client identifier
     * must be unique
     *
     * @return string
     */
    public function getDataCode();
}
