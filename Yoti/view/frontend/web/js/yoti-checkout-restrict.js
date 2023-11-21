define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/mage',
    'mage/validation',
    'domReady!'
], function ($, modal, $t) {
    "use strict";

    let canSubmit = false,
        yotiCheckoutModal = $('#yoti-checkout-modal'),
        verificationUrl,
        checkoutButton = $('.checkout');
       

    return function init(config) {
       verificationUrl = config.verificationUrl;
       bindClickEventListener();
       
    }

    function bindClickEventListener() {
        checkoutButton.off().on('click',function(){
            showYotiCheckoutModal();
        })
    }
    

    function showYotiCheckoutModal() {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass: 'yoti-checkout-modal',
            buttons: []
        };
        yotiCheckoutModal.find('.first-name').text(window.checkoutConfig.customerData.firstname);
        
        modal(options, yotiCheckoutModal).openModal();

        yotiCheckoutModal.find('.yoti-start').on('click', function(){
             $("body").trigger('processStart');
             canSubmit = true;
             window.location.href = verificationUrl;
        });
        $(".modal-header").remove();

        $('.yoti-modify-checkout').on('click', function(){
            yotiCheckoutModal.modal('closeModal');
        });

        yotiCheckoutModal.on('modalclosed', function() {
            canSubmit = false;
        });
    }
});