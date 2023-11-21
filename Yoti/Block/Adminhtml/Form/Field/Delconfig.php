<?php

declare(strict_types=1);

namespace BAT\Yoti\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Delconfig
 */
class Delconfig extends AbstractFieldArray
{
    /**
     * @var Avstatusinfo
     */
    private $AVStatusRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('AV_Status', [
            'label' => __('Yoti AV Status'),
            'renderer' => $this->getAVStatusRenderer(),
        ]);
        $this->addColumn('month', ['label' => __('Months'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add More');
    }
    // phpcs:enable

    /**
     * Prepare existing row data object
     * @throws LocalizedException
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $Avstatus = $row->getAvstatus();
        if ($Avstatus !== null) {
            $options['option_' . $this->getAVStatusRenderer()->calcOptionHash($Avstatus)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }
    // phpcs:enable

    /**
     * @throws LocalizedException
     */
    private function getAVStatusRenderer(): Avstatusinfo
    {
        if (!$this->AVStatusRenderer) {
            $this->AVStatusRenderer = $this->getLayout()->createBlock(
                Avstatusinfo::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->AVStatusRenderer;
    }
}
