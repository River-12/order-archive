<?php

namespace Riverstone\OrderArchive\Model\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Riverstone\OrderArchive\Model\Config\Source\ScheduleMode;
use Magento\Framework\Exception\LocalizedException;

class CronConfig extends \Magento\Framework\App\Config\Value
{
    public const CRON_STRING_PATH = 'crontab/default/jobs/river_orderarchive/schedule/cron_expr';
    public const CRON_MODEL_PATH = 'crontab/default/jobs/river_orderarchive/run/model';
    public const CRON_DAILY           = 'D';
    public const CRON_WEEKLY          = 'W';
    public const CRON_MONTHLY         = 'M';

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var mixed|string
     */
    protected $runModelPath = '';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param mixed|string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath       = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * After save execution function
     *
     * @return CronConfig
     * @throws \Exception
     */
    public function afterSave()
    {
        $enabled = $this->getData('groups/general/fields/enable/value');
        if ($enabled) {
            $frequency = $this->getData('groups/schedule/fields/schedule_for/value');
            $time = $this->getData('groups/schedule/fields/time/value');
            $cronExprArray = [
                (int)($time[1]),
                (int)($time[0]),
                $frequency == ScheduleMode::CRON_MONTHLY ? '1' : '*',
                '*',
                $frequency == ScheduleMode::CRON_WEEKLY ? '1' : '*',
            ];

            $cronExprString = join(' ', $cronExprArray);

            try {
                $this->configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString
                )->setPath(
                    self::CRON_STRING_PATH
                )->save();
                $this->configValueFactory->create()->load(
                    self::CRON_MODEL_PATH,
                    'path'
                )->setValue(
                    $this->runModelPath
                )->setPath(
                    self::CRON_MODEL_PATH
                )->save();
            } catch (LocalizedException $e) {
                throw new LocalizedException(__('We can\'t save the cron expression.'));

            }
        }

        return parent::afterSave();
    }
}
