<?php

namespace Riverstone\OrderArchive\Controller\Adminhtml\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Magento\Backend\App\Action\Context;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Force extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    protected $collectionFactory;
    protected $resultJsonFactory;
    protected $archive;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Archive $archive
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory,
        Archive $archive
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->archive = $archive;
    }

    /**
     * Execute function for force archive action in the system configuration
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        try {
            $collection = $this->collectionFactory->create()->addAttributeToSelect('*');
            $message = $this->archive->archive($collection);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return $result->setData(['message' => $message]);
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
