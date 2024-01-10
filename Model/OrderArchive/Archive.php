<?php

namespace Riverstone\OrderArchive\Model\OrderArchive;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Riverstone\OrderArchive\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Archive
{
    /**
     * Constructor
     *
     * @param Data $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data  $helperData,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * Archive function
     *
     * @param AbstractCollection $collection
     * @return string
     */
    public function archive(AbstractCollection $collection)
    {
        try {
            $archStatusToSelect = $this->helperData->getArchiveOrderStatus();
            $archiveDays = $this->helperData->getArchiveDays();
            $days = "-".$archiveDays." day";
            $currentDate = date("Y-m-d h:i:s"); // current date
            $archiveDate = strtotime($days, strtotime($currentDate));
            $customDate = date('Y-m-d h:i:s', $archiveDate);
            $archiveStatus = explode(',', $archStatusToSelect??"");
            $collection->addFieldToFilter('created_at', ['lteq' =>  $customDate])
                        ->addFieldToFilter('status', ['in'=> $archiveStatus ])
                        ->addFieldToFilter('river_order_archive', 0)
                        ->addFieldToFilter('river_order_unarchive', 0);
            $incrementer = 0;
            foreach ($collection as $orderItem) {
                   $orderItem->setData('river_order_archive', 1);
                   $orderItem->save();
                    ++$incrementer;
            }

            if ($incrementer) {
                $message = "A total of".$incrementer." record(s) were archived.";
                $this->logger->info('A total of'. $incrementer .'record(s) were archived.');
            } elseif ($incrementer === 0) {
                $message = "No orders were archived";
                $this->logger->info($message);
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->info($message);
        }
        return $message;
    }

    /**
     * MassArchive function
     *
     * @param AbstractCollection $collection
     * @param AbstractCollection $incrementCollect
     * @return string
     */
    public function massArchive(AbstractCollection $collection, AbstractCollection $incrementCollect)
    {
        try {
            $archStatusToSelect = $this->helperData->getArchiveOrderStatus();
            $archiveDays = $this->helperData->getArchiveDays();
            $days = "-".$archiveDays." day";
            $currentDate = date("Y-m-d h:i:s"); // current date
            $archiveDate = strtotime($days, strtotime($currentDate));
            $customDate = date('Y-m-d h:i:s', $archiveDate);
            $archiveStatus = explode(',', $archStatusToSelect??"");
            $itemId = [];
            $archiveCollection = $incrementCollect;
            foreach ($archiveCollection as $item) {
                  $itemId[] = $item->getIncrementId();
            }
            $archive = $collection->addFieldToFilter('created_at', ['lteq' =>  $customDate])
                        ->addFieldToFilter('status', ['in'=> $archiveStatus ])
                        ->addFieldToFilter('river_order_archive', 0)
                        ->addFieldToFilter('river_order_unarchive', 0);

            $incrementer = 0;
            foreach ($archive as $orderItem) {
                   $orderId = $orderItem->getIncrementId();
                   $orderItem->setData('river_order_archive', 1);
                   $orderItem->save();
                    ++$incrementer;
                $key = array_search($orderId, $itemId);
                if ($key !== false) {
                    unset($itemId[$key]);
                }

            }

            $message = '';
            if ($incrementer) {
                $message .= "A total of".$incrementer." record(s) were archived.";
                $this->logger->info('A total of'. $incrementer .'record(s) were archived.');
            } elseif ($incrementer === 0) {
                $message .= "No orders were archived.";
                $this->logger->info($message);
            }

            if ($itemId) {
                $errorItems = implode(",", $itemId);
                $message .= "These order id(s)".$errorItems." cannot be archived.";
                $this->logger->info($message);
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->info($message);
        }
        return $message;
    }

    /**
     * Unarchive function
     *
     * @param AbstractCollection $collection
     * @return string
     */
    public function unarchive(AbstractCollection $collection)
    {
        try {
            $incrementer = 0;
            foreach ($collection as $orderItem) {
                   $orderItem->setData('river_order_unarchive', 1);
                   $orderItem->setData('river_order_archive', 0);
                   $orderItem->save();
                    ++$incrementer;
            }

            if ($incrementer) {
                $message = "A total of".$incrementer." record(s) were unarchived.";
                $this->logger->info('A total of'. $incrementer .'record(s) were unarchived.');
            } elseif ($incrementer === 0) {
                $message = "No orders were unarchived";
                $this->logger->info($message);
            }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->logger->info($message);
        }
        return $message;
    }
}
