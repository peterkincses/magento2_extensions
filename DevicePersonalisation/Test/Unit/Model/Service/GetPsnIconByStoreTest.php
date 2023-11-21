<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Test\Unit\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface;
use BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterface;
use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\Service\GetPsnIconByStore;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetPsnIconByStoreTest extends TestCase
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
     * @var PsnIconRepositoryInterface|MockObject
     */
    protected MockObject $psnIconRepositoryMock;

    /**
     * @var PsnIconSearchResultsInterface|MockObject
     */
    protected MockObject $psnIconSearchResultsMock;

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

        $this->psnIconRepositoryMock = $this->getMockBuilder(PsnIconRepositoryInterface::class)
            ->onlyMethods(['getList', 'getIconsByStoreId'])
            ->getMockForAbstractClass();

        $this->psnIconSearchResultsMock = $this->getMockBuilder(PsnIconSearchResultsInterface::class)
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

        $this->psnIconRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->psnIconSearchResultsMock);

        $psnIconList = [];
        $getIconsByStoreIdArgs = [];
        $id = 0;

        foreach ($storeData as ['name' => $name, 'image' => $image, 'thumbnail' => $thumbnail]) {
            $psnFontItem = $this->getMockBuilder(PsnIconDataInterface::class)
                ->onlyMethods(['getIconName', 'getImage', 'getThumbnail', 'getIconId'])
                ->getMockForAbstractClass();

            $psnFontItem->expects($this->any())
                ->method('getIconId')
                ->willReturn(++$id);

            $psnFontItem->expects($this->any())
                ->method('getIconName')
                ->willReturn('Default name');

            $psnFontItem->expects($this->any())
                ->method('getImage')
                ->willReturn('default_image.jpg');

            $psnFontItem->expects($this->any())
                ->method('getThumbnail')
                ->willReturn('default_thumbnail.jpg');

            $psnIconList[] = $psnFontItem;
            $getIconsByStoreIdArgs[] = [$storeId, $id];
        }

        $this->psnIconSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($psnIconList);

        $this->psnIconRepositoryMock
            ->method('getIconsByStoreId')
            ->withConsecutive(...$getIconsByStoreIdArgs)
            ->willReturnOnConsecutiveCalls(...$storeData);

        $service = new GetPsnIconByStore(
            $this->searchCriteriaBuilderMock,
            $this->psnIconRepositoryMock
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
                        'icon_id' => 1,
                        'name' => 'Icon Name Example',
                        'image' => 'icon_image.jpg',
                        'thumbnail' => 'icon_thumbnail.jpg',
                    ],
                ],
                'expectedResult' => [
                    [
                        'icon_id' => 1,
                        'icon_name' => 'Icon Name Example',
                        'icon_image' => 'bat_device_personalisation/icons/icon_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/icons/thumbnails/icon_thumbnail.jpg',
                    ],
                ],
            ],
            [
                'storeId' => 2,
                'storeData' => [
                    [
                        'icon_id' => 1,
                        'name' => 'Icon Name Example 1',
                        'image' => 'icon_image_1.jpg',
                        'thumbnail' => 'icon_thumbnail_1.jpg',
                    ],
                    [
                        'icon_id' => 2,
                        'name' => null,
                        'image' => 'icon_image_2.jpg',
                        'thumbnail' => 'icon_thumbnail_2.jpg',
                    ],
                    [
                        'icon_id' => 3,
                        'name' => 'Icon Name Example 3',
                        'image' => null,
                        'thumbnail' => null,
                    ],
                ],
                'expectedResult' => [
                    [
                        'icon_id' => 1,
                        'icon_name' => 'Icon Name Example 1',
                        'icon_image' => 'bat_device_personalisation/icons/icon_image_1.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/icons/thumbnails/icon_thumbnail_1.jpg',
                    ],
                    [
                        'icon_id' => 2,
                        'icon_name' => 'Default name',
                        'icon_image' => 'bat_device_personalisation/icons/icon_image_2.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/icons/thumbnails/icon_thumbnail_2.jpg',
                    ],
                    [
                        'icon_id' => 3,
                        'icon_name' => 'Icon Name Example 3',
                        'icon_image' => 'bat_device_personalisation/icons/default_image.jpg',
                        'thumbnail_image' => 'bat_device_personalisation/icons/thumbnails/default_thumbnail.jpg',
                    ],
                ],
            ],
        ];
    }
}
