<?php

/** @noinspection PhpUndefinedClassInspection */

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Block;

use Magenerds\RichSnippet\Helper\Data;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class SchemaOrg
 *
 * @package     Magenerds\RichSnippet\Block
 * @file        SchemaOrg.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 * @author      Belinda Tschampel <b.tschampel@techdivision.com>
 */
class SchemaOrg extends Template // NOSONAR
{
    /**
     * Schema domain
     *
     * @var string
     */
    const SCHEMA_DOMAIN = 'https://schema.org/';

    /**
     * Worst rating
     *
     * @var int
     */
    const AGGREGATE_RATING_WORST_RATING = 1;

    /**
     * Best rating
     *
     * @var int
     */
    const AGGREGATE_RATING_BEST_RATING = 5;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var SummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logo
     */
    protected $logo;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var ResourceConnection
     */
    protected $connection;
    /** @var UrlFinderInterface */
    private $urlFinder;
    /** @var Manager */
    private $moduleManager;

    /**
     * SchemaOrg constructor.
     *
     * @param Registry $registry
     * @param SummaryFactory $reviewSummaryFactory
     * @param Data $helper
     * @param Logo $logo
     * @param Context $context
     * @param EventManager $eventManager
     * @param ResourceConnection $connection
     * @param UrlFinderInterface $urlFinder
     * @param array $data
     * @param ?Manager $moduleManager
     */
    public function __construct(
        Registry $registry,
        SummaryFactory $reviewSummaryFactory,
        Data $helper,
        Logo $logo,
        Context $context,
        EventManager $eventManager,
        ResourceConnection $connection,
        UrlFinderInterface $urlFinder,
        $data = [],
        Manager $moduleManager = null
    ) {
        $this->coreRegistry = $registry;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->helper = $helper;
        $this->logo = $logo;
        $this->eventManager = $eventManager;
        $this->connection = $connection;
        parent::__construct($context, $data);
        $this->urlFinder = $urlFinder;
        $this->moduleManager = $moduleManager ?: ObjectManager::getInstance()->get(Manager::class);
    }

    /**
     * Check if a value is a string and not empty.
     *
     * @param $value
     * @return bool
     */
    protected function valueIsSet($value)
    {
        return is_string($value) && strlen(trim($value));
    }

    /**
     * Retrieve current product model
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Logo image url if set to active
     *
     * @return string
     */
    protected function getLogo()
    {
        $logoUrl = '';
        if ($this->helper->getLogoConfig()) {
            $logoUrl = $this->logo->getLogoSrc();
        }
        return $logoUrl;
    }

    /**
     * Short or long description depending on configuration
     *
     * @return string
     */
    protected function getDescription()
    {
        if ($this->helper->getDescriptionType()) {
            return nl2br($this->getProduct()->getData('description') ?? '');
        } else {
            return nl2br($this->getProduct()->getData('short_description') ?? '');
        }
    }

    /**
     * Get store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @return Summary
     * @throws NoSuchEntityException
     */
    protected function getReviewSummary()
    {
        /** @var Summary $reviewSummary */
        $reviewSummary = $this->reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $this->getStore()->getId());
        /** @noinspection PhpDeprecationInspection */
        return $reviewSummary->load($this->getProduct()->getId());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getCurrencyCode()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Specific color name for product
     *
     * @return string
     */
    protected function getColor()
    {
        $colorAttribute = $this->helper->getColorConfig();

        return $this->getAttribute($colorAttribute);
    }

    /**
     * Sku value for product
     *
     * @return string
     */
    protected function getSku()
    {
        $skuAttribute = $this->helper->getSkuConfig();

        return $this->getAttribute($skuAttribute);
    }

    /**
     * Product id value for product
     *
     * @return string
     */
    protected function getProductId()
    {
        $productIdAttribute = $this->helper->getProductIdConfig();

        return $this->getAttribute($productIdAttribute);
    }

    /**
     * Specific brand name for product
     *
     * @return string
     */
    protected function getBrand()
    {
        $brandAttribute = $this->helper->getBrandConfig();

        return $this->getAttribute($brandAttribute);
    }

