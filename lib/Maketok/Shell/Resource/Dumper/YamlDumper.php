<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Shell\Resource\Dumper;

use Symfony\Component\Yaml\Dumper;

class YamlDumper implements DumperInterface
{
    /**
     * @var Dumper
     */
    private $dumper;

    public function __construct(Dumper $dumper)
    {
        $this->dumper = $dumper;
    }

    /**
     * {@inheritdoc}
     */
    public function write($input, $level = 3, $indent = 0)
    {
        return $this->dumper->dump($input, $level, $indent);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return 'yml';
    }
}
