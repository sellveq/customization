<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © 2021 Scandiweb, Ltd (http://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\Customization\Block\Page;

use JsonException;
use Magento\Backend\Block\Page\Footer as CoreFooter;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\View\Design\Theme\ListInterface;

class Footer extends CoreFooter
{
    private const string PACKAGE_JSON_FILE = 'package.json';

    // matched against the theme's full path, which getFullPath() returns as "<area>/<theme>"
    private const string SCANDIPWA_COMPONENT_NAME = 'frontend/scandipwa';

    /**
     * @var string|false
     */
    public $scandiPWAPackgeVersion;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param ListInterface $themeList
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        private readonly ListInterface $themeList,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productMetadata,
            $data
        );
    }

    /**
     * read the theme's package.json and set the version property from it
     * @return void
     */
    public function getPackageJsonData()
    {
        $pathToTheme = $this->getScandiPWADirectoryPath();

        if (!$pathToTheme) {
            $this->scandiPWAPackgeVersion = false;
            return;
        }

        // the theme registers itself from a subdirectory; package.json sits one level above it
        $packageJsonPath = dirname($pathToTheme) . '/' . self::PACKAGE_JSON_FILE;

        if (!file_exists($packageJsonPath)) {
            $this->scandiPWAPackgeVersion = 'n/a';
            return;
        }

        try {
            $packageData = json_decode(file_get_contents($packageJsonPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $this->scandiPWAPackgeVersion = 'n/a';
            return;
        }

        $this->scandiPWAPackgeVersion = $this->getScandiPWAFromPackageData($packageData);
    }

    /**
     * the registered directory of the ScandiPWA theme, or null when no installed theme is one
     * @return string|null
     */
    public function getScandiPWADirectoryPath()
    {
        $themeDirectoryPath = null;

        foreach ($this->themeList as $theme) {
            if (str_contains($theme->getFullPath(), self::SCANDIPWA_COMPONENT_NAME)) {
                $themeDirectoryPath = $this->componentRegistrar->getPath(
                    ComponentRegistrar::THEME,
                    $theme->getFullPath()
                );

                break;
            }
        }

        return $themeDirectoryPath;
    }

    /**
     * extract the ScandiPWA version from package.json data
     * @param array $data
     * @return string|false
     */
    public function getScandiPWAFromPackageData($data)
    {
        if (isset($data['dependencies']['@scandipwa/scandipwa'])) {
            return $data['dependencies']['@scandipwa/scandipwa'];
        }

        return $data['version'] ?? false;
    }

    /**
     * get the ScandiPWA theme version
     * @return string|false
     */
    public function getScandiPWAVersion()
    {
        $this->getPackageJsonData();

        return $this->scandiPWAPackgeVersion;
    }
}
