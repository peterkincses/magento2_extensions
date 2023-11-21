<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Column;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RestrictedWordsActions extends Column
{
    public const PERSONALISATION_URL_PATH_EDIT = 'bat_personalisation/restrictedwords/edit';

    public const PERSONALISATION_URL_PATH_DELETE = 'bat_personalisation/restrictedwords/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->authorization = $authorization;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['word_id'])) {
                    if ($this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_edit')) {
                        $item[$name]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::PERSONALISATION_URL_PATH_EDIT,
                                ['word_id' => $item['word_id'], 'store' => $storeId]
                            ),
                            'label' => __('Edit'),
                        ];
                    }
                    if ($this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_delete')) {
                        $item[$name]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::PERSONALISATION_URL_PATH_DELETE,
                                ['word_id' => $item['word_id']]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete ${ $.$data.restricted_word }'),
                                'message' => __('Are you sure you want to delete the word ${ $.$data.restricted_word }?'),
                            ],
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }

    public function prepare(): void
    {
        parent::prepare();

        $canEdit = $this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_edit');
        $canDelete = $this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_delete');

        if (!($canEdit || $canDelete)) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
