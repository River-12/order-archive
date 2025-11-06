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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RiverUnarchive extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::orderarchive';

    /** System config paths */
    public const XML_PATH_NOTIFY_AFTER_UNARCHIVING = 'order_archive/email_notification_unarchiving/notify_after_unarchiving';
    public const XML_PATH_SEND_TO_EMAIL_UNARCHIVING = 'order_archive/email_notification_unarchiving/send_to_email_unarchiving';
    private const XML_PATH_STORE_EMAIL_IDENTITY = 'trans_email/ident_general/email';
    private const XML_PATH_STORE_NAME_IDENTITY = 'trans_email/ident_general/name';

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Archive $archive
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data  $helperData,
        Archive $archive,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->archive = $archive;
        $this->scopeConfig = $scopeConfig;
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

             $notifyAfterArchiving = $this->scopeConfig->getValue(
                 self::XML_PATH_NOTIFY_AFTER_UNARCHIVING,
                 ScopeInterface::SCOPE_STORE
             );

            if ($notifyAfterArchiving) {
                $sendToEmail = $this->scopeConfig->getValue(
                    self::XML_PATH_SEND_TO_EMAIL_UNARCHIVING,
                    ScopeInterface::SCOPE_STORE
                );

                if ($sendToEmail) {
                    $content = 'The following orders have been unarchived:';

                    // Delegate to helper
                    $this->helperData->notifyOrderArchive($collection, $sendToEmail, $content);
                }
                if (!$sendToEmail) {
                    $this->messageManager->addErrorMessage(__('No email address configured.'));
                }
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('orderarchive/archivegrid/');
    }
}
