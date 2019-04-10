<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Block;

use Magenerds\RichSnippet\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Model\Review\Summary;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Theme\Block\Html\Header\Logo;

/**
 * Class Schemaorg
 *
 * @package     Magenerds\RichSnippet\Block
 * @file        Schemaorg.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 * @author      Belinda Tschampel <b.tschampel@techdivision.com>
 */
class Schemaorg extends Template
{
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
    /** @var EventManager */
    private $eventManager;

    /**
     * Schemaorg constructor.
     *
     * @param Registry $registry
     * @param SummaryFactory $reviewSummaryFactory
     * @param Data $helper
     * @param Logo $logo
     * @param Context $context
     * @param EventManager $eventManager
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        SummaryFactory $reviewSummaryFactory,
        Data $helper,
        Logo $logo,
        Context $context,
        EventManager $eventManager,
        $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->helper = $helper;
        $this->logo = $logo;
        $this->eventManager = $eventManager;
        parent::__construct($context, $data);
    }

    /**
     * Check if a value is a string and not empty.
     *
     * @param $value
     * @return bool
     */
    public function valueIsSet($value)
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
    public function getLogo()
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
    public function getDescription()
    {
        if ($this->helper->getDescriptionType()) {
            return nl2br($this->getProduct()->getData('description'));
        } else {
            return nl2br($this->getProduct()->getData('short_description'));
        }
    }

    /**
     * @return Summary
     */
    public function getReviewSummary()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        /** @var $reviewSummary Summary */
        $reviewSummary = $this->reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $storeId);
        $summaryModel = $reviewSummary->load($this->getProduct()->getId());

        return $summaryModel;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Specific color name for product
     *
     * @return string
     */
    public function getColor()
    {
        $colorAttribute = $this->helper->getColorConfig();

        return $this->getAttribute($colorAttribute);
    }

    /**
     * Sku value for product
     *
     * @return string
     */
    public function getSku()
    {
        $skuAttribute = $this->helper->getSkuConfig();

        return $this->getAttribute($skuAttribute);
    }

    /**
     * Product id value for product
     *
     * @return string
     */
    public function getProductId()
    {
        $productIdAttribute = $this->helper->getProductIdConfig();

        return $this->getAttribute($productIdAttribute);
    }

    /**
     * Specific brand name for product
     *
     * @return string
     */
    public function getBrand()
    {
        $brandAttribute = $this->helper->getBrandConfig();

        return $this->getAttribute($brandAttribute);
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
            $organization['@context'] = 'https://schema.org/';
            $organization['@type'] = 'Organization';
        }

        $organizationData = new DataObject($organization);
        $this->eventManager->dispatch('organization_schema_add_as_last', ['organizationSchema' => $organizationData]);
        $organization = $organizationData->convertToArray();

        return $organization;
    }

    /**
     * @return array
     */
    public function getProductSchema()
    {
        $productModel = $this->getProduct();
        $product = [];
        $offers = [];

        $summaryModel = $this->getReviewSummary();
        $reviewCount = $summaryModel->getReviewsCount();
        $ratingSummary = ($summaryModel->getRatingSummary()) ? $summaryModel->getRatingSummary() : 20;

        if ($reviewCount > 0) {
            $aggregateRating = $this->getProductSchemaData([], 5, 'bestRating');
            $aggregateRating = $this->getProductSchemaData($aggregateRating, 1, 'worstRating');
            $aggregateRating = $this->getProductSchemaData($aggregateRating, ($ratingSummary / 20), 'ratingValue');
            $aggregateRating = $this->getProductSchemaData($aggregateRating, $reviewCount, 'reviewCount');
            if (!empty($aggregateRating)) {
                $aggregateRating['@type'] = 'AggregateRating';
                $product['aggregateRating'] = $aggregateRating;
            }
        }

        $offers['@type'] = 'Offer';
        $offers = $this->getProductSchemaData($offers, $this->getCurrencyCode(), 'priceCurrency');
        $offers = $this->getProductSchemaData($offers, $productModel->isAvailable() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock", 'availability');
        $offers = $this->getProductSchemaData($offers, $productModel->getFinalPrice(), 'price');
        $offers = $this->getProductSchemaData($offers, $productModel->getUrlModel()->getUrl($productModel), 'url');
        $product['offers'] = $offers;

        $brand = $this->getProductSchemaData([], $this->getBrand(), 'name');
        if (!empty($brand)) {
            $brand['@type'] = 'Thing';
            $product['brand'] = $brand;
        }

        $product = $this->getProductSchemaData($product, $this->escapeQuote($this->stripTags($productModel->getName())), 'name');
        $product = $this->getProductSchemaData($product, $this->stripTags($this->getColor()), 'color');
        $product = $this->getProductSchemaData($product, $this->getLogo(), 'logo');
        $product = $this->getProductSchemaData($product, $this->escapeQuote($this->stripTags($this->getDescription())), 'description');
        $product = $this->getProductSchemaData($product, $this->getSku(), 'sku');
        $product = $this->getProductSchemaData($product, ($this->getProductId() ? __('Art.nr.:') . $this->stripTags($this->getProductId()) : ''), 'productID');
        $product = $this->getProductSchemaData($product, $productModel->getMediaGalleryImages()->getFirstItem()->getUrl(), 'image');

        if (!empty($product)) {
            $product['@context'] = 'https://schema.org/';
            $product['@type'] = 'Product';
        }

        $productData = new DataObject($product);
        $this->eventManager->dispatch('product_schema_add_as_last', ['productSchema' => $productData, 'productModel' => $productModel]);
        $product = $productData->convertToArray();

        return $product;
    }

    public function getOrganizationSchemaData(array $data, $name, $key)
    {
        $value = $this->helper->getDynamicConfigValue($name, 'organization_properties');
        if ($this->valueIsSet($value)) {
            $data[$key] = $value;
        }
        return $data;
    }

    public function getProductSchemaData(array $data, $value, $key)
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

        $attribute = $this->getProduct()->getResource()->getAttribute($code);

        if (!$attribute) {
            return $attributeValue;
        }

        if (in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
            $attributeValue = $this->getProduct()->getAttributeText($code);
        } else {
            $attributeValue = $this->getProduct()->getData($code);
        }

        return $attributeValue;
    }
}
