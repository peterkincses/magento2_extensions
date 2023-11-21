<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Helper;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Icons extends AbstractHelper
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var AuthSession
     */
    protected $authSession;

    public function __construct(
        Context $context,
        AclRetriever $aclRetriever,
        AuthSession $authSession
    ) {
        $this->aclRetriever = $aclRetriever;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    public function userHasAllScopes(): bool
    {
        $user = $this->authSession->getUser();
        $role = $user->getRole();
        return $role->getGwsIsAll();
    }

    public function getRoleData(): array
    {
        $user = $this->authSession->getUser();
        $role = $user->getRole();
        return $role->getData();
    }
}
