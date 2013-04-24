<?php
/**
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Form\View\Helper;

use Zend\Form\View\Helper\FormRow as ZendFormRow;
use Zend\Form\ElementInterface;


class FormRow extends ZendFormRow
{

    protected $elementDescriptionHelper;

    /**
     * @var string
     */
    protected $inputErrorClass = 'error';

    /**
     * @var string
     */
    protected $groupWrapper = '<div class="control-group%s" id="control-group-%s">%s%s</div>';

    /**
     * @var string
     */
    protected $controlWrapper = '<div class="controls" id="controls-%s">%s%s%s</div>';


    protected function getElementDescriptionHelper()
    {
        if ($this->elementDescriptionHelper) {
            return $this->elementDescriptionHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->elementDescriptionHelper = $this->view->plugin('form_element_description');
        }

        return $this->elementDescriptionHelper;
    }


    public function render(ElementInterface $element)
    {
        $escapeHtmlHelper         = $this->getEscapeHtmlHelper();
        $labelHelper              = $this->getLabelHelper();
        $elementHelper            = $this->getElementHelper();
        $elementErrorsHelper      = $this->getElementErrorsHelper();
        $elementDescriptionHelper = $this->getElementDescriptionHelper();

        $elementErrorsHelper->setAttributes(array('class'=> 'help-block'));

        $label              = $element->getLabel();
        $inputErrorClass    = $this->getInputErrorClass();
        $elementErrors      = $elementErrorsHelper->render($element);
        $elementDescription = $elementDescriptionHelper->render($element);

        if ($element->hasAttribute('id') === false) {
            $element->setAttribute('id', str_replace(array('[', ']'), array('-', ''), $element->getAttribute('name')));
        }

        $elementString = $elementHelper->render($element);

        $type = $element->getAttribute('type');
        if ($type === 'hidden') {
            return $elementString;
        }

        $id = $element->getAttribute('id');
        $addtClass = (!empty($elementErrors) && !empty($inputErrorClass)) ? ' ' . $inputErrorClass : '';

        $labelMarkup = '';

        if (isset($label) && '' !== $label) {

            if (null !== ($translator = $labelHelper->getTranslator())) {
                $label = $translator->translate(
                        $label, $labelHelper->getTranslatorTextDomain()
                );
            }

            if ($type !== 'multi_checkbox' && $type !== 'radio') {
                $labelClass = 'control-label';

                $labelMarkup = $labelHelper->openTag(array(
                    'for' => $id,
                    'class' => $labelClass,
                ));


                // todo allow for not escaping the label
                $labelMarkup .= $escapeHtmlHelper($label);
                $labelMarkup .= $labelHelper->closeTag();
            }
        }

        $markup = sprintf($this->controlWrapper,
                $id,
                $elementString,
                $elementDescription,
                $elementErrors
        );

        $markup = sprintf($this->groupWrapper, $addtClass, $id, $labelMarkup, $markup);


        if ($type === 'multi_checkbox' || $type === 'radio') {
            $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $markup);
        }

        return $markup;
    }
}
