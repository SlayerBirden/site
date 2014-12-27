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

/**
 * @codeCoverageIgnore
 */
class PhpFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $path = $this->locator->locate($resource);

        return include $path;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && $this->getExtension() === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return 'php';
    }
}
