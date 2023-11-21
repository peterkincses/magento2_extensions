<?php

declare(strict_types=1);

namespace BAT\Yoti\Block\Adminhtml;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Datepicker extends Field
{
    /**
     * @inheritDoc
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $html .= '<button type="button" style="display:none;" class="ui-datepicker-trigger '
            . 'v-middle" id="ui-datepicker-trigger-' . $element->getHtmlId() . '"><span>Select Date</span></button>';
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui"], function (jq) {
                jq(document).ready(function () {
                    jq("#' . $element->getHtmlId() . '").datepicker({dateFormat: "dd-mm-yy"});
                    jq(".ui-datepicker-trigger").removeAttr("style");
                    jq("#ui-datepicker-trigger-' . $element->getHtmlId() . '").click(function(){
                        jq("#' . $element->getHtmlId() . '").focus();
                    });
                });
            });
            </script>';
        return $html;
    }
}
