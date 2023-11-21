<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Block\Product\View;

use Magento\Catalog\Block\Product\View\Gallery;

/**
 * Gallery block plugin
 */
class GalleryPlugin
{
    /**
     * {@inheritDoc}
     */
    public function afterGetGalleryImagesJson(Gallery $galleryBlock, $result)
    {
        $product = $galleryBlock->getProduct();
        $galleryEntries = $product->getMediaGalleryEntries();

        if (!is_null($galleryEntries)) {
            $imagesItems = [];
            $entries = [];

            foreach ($galleryEntries as $entry) {
                if ($entry->getFile()) {
                    $entries[$entry->getFile()] = $entry->getTypes();
                }
            }
            foreach ($galleryBlock->getGalleryImages() as $image) {
                $imagesItems[] = [
                    'thumb' => $image->getData('small_image_url'),
                    'img' => $image->getData('medium_image_url'),
                    'full' => $image->getData('large_image_url'),
                    'caption' => ($image->getLabel() ?: $product->getName()),
                    'position' => $image->getPosition(),
                    'isMain' => $galleryBlock->isMainImage($image),
                    'isPsnFrontImage' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'psn_front_image'
                    ) : 0,
                    'isPsnBackImage' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'psn_background_image'
                    ) : 0,
                    'isBundleRegularImage' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'bundle_regular'
                    ) : 0,
                    'isBundleRegularImageBack' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'bundle_regular_back'
                    ) : 0,
                    'isBundleStrongImage' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'bundle_strong'
                    ) : 0,
                    'isBundleStrongImageBack' => isset($entries[$image->getFile()]) ? $this->isRoleImage(
                        $entries[$image->getFile()],
                        'bundle_strong_back'
                    ) : 0,
                    'type' => str_replace('external-', '', $image->getMediaType()),
                    'videoUrl' => $image->getVideoUrl(),
                    'image_roles' => isset($entries[$image->getFile()]) ? implode(',', $entries[$image->getFile()]) : '',
                ];
            }
            if (!empty($imagesItems)) {
                return json_encode($imagesItems);
            }
        }
        return $result;
    }

    private function isRoleImage(array $roles, string $result): bool
    {
        foreach ($roles as $role) {
            if ($role == $result) {
                return true;
            }
        }
        return false;
    }
}
