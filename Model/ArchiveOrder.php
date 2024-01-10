<?php
namespace Riverstone\OrderArchive\Model;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ArchiveOrder extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ArchiveOrder::class);
    }
}
