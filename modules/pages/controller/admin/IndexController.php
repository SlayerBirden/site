<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages\controller\admin;

use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractAdminController;

class IndexController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $this->setTemplate('pages.html.twig');
        $table = $this->ioc()->get('pages_table');
        try {
            $pages = $table->fetchAll();
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $pages = [];
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Pages CRUD Management',
            'description' => 'Pages',
            'pages' => $pages,
        ));
    }
}
