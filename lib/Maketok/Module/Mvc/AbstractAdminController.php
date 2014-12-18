<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Module\Mvc;

use \Maketok\Mvc\Controller\AbstractAdminController as BaseAbsractAdminController;

/**
 * @codeCoverageIgnore
 */
class AbstractAdminController extends BaseAbsractAdminController implements ModulesAwareControllerInterface
{
    use ControllerTemplateTrait;

    /**
     * init modules template path
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadModulePath('admin');
    }
}
