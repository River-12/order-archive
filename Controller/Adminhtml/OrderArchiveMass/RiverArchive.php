<?php

namespace Riverstone\OrderArchive\Controller\Adminhtml\OrderArchiveMass;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Riverstone\OrderArchive\Helper\Data;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class RiverArchive extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::orderarchive';

    /** System config paths */
    private const XML_PATH_NOTIFY_AFTER_ARCHIVING = 'order_archive/email_notification/notify_after_archiving';
    private const XML_PATH_SEND_TO_EMAIL = 'order_archive/email_notification/send_to_email';
    private const XML_PATH_STORE_EMAIL_IDENTITY = 'trans_email/ident_general/email';
    private const XML_PATH_STORE_NAME_IDENTITY = 'trans_email/ident_general/name';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Archive
     */
    protected $archive;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * RiverArchive constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Archive $archive
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helperData,
        Archive $archive,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->archive = $archive;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute mass archive and send notification if enabled.
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $incrementCollect = $this->filter->getCollection($this->collectionFactory->create());

            $message = $this->archive->massArchive($collection, $incrementCollect);
            $this->messageManager->addSuccessMessage($message);

            $notifyAfterArchiving = $this->scopeConfig->getValue(
                self::XML_PATH_NOTIFY_AFTER_ARCHIVING,
                ScopeInterface::SCOPE_STORE
            );

            if ($notifyAfterArchiving) {
                $sendToEmail = $this->scopeConfig->getValue(
                    self::XML_PATH_SEND_TO_EMAIL,
                    ScopeInterface::SCOPE_STORE
                );

                if ($sendToEmail) {
                    $content = 'The following orders have been archived:';
                    // Delegate to helper
                    $this->helperData->notifyOrderArchive($collection, $sendToEmail, $content);
                }
                if (!$sendToEmail) {
                    $this->messageManager->addErrorMessage(__('No email address configured.'));
                }
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('sales/order/');
    }
}
