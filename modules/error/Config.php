<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\error;

use Maketok\App\Helper\ContainerTrait;
use Maketok\Module\ConfigInterface;
use modules\error\controller\Index;

/**
 * @codeCoverageIgnore
 */
class Config implements ConfigInterface
{
    use ContainerTrait;

    /**
     * @return string
     */
    public function getVersion()
    {
        return '0.1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function initRoutes()
    {
        $this->ioc()->get('front_controller')->addDumper(['modules\error\controller\Index', 'dump']);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'error';
    }

    /**
     * magic method for returning string representation of the the config class
     * @return string
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function initListeners()
    {
        return;
    }
}
