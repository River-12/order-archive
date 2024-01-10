<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Riverstone\OrderArchive\Block\Archiveorder;

use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Riverstone\OrderArchive\Helper\Data;
use Magento\Sales\Helper\Reorder;
use Magento\Framework\Data\Helper\PostHelper;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class History extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Reorder $reorder
     * @param PostHelper $postHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        Data  $helperData,
        Reorder $reorder,
        PostHelper $postHelper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->postHelper = $postHelper;
        $this->reorder = $reorder;
    }

    /**
     * Function to get the orders
     *
     * @return false|Collection
     */
    public function getOrders()
    {

        $customerId = $this->customerSession->getData();

        if (!($customerId)) {
            return false;
        }
        $archStatusToSelect = $this->helperData->getArchiveOrderStatus();
        $archiveStatus = explode(',', $archStatusToSelect??"");
        $this->orders = $this->collectionFactory->create($customerId)->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'status',
            ['in' =>  $archiveStatus]
        )->addFieldToFilter(
            'river_order_archive',
            1
        )->setOrder(
            'created_at',
            'desc'
        );

        return $this->orders;
    }

    /**
     * To prepare the layout
     *
     * @return $this|History
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.order.history.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get order view URL
     *
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * Get order track URL
     *
     * @param object $order
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTrackUrl($order)
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    /**
     * Get unarchive URL
     *
     * @param object $order
     * @return string
     */
    public function getUnarchiveUrl($order)
    {
        return $this->getUrl('orderarchive/customer/unarchive', ['order_id' => $order->getId()]);
    }

    /**
     * Get customer account URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Get message for no orders.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getEmptyOrdersMessage()
    {
        return __('You have placed no archive orders.');
    }

    /**
     * Get the order item can be reordered
     *
     * @param int|string|mixed $orderId
     * @return bool
     */
    public function isReorder($orderId)
    {
        return $this->reorder->canReorder($orderId);
    }

    /**
     * Get the reorder url
     *
     * @param string|mixed $reOrderUrl
     * @return string
     */
    public function getPostHelper($reOrderUrl)
    {
        return $this->postHelper->getPostData($reOrderUrl);
    }
}
