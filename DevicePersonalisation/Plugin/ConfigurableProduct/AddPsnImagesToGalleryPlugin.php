<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\ConfigurableProduct;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class AddPsnImagesToGalleryPlugin
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function __construct(JsonSerializer $jsonSerializer)
    {
        $this->jsonSerializer = $jsonSerializer;
    }

    public function afterGetJsonConfig(Configurable $subject, string $result): string
    {
        $config = $this->jsonSerializer->unserialize($result);

        $allowProducts = $subject->getAllowProducts();
        if (!(is_array($allowProducts) && count($allowProducts))) {
            return $result;
        }

        foreach ($config['images'] as $productId => &$images) {
            $product = $this->getProductFromAllowProducts($allowProducts, $productId);
            if (!$product) {
                continue;
            }
            foreach ($images as &$imageData) {
                $imageData['isPsnFrontImage'] = $this->imageHasType($product, $imageData, 'psn_front_image');
                $imageData['isPsnBackImage'] = $this->imageHasType($product, $imageData, 'psn_background_image');
            }
        }

        return $this->jsonSerializer->serialize($config);
    }

    private function getProductFromAllowProducts(array $allowProducts, int $productId): ?ProductInterface
    {
        foreach ($allowProducts as $product) {
            if ($product->getId() == $productId) {
                return $product;
            }
        }

        return null;
    }

    private function imageHasType(ProductInterface $product, array $imageData, string $roleName): bool
    {
        // we use the full image url to get the image id from media gallery images\
        $imageId = '';
        if (isset($imageData['full'])) {
            $imageId = $this->getImageIdFromProduct(
                $product,
                $imageData['full']
            );
        }
        
        if (!$imageId) {
            return false;
        }

        // then we use the image id to get the types for said image from media gallery entries
        $types = $this->getImageTypesFromProduct(
            $product,
            $imageId
        );
        if (!$types) {
            return false;
        }

        return in_array($roleName, $types);
    }

    private function getImageIdFromProduct(ProductInterface $product, string $largeImageUrl): ?int
    {
        foreach ($product->getMediaGalleryImages() as $mediaGalleryImage) {
            if ($largeImageUrl == $mediaGalleryImage->getLargeImageUrl()) {
                return (int) $mediaGalleryImage->getId();
            }
        }

        return null;
    }

    private function getImageTypesFromProduct(ProductInterface $product, int $imageId): ?array
    {
        foreach ($product->getMediaGalleryEntries() as $mediaGalleryEntry) {
            if ($imageId == $mediaGalleryEntry->getId()) {
                return $mediaGalleryEntry->getTypes();
            }
        }

        return null;
    }
}
