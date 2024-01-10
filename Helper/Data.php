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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

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
}
