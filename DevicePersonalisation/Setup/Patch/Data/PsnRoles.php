<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\Acl\AclRetriever;
use BAT\Setup\Setup\Patch\Data\AdminRolesPatch;

class PsnRoles implements DataPatchInterface
{
    /**
     * @var roleFactory
     */
    private $roleFactory;

    /**
     * @var rulesFactory
     */
    private $rulesFactory;

    /**
     * @var AclRetriever
     */
    private $aclRetriever;

    public function __construct(
        RoleFactory $roleFactory,
        RulesFactory $rulesFactory,
        AclRetriever $aclRetriever
    ) {
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
        $this->aclRetriever = $aclRetriever;
    }

    public function apply(): void
    {
        $resource = [
            'BAT_DevicePersonalisation::personalisation',
            'BAT_DevicePersonalisation::font',
            'BAT_DevicePersonalisation::font_edit',
            'BAT_DevicePersonalisation::icon',
            'BAT_DevicePersonalisation::icon_edit',
            'BAT_DevicePersonalisation::pattern',
            'BAT_DevicePersonalisation::pattern_edit',
            'BAT_DevicePersonalisation::restricted_word',
            'BAT_DevicePersonalisation::restricted_word_create',
            'BAT_DevicePersonalisation::restricted_word_edit',
            'BAT_DevicePersonalisation::restricted_word_delete',
        ];

        $roles = [
            'Admin restricted',
            'Admin restricted (Vype UK)',
            'Admin restricted (Epok Germany)',
            'Admin restricted (Glo South Korea)',
            'Admin restricted (Lyft Austria)',
            'Admin restricted (Vype & Lyft DK)',
            'Admin restricted (Vype NL)',
            'Admin restricted (Vype DK & NL & Lyft SE & DK)',
            'Admin Restricted (Italy)',
            'Admin Restricted (Vype FR)',
            'Admin restricted (Glo & Vype & VELO DE)',
            'Admin restricted (Vype DE)',
            'Admin Restricted (Glo & Velo DE)',
            'Admin restricted (LYFT SE)',
            'Admin Restricted (Vype DK & Lyft SE & DK)',
            'Admin restricted (Vype IE)',
            'Admin restricted (Vype UK & IE)',
            'Admin Restricted (Velo NG)',
            'Admin Restricted (Glo KZ)',
            'Admin Restricted (Glo PL)',
        ];

        foreach ($roles as $roleName) {
            $role = $this->roleFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('role_name', $roleName)
                            ->getFirstItem();
            if ($role->getId()) {
                $mergedRec = [];
                $allowedResources = $this->aclRetriever->getAllowedResourcesByRole($role->getId());
                $mergedRec = array_merge($allowedResources, $resource);
                $this->rulesFactory->create()->setRoleId($role->getId())->setResources($mergedRec)->saveRel();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies()
    {
        return [
            AdminRolesPatch::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return [];
    }
}
