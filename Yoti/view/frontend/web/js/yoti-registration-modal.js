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
        yotiRegistrationModal = $('#yoti-registration-modal'),
        registrationForm = $('#form-validate.form-create-account'),
        submitButton = registrationForm.find('.action.submit.primary'),
        doubleOptIn = false;//customer email confirmation setting - modal should not trigger if customer needs to confirm account first

    return function init(config) {
        doubleOptIn = config.doubleOptIn;
        if (!doubleOptIn) {
            submitButton.find('span').text($t('Continue'));
        }
        browserCheck();
    }

    function browserCheck() {
        var userAgentExp = /(?:android)\s*(.*?)(?:ucbrowser)/gi;
        // navigator.mediaDevices evaluates to undefined on some older browsers
        if (typeof navigator.mediaDevices !== 'object' || userAgentExp.test(navigator.userAgent)) {
            $('.page.messages').append($('.yoti-registration-browser-error'));
            disableSubmitButton();
        } else {
            if (!doubleOptIn) {
                validateForm();
            }
        }
    }

    function disableSubmitButton() {
        submitButton.on('click', function(e){
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 500);
        });
    }


    function validateForm() {
        $(registrationForm).on('submit', function(e) {
            if(!canSubmit) {
                e.preventDefault();
                if(registrationForm.valid()) {
                    var firstName = registrationForm.find('input#firstname');
                    if (firstName) {
                        yotiRegistrationModal.find('.first-name').text(firstName.val());
                    }
                    showYotiRegistrationModal(registrationForm);
                }
            }
        });
    }

    function showYotiRegistrationModal(form) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            modalClass: 'yoti-registration-modal',
            buttons: []
        };
        modal(options, yotiRegistrationModal).openModal();

        yotiRegistrationModal.find('.yoti-start').on('click', function(){
             $("body").trigger('processStart');
             canSubmit = true;
             $(form).trigger('submit');
        });

        $('.yoti-modify-registration').on('click', function(){
            yotiRegistrationModal.modal('closeModal');
        });

        yotiRegistrationModal.on('modalclosed', function() {
            canSubmit = false;
        });
    }
});