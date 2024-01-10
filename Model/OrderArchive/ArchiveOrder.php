<?php
namespace Riverstone\OrderArchive\Model\OrderArchive;

/**
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class ArchiveOrder extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Intialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ArchiveOrder::class);
    }
}
