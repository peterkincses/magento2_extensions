<?php

declare(strict_types=1);

namespace BAT\Yoti\Controller\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use BAT\Yoti\Model\YotiDocScanSessionRequest;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class YotiDocSession extends Action implements HttpPostActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var YotiDocScanSessionRequest
     */
    protected $yotiDocScanSessionRequest;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonSerializer $jsonSerializer,
        LoggerInterface $logger,
        YotiSession $yotiSession,
        YotiHelper $yotiHelper,
        YotiDocScanSessionRequest $yotiDocScanSessionRequest
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->yotiSession = $yotiSession;
        $this->yotiHelper = $yotiHelper;
        $this->yotiDocScanSessionRequest = $yotiDocScanSessionRequest;
        parent::__construct($context);
    }

    public function execute(): ResponseInterface
    {
        $result = [];
        if (!$this->yotiHelper->isDocScanEnabled() || !$this->yotiSession->hasData('doc_customer_id')) {
            return $this->jsonResponse(['error_message' => __('Internal Error.')]);
        }
        try {
            $result = $this->yotiDocScanSessionRequest->execute();
            if (isset($result['sessionID'])) {
                $customerId = (int) $this->yotiSession->getData('doc_customer_id');
                $this->yotiHelper->updateAccStatusDetails(
                    $customerId,
                    'started doc scan'
                );
                $this->yotiSession->setData('yoti_doc_scan_sess_id', $result['sessionID']);
            }
            if ($this->yotiHelper->isDocScanSandboxMode()) {
                $result['url'] = $this->yotiHelper->getDocScanSandboxApiUrl();
            } elseif (!$this->yotiHelper->isDocScanSandboxMode() && $this->yotiHelper->getIframeUrl()) {
                $result['url'] = $this->yotiHelper->getIframeUrl();
            }
        } catch (Exception $e) {
            $result['error_message'] = $e->getMessage();
            $this->logger->error('Yoti Generate session controller error ' . $e->getMessage());
        }

        return $this->jsonResponse($result);
    }

    /**
     * @param array $response
     * @return mixed
     */
    public function jsonResponse(array $response)
    {
        return $this->getResponse()->representJson(
            $this->jsonSerializer->serialize($response)
        );
    }
}
