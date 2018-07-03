<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\RichSnippet\Block;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Category;
use Magento\Cms\Model\Page;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magenerds\RichSnippet\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Facebookopengraph
 *
 * @package     Magenerds\RichSnippet\Block
 * @file        Schemaorg.php
 * @copyright   Copyright (c) 2018 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julia Mehringer <j.mehringer@techdivision.com>
 */
class Facebookopengraph extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Schemaorg constructor.
     * @param Registry $registry
     * @param Data $helper
     * @param ImageBuilder $imageBuilder
     * @param DirectoryList $directoryList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        Data $helper,
        ImageBuilder $imageBuilder,
        DirectoryList $directoryList,
        Context $context,
        $data = []
    )
    {
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->imageBuilder = $imageBuilder;
        $this->directoryList = $directoryList;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current product model
     *
     * @return Product
     */
    private function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve current category model
     *
     * @return Category
     */
    private function getCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Returns the file extension type
     *
     * @param $path
     * @return mixed
     */
    private function getTypeExension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Retrieve product image
     *
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     *
     * @return Image
     */
    private function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * Get URL of the current page
     *
     * @return string
     */
    private function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Returns the title of the category
     *
     * @return string
     */
    private function getCategoryTitle()
    {
        $title = $this->getCategory()->getData('meta_title');
        if($title) {
            return $title;
        }
        return '';
    }

    /**
     * Returns the category meta description
     *
     * @return string
     */
    private function getCategoryDescription()
    {
        if($this->getCategory()->getData('meta_description')) {
            return $this->getCategory()->getData('meta_description');
        }
        return '';
    }

    /**
     * Returns the category image url
     *
     * @return string
     */
    private function getCategoryImageUrl()
    {
        return $this->getCategory()->getImageUrl();
    }

    /**
     * Gets the filesystem path of the category image
     *
     * @return string
     */
    private function getCategoryImage()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category' . DIRECTORY_SEPARATOR . $this->getCategory()->getImage();
    }

    /**
     * Returns the size information for the category image
     *
     * @return array|bool
     */
    private function getCategoryImageSize()
    {
        return getimagesize($this->getCategoryImage());
    }

    /**
     * Returns CMS page
     *
     * @return Page
     */
    private function getCmsPage()
    {
        return $this->getLayout()->getBlock('cms_page')->getPage();
    }

    /**
     * Returns the title of the page
     *
     * @return string
     */
    private function getPageTitle()
    {
        $title = $this->getLayout()->getBlock('page.main.title');
        if($title) {
            return $title->getPageTitle();
        }
        return '';
    }

    /**
     * Returns the cms meta description
     *
     * @return string
     */
    private function getCmsDescription()
    {
        $page = $this->getCmsPage();
        if($page && $page->getData('meta_description')) {
            return $page->getData('meta_description');
        }
        return '';
    }

    /**
     * Returns the facebook app id
     *
     * @return string
     */
    public function getFacebookAppId()
    {
        return $this->helper->getFacebookAppIdConfig();
    }

    /**
     * Returns the data needed to render the meta tags
     *
     * @return array
     */
    public function getOpenGraphData()
    {
        $ogData = [];

        if($this->getRequest()->getFullActionName() === 'catalog_product_view') {
            $productImage = $this->getImage($this->getProduct(), 'product_base_image');

            if($productImage) {
                $imageUrl = $productImage->getImageUrl();
                $ogData['image:secure_url'] = $this->escapeUrl($imageUrl);
                $ogData['image:type'] = $this->getTypeExension($imageUrl);
                $ogData['image:width'] = $productImage->getWidth();
                $ogData['image:height'] = $productImage->getHeight();
            }
        }

        if($this->getRequest()->getFullActionName() === 'catalog_category_view') {
            $categoryImage = $this->getCategoryImage();
            $categoryImageUrl = $this->getCategoryImageUrl();
            $categoryImageSize = $this->getCategoryImageSize();

            $ogData['type'] = 'product.group';
            $ogData['title'] = $this->escapeHtml($this->getCategoryTitle());
            $ogData['description'] = $this->escapeHtml($this->getCategoryDescription());
            $ogData['url'] = $this->escapeUrl($this->getCurrentUrl());
            if($categoryImage) {
                $ogData['image'] = $this->escapeUrl($categoryImageUrl);
                $ogData['image:secure_url'] = $this->escapeUrl($categoryImageUrl);
                $ogData['image:type'] = $this->getTypeExension($categoryImageUrl);
                if($categoryImageSize) {
                    $ogData['image:width'] = $categoryImageSize[0];
                    $ogData['image:height'] = $categoryImageSize[1];
                }
            }
        }

        if($this->getRequest()->getFullActionName() === 'cms_page_view') {
            $ogData['type'] = 'article';
            $ogData['title'] = $this->escapeHtml($this->getPageTitle());
            $ogData['description'] = $this->getCmsDescription();
            $ogData['url'] = $this->escapeUrl($this->getCurrentUrl());
        }

        return $ogData;
    }
}