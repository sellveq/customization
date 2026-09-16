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

namespace ScandiPWA\Customization\Model\Design\Backend;

use InvalidArgumentException;
use JsonException;
use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFileSystem;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Theme\Model\Design\Backend\Favicon as SourceFavicon;
use ScandiPWA\Customization\Controller\AppIcon;
use ScandiPWA\Customization\Controller\Webmanifest;

class Favicon extends SourceFavicon
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param UploaderFactory $uploaderFactory
     * @param RequestDataInterface $requestData
     * @param Filesystem $filesystem
     * @param UrlInterface $urlBuilder
     * @param AppIcon $appIcon
     * @param Webmanifest $webmanifest
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Database|null $databaseHelper
     * @param IoFileSystem|null $ioFileSystem
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UploaderFactory $uploaderFactory,
        RequestDataInterface $requestData,
        Filesystem $filesystem,
        UrlInterface $urlBuilder,
        private readonly AppIcon $appIcon,
        private readonly Webmanifest $webmanifest,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?Database $databaseHelper = null,
        ?IoFileSystem $ioFileSystem = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $uploaderFactory,
            $requestData,
            $filesystem,
            $urlBuilder,
            $resource,
            $resourceCollection,
            $data,
            $databaseHelper,
            $ioFileSystem
        );
    }

    /**
     * {@inheritdoc}
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $values = $this->getValue();
        $value = reset($values) ?: [];
        $isUpload = is_array($value) && !isset($value['exists']);

        parent::beforeSave();

        if ($isUpload) {
            $this->refuseNonSquareImage();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws FileSystemException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ValidatorException
     */
    public function afterSave()
    {
        $sourcePath = $this->getStoredImagePath();

        // a re-save must not rebuild, but a store whose icons were never generated gets them on the next save
        if ($sourcePath !== null && ($this->isValueChanged() || !$this->appIcon->hasGeneratedIcons())) {
            $this->appIcon->buildAppIcons($sourcePath);
            $this->webmanifest->write();
        }

        return parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedExtensions()
    {
        return ['png'];
    }

    /**
     * absolute path of the saved favicon, resolved the way core's File::afterLoad() resolves it
     * @return string|null
     * @throws ValidatorException
     */
    private function getStoredImagePath()
    {
        $value = $this->getValue();

        if (!$value || is_array($value)) {
            return null;
        }

        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $path = $this->_getUploadDir() . '/' . basename($value);

        return $this->_mediaDirectory->isFile($path) ? $this->_mediaDirectory->getAbsolutePath($path) : null;
    }

    /**
     * refuse a source the resizer would squash: keepFrame(false) makes every icon exactly square
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws ValidatorException
     */
    private function refuseNonSquareImage()
    {
        $sourcePath = $this->getStoredImagePath();

        if ($sourcePath === null) {
            return;
        }

        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $size = getimagesize($sourcePath);

        if ($size === false) {
            $this->deleteStoredImage($sourcePath);

            throw new LocalizedException(__('The favicon could not be read as an image.'));
        }

        if ($size[0] !== $size[1]) {
            $this->deleteStoredImage($sourcePath);

            throw new LocalizedException(
                __('The favicon must be square; this one is %1 by %2 pixels.', $size[0], $size[1])
            );
        }
    }

    /**
     * remove a refused upload so a rejected save leaves nothing behind
     * @param string $sourcePath
     * @return void
     * @throws FileSystemException
     * @throws ValidatorException
     */
    private function deleteStoredImage(string $sourcePath)
    {
        $this->_mediaDirectory->delete($this->_mediaDirectory->getRelativePath($sourcePath));
        $this->unsValue();
    }
}
