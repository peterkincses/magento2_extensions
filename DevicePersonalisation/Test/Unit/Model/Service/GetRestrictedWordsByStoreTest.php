<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Test\Unit\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use BAT\DevicePersonalisation\Model\Service\GetRestrictedWordsByStore;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetRestrictedWordsByStoreTest extends TestCase
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
     * @var SearchCriteriaInterface|MockObject
     */
    protected MockObject $searchCriteriaMock;

    /**
     * @var PsnRestrictedWordsRepositoryInterface|MockObject
     */
    protected MockObject $psnRestrictedWordsRepositoryMock;

    /**
     * @var PsnRestrictedWordsSearchResultsInterface|MockObject
     */
    protected MockObject $psnRestrictedWordsSearchResultsMock;

    protected function setUp(): void
    {
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFilter', 'create'])
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->psnRestrictedWordsRepositoryMock = $this->getMockBuilder(PsnRestrictedWordsRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMockForAbstractClass();

        $this->psnRestrictedWordsSearchResultsMock = $this->getMockBuilder(PsnRestrictedWordsSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems'])
            ->getMockForAbstractClass();
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        int $itemCount,
        array $expected
    ): void {
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('store_id', 1)
            ->willReturnSelf();

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);

        $this->psnRestrictedWordsRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->psnRestrictedWordsSearchResultsMock);

        $psnItems = [];

        foreach (range(1, $itemCount) as $i) {
            $psnItemMock = $this->getMockBuilder(PsnRestrictedWordsInterface::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getRestrictedWord'])
                ->getMockForAbstractClass();

            $psnItemMock->expects($this->once())
                ->method('getRestrictedWord')
                ->willReturn('word' . $i);

            $psnItems[] = $psnItemMock;
        }

        $this->psnRestrictedWordsSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn($psnItems);

        $service = new GetRestrictedWordsByStore(
            $this->searchCriteriaBuilderMock,
            $this->psnRestrictedWordsRepositoryMock
        );

        $this->assertEquals($expected, $service->execute($this->storeMock));
    }

    public function executeDataProvider(): array
    {
        return [
            [
                'itemCount' => 3,
                'expected' => ['word1', 'word2', 'word3'],
            ],
        ];
    }
}
