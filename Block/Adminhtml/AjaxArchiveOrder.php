<?php

namespace Riverstone\OrderArchive\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class AjaxArchiveOrder extends \Magento\Config\Block\System\Config\Form\Field
{

    public const BUTTON_TEMPLATE = 'river/orderarchive/button.phtml';

    /**
     * Function to set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->getTemplate()) {
            $this->setTemplate(self::BUTTON_TEMPLATE);
        }
    }

    /**
     * Render function
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the ajax force url
     *
     * @return string
     */
    public function getAjaxArchiveForceUrl()
    {
        return $this->getUrl('orderarchive/ajax/force');
    }

    /**
     * To get element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'html_id' => $element->getHtmlId(),
            ]
        );

        return $this->_toHtml();
    }
}
