<?php

namespace Supinfo\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Form;

abstract class AdminController extends EntityController
{

    /*
     *
     */
    
    private function getAdminTypes()
    {
        return array(
            'edit',
            'delete',
            'list',
        );
    }



    /*
     * 
     */

    public function adminAction($_admin_type)
    {
        $this->initialize();

        if (!in_array($_admin_type, $this->getAdminTypes())) {
            throw $this->createNotFoundException();
        }

        $response = call_user_func(array($this, $_admin_type.'Action'));

        return $response ?
            $response :
            $this->renderAdminView($_admin_type);
    }

    private function initialize()
    {
        if (!class_exists($this->getEntityClassName())) {
            throw new Exception('AdminController: Entity Class "'.$this->getEntityClassName().'" can not be found.');
        }
    }



    /*
     *  View override.
     */

    protected function renderAdminView($admin_type)
    {
        $viewFullName = $this->getViewFullName($admin_type);

        if (!$this->viewExists($viewFullName)) {
            $viewFullName = $this->getDefaultAdminViewFullName($admin_type);
        }
        //echo $viewFullName;
        return $this->render($viewFullName, $this->viewData);
    }



    /*
     *  Form stuff.
     */

    protected function getEntityTypeClassName()
    {
        return $this->getBundleNamespace().'\Form\\'.$this->getEntityName().'Type';
    }

    protected function createEntityForm()
    {
        $entityTypeClassName = $this->getEntityTypeClassName();
        $entityType = new $entityTypeClassName();

        $this->entityForm = $this->get('form.factory')->create($entityType, $this->entity);
    }



    /*
     *  View stuff.
     */

    protected function getViewFileName($admin_type)
    {
        return $admin_type.'.html.twig';
    }

    protected function getAdminViewFormat()
    {
        return $this->getBundleFormat().':Admin';
    }

    protected function getViewFormat()
    {
        return $this->getAdminViewFormat().'\\'.$this->getEntityName();
    }

    protected function getViewFullName($admin_type)
    {
        return $this->getViewFormat().':'.$this->getViewFileName($admin_type);
    }

    protected function getDefaultAdminViewFullName($admin_type)
    {
        return $this->getAdminViewFormat().':'.$admin_type.'.html.twig';
    }

    protected function viewExists($viewFullName)
    {
        return $this->get('templating')->exists($viewFullName);
    }



    /*
     *  Routes stuff.
     */

    protected function getRoutes()
    {
        $adminRoutes = array(
            'new' => $this->getEntityName().'_admin_new',
            'edit' => $this->getEntityName().'_admin_edit',
            'delete' => $this->getEntityName().'_admin_delete',
            'list' => $this->getEntityName().'_admin_list',
        );

        return array_merge(parent::getRoutes(), $adminRoutes);
    }

    protected function redirectToList($page = null)
    {
        $redirectUrl = $this->generateUrl($this->getRoute('list'), array('page' => $page));
        return $this->redirect($redirectUrl);
    }

    protected function redirectToEdit()
    {
        $redirectUrl = $this->generateUrl($this->getRoute('edit'), array('id' => $this->entity->getId()));
        return $this->redirect($redirectUrl);
    }


    
    /*
     *  Actions.
     */
    
    protected function editAction()
    {
        $this->fetchEntity();
        $this->createEntityForm();

        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $this->entityForm->bindRequest($request);

            if ($this->entityForm->isValid()) {
                // Save modifications to DB.
                $em = $this->getEntityManager();
                $em->persist($this->entity);
                $em->flush();

                // TODO: Set flash notice.

                // Redirect to the edit page.
                return $this->redirectToList();
            }
        }
    }

    protected function deleteAction()
    {
        $this->fetchEntity();

        $em = $this->getEntityManager();
        $em->remove($this->entity);
        $em->flush();

        // TODO: Set flash notice.

        // Redirect to the list.
        return $this->redirectToList();
    }
    
}