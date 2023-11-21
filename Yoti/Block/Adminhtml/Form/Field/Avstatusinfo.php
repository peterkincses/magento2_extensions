<?php

declare(strict_types=1);

namespace BAT\Yoti\Block\Adminhtml\Form\Field;

use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use Magento\Framework\view\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class Avstatusinfo extends Select
{

    /**
     * @var YotiApprovalAttributeOptions
     */
    protected $YotiOptions;

    /**
     * constructor.
     */
    public function __construct(Context $context, YotiApprovalAttributeOptions $YotiOptions)
    {
        $this->YotiOptions = $YotiOptions;
        parent::__construct($context);
    }

    /**
     * Set "name" for <select> element
     * @return $this
     */
    public function setInputName(string $value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     * @return $this
     */
    public function setInputId(string $value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }
    // phpcs:enable

    private function getSourceOptions(): array
    {
        return $this->YotiOptions->getAllOptions();
    }
}
