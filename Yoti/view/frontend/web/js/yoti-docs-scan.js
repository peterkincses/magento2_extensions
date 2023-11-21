define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function($, $t, modal) {
        const errorView = document.getElementById('error');
        const errorMessage = document.getElementById('error-message');
        const errorHint = document.getElementById('error-hint');
        const retryBtn = document.getElementById('retry-btn');
        const retryDocBtn = document.getElementById('retry-doc-btn');
        const emailBtn = document.getElementById('email-btn');
        const chatBtn = document.getElementById('chat-btn');

        var button = $('.yoti-upload-documents');
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: '',
            buttons: [],
            modalClass : 'yoti-docs-scan__modal'
        };
        button.on('click', function(e) {
            e.stopPropagation();

            $(errorView).addClass('hidden');
            $('.yoti-id-scan-notice').addClass('hidden');
            $('.yoti-id-scan-error').text('');

            $.ajax({
                url: BASE_URL + 'verification/customer/yotiDocSession',
                type: "POST",
                cache: false,
                loader: true
            }).done(function(data){
                showYotiSessionResponse(data);
            });
        });

        function showYotiSessionResponse(data) {
            if(data.sessionID && data.sessionToken) {
                var iframeUrl = 'https://api.yoti.com/idverify/v1/web/index.html?sessionID='+data.sessionID+'&sessionToken=' + data.sessionToken;
                if (data.url) {
                    iframeUrl = data.url+'/web/index.html?sessionID='+data.sessionID+'&sessionToken='+data.sessionToken;
                }
                $('.yoti-docs-scan-iframe').html('<iframe allow="camera" class="yoti-docs-scan-popup" frameborder="0" seamless src="'+iframeUrl+'" width="100%" height="600"></iframe>');
                $(".yoti-docs-scan-iframe").modal(options).modal('openModal');
            } else if (data.error_message){
                $('.yoti-id-scan-error').text(data.error_message);
            } else {
                $('.yoti-id-scan-error').text($t('Sorry we couldn\'t launch our ID Scan Service'));
            }
        }

        // https://developers.yoti.com/v4.0/yoti-doc-scan/render-the-user-view#event-notifications
        window.addEventListener(
            'message',
            function(event) {
                console.log('Message received', event.data);

                if (event.data.eventType === 'SUCCESS') {
                    $(".yoti-docs-scan-iframe").modal('closeModal');
                    var docUpdateRequestCount = 0,
                        yotiResult = $('.yoti-wrap .yoti-result');

                    function docUpdateCall() {
                        console.log(docUpdateRequestCount);
                        $('.yoti-id-scan-error').text('');
                        $('.yoti-id-scan-notice').addClass('hidden');
                        $.ajax({
                            url: BASE_URL + 'verification/customer/yotiDocUpdate',
                            type: "POST",
                            cache: false,
                            loader: true,
                            data: { response: event.data.eventType},
                        }).done(function(data){
                            if (data.status) {
                                if (data.redirect) { //SUCCESS
                                    window.location.replace(data.redirect);
                                } else if (data.status === 2) {

                                    docUpdateRequestCount++;
                                    yotiResult.addClass('yoti-docscan-ajax-error');

                                    if (docUpdateRequestCount < 21) {
                                        $('.yoti-id-scan-notice.retry').removeClass('hidden');
                                        setTimeout(function () {
                                            docUpdateCall();
                                        }, 30000);
                                    } else {
                                        $('.yoti-id-scan-notice.no-retries').removeClass('hidden');
                                    }

                                } else if (data.status == 3) {
                                    yotiResult.addClass('yoti-docscan-ajax-error');
                                    $('.yoti-id-scan-notice.status-3').removeClass('hidden');
                                } else if (data.status == 4) {
                                    // Unable to read ID / not clear
                                    $('.yoti-wrap section').addClass('hidden');
                                    yotiResult.addClass('yoti-docscan-ajax-error');
                                    $(errorView).removeClass('hidden');
                                    $(errorMessage).text($t('We were unable to read your ID information due to the image quality.'));
                                    $(retryBtn).addClass('hidden');

                                    if (data.can_retry) {
                                        // retry allowed
                                        $(errorHint).text($t('Please make sure that the information on your ID is easily readable.'));
                                        $(retryDocBtn).removeClass('hidden');
                                        yotiResult.removeClass('hidden');
                                    } else {
                                        // retry not allowed
                                        $(errorHint).text($t('Please contact us so we can assist you further.'));
                                        $(retryDocBtn).addClass('hidden');
                                        if ($('#liveagent-online').is(':visible')) {
                                            $(chatBtn).removeClass('hidden');
                                        } else {
                                            $(emailBtn).removeClass('hidden');
                                        }
                                    }
                                }

                            } else {
                                yotiResult.addClass('yoti-docscan-ajax-error');
                                $('.yoti-id-scan-error').text(data.error_message);
                                setTimeout(function () {
                                    window.location.replace('//google.com');
                                }, 5000);
                            }

                        });
                    }

                    docUpdateCall();
                }
            }
        );
    }
);
