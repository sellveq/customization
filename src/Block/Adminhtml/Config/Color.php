<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © 2015 Scandiweb, Ltd (http://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace ScandiPWA\Customization\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Color extends Field
{
    private const COMPONENT = 'ScandiPWA_Customization/js/color-picker';

    /**
     * @param Context $context
     * @param SecureHtmlRenderer $secureRenderer
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml($element)
    {
        $htmlId = $element->getHtmlId();

        $config = [
            'element' => '#' . $htmlId,
            'pickerElement' => '#' . $htmlId . '_picker',
            'value' => (string)$element->getData('value'),
        ];

        if ($this->_isInheritCheckboxRequired($element)) {
            $config['inheritElement'] = '#' . $htmlId . '_inherit';
        }

        $script = sprintf(
            'require([%s], function (colorPicker) { colorPicker(%s); });',
            json_encode(self::COMPONENT, JSON_THROW_ON_ERROR),
            json_encode($config, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        );

        $control = sprintf(
            '<div class="scandipwa-color-field">%s'
            . '<input type="text" class="scandipwa-color-picker" id="%s_picker" tabindex="-1" />'
            . '</div>',
            $element->getElementHtml(),
            $this->escapeHtmlAttr($htmlId)
        );

        return $control . /* @noEscape */ $this->secureRenderer->renderTag('script', [], $script, false);
    }
}
