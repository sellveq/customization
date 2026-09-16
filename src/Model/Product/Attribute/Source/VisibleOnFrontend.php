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

namespace ScandiPWA\Customization\Model\Product\Attribute\Source;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class VisibleOnFrontend extends AbstractSource
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $attributes = $this->collectionFactory
                ->create()
                ->setItemObjectClass(Attribute::class)
                ->addStoreLabel($this->storeManager->getStore()->getId())
                ->setOrder('position', 'ASC')
                ->addFieldToFilter('additional_table.is_visible_on_front', ['gt' => 0])
                ->load()
                ->getItems();

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                $this->_options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getStoreLabel()
                ];
            }

            if ($this->_options) {
                array_unshift($this->_options, ['value' => '', 'label' => __('Please select an attribute.')]);
            } else {
                $this->_options = [['value' => '', 'label' => __('No attributes (visible on frontend) to select.')]];
            }
        }

        return $this->_options;
    }
}
