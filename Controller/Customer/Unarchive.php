<?php
namespace Riverstone\OrderArchive\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Controller\OrderInterface;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;

class Unarchive extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param Archive $archive
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Riverstone\OrderArchive\Model\OrderArchive\Archive $archive
    ) {
        $this->order = $order;
        $this->archive = $archive;
        parent::__construct($context);
    }
    /**
     * Execute action for my archive orders page
     *
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $orderData = $this->order->load($orderId);
            $collection = $orderData->getCollection()->addFieldToFilter('entity_id', $orderId);
            $message = $this->archive->unarchive($collection);
            $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('sales/order/history');
    }
}
