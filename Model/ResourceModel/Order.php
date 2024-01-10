<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Riverstone\OrderArchive\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\ResourceModel\Order\Handler\State as StateHandler;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class Order extends \Magento\Sales\Model\ResourceModel\Order
{
    /**
     * @var string
     */
    protected $eventPrefix = 'sales_order_resource';

    /**
     * @var string
     */
    protected $eventObject = 'resource';

    /**
     * @var StateHandler
     */
    protected $stateHandler;

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }

    /**
     * Save action
     *
     * @param AbstractModel $object
     * @return \Magento\Sales\Model\ResourceModel\Order|Order
     * @throws AlreadyExistsException
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->stateHandler->check($object);
        return parent::save($object);
    }
}
