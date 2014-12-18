<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;

/**
 * @codeCoverageIgnore
 */
class HttpStreamedHandler extends AbstractProcessingHandler
{

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        echo (string) $record['formatted'];
        @ob_flush();
        flush();
    }
}
