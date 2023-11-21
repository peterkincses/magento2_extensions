<?php

declare(strict_types=1);

namespace BAT\Yoti\Cron;

use BAT\Yoti\Helper\Data;
use BAT\Yoti\Model\Config;
use BAT\Yoti\Service\AutomaticDeletion;
use Psr\Log\LoggerInterface;

class AccountDelete
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var AutomaticDeletion
     */
    private $deleteCustomer;

    /**
     * AccountDelete constructor.
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helper,
        Config $config,
        AutomaticDeletion $deleteCustomer
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->config = $config;
        $this->deleteCustomer = $deleteCustomer;
    }

    public function execute(): void
    {
        if (!empty($this->config->getDeleteAccountEnabledWebsiteList())) {
            $this->logger->info('Cronjob AccountDeletion started.');
            $this->deleteCustomer->execute($this->config->getDeleteAccountEnabledWebsiteList());
            $this->logger->info('Cronjob AccountDeletion is executed.');
        }
    }
}
