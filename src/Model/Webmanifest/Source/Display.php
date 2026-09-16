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

namespace ScandiPWA\Customization\Model\Webmanifest\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Display extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        return [
            ['value' => 'fullscreen', 'label' => __('Fullscreen')],
            ['value' => 'standalone', 'label' => __('Standalone')],
            ['value' => 'minimal-ui', 'label' => __('Minimal UI')],
            ['value' => 'browser', 'label' => __('Browser')],
        ];
    }
}
