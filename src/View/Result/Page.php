<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_Customization
 * @copyright   Copyright © Scandiweb, Inc. All rights reserved.
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\Customization\View\Result;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\EntitySpecificHandlesList;
use Magento\Framework\View\Layout\BuilderFactory;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Page\Config\RendererFactory;
use Magento\Framework\View\Page\Layout\Reader;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use ScandiPWA\Customization\Controller\AppIcon;
use ScandiPWA\Locale\View\Result\Page as LocalePage;

class Page extends LocalePage
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     * @param Context $context
     * @param LayoutFactory $layoutFactory
     * @param ReaderPool $layoutReaderPool
     * @param InlineInterface $translateInline
     * @param BuilderFactory $layoutBuilderFactory
     * @param GeneratorPool $generatorPool
     * @param RendererFactory $pageConfigRendererFactory
     * @param Reader $pageLayoutReader
     * @param DirectoryList $directoryList
     * @param Json $json
     * @param string $template
     * @param AppIcon $appIcon
     * @param bool $isIsolated
     * @param EntitySpecificHandlesList|null $entitySpecificHandlesList
     * @param string|null $action
     * @param array $rootTemplatePool
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        ResolverInterface $localeResolver,
        Context $context,
        LayoutFactory $layoutFactory,
        ReaderPool $layoutReaderPool,
        InlineInterface $translateInline,
        BuilderFactory $layoutBuilderFactory,
        GeneratorPool $generatorPool,
        RendererFactory $pageConfigRendererFactory,
        Reader $pageLayoutReader,
        DirectoryList $directoryList,
        protected readonly Json $json,
        string $template,
        protected readonly AppIcon $appIcon,
        $isIsolated = false,
        ?EntitySpecificHandlesList $entitySpecificHandlesList = null,
        $action = null,
        $rootTemplatePool = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();

        parent::__construct(
            $localeResolver,
            $context,
            $layoutFactory,
            $layoutReaderPool,
            $translateInline,
            $layoutBuilderFactory,
            $generatorPool,
            $pageConfigRendererFactory,
            $pageLayoutReader,
            $template,
            $directoryList,
            $isIsolated,
            $entitySpecificHandlesList,
            $action,
            $rootTemplatePool
        );
    }

    /**
     * get config by section name
     * @param string $sectionName
     * @return mixed a section as an array, a single path as its scalar value, null when unset
     * @throws NoSuchEntityException
     */
    public function getThemeConfiguration(string $sectionName)
    {
        return $this->scopeConfig->getValue(
            $sectionName,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * get store list json
     * @return string
     * @throws InvalidArgumentException
     */
    public function getStoreListJson()
    {
        $result = [];
        $storeList = $this->storeManager->getStores();

        foreach ($storeList as $store) {
            $result[] = $store->getCode();
        }

        return $this->json->serialize($result);
    }

    /**
     * get app icon links data
     * @return array[]
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function getAppIconData()
    {
        return $this->appIcon->getIconLinks();
    }

    /**
     * get current website code
     * @return string
     * @throws LocalizedException
     */
    public function getWebsiteCode()
    {
        return $this->storeManager->getWebsite()->getCode();
    }

    /**
     * get current store currency code
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStoreCurrency()
    {
        return $this->storeManager->getStore()
            ->getCurrentCurrency()
            ->getCode();
    }
}
