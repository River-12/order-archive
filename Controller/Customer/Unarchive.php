<?php
namespace Riverstone\OrderArchive\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Riverstone\OrderArchive\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Unarchive extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderCollectionFactory
     */
    protected $order;

    /**
     * @var Archive
     */
    protected $archive;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Path to notify after unarchiving config
     */
    public const XML_PATH_NOTIFY_AFTER_UNARCHIVING = 'order_archive/email_notification_unarchiving/notify_after_unarchiving';

    /**
     * Path to send to email unarchiving config
     */
    public const XML_PATH_SEND_TO_EMAIL_UNARCHIVING = 'order_archive/email_notification_unarchiving/send_to_email_unarchiving';
    
    /**
     * Unarchive constructor.
     *
     * @param Context $context
     * @param OrderCollectionFactory $order
     * @param Archive $archive
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        OrderCollectionFactory $order,
        Archive $archive,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->order = $order;
        $this->archive = $archive;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
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
            //$orderData = $this->order->get($orderId);
            $orderCollection = $this->order->create();
            $orderCollection->addFieldToFilter('entity_id', $orderId);
            $message = $this->archive->unarchive($orderCollection);
            $this->sendNotification($orderCollection); // Send email after unarchiving
            $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('sales/order/history');
    }

    /**
     * Send an email notification after unarchiving orders
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @return void
     */
    protected function sendNotification($collection)
    {
        $notifyUnarchiving = $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_AFTER_UNARCHIVING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($notifyUnarchiving) {
            $sendToEmail = $this->scopeConfig->getValue(
                self::XML_PATH_SEND_TO_EMAIL_UNARCHIVING,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($sendToEmail) {
                // Delegate to helper method to send notification email
                $this->helperData->notifyOrderUnArchive($collection, $sendToEmail);
            }
            if (!$sendToEmail) {
                $this->messageManager->addErrorMessage(__('No email address configured.'));
            }
        }
    }
}
