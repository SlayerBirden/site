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

class Stdin implements ProviderInterface
{
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
        fputs(STDOUT, "Please insert next option '$key' [$default]:\n");
        $line = fgets(STDIN);
        if ($line !== false) {
            $this->args[$key] = $line;
            fputs(STDOUT, "Accepted\n");
            $line = rtrim($line);
            if (!empty($line)) {
                return $line;
            }
        }
        return $default;
    }
}
