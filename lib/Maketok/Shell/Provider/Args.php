<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Provider;

use Maketok\App\Helper\UtilityHelperTrait;
use Maketok\Http\Request;

class Args implements ProviderInterface
{
    use UtilityHelperTrait;
    /**
     * @var string[]
     */
    protected $args = [];

    /**
     * {@inheritdoc}
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * {@inheritdoc}
     */
    public function getArg($key, $default)
    {
        $this->args = array_merge($this->args, getopt('', [$key . '::']));
        return $this->getIfExists($key, $this->args, $default);
    }
}