    /**
     * Get page
     *
     * @return string
     */
    protected function getPage()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getRequest()->getFullActionName();
    }

    /**
     * Retrieve current category model
     *
     * @return Category|null
     */
    protected function getCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Get category rating
     *
     * @return array|bool
     * @throws NoSuchEntityException
     */
    protected function getCategoryRating()
    {
        // set default return value
        $defaultReturn = false;

        if ($this->getCategory() === null) {
            return $defaultReturn;
        }

        // get products
        if (!($productIds = $this->getCategory()->getProductCollection()->getAllIds())) {
            return $defaultReturn;
        }

        // get rating data
        $select = $this->connection->getConnection()->select()
            ->from('rating_option_vote_aggregated')
            ->where('entity_pk_value IN (?)', $productIds)
            ->where('store_id IN (?)', [Store::DEFAULT_STORE_ID, $this->getStore()->getId()])
            ->group('entity_pk_value')
            ->order('store_id ' . Collection::SORT_ORDER_DESC);

        // select from sub-select
        $select = join(' ', [
            'SELECT',
            join(', ', [
                'COUNT(vote_count) as item_count',
                'SUM(vote_count) as vote_count',
                'SUM(vote_value_sum) as vote_sum',
                'SUM(percent_approved) as percent_approved'
            ]),
            'FROM',
            '(' . (string)$select . ') main'
        ]);

        // get data
        $data = $this->connection->getConnection()->fetchRow($select);

        // calculate average
        if (!$data || !$data['item_count'] || !isset($data['item_count'])) {
            return $defaultReturn;
        }

        // return average rating and count
        $count = $data['vote_count'] * ($data['percent_approved'] / $data['item_count'] / 100);
        $sum = $data['vote_sum'] * ($data['percent_approved'] / $data['item_count'] / 100);
        $avg = $count ? ($sum / $count) : 0;
        $avg = max($avg, static::AGGREGATE_RATING_WORST_RATING);
        $avg = min($avg, static::AGGREGATE_RATING_BEST_RATING);
        return [round($avg, 2), floor($count)];
    }

    /**
     * @return array
     */
    public function getOrganizationSchema()
    {
        $organization = [];
        $address = [];
        $brand = [];

        $address = $this->getOrganizationSchemaData($address, 'company_street', 'streetAddress');
        $address = $this->getOrganizationSchemaData($address, 'company_postalcode', 'postalCode');
        $address = $this->getOrganizationSchemaData($address, 'company_location', 'addressLocality');
        if (!empty($address)) {
            $address['@type'] = 'PostalAddress';
            $organization['address'] = $address;
        }

        $brand = $this->getOrganizationSchemaData($brand, 'company_brand', 'name');
        if (!empty($brand)) {
            $brand['@type'] = 'Thing';
            $organization['brand'] = $brand;
        }

        $organization = $this->getOrganizationSchemaData($organization, 'company_name', 'name');
        $organization = $this->getOrganizationSchemaData($organization, 'company_email', 'email');
        $organization = $this->getOrganizationSchemaData($organization, 'company_fax', 'faxNumber');
        $organization = $this->getOrganizationSchemaData($organization, 'company_phone', 'telephone');
        $organization = $this->getOrganizationSchemaData($organization, 'company_founding_date', 'foundingDate');
        $organization = $this->getOrganizationSchemaData($organization, 'company_url', 'url');
        $organization = $this->getOrganizationSchemaData($organization, 'company_logo', 'logo');

        if (!empty($organization)) {
            $organization['@context'] = static::SCHEMA_DOMAIN;
            $organization['@type'] = 'Organization';
        }

        $organizationData = new DataObject($organization);
        $this->eventManager->dispatch('organization_schema_add_as_last', ['organizationSchema' => $organizationData]);
        return $organizationData->convertToArray();
    }

    /**
     * Get schema depending on page
     *
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function getSchema()
    {
        // add ratings
        if (!$this->helper->getSchemaEnable()) {
            return false;
        }
        return ($this->getPage() === 'catalog_category_view')
                ? $schema = $this->getCategorySchema()
                : $schema = $this->getProductSchema();
    }

    /**
     * Get schema depending on page
     *
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function getSchemaBreadcrumb()
    {
        if (!$this->helper->getSchemaEnableBreadcrumb()) {
            return false;
        }
        $schema = $this->getCategorySchemaBreadcrumb($this->getPage() !== 'catalog_category_view');
        return is_array($schema) ? $schema : false;
    }

    /**
     * Get category schema
     *
     * @return array|bool
     * @throws NoSuchEntityException
     */
    protected function getCategorySchema()
    {
        if ($this->getCategory() === null) {
            return false;
        }
        // set category schema
        $schema = [
            '@context' => static::SCHEMA_DOMAIN,
            '@type' => 'Offer',
            'name' => $this->getCategory()->getName(),
        ];

        // add ratings
        if ($this->moduleManager->isEnabled('Magento_Review') && $this->helper->getSchemaEnableCategoryRatings()) {
            $rating = $this->getCategoryRating();
            if ($rating) {
                $schema = array_merge($schema, [
                    'aggregateRating' => [
                        '@type' => 'AggregateRating',
                        'ratingValue' => $rating[0],
                        'reviewCount' => $rating[1],
                        'worstRating' => static::AGGREGATE_RATING_WORST_RATING,
                        'bestRating' => static::AGGREGATE_RATING_BEST_RATING,
                    ]
                ]);
            }
        }

        // return schema
        return $schema;
    }

    /**
     * Get category schema
     *
     * @param bool $productPage
     * @return array|bool
     */
    protected function getCategorySchemaBreadcrumb($productPage)
    {
        // set category schema
        $schema = [
            '@context' => static::SCHEMA_DOMAIN,
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        $category = $this->getCategoryForBreadcrumb($productPage);
        if (!$category) {
            return false;
        }
        $crumbs = $this->getBreadcrumbPath($category);
        if (isset($crumbs['category' . $this->getCategory()->getId()])) {
            $crumbs['category' . $this->getCategory()->getId()]['link'] = $this->getFinalUrlFromCategory($this->getCategory());
        }
        if (isset($crumbs['product'])) {
            unset($crumbs['product']);
        }
        $schemaCrumb = [];
        foreach ($crumbs as $crumb) {
            $schemaCrumb[] = [
                '@type' => 'ListItem',
                'position' => (count($schemaCrumb) + 1),
                'name' => $crumb['label'],
                'item' => $crumb['link'],
            ];
        }
        $schema['itemListElement'] = $schemaCrumb;

        // return schema
        return $schema;
    }

    /**
     * Get category url
     *
     * @param Category $category
     * @return string
     */
    public function getFinalUrlFromCategory(\Magento\Catalog\Model\Category  $category)
    {
        if ($category->hasData('request_path') && $category->getRequestPath() != '') {
            $category->setData('url', $category->getUrlInstance()->getDirectUrl($category->getRequestPath()));
            return $category->getData('url');
        }

        $rewrite = $this->urlFinder->findOneByData([
            UrlRewrite::ENTITY_ID => $category->getId(),
            UrlRewrite::ENTITY_TYPE => CategoryUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::STORE_ID => $category->getStoreId(),
            UrlRewrite::REDIRECT_TYPE => 0, // Final URL, no redirect. TD-CHANGE from origin
        ]);
        if ($rewrite) {
            $category->setData('url', $category->getUrlInstance()->getDirectUrl($rewrite->getRequestPath()));
            return $category->getData('url');
        }

        $category->setData('url', $category->getCategoryIdUrl());
        return $category->getData('url');
    }


    /**
     * Get product schema
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductSchema()
    {
        $productModel = $this->getProduct();
        $product = [];
        $offers = [];

        if ($this->moduleManager->isEnabled('Magento_Review')) {
            $summaryModel = $this->getReviewSummary();
            $reviewCount = $summaryModel->getReviewsCount();
            $ratingSummary = ($summaryModel->getRatingSummary()) ? $summaryModel->getRatingSummary() : 20;

            if ($reviewCount > 0) {
                $aggregateRating = $this->getProductSchemaData([], static::AGGREGATE_RATING_BEST_RATING, 'bestRating');
                $aggregateRating = $this->getProductSchemaData($aggregateRating, static::AGGREGATE_RATING_WORST_RATING, 'worstRating');
                $aggregateRating = $this->getProductSchemaData($aggregateRating, ($ratingSummary / 20), 'ratingValue');
                $aggregateRating = $this->getProductSchemaData($aggregateRating, $reviewCount, 'reviewCount');
                if (!empty($aggregateRating)) {
                    $aggregateRating['@type'] = 'AggregateRating';
                    $product['aggregateRating'] = $aggregateRating;
                }
            }
        }

        $offers['@type'] = 'Offer';
        $offers = $this->getProductSchemaData($offers, $this->getCurrencyCode(), 'priceCurrency');
        $offers = $this->getProductSchemaData($offers, static::SCHEMA_DOMAIN . ($productModel->isAvailable() ? 'InStock' : 'OutOfStock'), 'availability');
        $offers = $this->getProductSchemaData($offers, round($productModel->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), 2), 'price');
        $offers = $this->getProductSchemaData($offers, $productModel->getUrlModel()->getUrl($productModel), 'url');
        $product['offers'] = $offers;

        $brand = $this->getProductSchemaData([], $this->getBrand(), 'name');
        if (!empty($brand)) {
            $brand['@type'] = 'Thing';
            $product['brand'] = $brand;
        }

        /** @noinspection PhpDeprecationInspection */
        $product = $this->getProductSchemaData($product, $productModel->getName(), 'name');
        $product = $this->getProductSchemaData($product, $this->getColor(), 'color');
        $product = $this->getProductSchemaData($product, $this->getLogo(), 'logo');
        /** @noinspection PhpDeprecationInspection */
        $product = $this->getProductSchemaData($product, $this->getDescription(), 'description');
        $product = $this->getProductSchemaData($product, $this->getSku(), 'sku');
        $product = $this->getProductSchemaData($product, ($this->getProductId() ? __('Art.nr.:') . $this->getProductId() : ''), 'productID');
        /** @noinspection PhpUndefinedMethodInspection */
        $product = $this->getProductSchemaData($product, $productModel->getMediaGalleryImages()->getFirstItem()->getUrl(), 'image');

        if (!empty($product)) {
            $product['@context'] = static::SCHEMA_DOMAIN;
            $product['@type'] = 'Product';
        }

        $productData = new DataObject($product);
        $this->eventManager->dispatch('product_schema_add_as_last', ['productSchema' => $productData, 'productModel' => $productModel]);
        return $productData->convertToArray();
    }

    /**
     * @param array|DataObject $data
     * @param mixed $name
     * @param string $key
     * @return array|DataObject
     */
    public function getOrganizationSchemaData($data, $name, $key)
    {
        $value = $this->helper->getDynamicConfigValue($name, 'organization_properties');
        if ($this->valueIsSet($value)) {
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * @param array|DataObject $data
     * @param mixed $value
     * @param string $key
     * @return array|DataObject
     */
    public function getProductSchemaData($data, $value, $key)
    {
        if (is_string($value)) {
            if ($this->valueIsSet($value)) {
                $data[$key] = $value;
            }
        } elseif ($value !== null && $value !== false) {
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * Returns the attribute value for the given attribute code
     *
     * @param $code
     * @return string
     */
    protected function getAttribute($code)
    {
        $attributeValue = '';

        /** @noinspection PhpUndefinedMethodInspection PhpDeprecationInspection */
        $attribute = $this->getProduct()->getResource()->getAttribute($code);

        if (!$attribute) {
            return $attributeValue;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
            $attributeValue = $this->getProduct()->getAttributeText($code);
        } else {
            $attributeValue = $this->getProduct()->getData($code);
        }

        return $attributeValue;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('Magenerds_RichSnippet::head/schema.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * Return current category path or get it from current category
     *
     * Creating array of categories|product paths for breadcrumbs
     *
     * @param Category $category
     * @return array
     */
    public function getBreadcrumbPath(Category $category)
    {
        $path = [];
        if ($category) {
            $pathInStore = $category->getPathInStore();
            $pathIds = array_reverse(explode(',', $pathInStore));

            $categories = $category->getParentCategories();

            // add category path breadcrumb
            foreach ($pathIds as $categoryId) {
                if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    $path['category' . $categoryId] = [
                        'label' => $categories[$categoryId]->getName(),
                        'link' => $this->getFinalUrlFromCategory($categories[$categoryId])
                    ];
                }
            }
        }
        return $path;
    }

    /**
     * @param bool $productPage
     * @return Category|null
     */
    protected function getCategoryForBreadcrumb($productPage)
    {
        $category = $this->getCategory();
        if ($productPage) {
            $categoryId = $this->getProduct()->getMetaSeoCategory();
            if (!empty($categoryId) && $category) {
                $category = $category->load($categoryId);
            }
            if ($category === null) {
                $category = $this->getProduct()->getCategory();
            }
        }
        return $category;
    }

}
