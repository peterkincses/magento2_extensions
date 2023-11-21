define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate',
    'domReady!'
], function($){
    'use strict';
    return function(param) {
        
        var restrictedWords = param.restrictedWords,
            psnRestrictedCharacters = param.psnRestrictedCharacters;

        $.validator.addMethod(
            "psn-restricted-words",
            function(value) {
                var inputString = value.toLowerCase();
                var inputArray = inputString.split(' ');
                return !(inputArray.some(function(item){
                    return restrictedWords.includes(item)
                }));
            },
            $.mage.__("Sorry, this is a restricted word we would prefer you don't use.")
        );

        $.validator.addMethod(
            "psn-restricted-characters",
            function (value) {
                return $.mage.isEmptyNoTrim(value) || new RegExp(psnRestrictedCharacters).test(value)
            },
            $.mage.__("Sorry, one or more of these characters is not allowed.")
        );
    }
});