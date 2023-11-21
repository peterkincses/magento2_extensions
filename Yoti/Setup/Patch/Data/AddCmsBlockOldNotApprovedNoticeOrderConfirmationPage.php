<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block as BlockResourceModel;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store as MagentoStore;

class AddCmsBlockOldNotApprovedNoticeOrderConfirmationPage implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var BlockResourceModel
     */
    private $blockResourceModel;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        StoreRepositoryInterface $storeRepository,
        BlockResourceModel $blockResourceModel
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
        $this->blockResourceModel = $blockResourceModel;
    }

    public function apply(): void
    {

        try {
            $store = $this->storeRepository->get('vype_fr_fr_fr');
            $storeId = $store->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = MagentoStore::DEFAULT_STORE_ID;
        }

        $content = <<<CONTENT
<div class="old-account-av-notice">
    <div class="old-account-av-notice__header">
        <h2>Message important</h2>
    </div>
    <div class="old-account-av-notice__body">
        <p>
            <span>Nous vous rappelons que la vérification d’âge est nécessaire pour pouvoir continuer à passer commande.
             Bientôt les consommateurs qui n’auront pas vérifié leur compte ne pourront plus commander sur govype.
             </span>
        </p>
        <p>
            <span>Cette étape ne vous prendra pas plus de 5 minutes et aucune donnée personnelle n’est conservée.</span>
        </p>
        <p><a class="old-account-av-notice__btn pagebuilder-button-primary" href="{{store url='verification/customer/age'}}" title="Commencer">Commencer</a></p>
    </div>
</div>
CONTENT;

        $newCmsStaticBlock = [
            'title' => 'Yoti old account not approved notice order success page content',
            'identifier' => 'yoti-old-not-approved-notice-order-success-page',
            'content' => $content,
            'is_active' => 1,
            'stores' => $storeId,
        ];

        $this->moduleDataSetup->startSetup();

        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->blockFactory->create();
        $block->setData($newCmsStaticBlock);
        $this->blockResourceModel->save($block);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
