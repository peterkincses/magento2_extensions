<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Test\Unit\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnFontsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use BAT\DevicePersonalisation\Model\Service\GetPsnFontsByStore;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetPsnFontsByStoreTest extends TestCase
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
     * @var PsnFontsRepositoryInterface|MockObject
     */
    protected MockObject $psnFontsRepositoryMock;

    /**
     * @var PsnFontsSearchResultsInterface|MockObject
     */
    protected MockObject $psnFontsSearchResultsMock;

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

        $this->psnFontsRepositoryMock = $this->getMockBuilder(PsnFontsRepositoryInterface::class)
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();

        $this->psnFontsSearchResultsMock = $this->getMockBuilder(PsnFontsSearchResultsInterface::class)
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

        $this->psnFontsRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->psnFontsSearchResultsMock);

        $psnFontsList = [];
        $id = 0;

        foreach ($storeData as $storeDataItem) {
            $psnFontItem = $this->getMockBuilder(PsnFontsInterface::class)
                ->onlyMethods(['getStoreData', 'getFontId', 'getName', 'getFontSize', 'getPreviewText'])
                ->getMockForAbstractClass();

            $psnFontItem->expects($this->once())
                ->method('getStoreData')
                ->with($storeId)
                ->willReturn($storeDataItem);

            $psnFontItem->expects($this->any())
                ->method('getFontId')
                ->willReturn(++$id);

            $psnFontItem->expects($this->any())
                ->method('getName')
                ->willReturn('Default font name');

            $psnFontItem->expects($this->any())
                ->method('getFontSize')
                ->willReturn('Default font size');

            $psnFontItem->expects($this->any())
                ->method('getPreviewText')
                ->willReturn('Default preview text');

            $psnFontsList[] = $psnFontItem;
        }

        $this->psnFontsSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($psnFontsList);

        $service = new GetPsnFontsByStore(
            $this->searchCriteriaBuilderMock,
            $this->psnFontsRepositoryMock
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
                        'font_id' => 1,
                        'name' => 'Arial',
                        'font_size' => '12px',
                        'preview_text' => 'Sample description',
                        'font_file' => 'font.ttf',
                    ],
                ],
                'expectedResult' => [
                    [
                        'font_id' => 1,
                        'font_name' => 'Arial',
                        'font_size' => '12px',
                        'preview_text' => 'Sample description',
                        'font' => 'bat_device_personalisation/fonts/font.ttf',
                    ],
                ],
            ],
            [
                'storeId' => 2,
                'storeData' => [
                    [
                        'font_id' => 1,
                        'name' => 'Arial',
                        'font_size' => '12px',
                        'preview_text' => 'Sample description',
                        'font_file' => 'font.ttf',
                    ],
                    [
                        'font_id' => 2,
                        'name' => 'Tahoma',
                        'font_size' => null,
                        'preview_text' => 'Preview description',
                        'font_file' => 'font.ttf',
                    ],
                    [
                        'font_id' => 3,
                        'name' => null,
                        'font_size' => '16px',
                        'preview_text' => null,
                        'font_file' => 'font.ttf',

                    ],
                ],
                'expectedResult' => [
                    [
                        'font_id' => 1,
                        'font_name' => 'Arial',
                        'font_size' => '12px',
                        'preview_text' => 'Sample description',
                        'font' => 'bat_device_personalisation/fonts/font.ttf',
                    ],
                    [
                        'font_id' => 2,
                        'font_name' => 'Tahoma',
                        'font_size' => 'Default font size',
                        'preview_text' => 'Preview description',
                        'font' => 'bat_device_personalisation/fonts/font.ttf',
                    ],
                    [
                        'font_id' => 3,
                        'font_name' => 'Default font name',
                        'font_size' => '16px',
                        'preview_text' => 'Default preview text',
                        'font' => 'bat_device_personalisation/fonts/font.ttf',
                    ],
                ],
            ],
        ];
    }
}
