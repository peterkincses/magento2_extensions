define([
    'jquery',
    'mage/url'
], function ($, url) {
    'use strict';

    return function (config) {
        $('body').on('change', '#entity', function () {
            if ($(this).val() == "restricted_word") {
                $('#sample-file-link').after('<a id ="store-down" style="margin-left:10px;" title="Download store list" type="button" class="action-default scalable save primary"><span>check store</span></a>');
                $('#store-down').attr('href', config.downloadUrl);
            } else {
                $('#store-down').remove();
            }
        });
    }
});