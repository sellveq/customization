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

namespace ScandiPWA\Customization\Model\Block\Source;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Block extends AbstractSource
{
    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $blocks = $this->collectionFactory
                ->create()
                ->addFieldToFilter(BlockInterface::IS_ACTIVE, ['eq' => 1])
                ->load()
                ->getItems();

            /** @var BlockInterface $block */
            foreach ($blocks as $block) {
                $this->_options[] = [
                    'value' => $block->getIdentifier(),
                    'label' => $block->getTitle()
                ];
            }

            if ($this->_options) {
                array_unshift($this->_options, ['value' => '', 'label' => __('Please select a static block.')]);
            } else {
                $this->_options = [['value' => '', 'label' => __('No static blocks to select.')]];
            }
        }

        return $this->_options;
    }
}
