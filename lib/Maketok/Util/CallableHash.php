<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Util;

class CallableHash
{

    /**
     * @param callable $client
     * @return string
     */
    public function getHash($client)
    {
        if (!is_callable($client)) {
            throw new \InvalidArgumentException("Will hash only callables.");
        }
        if (is_object($client) && $client instanceof \Closure) {
            $closureHelper = new ClosureComparer();
            return md5(serialize($closureHelper->getClosureContents($client)));
        } else {
            if (is_array($client) && isset($client[0]) && is_object($client[0])) {
                // we don't need a unique hash for changing object here
                // we only need to hash class name
                $client[0] = get_class($client[0]);
            } elseif (is_object($client)) {
                // same goes to invokers
                $client = get_class($client);
            }
            return md5(serialize($client));
        }
    }
}
