<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 11 avr. 2013
 * Encoding : UTF-8
 */
namespace Vpw\Form\View\Helper;

use Zend\Form\View\Helper\Form as FormHelper;
use Zend\Form\FormInterface;
use Zend\Form\FieldsetInterface;

class Form extends FormHelper
{

    /**
     * (non-PHPdoc)
     * @see \Zend\Form\View\Helper\Form::render()
     */
    public function render(FormInterface $form)
    {
        return $this->openTag($form) .
            $this->renderContent($form) .
            $this->closeTag();
    }

    public function renderContent(FormInterface $form)
    {
        if (method_exists($form, 'prepare')) {
            $form->prepare();
        }

        $formContent = '';

        foreach ($form as $element) {
            if ($element instanceof FieldsetInterface) {
                $formContent.= $this->getView()->formCollection($element);
            } else {
                $formContent.= $this->getView()->formRow($element);
            }
        }

        return $formContent;
    }

}