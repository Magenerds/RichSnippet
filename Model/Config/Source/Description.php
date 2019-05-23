<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\Richsnippet\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Description
 *
 * @package     Magenerds\RichSnippet\Model\Config\Source
 * @file        Description.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Belinda Tschampel <b.tschampel@techdivision.com>
 * @author      Philipp Steinkopff <p.steinkopff@techdivision.com>
 */
class Description implements ArrayInterface
{
    /**
     * Return list of Description Options
     *
     * @return array config attribute info
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Short Description')
            ],
            [
                'value' => 1,
                'label' => __('Long Description')
            ]
        ];
    }
}
