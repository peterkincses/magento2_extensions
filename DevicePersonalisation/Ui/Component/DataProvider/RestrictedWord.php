<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\DataProvider;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class RestrictedWord extends AbstractDataProvider
{
    /**
     * @var AddFilterToCollectionInterface[]
     */
    private $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    private $addFilterStrategies;

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    /**
     * @param mixed[] $addFieldStrategies
     * @param mixed[] $addFilterStrategies
     * @param mixed[] $meta
     * @param mixed[] $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $modifiersPool,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->modifiersPool = $modifiersPool;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function addFilter(Filter $filter): void
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }
}
