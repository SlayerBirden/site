<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Worker\Webserver;

use Maketok\Shell\Worker\AbstractWorker;

class Apache extends AbstractWorker
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        // this is pretty simple worker
        // we need to check if the webserver used is apache
        $webserver = $this->installer->getArg("webserver");
        if ($webserver == 'apache') {
            $fpath = [AR . '/public/.htaccess', AR . '/public/admin/.htaccess'];
            foreach ($fpath as $path) {
                if (!file_exists($path) && file_exists($path . '.sample')) {
                    copy($path . '.sample', $path);
                }
            }
        }
    }

    /**
     * @return string representation
     */
    public function __toString()
    {
        return 'webserver_apache';
    }
}
