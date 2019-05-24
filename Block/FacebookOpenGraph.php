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
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magenerds\RichSnippet\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FacebookOpenGraph
 *
 * @package     Magenerds\RichSnippet\Block
 * @file        FacebookOpenGraph.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Julia Mehringer <j.mehringer@techdivision.com>
 */
class FacebookOpenGraph extends Template
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
    protected $directoryList;

    /**
     * FacebookOpenGraph constructor.
     *
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
    protected function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Retrieve current category model
     *
     * @return Category
     */
    protected function getCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * Returns the file extension type
     *
     * @param $path
     * @return mixed
     */
    protected function getTypeExension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Retrieve product image
     *
     * @param Product $product
     * @param string $imageId
     * @param array $attributes
     * @return Image
     */
    protected function getImage($product, $imageId, $attributes = [])
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
    protected function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Returns the title of the category
     *
     * @return string
     */
    protected function getCategoryTitle()
    {
        if ($title = $this->getCategory()->getData('meta_title')) {
            return $title;
        }
        return $this->getCategory()->getName() ?: '';
    }

    /**
     * Returns the category meta description
     *
     * @return string
     */
    protected function getCategoryDescription()
    {
        $description = $this->getCategory()->getData('meta_description');
        if ($description) {
            return $description;
        }
        return '';
    }

    /**
     * Returns the category image url
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getCategoryImageUrl()
    {
        return $this->getCategory()->getImageUrl();
    }

    /**
     * Gets the filesystem path of the category image
     *
     * @return string
     * @throws FileSystemException
     */
    protected function getCategoryImage()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'category' . DIRECTORY_SEPARATOR . $this->getCategory()->getImage();
    }

    /**
     * Returns the size information for the category image
     *
     * @return array|bool
     * @throws FileSystemException
     */
    protected function getCategoryImageSize()
    {
        $image = $this->getCategoryImage();

        if (is_file($image)) {
            return getimagesize($image);
        }
        return false;
    }

    /**
     * Returns CMS page
     *
     * @return Page
     * @throws LocalizedException
     */
    protected function getCmsPage()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getLayout()->getBlock('cms_page')->getPage();
    }

    /**
     * Returns the title of the CMS page
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getCmsPageTitle()
    {
        $title = $this->getCmsPage()->getTitle();
        if ($title) {
            return $title;
        }
        return '';
    }

    /**
     * Returns the CMS Page meta description
     *
     * @return string
     * @throws LocalizedException
     */
    protected function getCmsDescription()
    {
        $page = $this->getCmsPage();
        if ($page && $page->getData('meta_description')) {
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
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getOpenGraphData()
    {
        $ogData = [];

        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->getRequest()->getFullActionName() === 'catalog_product_view') {
            $productImage = $this->getImage($this->getProduct(), 'product_base_image');

            if ($productImage) {
                $imageUrl = $productImage->getImageUrl();
                $ogData = array_merge($ogData, [
                    'image:secure_url' => $imageUrl,
                    'image:type' => $this->getTypeExension($imageUrl),
                    'image:width' => $productImage->getWidth(),
                    'image:height' => $productImage->getHeight(),
                ]);
            }
        } /** @noinspection PhpUndefinedMethodInspection */
        elseif ($this->getRequest()->getFullActionName() === 'catalog_category_view') {
            $categoryImageUrl = $this->getCategoryImageUrl();
            $categoryImageSize = $this->getCategoryImageSize();

            $ogData = array_merge($ogData, [
                'type' => 'product.group',
                'title' => $this->getCategoryTitle(),
                'description' => $this->getCategoryDescription(),
                'url' => $this->getCurrentUrl(),
            ]);

            if ($categoryImageUrl) {
                $ogData = array_merge($ogData, [
                    'image' => $categoryImageUrl,
                    'image:secure_url' => $categoryImageUrl,
                    'image:type' => $this->getTypeExension($categoryImageUrl),
                ]);

                if ($categoryImageSize) {
                    $ogData = array_merge($ogData, [
                        'image:width' => $categoryImageSize[0],
                        'image:height' => $categoryImageSize[0],
                    ]);
                }
            }
        } /** @noinspection PhpUndefinedMethodInspection */
        elseif ($this->getRequest()->getFullActionName() === 'cms_page_view') {
            $ogData = array_merge($ogData, [
                'type' => 'article',
                'title' => $this->getCmsPageTitle(),
                'description' => $this->getCmsDescription(),
                'url' => $this->getCurrentUrl(),
            ]);
        }

        return $ogData;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('Magenerds_RichSnippet::head/fb_open_graph.phtml');
        }
        return parent::_toHtml();
    }
}
