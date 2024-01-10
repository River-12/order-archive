<?php
namespace Riverstone\OrderArchive\Controller\Customer;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * Execute action for my archive orders page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
