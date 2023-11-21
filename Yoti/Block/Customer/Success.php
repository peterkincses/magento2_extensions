<?php

declare(strict_types=1);

namespace BAT\Yoti\Block\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Success extends Template
{

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var bool
     */
    protected $yotiVerifiedStatus;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        CustomerSession $customerSession,
        array $data = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isYotiVerified(): bool
    {
        if (isset($this->yotiVerifiedStatus)) {
            return $this->yotiVerifiedStatus;
        }
        $this->yotiVerifiedStatus = false;
        $status = (int) $this->dataPersistor->get('yoti_verified_status');
        if ($status == 1) {
            $this->yotiVerifiedStatus = true;
        }

        return $this->yotiVerifiedStatus;
    }

    public function getCustomerFirstName(): string
    {
        $result = '';
        if ($this->customerSession->isLoggedIn()) {
            $result = $this->customerSession->getCustomer()->getFirstname();
        }

        return $result;
    }
}
