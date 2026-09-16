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

class Orientation extends AbstractSource
{
    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        return [
            ['value' => 'any', 'label' => __('Any')],
            ['value' => 'natural', 'label' => __('Natural')],
            ['value' => 'landscape', 'label' => __('Landscape')],
            ['value' => 'landscape-primary', 'label' => __('Landscape Primary')],
            ['value' => 'landscape-secondary', 'label' => __('Landscape Secondary')],
            ['value' => 'portrait', 'label' => __('Portrait')],
            ['value' => 'portrait-primary', 'label' => __('Portrait Primary')],
            ['value' => 'portrait-secondary', 'label' => __('Portrait Secondary')],
        ];
    }
}
