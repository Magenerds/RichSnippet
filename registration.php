<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

use Magento\Framework\Component\ComponentRegistrar;

/**
 * @category   Magenerds
 * @package    Magenerds_RichSnippet
 * @subpackage Module
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
 * @author     Belinda Tschampel <b.tschampel@techdivision.com>
 * @author     Philipp Steinkopff <p.steinkopff@techdivision.com>
 */
ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Magenerds_RichSnippet', __DIR__);
