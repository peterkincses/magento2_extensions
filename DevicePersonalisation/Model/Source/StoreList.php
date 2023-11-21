<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Source;

use Magento\Framework\Option\ArrayInterface as ArrayInterfaceAlias;
use Magento\Store\Model\GroupFactory as GroupFactory;
use Magento\Store\Model\WebsiteFactory as WebsiteFactory;

class StoreList implements ArrayInterfaceAlias
{
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    public function __construct(
        GroupFactory $groupFactory,
        WebsiteFactory $websiteFactory
    ) {
        $this->groupFactory = $groupFactory;
        $this->websiteFactory = $websiteFactory;
    }

    /**
     * @return mixed[]
     */
    public function toOptionArray(): array
    {
        $websites = $this->websiteFactory->create()->getCollection();
        $allgroups = $this->groupFactory->create()->getCollection();
        $groups = [];

        foreach ($websites as $website) {
            $values = [];
            foreach ($allgroups as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $values[] = ['label' => $group->getName(), 'value' => $group->getId()];
                }
            }
            $groups[] = ['label' => $website->getName(), 'value' => $values];
        }

        return $groups;
    }
}
