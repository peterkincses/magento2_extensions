define([
    'jquery',
    'mage/mage',
    'mage/validation',
    'mage/tabs',
    'jquery/ui',
    'jquery/validate',
    'domReady!'
], function ($) {
    'use strict';

    let tabs,
        preview = '.product.media .personalisation-preview',
        previewTextPositionFrontTop,
        previewTextPositionFrontLeft,
        previewTextPositionBackTop,
        previewTextPositionBackLeft,
        previewImagePosition,
        addToCartForm,
        currency,
        productPrice,
        productPriceVal,
        psnPrice,
        psnFinalPriceWrap,
        galleryIndex,
        psnFormInput;

    function init(config, el) {
        tabs = $(el),
        previewTextPositionFrontTop = config.previewTextPositionFrontTop,
        previewTextPositionFrontLeft = config.previewTextPositionFrontLeft,
        previewTextPositionBackTop = config.previewTextPositionBackTop,
        previewTextPositionBackLeft = config.previewTextPositionBackLeft,
        previewImagePosition = config.previewImagePosition,
        addToCartForm = tabs.parents('form:first'),
        currency = config.currency,
        psnPrice = config.psnPrice,
        productPrice    = $('.product-info-main .product-info-price .normal-price .price-wrapper:not(.subscription)'),
        productPriceVal = productPrice.attr('data-price-amount'),
        psnFinalPriceWrap = tabs.find('#tab-summary .psn-summary-price--personalised'),
        psnFormInput    = addToCartForm.find('input[name=is_product_personalised]');

        personalisationTabs();
        personalisationPreview();
        personalisationTooltipClickHandler();
        personalisationPriceTooltipMobileViewHandler();
    }

    function personalisationTabs() {
        tabs.tabs();
        showTabs();
        checkTabIndex();
        jumpToTab();
        tabContent();
    }

    function showTabs() {
        $('[data-role=pdp-personalisation-toggle]').on('click', function() {
            $('body').addClass('personalisation-tabs-visible');
            $('html, body').animate({
                scrollTop: $('.column.main').offset().top
            }, 800);
            if(('.pdp-personalisation-toggle-btn-container').length > 0) {
                $('#product-options-wrapper').insertAfter('.pdp-personalisation-tabs');
            }
            triggerFirstSwatch();
            setDefaultOptions();
            toggleFrontBackImage('front');
        })
    }

    function triggerFirstSwatch() {
        let swatch = $('.product-options-wrapper .swatch-option.color:first-of-type');
        if(swatch.length && !swatch.siblings('.selected').length) {
            swatch.trigger('click');
        }
    }

    function tabContent() {
        customRadioSelect();
        frontOptionToggle();
        frontOptionBack();
        filterOptions();
        calculatePsnPrice();
        clearTabSelection();
        cancelPersonalisation();
    }

    function personalisationPreview() {
        setPreviewPosition();
        applyFrontPattern();
        applyFrontIcon();
        applyFontFamily();
        applyTextDirection();
        applyText();
    }

    function frontOptionToggle() {
        tabs.find('.front-options .option').on('click', function() {
            let option = $(this),
                target = option.attr('data-target');

            option.siblings().removeClass('selected');
            option.addClass('selected');
            tabs.find('.front-options').addClass('hide');

            tabs.find('.front-option-content').removeClass('active');
            $('#'+target).addClass('active');
        });
    }

    function frontOptionBack() {
        tabs.find('.front-option-back').on('click', function(e) {
            e.preventDefault();
            tabs.find('.front-options').removeClass('hide');
            tabs.find('.front-option-content').removeClass('active');
        });
    }

    function customRadioSelect() {
        tabs.find('.personalisation-custom-radio').on('click', function(){
            $(this).siblings('.personalisation-custom-radio').removeClass('active');
            $(this).addClass('active');
            $(this).find('input[type="radio"]').attr('checked', true);
        });
    }

    function checkTabIndex() {
        let personalisationTabLi = tabs.find('.tabs-navigation li');
        personalisationTabLi.find('a').on('click', function() {
            if($(this).parent().index() + 1 == personalisationTabLi.length) {
                $('body').addClass('personalisation-tabs-summary-active');
                summaryContent();
            } else {
                $('body').removeClass('personalisation-tabs-summary-active');
            }
            toggleFrontBackPreview($(this).parent('li'));
        })
    }

    function jumpToTab() {
        tabs.find('.tab-toggle').on('click', function(e) {
            e.preventDefault();

            let tabTarget = $(this).attr('data-target-tab'),
                tabSelector = tabs.find('.tabs-navigation li')[tabTarget];

            $(tabSelector).find('a').trigger('click');
        });
    }

    function cancelPersonalisation() {
        $('.product-info-main .pdp-personalisation-cancel a').on('click', function(e) {
            e.preventDefault();
            $('body').removeClass('personalisation-tabs-visible personalisation-tabs-summary-active');
            //reset tab, clear selections and preview
            tabs.find('.tabs-navigation li').first().find('a').trigger('click');
            tabs.find('.option').removeClass('active');
            tabs.find('input[type=radio]').attr('checked', false);
            tabs.find('input[type=text]').val('');
            validationClear();
            disableSaveButton(tabs.find('.tab-toggle.save'), true);
            $(preview +':not(.text)').html('');
            $(preview+'.text span').html('');
            psnFormInput.val(0);
        });
    }

    //clear summary, uncheck radio, clear preview, remove text input value of a targeted tab
    function clearTabSelection() {
        tabs.find('.clear-selection').on('click', function(e) {
            e.preventDefault();
            let clearArea = $(this).attr('data-clear-area');
            $(preview +'.'+clearArea+':not(.text)').html('');
            $(preview +'.'+clearArea+'.text span').html('');
            tabs.find('#summary-'+clearArea).find('dl, .img-wrap').html('');
            tabs.find('#tab-'+clearArea+' input[type=radio]').attr('checked', false);
            tabs.find('#tab-'+clearArea+' .option').removeClass('active');
            tabs.find('#tab-'+clearArea+' input[type=text]').val('');
            validationClear();
            disableSaveButton(tabs.find('#tab-'+clearArea+' .tab-toggle.save'), true);
            setDefaultOptions('#tab-'+clearArea);
            checkIsPersonalised();
        });
    }

    //clear front option selections and preview
    function clearFrontSelection() {
        let siblings = tabs.find('.front-option-content:not(.active)'),
            activeOption = siblings.find('.personalisation-options:not(.select-first)').find('.option.active');

        $(preview +'.front:not(.text)').html('');
        $(preview +'.front.text span').html('');
        activeOption.find('input[type=radio]').attr('checked', false);
        activeOption.find('input[type=text]').val('');
        activeOption.removeClass('active');
        siblings.find('input.personalisation-text-input').val('');
        validationClear();
        disableSaveButton(siblings.find('.tab-toggle.save'), true);
    }

    function validationClear(){
        try {
            addToCartForm.validation('clearError');
        } catch(e){
            console.error(e);
            console.debug('Validation will now be initialised manually from pdp-personalisation.js – BAT issue #528495');
            addToCartForm.validation(); // initialise validation
            addToCartForm.validation('clearError'); // carry on
        }
    }

    function calculatePsnPrice() {
        let psnFinalPrice = currency + parseFloat(+productPriceVal + +psnPrice).toFixed(2);

        tabs.find('.psn-summary-price--default').html(productPrice.clone());
        psnFinalPriceWrap.text(psnFinalPrice);
    }

    function setDefaultOptions(target) {
        let area = target !== undefined ? target : '';
        tabs.find(area+' .select-first .option:first-of-type input[type="radio"]').trigger('click');
    }

    function toggleFrontBackPreview(element) {
        let previewArea = element.attr('data-preview-area');
        if(previewArea !== undefined){
            $(preview +':not([data-personalisation-area="'+previewArea+'"])').addClass('hide');
            $(preview +'[data-personalisation-area="'+previewArea+'"]').removeClass('hide');
            toggleFrontBackImage(previewArea);
        }
    }

    function toggleFrontBackImage(area) {
        let pdpGallery = $('.gallery-placeholder .fotorama-item'),
            fotorama = pdpGallery.data('fotorama');

        if(fotorama) {
            if(area == 'front') {
                galleryIndex = getPsnFrontImageIndex(fotorama.data);
            } else if (area == 'back') {
                galleryIndex = getPsnBackImageIndex(fotorama.data);
            }

            if(galleryIndex > -1) {
                fotorama.show({index: galleryIndex, time: 0});
            } else {
                console.log('no '+ area + 'image configured');
            }
        }
    }

    function getPsnFrontImageIndex(data) {
        var psnFrontImgIndex;

        if (_.every(data, function (item) {
            return _.isObject(item);
        })
        ) {
            psnFrontImgIndex = _.findIndex(data, function (item) {
                return item.isPsnFrontImage;
            });
        }

        return psnFrontImgIndex;
    }

    function getPsnBackImageIndex(data) {
        var psnBgImgIndex;

        if (_.every(data, function (item) {
            return _.isObject(item);
        })
        ) {
            psnBgImgIndex = _.findIndex(data, function (item) {
                return item.isPsnBackImage;
            });
        }

        return psnBgImgIndex;
    }

    //filter options if multiple categories
    function filterOptions() {
        $('.personalisation-option-filters li').on('click', function(){
            let category = $(this).attr('data-category'),
                option = $(this).parent().next('.personalisation-options').find('.option');

            $(this).addClass('selected');
            $(this).siblings().removeClass('selected');

            if($(this).attr('data-category') == 'all') {
                option.removeClass('hide');
            } else {
                $(option).each(function(){
                    if($(this).attr('data-category') == category) {
                        $(this).removeClass('hide');
                    } else {
                        $(this).addClass('hide');
                    }
                })
            }
        });
        $('.personalisation-option-filters li:first-of-type').trigger('click');
    }

    function disableSaveButton(button, status) {
        let buttonTabParent = $(button).closest('div[role=tabpanel]'),
            buttonParentHeader = tabs.find('.tab-header[aria-controls='+buttonTabParent.attr('id')+']');

        $(button).attr('disabled', status);

        if(status) {
            buttonParentHeader.addClass('error');
        } else {
            buttonParentHeader.removeClass('error');
        }
    }

    function setPreviewPosition() {
        if(previewTextPositionFrontTop) {
            $(preview + '.text.front').css({
                'top': previewTextPositionFrontTop + '%',
            });
        }

        if(previewTextPositionFrontLeft) {
            $(preview + '.text.front').css({
                'left': previewTextPositionFrontLeft + '%'
            });
        }

        if(previewTextPositionBackTop) {
            $(preview + '.text.back').css({
                'top': previewTextPositionBackTop + '%',
            });
        }

        if(previewTextPositionBackLeft) {
            $(preview + '.text.back').css({
                'left': previewTextPositionBackLeft + '%'
            });
        }

        if(previewImagePosition) {
            $(preview + '.icon').css('top', previewImagePosition + '%')
            $(preview + '.pattern').css('top', previewImagePosition + '%')
        }
    }

    function applyFrontPattern() {
        $('input[name="personalisation_front_pattern"]').on('click', function() {
            let input = $(this);

            clearFrontSelection();
            $(preview + '.pattern').html('<img src="'+input.attr('data-preview-image')+'" />');
            disableSaveButton(input.attr('data-save-button'), false);
        });
    }

    function applyFrontIcon() {
        $('input[name="personalisation_front_icon"]').on('click', function() {
            let input = $(this);

            clearFrontSelection();
            $(preview + '.icon').html('<img src="'+input.attr('data-preview-image')+'" />');
            disableSaveButton(input.attr('data-save-button'), false);
        });
    }

    function applyText() {
        var timer,
            timeoutVal = 1000,
            textKeypressCount = 0;

        tabs.find('.personalisation-text-input').on('keyup', function() {
            textKeypressCount++;
            var input = $(this),
                previewArea = input.attr('data-area'),
                previewText = '',
                isSaveDisabled = true;

            try {
                $.validator.validateSingleElement(input); // triggers showErrors() for the user
            } catch(e){
                console.error(e);
                console.error('Unable to validateSingleElement. Validation will still occur, but a message will not be shown to the user.');
            }

            if(typeof input.valid == 'function' && !!input.valid()) {
                if(previewArea == 'front') {
                    if(textKeypressCount === 1) {
                        clearFrontSelection();
                    }
                }
                previewText = input.val();
                isSaveDisabled = false;
                window.clearTimeout(timer);
                timer = window.setTimeout(() => {
                    textKeypressCount = 0;
                }, timeoutVal);
            }
            $(preview + '.'+ previewArea +'-text span').text(previewText);
            disableSaveButton(input.attr('data-save-button'), isSaveDisabled);
        });
    }

    function applyTextDirection() {
        tabs.find('input.personalisation-text-direction').on('click', function() {
            let option = $(this),
                area = option.attr('data-area'),
                input = tabs.find('input#personalisation_'+area+'_text');

            $(preview + '.'+area+'-text').attr('data-direction', option.val());
            input.attr('maxlength', option.attr('data-max-length'));
            input.trigger('keyup');
            input.parent().next('.note').find('.limit').text(option.attr('data-max-length'));
        });
    }

    function applyFontFamily() {
        tabs.find('input.personalisation-font-family').on('click', function() {
            $(preview + '.'+$(this).attr('data-area')+'-text span').attr('class', $(this).attr('data-font-class'));
        });
    }

    function summaryContent() {
        //@todo: optimise to use knockout to speed it up
        tabs.find('.summary tr dl').empty();
        tabs.find('.summary .error').addClass('hide');

        let disableAddToCart = false,
            frontTextInput  = tabs.find('input#personalisation_front_text'),
            summaryFront    = tabs.find('#summary-front'),
            backTextInput   = tabs.find('input#personalisation_back_text'),
            summaryBack     = tabs.find('#summary-back'),
            frontFontFamily = tabs.find('input[name="personalisation_front_text_font_family"]:checked').val(),
            backFontFamily  = tabs.find('input[name="personalisation_back_text_font_family"]:checked').val();

        if(frontTextInput.val()) {
            if(frontTextInput.hasClass('mage-error')) {
                summaryFront.find('.error').removeClass('hide');
                disableAddToCart = true;
            } else {
                let dataValue;
                tabs.find('#tab-front input[type="radio"]:checked').each(function() {
                    dataValue = $(this).attr('data-input-value') !== undefined ? $(this).attr('data-input-value') : $(this).val();
                    summaryFront.find('dl').append('<dt>'+ $(this).attr('data-input-label')+'</dt><dd>'+dataValue+'</dd>');
                });
                summaryFront.find('dl').append('<dt>' + frontTextInput.attr('data-input-label') + '</dt><dd>' + frontTextInput.val() + '</dd>');
                summaryFront.find('.img-wrap').html('<span class="'+frontFontFamily+'">'+frontTextInput.val()+'</span>');
            }
        } else {
            let dataValue;
            tabs.find('.front-option-content:not(.front-option-content-text)').find('input[type="radio"]:checked').each(function() {
                dataValue = $(this).attr('data-input-value') !== undefined ? $(this).attr('data-input-value') : $(this).val();
                summaryFront.find('dl').append('<dt>'+ $(this).attr('data-input-label')+'</dt><dd>'+dataValue+'</dd>');
            });
            summaryFront.find('.img-wrap').html($(preview +'.front img').clone());
        }

        if(backTextInput.val()) {
            if(backTextInput.hasClass('mage-error')) {
                summaryBack.find('.error').removeClass('hide');
                disableAddToCart = true;
            } else {
                let dataValue;
                tabs.find('#tab-back input[type="radio"]:checked').each(function() {
                    dataValue = $(this).attr('data-input-value') !== undefined ? $(this).attr('data-input-value') : $(this).val();
                    summaryBack.find('dl').append('<dt>'+ $(this).attr('data-input-label')+'</dt><dd>'+dataValue+'</dd>');
                });
                summaryBack.find('dl').append('<dt>' + backTextInput.attr('data-input-label') + '</dt><dd>' + backTextInput.val() + '</dd>');
                summaryBack.find('.img-wrap').html('<span class="'+backFontFamily+'">'+backTextInput.val()+'</span>');
            }
        }

        checkIsPersonalised();

        //if there is an error, disable the add to cart button
        addToCartForm.find('.action.primary.tocart').attr('disabled', disableAddToCart);
    }

    function checkIsPersonalised() {
        let hasPsnOption = false;

        $(preview + '.text span').each(function() {
            if($(this).html().length > 0) {
                hasPsnOption = true;
            }
        });

        if($(preview + '.pattern img').length || $(preview + '.icon img').length) {
            hasPsnOption = true;
        }

        if(hasPsnOption) {
            tabs.removeClass('not-yet-personalised');
        } else {
            tabs.addClass('not-yet-personalised');
        }

        psnFormInput.val(hasPsnOption * 1);
    }

    function personalisationTooltipClickHandler() {
        $('#pdp-personalisation-panel-price .icon-tooltip').on('click', function() {
            $('#pdp-price-breakdown').toggle();
        });

        $(document).on('click', function(e) {
            if(!$(e.target).closest('#pdp-price-breakdown, #pdp-personalisation-panel-price .icon-tooltip').length) {
                $('#pdp-price-breakdown').hide();
            }
        });
    }

    function personalisationPriceTooltipMobileViewHandler (){
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            $('.pdp-personalisation-price').insertBefore('.pdp-personalisation-tabs-nav');
        }
    }

    return init;
});
