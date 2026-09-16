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

use JsonException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;

class Webmanifest
{
    private const string WEBMANIFEST_CONFIG_PATH = 'webmanifest_customization/webmanifest/';

    private const string STORAGE_PATH = 'webmanifest/manifest.json';

    private const array ALLOWED_FIELDS = [
        'name',
        'short_name',
        'description',
        'background_color',
        'lang',
        'theme_color',
        'start_url',
        'orientation',
        'display',
        'categories',
        'dir',
        'iarc_rating_id',
        'icons',
        'prefer_related_applications',
        'related_applications',
        'scope',
        'screenshots',
        'serviceworker',
        'shortcuts'
    ];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $fileSystem
     * @param AppIcon $appIcon
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Filesystem $fileSystem,
        private readonly AppIcon $appIcon
    ) {}

    /**
     * generate JSON string from allowed web manifest fields
     * @param array $data
     * @return string|false
     * @throws JsonException
     */
    protected function getGeneratedJson(array $data)
    {
        $arrayKeys = array_keys($data);
        if (empty($arrayKeys)) {
            return false;
        }

        $unSupportedKeys = array_filter($arrayKeys, function ($key) {
            return !in_array($key, self::ALLOWED_FIELDS);
        });

        foreach ($unSupportedKeys as $unSupportedKey) {
            unset($data[$unSupportedKey]);
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * save web manifest JSON data to media storage
     * @param array $data
     * @return void
     * @throws FileSystemException
     * @throws JsonException
     * @throws ValidatorException
     */
    public function saveJson(array $data)
    {
        $jsonData = $this->getGeneratedJson($data);

        if (!$jsonData || empty($data)) {
            return;
        }

        $fileWriter = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA);

        $fileWriter->writeFile(self::STORAGE_PATH, $jsonData);
    }

    /**
     * the one writer: the data patch, the section observer and the favicon backend model all call it
     * @return void
     * @throws FileSystemException
     * @throws JsonException
     * @throws ValidatorException
     */
    public function write()
    {
        $data = $this->load();
        $data['icons'] = $this->appIcon->getIconData();

        $this->saveJson($data);
    }

    /**
     * load web manifest configuration values
     * @return array
     */
    public function load()
    {
        $data = [];
        foreach (self::ALLOWED_FIELDS as $field) {
            $value = $this->scopeConfig->getValue(self::WEBMANIFEST_CONFIG_PATH . $field);
            if (!empty($value)) {
                if (in_array($field, ['background_color', 'theme_color'])) {
                    $value = '#' . $value;
                }
                $data[$field] = $value;
            }
        }

        return $data;
    }
}
