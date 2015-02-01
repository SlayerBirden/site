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
use Maketok\Model\TableMapper;
use Maketok\Mvc\Controller\AbstractAdminController;
use Maketok\Mvc\RouteException;
use Maketok\Util\Exception\ModelException;
use Maketok\Util\Exception\ModelInfoException;
use modules\pages\Model\Page;
use Symfony\Component\Form\FormInterface;

class PageController extends AbstractAdminController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $this->setTemplate('page.html.twig');
        $model = $this->init($request);
        $form = $this->getFormFactory()->create('page', $model);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handle($form);
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Edit Page ' . $model->title,
            'description' => 'Page ' . $model->title,
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $model = $this->init($request);
        /** @var TableMapper $table */
        $table = $this->ioc()->get('pages_table');
        try {
            $table->delete($model);
        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf("Could not remove page #%d", $model->id));
        }
        return $this->redirect('/blog');
    }

    /**
     * @param Request $request
     * @return \modules\pages\model\Page
     * @throws RouteException
     */
    protected function init(Request $request)
    {
        $id = $request->getAttributes()->get('id');
        if ($id === null) {
            // route exception will lead to 404
            throw new RouteException("Can not init page without id.");
        }
        /** @var TableMapper $table */
        $table = $this->ioc()->get('pages_table');
        try {
            return $table->find($id);
        } catch (ModelException $e) {
            throw new RouteException("Could not find model by id.");
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $this->setTemplate('page.html.twig');
        $form = $this->getFormFactory()->create('page', new Page());
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $this->handle($form);
        }
        return $this->prepareResponse($request, array(
            'title' => 'Maketok Admin - Add New Page',
            'description' => 'Page Creation',
            'form' => $form->createView(),
        ));
    }

    /**
     * handle form request
     * @param FormInterface $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handle(FormInterface $form)
    {
        /** @var TableMapper $table */
        $table = $this->ioc()->get('pages_table');
        try {
            $data = $form->getData();
            $table->save($data);
            $this->getSession()->getFlashBag()->add(
                'success',
                'The page was saved successfully!'
            );
        } catch (ModelInfoException $e) {
            $this->getSession()->getFlashBag()->add(
                'notice',
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->getLogger()->err($e);
            $this->getSession()->getFlashBag()->add(
                'error',
                sprintf("There was an error processing your request.\nThe error text: %s", $e->getMessage())
            );
            return $this->returnBack();
        }
        return $this->redirect('pages');
    }
}
