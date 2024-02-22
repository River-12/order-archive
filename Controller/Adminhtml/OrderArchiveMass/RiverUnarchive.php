<?php

namespace Riverstone\OrderArchive\Controller\Adminhtml\OrderArchiveMass;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Riverstone\OrderArchive\Helper\Data;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Magento\Framework\Controller\ResultFactory;

class RiverUnarchive extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    protected $filter;
    protected $collectionFactory;
    protected $helperData;
    protected $archive;
  /**
   * Authorization level of a basic admin session
   */
    public const ADMIN_RESOURCE = 'Magento_Sales::orderarchive';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Archive $archive
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data  $helperData,
        Archive $archive
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->archive = $archive;
    }

    /**
     * Execute function for mass archive action in the grid
     *
     * @return ResponseInterface|Redirect|(Redirect&ResultInterface)|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $message = $this->archive->unarchive($collection);
            $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('orderarchive/archivegrid/');
    }
}
