<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store as MagentoStore;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;

class UpdateCmsBlockOldNotApprovedNoticeForGloPoland implements DataPatchInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BlockRepositoryInterface $blockRepository,
        StoreRepositoryInterface $storeRepository,
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [AddCmsBlockOldNotApprovedNoticeForGloPoland::class];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        try {
            $store = $this->storeRepository->get('glo_pl_pl_pl');
            $storeId = $store->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = MagentoStore::DEFAULT_STORE_ID;
        }
        /** @var \Magento\Cms\Model\Block $cmsBlock */
        $cmsBlock = $this->blockFactory->create();

        $identifier = 'yoti-old-not-approved-notice';
        $content = <<<CONTENT
<div class="old-account-av-notice">
    <div class="old-account-av-notice__header">
        <h2>Ważna wiadomość</h2>
    </div>
    <div class="old-account-av-notice__body">
        <p><span>Działając odpowiedzialnie dokładamy wszelkich starań, aby nasze produkty nie trafiały do osób nieletnich.Z tego powodu musimy być pewni, że skończyłeś/aś 18 lat, abyś mógł/a sfinalizować swoje zamówienie.Prosimy, potwierdź swój wiek. Potrzebny będzie do tego aparat lub kamera w komputerze lub telefonie.</span></p> 
		<p><span>Zdjęcie dokumentu nie jest zapisywane, a niezbędne dane (data urodzenia oraz imię i nazwisko) są weryfikowane przez automatyczny algorytm rozpoznawania obrazu w czasie rzeczywistym.</p></span>
		<p><span>Więcej informacji znajdziesz w naszej <a target="_blank title="Polityce Prywatności" href="{{store url='polityka-prywatnosci'}}">Polityce Prywatności</a>.</span></p>

        <p><a class="old-account-av-notice__btn pagebuilder-button-primary" href="{{store url='verification/customer/age'}}" title="Rozpocznij proces weryfikacji">Rozpocznij proces weryfikacji</a></p>
    </div>
</div>
CONTENT;

        $this->searchCriteriaBuilder->addFilter('identifier', $identifier);
        $this->searchCriteriaBuilder->addFilter('store_id', $storeId);

        $cmsBlockData = $this->blockRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        $blockId = '';
        if (count($cmsBlockData) > 0) {
            foreach ($cmsBlockData as $blockInfo) {
                $blockId = (int) $blockInfo->getBlockId();
            }

            try {
                $this->blockRepository->getById($blockId);
            } catch (LocalizedException $exception) {
                $this->logger->critical('UpdateCmsBlockOldNotApprovedNoticeForGloPoland: ' . $e->getMessage(), ['exception' => $e]);
            }

            $data['block_id'] = $blockId;
            $data['content'] = $content;
            $data['_first_store_id'] = $storeId;
            $data['store_id'] = [$storeId];
            $cmsBlock->setData($data);

            $this->moduleDataSetup->startSetup();
            try {
                $this->blockRepository->save($cmsBlock);
            } catch (LocalizedException $e) {
                $this->logger->critical('UpdateCmsBlockOldNotApprovedNoticeForGloPoland: ' . $e->getMessage(), ['exception' => $e]);
            }
            $this->moduleDataSetup->endSetup();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
