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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Force extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Archive
     */
    protected $archive;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    // Define system config paths
    private const XML_PATH_NOTIFY_AFTER_ARCHIVING = 'order_archive/email_notification/notify_after_archiving';
    private const XML_PATH_SEND_TO_EMAIL = 'order_archive/email_notification/send_to_email';
    private const XML_PATH_STORE_EMAIL_IDENTITY = 'trans_email/ident_general/email';
    private const XML_PATH_STORE_NAME_IDENTITY = 'trans_email/ident_general/name';

    /**
     * Constructor
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Archive $archive
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory,
        Archive $archive,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->archive = $archive;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
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
            
            // Check if email notification is enabled
            $notifyAfterArchiving = $this->scopeConfig->getValue(
                self::XML_PATH_NOTIFY_AFTER_ARCHIVING,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($notifyAfterArchiving) {
                $sendToEmail = $this->scopeConfig->getValue(
                    self::XML_PATH_SEND_TO_EMAIL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                if ($sendToEmail) {
                    // Prepare order list and send notification
                    $orderList = $this->prepareOrderList($collection);
                    $this->sendEmailNotification($sendToEmail, $orderList);
                }
                if (!$sendToEmail) {
                    $this->messageManager->addErrorMessage(__('No email address configured.'));
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return $result->setData(['message' => $message]);
    }

    /**
     * Prepare the list of orders for the email notification
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
     * @param string $sendToEmail
     * @param string $orderList
     * @throws LocalizedException
     */
    protected function sendEmailNotification(string $sendToEmail, string $orderList): void
    {
        try {
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

            if (empty(trim($orderList))) {
                return;
            }

            $templateVars = [
                'order_list' => $orderList,
                'content' => 'The following orders have been archived:'
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_archive_notification_template') // Assume you have a template set
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

        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Failed to send email notification: %1', $e->getMessage())
            );
        }
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
