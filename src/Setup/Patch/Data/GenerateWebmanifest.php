<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © Scandiweb, Inc. All rights reserved.
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\Customization\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use ScandiPWA\Customization\Controller\Webmanifest as WebmanifestGenerator;
use Throwable;

class GenerateWebmanifest implements DataPatchInterface
{
    /**
     * @param WebmanifestGenerator $webmanifestGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly WebmanifestGenerator $webmanifestGenerator,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        try {
            $this->webmanifestGenerator->write();
        } catch (Throwable $e) {
            $this->logger->error(
                'ScandiPWA_Customization: the web manifest was not written: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
