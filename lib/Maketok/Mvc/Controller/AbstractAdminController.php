<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maketok\Mvc\Controller;

/**
 * @codeCoverageIgnore
 */
class AbstractAdminController extends AbstractController
{

    /**
     * init
     * add base template path
     */
    public function __construct()
    {
        $this->addTemplatePath(AR . '/src/admin/view');
    }
}
