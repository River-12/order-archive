<?php
namespace Riverstone\OrderArchive\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ArchiveOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
   /**
    * Intialization
    *
    * @return void
    */
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }
}
