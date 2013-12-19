<?php

namespace Vpw\Mvc\Controller;

use Zend\Form\Form;

use Vpw\Dal\ModelObject;

use Zend\View\Model\ViewModel;

use Zend\Mvc\Controller\AbstractActionController;
use Vpw\Form\Fieldset\MetadataBasedFieldset;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Paginator\Paginator;
use Vpw\Dal\ModelCollection;

abstract class AbstractBackofficeController extends AbstractActionController
{
    /**
     *
     * @var UnderscoreToCamelCase
     */
    private static $filter;

    /**
     * Lazy load
     * @return \Zend\Filter\Word\UnderscoreToCamelCase
     */
    private static function getFilter()
    {
        if (self::$filter === null) {
            self::$filter = new UnderscoreToCamelCase();
        }

        return self::$filter;
    }

    protected $successMessages = array(
        'add' => "L'objet a bien été inséré en base de données.",
        'edit' => "L'objet a bien été modifié en base de données.",
        'delete' => "L'objet a bien été supprimé de la base de données.",
    );

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function createViewModel()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $viewModel;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $options = $this->getCollectionOptions();

        $collection = $this->findCollection($options);
        $paginator = new Paginator(new \Zend\Paginator\Adapter\Null($collection->getTotalNbRows()));
        $paginator->setItemCountPerPage($options['limit']);
        $paginator->setCurrentPageNumber($options['page']);

        $viewModel = $this->createViewModel();
        $viewModel->setVariable($this->getCollectionName(), $collection);
        $viewModel->setVariable('collectionOptions', $options);
        $viewModel->setVariable('paginator', $paginator);

        return $viewModel;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        $viewModel = $this->createViewModel();
        $viewModel->setVariable($this->getModelName(), $this->getModelObject());

        return $viewModel;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        return $this->editAction();
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $viewModel = $this->createViewModel();
        $model     = $this->getModelObject();
        $form      = $this->createEditForm();

        $viewModel->setVariable('form', $form);
        $viewModel->setVariable($this->getModelName(), $model);

        $form->bind($model);

        if ($this->getRequest()->isPost() === true) {

            $form->setData($this->getFormData());

            if ($form->isValid() === false) {
                $viewModel->formMessages = $form->getMessages();
                return $viewModel;
            }

            $this->populateModelWithEditForm($form, $model);

            try {
                if ($model->isLoaded() === false) {
                    $this->insertModelObject($model);
                } else {
                    $this->updateModelObject($model);
                }
                $viewModel->successMessage = $this->getSuccessMessage();
            } catch (\Exception $e) {
                echo '<pre>', $e, '</pre>';
                $viewModel->failedMessage = $e->getMessage();
            }
        }

        return $viewModel;
    }

    protected function insertModelObject(ModelObject $model)
    {
        $this->getMapper()->insert($model);
    }

    protected function updateModelObject(ModelObject $model)
    {
        $this->getMapper()->update($model);
    }

    protected function getSuccessMessage()
    {
        return $this->successMessages[$this->getEvent()->getRouteMatch()->getParam('action')];
    }

    protected function getFormData()
    {
        return $this->getRequest()->getPost($this->getFormName());
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $viewModel = $this->createViewModel();
        $viewModel->setVariable($this->getModelName(), $this->getModelObject());

        if ($this->getRequest()->isPost() === true) {
            if ($this->getRequest()->getPost('confirm') !== null) {
                try {
                    //$this->getMapper()->delete();
                    $viewModel->successMessage = $this->successMessages['delete'];
                } catch (\Exception $e) {
                    $viewModel->failedMessage = $e->getMessage();
                }
            }
        }

        return $viewModel;
    }

    /**
     * Crée un formulaire à partir des méta données du mapper.
     * @return \Zend\Form\Form
     */
    protected function createEditForm()
    {
        $form = new Form($this->getFormName());
        $form->setWrapElements(true);

        $form->setAttribute('method', 'post');
        $form->setAttribute('class', 'form form-horizontal');
        $form->add($this->getEditFieldset(), array('name' => 'data'));

        return $form;
    }

    protected function getFormName()
    {
        return $this->getMapper()->getTableName();
    }

    /**
     *
     * @param Form        $form
     * @param ModelObject $model
     */
    protected function populateModelWithEditForm(Form $form, ModelObject $model)
    {

    }

    /**
     *
     * @return \Vpw\Form\Fieldset\MetadataBasedFieldset
     */
    protected function getEditFieldset()
    {
        $fieldset = new MetadataBasedFieldset($this->getMapper()->getMetadata());
        $fieldset->setUseAsBaseFieldset(true);

        return $fieldset;
    }

    /**
     * Get the mapper used to interact with a specific storage
     * @return \Vpw\Dal\Mapper\MapperInterface
     */
    abstract protected function getMapper();

    /**
     *
     * @return string
     */
    protected function getModelName()
    {
        $name = self::getFilter()->filter($this->getMapper()->getTableName());
        $name[0] = strToLower($name[0]);

        return $name;
    }

    /**
     *
     * @return string
     */
    protected function getCollectionName()
    {
        return $this->getModelName() . 's';
    }

    /**
     * @return \Vpw\Dal\ModelObject
     */
    protected function getModelObject()
    {
        $key = $this->getModelObjectKey();

        if ($key === null) {
            return $this->createModelObject();
        }

        return $this->findModelObject($key);
    }

    /**
     * Retourne une collection d'object paginé (utilsé principalement dans la méthode indexAction)
     * @return ModelCollection
     */
    protected function findCollection($options)
    {
        return $this->getMapper()->findAll(null, $options);
    }

    protected function getCollectionOptions()
    {
        $request = $this->getEvent()->getRequest();

        $options = array(
            'limit' => $request->getQuery('limit', 30),
            'page' => $request->getQuery('page', 1),
            'order' => $request->getQuery('order', null),
        );

        $options['offset'] = $options['limit'] * ($options['page'] - 1);

        return $options;
    }


    /**
     * @return string
     */
    protected function getModelObjectKey()
    {
        return $this->getEvent()->getRouteMatch()->getParam('id', null);
    }

    /**
     * @return \Vpw\Dal\ModelObject
     */
    protected function createModelObject()
    {
        return $this->getMapper()->createModelObject();
    }

    /**
     * @return \Vpw\Dal\ModelObject
     */
    protected function findModelObject($key)
    {
        return $this->getMapper()->find($key);
    }
}
