<?php

namespace Riverstone\OrderArchive\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Riverstone\OrderArchive\Helper\Data;

class MassAction extends \Magento\Ui\Component\MassAction
{
    protected $authorization;
    protected $helper;
    /**
     * @param ContextInterface $context
     * @param AuthorizationInterface $authorization
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Riverstone\OrderArchive\Helper\Data $helper,
        array $components,
        array $data
    ) {
        $this->authorization = $authorization;
        $this->helper = $helper;
        parent::__construct($context, $components, $data);
    }

    /**
     * Prepare function to filter massaction based on system config values
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $result = $this->helper->getExtensionStatus();
        if ($result == 0) {
            $config = $this->getConfiguration();
            $notAllowedActions = ['orderarchive'];
            $allowedActions = [];
            foreach ($config['actions'] as $action) {
                if (!in_array($action['type'], $notAllowedActions)) {
                    $allowedActions[] = $action;
                }
            }

            $config['actions'] = $allowedActions;
            $this->setData('config', (array)$config);
        }
    }
}
