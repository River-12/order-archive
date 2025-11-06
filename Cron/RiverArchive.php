<?php

namespace Riverstone\OrderArchive\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Psr\Log\LoggerInterface;
use Riverstone\OrderArchive\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\Notification\MessageInterface;

class RiverArchive
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Riverstone\OrderArchive\Model\OrderArchive\Archive
     */
    protected $archive;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Riverstone\OrderArchive\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    // System config paths
    private const XML_PATH_NOTIFY_AFTER_ARCHIVING = 'order_archive/email_notification/notify_after_archiving';
    private const XML_PATH_SEND_TO_EMAIL = 'order_archive/email_notification/send_to_email';
    private const XML_PATH_STORE_EMAIL_IDENTITY = 'trans_email/ident_general/email';
    private const XML_PATH_STORE_NAME_IDENTITY = 'trans_email/ident_general/name';

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Archive $archive
     * @param LoggerInterface $logger
     * @param Data $helperData
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory,
        Archive $archive,
        LoggerInterface $logger,
        Data $helperData,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->archive = $archive;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Execute the cron function
     *
     * @return string
     */
    public function execute()
    {
        $enabled = $this->helperData->getExtensionStatus();
        if ($enabled) {
            $collection = $this->collectionFactory->create()->addAttributeToSelect('*');
            $result = $this->archive->archive($collection);

            // Email notification functionality
            $this->sendEmailNotification($collection);

            return $result;
        }
        return true;
    }

    /**
     * Prepare the list of archived orders.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @return string
     */
    protected function prepareOrderList($collection): string
    {
        $orderList = '';
        foreach ($collection as $order) {
            $orderList .= "Order ID: " . $order->getIncrementId() . "\n";
        }
        return $orderList;
    }

    /**
     * Send email notification after archiving orders.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @throws LocalizedException
     */
    protected function sendEmailNotification($collection): void
    {
        // Check if notification is enabled in system config
        $notifyAfterArchiving = $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_AFTER_ARCHIVING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($notifyAfterArchiving) {
            // Get the configured email recipient
            $sendToEmail = $this->scopeConfig->getValue(
                self::XML_PATH_SEND_TO_EMAIL,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($sendToEmail) {
                // Prepare the list of archived orders
                $orderList = $this->prepareOrderList($collection);

                // Send email notification
                $this->sendEmail($sendToEmail, $orderList);
            }
            
            if (!$sendToEmail) {
                $this->logger->error('No email address configured for notifications.');
            }
        }
    }

    /**
     * Send the email with the list of archived orders.
     *
     * @param string $sendToEmail
     * @param string $orderList
     * @throws LocalizedException
     */
    protected function sendEmail(string $sendToEmail, string $orderList): void
    {
        try {

            if (empty(trim($orderList))) {
                return;
            }
            
            $storeId = $this->storeManager->getStore()->getId();

            // Get admin sender info from store config
            $senderEmail = $this->scopeConfig->getValue(
                self::XML_PATH_STORE_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $senderName = $this->scopeConfig->getValue(
                self::XML_PATH_STORE_NAME_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $templateVars = [
                'order_list' => $orderList,
            ];

            // Create transport to send email
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_archive_notification_template')  // Update template ID
                ->setTemplateOptions([
                    'area' => Area::AREA_ADMINHTML,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFrom([
                    'email' => $senderEmail,
                    'name'  => $senderName,
                ])
                ->addTo($sendToEmail)
                ->getTransport();

            $transport->sendMessage();

            $this->logger->info('Email notification sent successfully to: ' . $sendToEmail);

        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Failed to send email notification: %1', $e->getMessage())
            );
        }
    }
}
