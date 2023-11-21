define([
    'jquery',
], function ($) {
    'use strict';

    var customerSectionUrl = BASE_URL + 'customer/section/load/?sections=customer,cart&force_new_section_timestamp=false';

    function getParameterByName(name, url = window.location.href) {
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

    $.ajax({
        method: "GET",
        url: customerSectionUrl
    }).done(function(data) {
        if(data.customer.firstname) {
            $('.yoti-wrap .page-title span').text(data.customer.firstname);
        }
        let redirect = getParameterByName('redirect');
        if (redirect && redirect.length) {
            $('.yoti-wrap .actions .home').attr('href', redirect);
            $('.yoti-wrap .actions .home').removeClass('hidden');
        } else if(data.cart.summary_count > 0) {
            $('.yoti-wrap .actions .checkout').removeClass('hidden');
        } else {
            $('.yoti-wrap .actions .home').removeClass('hidden');
        }
    }).fail(function(){
        $('.yoti-wrap .actions .home').removeClass('hidden');
    });

});
