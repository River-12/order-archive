<?php

namespace Riverstone\OrderArchive\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Riverstone\OrderArchive\Model\OrderArchive\Archive;
use Psr\Log\LoggerInterface;
use Riverstone\OrderArchive\Helper\Data;

class RiverArchive
{
    protected $collectionFactory;
    protected $resultJsonFactory;
    protected $archive;
    protected $logger;
    protected $helperData;
    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $resultJsonFactory
     * @param Archive $archive
     * @param LoggerInterface $logger
     * @param Data $helperData
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        JsonFactory $resultJsonFactory,
        Archive $archive,
        LoggerInterface $logger,
        Data  $helperData
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->archive = $archive;
        $this->logger = $logger;
        $this->helperData = $helperData;
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
            return $this->archive->archive($collection);
        }
        return true;
    }
}
