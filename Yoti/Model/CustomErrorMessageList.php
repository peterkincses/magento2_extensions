<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use Magento\Framework\Phrase;

class CustomErrorMessageList
{
    public function getErrorMessageByCode(string $errorCode): Phrase
    {
        $defaultErrorMessage = __('Unknown Error.');
        if (empty($errorCode)) {
            return $defaultErrorMessage;
        }

        $errorList = $this->getErrorMessageList();
        if (isset($errorList[$errorCode])) {
            return $errorList[$errorCode];
        }

        return $defaultErrorMessage;
    }

    public function getErrorMessageList(): array
    {
        return [
            'FACE_NOT_FOUND' => __('Face not found.'),
            'MULTIPLE_FACES' => __('Multiple faces in the image provided.'),
            'FACE_BOX_TOO_SMALL' => __('The face in the image provided is too small.'),
            'FACE_TO_IMAGE_RATIO_TOO_LOW' => __('Face ratio is lower than the minimum ratio.'),
            'FACE_TO_IMAGE_RATIO_TOO_HIGH' => __('Face ratio is bigger than the maximum ratio.'),
            'IMAGE_TOO_BRIGHT' => __('Image too bright.'),
            'IMAGE_TOO_DARK' => __('Image too dark.'),
            'INSUFFICIENT_AREA_AROUND_THE_FACE' => __('Insufficient area around the face in the image provided.'),
            'APP_NOT_FOUND' => __('A technical problem has occurred.'),
            'INVALID_X_YOTI_AUTH_ID' => __('A technical problem has occurred.'),
            'INVALID_APP_ID' => __('A technical problem has occurred.'),
            'INVALID_PUBLIC_KEY' => __('A technical problem has occurred.'),
            'DISABLED_APP_STATE' => __('A technical problem has occurred.'),
            'INVALID_ORG_ID' => __('A technical problem has occurred.'),
            'ORG_NOT_FOUND' => __('A technical problem has occurred.'),
            'INVALID_YOTI_AUTH_DIGEST' => __('A technical problem has occurred.'),
            'INVALID_NONCE' => __('A technical problem has occurred.'),
            'INVALID_TIMESTAMP' => __('A technical problem has occurred.'),
            'INVALID_PUBLIC_KEY_ENCODING' => __('A technical problem has occurred.'),
            'UNSUPPORTED_ALGORITHM' => __('A technical problem has occurred.'),
            'INVALID_SIGNATURE' => __('A technical problem has occurred.'),
            'INVALID_ORG_STATUS' => __('A technical problem has occurred.'),
            'INVALID_BODY_ENCODING' => __('A technical problem has occurred.'),
            'INVALID_ENDPOINT' => __('A technical problem has occurred.'),
            'INVALID_METADATA_DEVICE' => __('Invalid device metadata provided.'),
            'IMAGE_NOT_PROVIDED' => __('Image has not been provided.'),
            'INVALID_PRODUCT' => __('A technical problem has occurred.'),
            'INVALID_B64_IMAGE' => __('A technical problem has occurred.'),
            'FAIL_PREDICTION' => __('A technical problem has occurred.'),
            'PAYLOAD_TOO_LARGE' => __('Payload too large.'),
            'UNSPECIFIED_ERROR' => __('A technical problem has occurred.'),
            'SERVICE_UNAVAILABLE' => __('A technical problem has occurred.'),
            'UNSUPPORTED_IMAGE_FORMAT' => __(
                'Image format not supported. Only JPEGs (95 to 100 quality) and PNGs are allowed.'
            ),
            'IMAGE_SIZE_TOO_BIG' => __('Image size too big, the maximum size is 2MB.'),
            'IMAGE_SIZE_TOO_SMALL' => __('Image size too small, the minimum size is 50KB.'),
            'MIN_HEIGHT' => __('Minimum image height supported is 300 pixels.'),
            'MAX_HEIGHT' => __('Maximum image height supported is 2000 pixels.'),
            'MIN_WIDTH' => __('Minimum image width supported is 300 pixels.'),
            'MAX_WIDTH' => __('Maximum image width supported is 2000 pixels.'),
            'MIN_PIXELS' => __('Minimum number of image pixels supported is 90,000.'),
            'MAX_PIXELS' => __('Maximum number of image pixels supported is 2,100,000.'),
            'IMAGE_WRONG_CHANNELS' => __('Missing colour channel, the input image must be RGB or RGBA.'),
            'IMAGE_GRAYSCALE_NOT_SUPPORTED' => __('Grayscale images not supported.'),
        ];
    }
}
