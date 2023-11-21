<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Checkout;

use Magento\Framework\Exception\LocalizedException;
use BAT\DevicePersonalisation\Model\PsnRestrictedWords\InputCheck;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use Magento\Store\Model\StoreManagerInterface;

class Cart
{
    /**
     * @var InputCheck
     */
    protected $inputCheckValidation;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        InputCheck $inputCheckValidation,
        PersonalisationHelper $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->inputCheckValidation = $inputCheckValidation;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        if (
            isset($requestInfo['is_product_personalised']) &&
            !empty($requestInfo['is_product_personalised']) &&
            $this->dataHelper->isEnabled()
        ) {
            $storeId = $this->storeManager->getStore()->getId();
            if (!$this->inputCheckValidation->isValid($requestInfo['personalisation_back_text'], (int) $storeId)) {
                throw new LocalizedException(__("Sorry, this is a restricted word we would prefer you don't use."));
            }
        }

        return [$productInfo, $requestInfo];
    }
}
