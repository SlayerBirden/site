<?php
/**
 * This is a part of Maketok site package.
 *
 * @author Oleg Kulik <slayer.birden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace modules\pages\controller;

use Maketok\Http\Request;
use Maketok\Mvc\Controller\AbstractBaseController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use modules\pages\Model\PageTable;

class PageController extends AbstractBaseController
{
    public function indexAction(Request $request)
    {
        $page = $this->initPage($request);
        if (empty($page->layout)) {
            $this->setTemplate('page.html.twig');
        } else {
            $this->setTemplate('page' . $page->id);
        }
        return $this->prepareResponse($request, array(
            'page' => $page,
        ));
    }


    /**
     * @param Request $request
     * @return \modules\pages\model\Page
     * @throws RouteException
     */
    protected function initPage(Request $request)
    {
        $id = $request->getAttributes()->get('page_id');
        if ($id === null) {
            throw new RouteException("Page id wasn't set.");
        }
        /** @var PageTable $table */
        $table = $this->ioc()->get('pages_table');
        try {
            return $table->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }
}
