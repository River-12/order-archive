<?php

namespace Riverstone\OrderArchive\Plugin;

use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class History
{

    /**
     * Constructor
     *
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param Config $orderConfig
     */
    public function __construct(
        Session $customerSession,
        CollectionFactory $collectionFactory,
        Config $orderConfig
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->orderConfig = $orderConfig;
    }

    /**
     * Function to display non archived orders
     *
     * @param \Magento\Sales\Block\Order\History $subject
     * @param array|mixed $result
     * @return false|Collection
     */
    public function aroundGetOrders(
        \Magento\Sales\Block\Order\History $subject,
        $result
    ) {
        $customerId = $this->customerSession->getData();

        if (!($customerId)) {
            return false;
        }
        $this->orders = $this->collectionFactory->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' =>  $this->orderConfig->getVisibleOnFrontStatuses()]
        )->addFieldToFilter(
            'river_order_archive',
            0
        )->setOrder(
            'created_at',
            'desc'
        );
        return $this->orders;
    }
}
