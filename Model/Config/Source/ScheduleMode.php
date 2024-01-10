<?php

namespace Riverstone\OrderArchive\Model\Config\Source;

class ScheduleMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public const CRON_WEEKLY = 'W';
    public const CRON_MONTHLY = 'M';
    public const CRON_DAILY = 'D';

    /**
     * @var array
     */
    protected static $options;

    /**
     * To option array function
     *
     * @return array|array[]
     */
    public function toOptionArray()
    {
        if (!self::$options) {
            self::$options = [
            ['label' => __('Daily'), 'value' => self::CRON_DAILY],
            ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
            ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY]
            ];
        }
        return self::$options;
    }
}
