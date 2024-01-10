<?php
namespace Riverstone\OrderArchive\Controller\Adminhtml\ArchiveGrid;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute function for archive order grid
     *
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Riverstone_OrderArchive::manager');
            $resultPage->getConfig()->getTitle()->prepend(__('My Archive Orders'));
            return $resultPage;
    }

    /**
     * Is allowed function
     *
     * @return true
     */
    protected function _isAllowed()
    {
            return true;
    }
}
