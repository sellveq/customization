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

namespace ScandiPWA\Customization\Controller;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class AppIcon
{
    private const string REFERENCE_IMAGE_PATH = 'favicon/favicon.png';

    private const string STORAGE_PATH = 'favicon/icons/';

    private const array IMAGE_RESIZING_CONFIG = [
        'ios' => [
            'type' => 'ios',
            'sizes' => [120, 152, 167, 180, 1024]
        ],
        'ios_startup' => [
            'type' => 'ios_startup',
            'sizes' => [2048, 1668, 1536, 1125, 1242, 750, 640]
        ],
        'android' => [
            'type' => 'android',
            'sizes' => [36, 48, 72, 96, 144, 192, 512]
        ]
    ];

    /**
     * @param Filesystem $fileSystem
     * @param AdapterFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Filesystem $fileSystem,
        private readonly AdapterFactory $imageFactory,
        private readonly StoreManagerInterface $storeManager
    ) {}

    /**
     * save a resized image under the given name
     * @param string $source
     * @param string $name
     * @param int|null $width
     * @param int|null $height
     * @return void
     * @throws InvalidArgumentException
     * @throws ValidatorException
     */
    protected function saveImage($source, $name, $width = null, $height = null)
    {
        $path = $this->fileSystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(self::STORAGE_PATH) . $name . '.png';

        $this->saveImageWithPath($source, $path, $width, $height);
    }

    /**
     * resize and save an image to the given path
     * @param string $source
     * @param string $targetPath
     * @param int|null $width
     * @param int|null $height
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function saveImageWithPath($source, $targetPath, $width = null, $height = null)
    {
        if (!file_exists($source) || !is_file($source)) {
            return false;
        }

        $imageResize = $this->imageFactory->create();
        $imageResize->open($source);
        $imageResize->keepTransparency(true);

        // every declared size is produced, upscaling a small source: a platform ignores an icon smaller than it claims
        if ($width !== null && $height !== null) {
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(false);
            $imageResize->resize($width, $height);
        }

        try {
            $imageResize->save($targetPath);
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * names of the icons that exist under the storage path
     * @return array
     * @throws ValidatorException
     */
    protected function getGeneratedIconNames()
    {
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $names = [];

        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        foreach ($mediaDirectory->search(self::STORAGE_PATH . '*.png') as $path) {
            $names[basename($path, '.png')] = true;
        }

        return $names;
    }

    /**
     * whether any icon has been generated yet
     * @return bool
     * @throws ValidatorException
     */
    public function hasGeneratedIcons()
    {
        return (bool)count($this->getGeneratedIconNames());
    }

    /**
     * get icon data for web manifest generation
     * @return array
     * @throws ValidatorException
     */
    public function getIconData()
    {
        $generated = $this->getGeneratedIconNames();
        $output = [];

        foreach (self::IMAGE_RESIZING_CONFIG as $config) {
            foreach ($config['sizes'] as $size) {
                $name = 'icon_' . $config['type'] . '_' . $size . 'x' . $size;
                if (!isset($generated[$name])) {
                    continue;
                }

                $output[] = [
                    // relative to the manifest's own URL under media/webmanifest/
                    'src' => '../' . self::STORAGE_PATH . $name . '.png',
                    'type' => 'image/png',
                    'sizes' => $size . 'x' . $size,
                    'purpose' => 'any'
                ];
            }
        }

        return $output;
    }

    /**
     * get icon links keyed by type and size
     * @return array[]
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function getIconLinks()
    {
        $output = [
            'icon' => [],
            'ios' => [],
            'ios_startup' => [],
            'android' => []
        ];

        $generated = $this->getGeneratedIconNames();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::STORAGE_PATH;

        foreach (self::IMAGE_RESIZING_CONFIG as $type => $config) {
            foreach ($config['sizes'] as $size) {
                $name = 'icon_' . $config['type'] . '_' . $size . 'x' . $size;
                if (!isset($generated[$name])) {
                    continue;
                }

                $link = [
                    'href' => $baseUrl . $name . '.png',
                    'sizes' => $size . 'x' . $size
                ];
                $output[$type][$size . 'x' . $size] = $link;
                $output['icon'][$size . 'x' . $size] = $link;
            }
        }

        return $output;
    }

    /**
     * write the reference favicon the resized icons are cut from
     * @param string $sourcePath
     * @return void
     * @throws InvalidArgumentException
     * @throws ValidatorException
     */
    private function buildFaviconImage(string $sourcePath)
    {
        $targetPath = $this->fileSystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath(self::REFERENCE_IMAGE_PATH);

        $this->saveImageWithPath($sourcePath, $targetPath);
    }

    /**
     * build all app icon variants from the given source image
     * @param string $sourcePath
     * @return void
     * @throws InvalidArgumentException
     * @throws ValidatorException
     */
    public function buildAppIcons(string $sourcePath)
    {
        if (!file_exists($sourcePath)) {
            return;
        }

        $this->buildFaviconImage($sourcePath);

        foreach (self::IMAGE_RESIZING_CONFIG as $config) {
            foreach ($config['sizes'] as $size) {
                $this->saveImage($sourcePath, 'icon_' . $config['type'] . '_' . $size . 'x' . $size, $size, $size);
            }
        }

        // Glob caches per process, so without this a reader that globbed earlier still sees no icons
        Glob::clearCache();
    }
}
