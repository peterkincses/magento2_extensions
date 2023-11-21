<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Test\Unit\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Model\Service\GetPsnPatternsByStore;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetPsnPatternsByStoreTest extends TestCase
{
    /**
     * @var StoreInterface|MockObject
     */
    protected MockObject $storeMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected MockObject $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|MockObject
     */
    protected MockObject $searchCriteriaMock;

    /**
     * @var PsnPatternsRepositoryInterface|MockObject
     */
    protected MockObject $psnPatternsRepositoryMock;

    /**
     * @var PsnPatternsSearchResultsInterface|MockObject
     */
    protected MockObject $psnPatternsSearchResultsMock;

    protected function setUp(): void
    {
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->onlyMethods(['getId'])
            ->addMethods(['getBaseUrl'])
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFilter', 'create'])
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->psnPatternsRepositoryMock = $this->getMockBuilder(PsnPatternsRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();

        $this->psnPatternsSearchResultsMock = $this->getMockBuilder(PsnPatternsSearchResultsInterface::class)
            ->onlyMethods(['getItems'])
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        int $storeId,
        array $storeData,
        array $expectedResult
    ): void {
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($storeId);

        $this->searchCriteriaBuilderMock->expects($this->exactly(2))
            ->method('addFilter')
            ->withConsecutive(
                ['store_id', $storeId, 'eq'],
                ['override_table.status', 1]
            )
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->psnPatternsRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->psnPatternsSearchResultsMock);

        $psnPatternsList = [];
        $id = 0;

        foreach ($storeData as $storeDataItem) {
            $psnFontItem = $this->getMockBuilder(PsnPatternsInterface::class)
                ->onlyMethods(['getStoreData', 'getPatternId', 'getName', 'getImage', 'getThumbnail'])
                ->getMockForAbstractClass();

            $psnFontItem->expects($this->once())
                ->method('getStoreData')
                ->with($storeId)
                ->willReturn($storeDataItem);

            $psnFontItem->expects($this->any())
                ->method('getPatternId')
                ->willReturn(++$id);

            $psnFontItem->expects($this->any())
                ->method('getName')
                ->willReturn('Default name');

            $psnFontItem->expects($this->any())
                ->method('getImage')
                ->willReturn('default_image.jpg');

            $psnFontItem->expects($this->any())
                ->method('getThumbnail')
                ->willReturn('Default thumbnail');

            $psnPatternsList[] = $psnFontItem;
        }

        $this->psnPatternsSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($psnPatternsList);

        $service = new GetPsnPatternsByStore(
            $this->searchCriteriaBuilderMock,
            $this->psnPatternsRepositoryMock
        );

        $result = $service->execute($this->storeMock);

        $this->assertIsArray($result);
        $this->assertEquals($expectedResult, $result);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                'storeId' => 1,
                'storeData' => [
                    [
                        'pattern_id' => 1,
                        'name' => 'Pattern Name Example',
                        'image' => 'pattern_image.jpg',
                        'thumbnail' => 'pattern_thumbnail.jpg',
                    ],
                ],
                'expectedResult' => [
                    [
                        'pattern_id' => 1,
                        'pattern_name' => 'Pattern Name Example',
                        'pattern_image' => 'bat_device_personalisation/patterns/pattern_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/patterns/thumbnails/pattern_thumbnail.jpg',
                    ],
                ],
            ],
            [
                'storeId' => 2,
                'storeData' => [
                    [
                        'pattern_id' => 1,
                        'name' => 'Pattern Name Example',
                        'image' => 'pattern_image.jpg',
                        'thumbnail' => 'pattern_thumbnail.jpg',
                    ],
                    [
                        'pattern_id' => 2,
                        'name' => 'Pattern Name Example 2',
                        'image' => null,
                        'thumbnail' => 'pattern_thumbnail_2.jpg',
                    ],
                    [
                        'pattern_id' => 3,
                        'name' => null,
                        'image' => null,
                        'thumbnail' => 'pattern_thumbnail_3.jpg',
                    ],
                ],
                'expectedResult' => [
                    [
                        'pattern_id' => 1,
                        'pattern_name' => 'Pattern Name Example',
                        'pattern_image' => 'bat_device_personalisation/patterns/pattern_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/patterns/thumbnails/pattern_thumbnail.jpg',
                    ],
                    [
                        'pattern_id' => 2,
                        'pattern_name' => 'Pattern Name Example 2',
                        'pattern_image' => 'bat_device_personalisation/patterns/default_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/patterns/thumbnails/pattern_thumbnail_2.jpg',
                    ],
                    [
                        'pattern_id' => 3,
                        'pattern_name' => 'Default name',
                        'pattern_image' => 'bat_device_personalisation/patterns/default_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/patterns/thumbnails/pattern_thumbnail_3.jpg',
                    ],
                ],
            ],
        ];
    }
}
