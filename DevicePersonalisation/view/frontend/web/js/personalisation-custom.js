define([
    'jquery',
    'mage/translate',
    'slick'
], function($) {
    'use strict';

    var pdpPersonalisationBlock = $('.pdp-personalisation-toggle-btn-container');

    $(document).ready(function () {

        if(pdpPersonalisationBlock.length > 0){
            $('#product-options-wrapper').insertBefore(pdpPersonalisationBlock);
            $('.pdp-personalisation-cancel-btn-block').insertBefore('.tabs-content');
        }

        $('.product-info-main .pdp-personalisation-cancel a').on('click', function(e) {
            e.preventDefault();
            $('#product-options-wrapper').insertBefore(pdpPersonalisationBlock);
        });

        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $('.pdp-personalisation-price').insertBefore('.pdp-personalisation-tabs-nav');
            $('.front-options .option').on('click', function() {
                if($(this).attr('data-target') === 'front-pattern-content') {
                    $('#front-pattern-content .personalisation-options').css('display','block');
                    setTimeout(function() {
                        $('#front-pattern-content.active .personalisation-options').slick({
                            infinite: true,
                            slidesToShow: 3
                        });
                    }, 200);
                }
            });
        }
    });
});
