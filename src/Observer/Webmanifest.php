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

namespace ScandiPWA\Customization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use ScandiPWA\Customization\Controller\Webmanifest as WebmanifestController;
use Throwable;

class Webmanifest implements ObserverInterface
{
    /**
     * @param WebmanifestController $webmanifestController
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly WebmanifestController $webmanifestController,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        try {
            $this->webmanifestController->write();
        } catch (Throwable $e) {
            $this->logger->error(
                'ScandiPWA_Customization: the web manifest was not written: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
