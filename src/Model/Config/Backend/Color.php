<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace ScandiPWA\Customization\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class Color extends Value
{
    // the storefront writes the value straight into `#%s`, so a non-hex value reaches the page as invalid CSS
    private const PATTERN = '/^[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = (string)$this->getValue();

        // empty is allowed: a theme colour falls back to the system value and the manifest omits an empty field
        if ($value !== '' && !preg_match(self::PATTERN, $value)) {
            throw new LocalizedException(
                __('%1 must be 6 or 8 hexadecimal digits with no leading "#".', $this->getFieldLabel())
            );
        }

        return parent::beforeSave();
    }

    /**
     * the field's admin label, falling back to its configuration path
     * @return string
     */
    private function getFieldLabel()
    {
        return (string)($this->getData('field_config/label') ?: $this->getPath());
    }
}
