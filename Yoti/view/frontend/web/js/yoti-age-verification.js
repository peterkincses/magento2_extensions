define([
    'jquery',
    'ko',
    'uiComponent',
    'mage/translate',
    'mage/url',
    'mage/mage',
    'BAT_Yoti/js/yoti-face-capture'
], function ($, ko, Component, $t, urlBuilder) {
    'use strict';

    const videoWrapper = document.getElementById('video-div');
    const introView = document.getElementById('intro');
    const cameraView = document.getElementById('camera');
    const prepareVideo = document.getElementById('prepare-video');
    const prepareResultView = document.getElementById('preparing-result');
    const resultView = document.getElementById('result');
    const submitActualAgeForm = document.getElementById('submit-actual-age');
    const badResult = document.getElementById('bad-result');
    const errorView = document.getElementById('error');
    const errorMessage = document.getElementById('error-message');
    const errorHint = document.getElementById('error-hint');
    const retryBtn = document.getElementById('retry-btn');
    const emailBtn = document.getElementById('email-btn');
    const chatBtn = document.getElementById('chat-btn');
    const cameraErrors = {
        NotAllowedError: 'NotAllowedError',
        GeneralError: 'GeneralError',
        BrowserNotSupported: 'BrowserNotSupported',
        CameraNotDetected: 'CameraNotDetected',
        TypeError: 'TypeError',
        TypeErrorMessage: "undefined is not an object (evaluating 'navigator.mediaDevices.getUserMedia')",
    };

    const INTRO = 588001;
    const CAMERA = 139964;
    const PREPARING_RESULT = 69213;
    const UNCERTAIN = 460451;
    const RETRY = 39019;
    const BAD_RESULT = 22860;
    const ERROR = 39019;
    const SUCCESS = 460500;
    let appState = INTRO;
    let faceAssetsPath;
    let yotiLangCode = "en";

    function init(config, el) {
        faceAssetsPath = config.yotiFaceCaptureAssetsPath;
        yotiLangCode = config.yotiLangCode;

        backToIntro();
        restart();
        startAgeEstimation();
        display();
        launchChat();
        contactPage();
        deviceCheck();

        // Skip straight to face scan if user is retrying again
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        const retry = urlParams.get('retry');

        if (retry == 1) {
            nextView(CAMERA);
        }
    }

    function runFaceScan() {
        const props = {
            faceCaptureAssetsRootUrl: faceAssetsPath,
            captureMethod: "auto",
            language: yotiLangCode,
            onSuccess: function(image) {
                let deviceType = deviceCheck();
                let imagePostData = {
                    data: image.image,
                    device: deviceType,
                    form_key: $('.column.main input[name="form_key"]').val()
                };
                nextView(PREPARING_RESULT);
                postImage(imagePostData);
            }
        };

        Yoti.FaceCaptureModule.render(props, document.getElementById('face-capture-module-root'));

        prepareVideo.classList.add('hidden');
        videoWrapper.classList.remove('hidden');
    };

    function display(result) {
        switch (appState) {
            case INTRO: {
                $('.yoti-wrap section').addClass('hidden');
                $(introView).removeClass('hidden');
            }
                break;
            case CAMERA: {
                $('.yoti-wrap').removeClass('show-preview');
                $('.yoti-wrap section').addClass('hidden');
                $(videoWrapper).addClass('hidden');
                $(cameraView).removeClass('hidden');
                $(prepareVideo).removeClass('hidden');
                runFaceScan();
            }
                break;
            case PREPARING_RESULT: {
                console.log('preparing result');
                $('.yoti-wrap section').addClass('hidden');
                $(prepareResultView).removeClass('hidden');
            }
                break;
            case RETRY: {
                $('.yoti-wrap section').addClass('hidden');
                $(errorView).removeClass('hidden');

                if (result.error_code === 4) {
                    // Face scan unclear
                    if (result.can_retry) {
                        // retry allowed
                        $(errorMessage).text($t('No face detected'));
                        $(errorHint).text($t('Please note that your photo will not be kept after estimating your age.'));
                    } else {
                        // retry no longer allowed
                        $(errorMessage).text($t('We were unable to detect your face in order to estimate your age.'));
                        $(errorHint).text($t('Please contact us so we can assist you further.'));
                        $(retryBtn).addClass('hidden');

                        if ($('#liveagent-online').is(':visible')) {
                            $(chatBtn).removeClass('hidden');
                        } else {
                            $(emailBtn).removeClass('hidden');
                        }
                    }
                } else {
                    if (result) {
                        if (result.error_message) {
                            $(errorMessage).text(result.error_message);
                        } else if (result.error) {
                            $(errorMessage).text(result.error);
                        }
                    }
                }
            }
                break;
            case UNCERTAIN: {
                $('.yoti-wrap section').addClass('hidden');
                $(resultView).removeClass('hidden');
            }
                break;
            case SUCCESS: {
                let redirectUrl = BASE_URL + 'verification/customer/success';
                if (result && 'redirect' in result && result.redirect) {
                    redirectUrl = redirectUrl + '?redirect=' + result.redirect;
                }
                window.location.replace(redirectUrl);
            }
                break;
            case BAD_RESULT: {
                $('.yoti-wrap section').addClass('hidden');
                $(badResult).removeClass('hidden');
                $.mage.redirect('https://google.com','assign', 10000);
            }
                break;
            case ERROR:
            default: {
                $('.yoti-wrap section').addClass('hidden');
                $(errorView).removeClass('hidden');
                if (result) {
                    if (result.error_message) {
                        $(errorMessage).text(result.error_message);
                    } else if (result.error) {
                        $(errorMessage).text(result.error);
                    } 
                }
            }
                break;
        }
    }

    const nextView = function (view, result) {
        appState = view;
        display(result);
    }

    function printResults(result) {
        if(typeof result.is_yoti_verified !== 'undefined') {
            console.log(result.is_yoti_verified);
            if (result.is_yoti_verified == 3) {
                console.log('retry');
                nextView(RETRY, result);
            } else if (result.is_yoti_verified == 2) {
                console.log('uncertain');
                nextView(UNCERTAIN, result);
            } else if (result.is_yoti_verified == 1) {
                console.log('success');
                localStorage.removeItem("mage-cache-storage");
                nextView(SUCCESS, result);
            } else {
                console.log('bad result');
                nextView(BAD_RESULT, result);
            }
        } else {
            console.log('yoti not defined');
            nextView(ERROR, result);
        }
    };

    function postImage(data) {
        $.ajax({
            method: 'post',
            url: BASE_URL + "verification/customer/fetch",
            data: data,
            loader: true
        }).done(function(data) {
            printResults(data);
        }).fail(function(){
            setCameraError(cameraErrors.GeneralError);
        });
    };

    function submitActualAge(e) {
        e.preventDefault();
        const formData = submitActualAgeForm.elements;
        const body = {
            actualAge: formData['actual-age'].value,
        }

        /*
        fetch('/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body),
        }).then((response) => {
            response.json().then(() => {

            });
        });
        */

        // On Success
        // collectActualAge.classList.add('hidden');
        // thankYouForSubmitting.classList.remove('hidden');

    }

    function setCameraError(error) {
        switch (error) {
            case cameraErrors.GeneralError:
            default:
                $(errorMessage).text($t('Something went wrong.'));
                $(errorHint).text($t('Please refresh the browser and try again.'));
                break;
        }
        nextView(ERROR);
    }

    const backToIntro = function () {
        $('.back-to-intro').on('click', function(){
            nextView(INTRO);
        });
    }

    const restart = function () {
        $('.restart').on('click', function(){
            // Temporary solution - reload page to retry parameter, to allow another face scan
            window.location = window.location.href.split("?")[0] + "?retry=1";
        });
    }

    const launchChat = function () {
        $('.yoti-wrap .start-chat').on('click', function(){
            $('#liveagent-online a').trigger('click');
        });
    }

    const contactPage = function () {
        $('.yoti-wrap .email').on('click', function(){
            window.location.replace(BASE_URL + 'contact');
        });
    }

    const startAgeEstimation = function () {
        $('.start-age-estimation').on('click', function(){
            nextView(CAMERA);
        });
    }

    const deviceCheck = function () {
        let check = false;
        (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
        if (!check) {
            return "laptop";
        } else {
            $('body').addClass('mobile-tablet');
            return "mobile";
        }
    }

    return init;

});
