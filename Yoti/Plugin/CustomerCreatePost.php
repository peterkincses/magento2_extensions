<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use Closure;
use Magento\Customer\Controller\Account\CreatePost;

class CustomerCreatePost
{
    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    public function __construct(
        YotiSession $yotiSession,
        YotiHelper $yotiHelper
    ) {
        $this->yotiSession = $yotiSession;
        $this->yotiHelper = $yotiHelper;
    }

    /**
     * @return mixed
     */
    public function aroundExecute(CreatePost $createPost, Closure $proceed)
    {
        $result = $proceed();
        if (!$this->yotiHelper->isEnabled()) {
            return $result;
        }

        if ($this->yotiHelper->isDoubleOptIn()) {
            return $result;
        }

        /**
         * check if we have customer id in yoti session
         * that means the customer account was created
         */
        $customerId = $this->yotiSession->getData('customer_id');
        if ($customerId) {
            $params['token'] = $this->yotiHelper->getHashedCustomerId($customerId);
            $result->setPath('verification/customer/age', $params);
        }
        return $result;
    }
}
