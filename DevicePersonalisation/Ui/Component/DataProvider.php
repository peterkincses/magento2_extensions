<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataprovider;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;
use Magento\Ui\Component\Container;

class DataProvider extends UiDataprovider
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var AddFilterInterface[]
     */
    private $additionalFilterPool;

    /**
     * @param mixed[] $meta
     * @param mixed[] $data
     * @param mixed[] $additionalFilterPool
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = [],
        array $additionalFilterPool = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->meta = array_replace_recursive($meta, $this->prepareMetadata());
        $this->additionalFilterPool = $additionalFilterPool;
        $this->request = $request;
    }

    private function getAuthorizationInstance(): AuthorizationInterface
    {
        if ($this->authorization === null) {
            $this->authorization = ObjectManager::getInstance()->get(AuthorizationInterface::class);
        }
        return $this->authorization;
    }

    /**
     * @return mixed[]
     */
    public function prepareMetadata(): array
    {
        $metadata = [];

        if (!$this->getAuthorizationInstance()->isAllowed('BAT_DevicePersonalisation::icon')) {
            $metadata = [
                'cms_page_columns' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'editorConfig' => [
                                    'enabled' => false,
                                ],
                                'componentType' => Container::NAME,
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $metadata;
    }

    protected function prepareUpdateUrl(): void
    {
        $this->data['config']['filter_url_params']['store_id'] = $this->request->getParam('store');
        parent::prepareUpdateUrl();
    }

    public function addFilter(Filter $filter): void
    {
        if (!empty($this->additionalFilterPool[$filter->getField()])) {
            $this->additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilder, $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
