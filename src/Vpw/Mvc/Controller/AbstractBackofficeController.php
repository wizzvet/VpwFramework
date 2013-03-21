<?php

namespace Vpw\Mvc\Controller;


use Zend\View\Helper\Partial;

use Vpw\Dal\Mapper\DbMapper;

use Vpw\Table\Column;

use Vpw\Table\Table;

use Vpw\Dal\ModelObject;

use Zend\View\Model\ViewModel;

use Vpw\Form\SpecFactory;

use Zend\Db\Adapter\Adapter;

use Zend\Mvc\Controller\AbstractActionController;

abstract class AbstractBackofficeController extends AbstractActionController
{
    /**
     * @var DbMapper
     */
    private $mapper;


    /**
     * Binded model to the form
     * @var ModelObject
     */
    private $model;


    protected $successMessages = array(
        'add' => "L'objet a bien été ajouté.",
        'edit' => "L'objet a bien été modifié.",
        'delete' => "L'objet a bien été supprimé.",
    );


    protected function createViewModel()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal($this->getRequest()->isXmlHttpRequest());
        return $viewModel;
    }


    public function indexAction()
    {
        $viewModel = $this->createViewModel();

        $table = $this->createListTable();
        $table->setRows($this->getListTableRows());

        $viewModel->table = $table;

        return $viewModel;
    }

    public function addAction()
    {
        $viewModel = $this->createViewModel();

        $form = $this->createForm('add');
        $model = $this->getModel();

        $form->bind($model);

        $viewModel->form = $form;

        if ($this->getRequest()->isPost() === true) {
            $form->setData($this->getRequest()->getPost());

            if ($form->isValid() === false) {
                $viewModel->formMessages = $form->getMessages();
                return $viewModel;
            }

            try {
                $this->getMapper()->insert($model);
                $viewModel->successMessage = $this->successMessages['add'];
            } catch (\Exception $e) {
                $viewModel->failedMessage = $e->getMessage();
            }
        }



        return $viewModel;
    }

    public function detailsAction()
    {
        $viewModel = $this->createViewModel();

        $id = $this->getEvent()->getRouteMatch()->getParam('id');

        $viewModel->model = $this->getMapper()->find($id);

        return $viewModel;
    }

    public function editAction()
    {
        return new ViewModel();
    }

    public function deleteAction()
    {
        return new ViewModel();
    }




    /**
     * Crée une table à partir des méta données SQL
     * et ajoute la colonne des liens
     *
     * @return \Vpw\Table\Table
     */
    protected function createListTable()
    {
        $factory = new \Vpw\Table\Factory();
        $table =  $factory->createTable($this->getMapper()->getMetadata());

        $colLinks = new Column('links');
        $colLinks->setTemplate('backoffice-table-row-links');

        $table->addColumn($colLinks);

        return $table;
    }


    protected function getListTableRows()
    {
        $row = array();

        $routeMatch = $this->getEvent()->getRouteMatch();
        $params = $routeMatch->getParams();

        foreach ($this->getMapper()->findAll() as $model) {
            $row = $model->getArrayCopy();
            $row['links'] = array(
                'details_link' => $this->url()->fromRoute(
                    $routeMatch->getMatchedRouteName,
                    array(
                        'controller' => $params['__CONTROLLER__'],
                        'action' => 'details',
                        'id' => $model->getIdentityKey()
                    )
                )
            );

            $rows[] = $row;
        }

        return $rows;
    }


    /**
     * Crée un formulaire à partir des méta données du mapper.
     * @return \Zend\Form\Form
     */
    protected function createForm($type)
    {
        $specFactory = new SpecFactory($this->getMapper()->getMetadata());

        $factory = new \Zend\Form\Factory();
        return $factory->createForm($specFactory->getFormSpec($type));
    }

    /**
     * Get the mapper used to interact with a specific storage
     * @return \Vpw\Mvc\Controller\DbMapper
     */
    protected function getMapper()
    {
        if ($this->mapper == null) {
            $this->mapper = $this->createMapper($this->getDbAdapter());
        }

        return $this->mapper;
    }

    protected function getDbAdapter()
    {
        return $this->getServiceLocator()->get('Db');
    }

    /**
     *
     * @param Adapter $adapter
     * @return DbMapper
     */
    abstract protected function createMapper(Adapter $adapter);


    /**
     * Get the Model Object
     * @return \Vpw\Dal\ModelObject
     */
    protected function getModel()
    {
        if ($this->model == null) {
            $this->model = $this->mapper->createModelObject();
        }

        return $this->model;
    }

}
