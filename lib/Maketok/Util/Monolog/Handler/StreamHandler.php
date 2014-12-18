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

use Monolog\Handler\StreamHandler as BaseStreamHandler;

/**
 * @codeCoverageIgnore
 */
class StreamHandler extends BaseStreamHandler
{
    /**
     * {@inheritdoc}
     */
    public function write(array $record)
    {
        if (null === $this->stream) {
            if (!$this->url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }
            $dir = dirname($this->url);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, TRUE);
            }
        }
        parent::write($record);
    }
}
