<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Riverstone\OrderArchive\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Get the archive order status
     *
     * @param string $scope
     * @return mixed
     */
    public function getArchiveOrderStatus($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'order_archive/general/archive_order_status',
            $scope
        );
    }

    /**
     * Get the archive days
     *
     * @param string $scope
     * @return mixed
     */
    public function getArchiveDays($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'order_archive/general/orders_older_than',
            $scope
        );
    }

    /**
     * Get the extension status
     *
     * @param string $scope
     * @return mixed
     */
    public function getExtensionStatus($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'order_archive/general/enable',
            $scope
        );
    }

    /**
     * Notify after archiving orders.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param string $sendToEmail
     * @param string $content
     * @return void
     */
    public function notifyOrderArchive($collection, $sendToEmail, $content)
    {
        $orderList = $this->prepareOrderList($collection);
        $this->sendEmailNotification($sendToEmail, $orderList, $content);
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
     * @param string $sendToEmail
     * @param string $orderList
     * @param string $content
     * @throws LocalizedException
     * @return void
     */
    protected function sendEmailNotification(string $sendToEmail, string $orderList, $content): void
    {
        try {

            if (empty(trim($orderList))) {
                return;
            }

            $storeId = $this->storeManager->getStore()->getId();
            $senderEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $senderName = $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $templateVars = [
                'order_list' => $orderList,
                'content' => $content
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_archive_notification_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
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
     * Notify user after unarchiving orders.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $collection
     * @param string $sendToEmail
     * @return void
     */
    public function notifyOrderUnArchive($collection, $sendToEmail)
    {
        $orderList = $this->prepareOrderList($collection);
        $this->sendEmailNotificationUnarchive($sendToEmail, $orderList);
    }

    /**
     * Send email notification after unarchiving orders.
     *
     * @param string $sendToEmail
     * @param string $orderList
     * @throws LocalizedException
     * @return void
     */
    protected function sendEmailNotificationUnarchive(string $sendToEmail, string $orderList): void
    {
        try {
            if (empty(trim($orderList))) {
                return; // Do not send email if there are no archived orders
            }

            $storeId = $this->storeManager->getStore()->getId();
            $senderEmail = $this->scopeConfig->getValue(
                'trans_email/ident_general/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $senderName = $this->scopeConfig->getValue(
                'trans_email/ident_general/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $templateVars = [
                'order_list' => $orderList,
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('order_unarchive_notification_template')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
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
}
