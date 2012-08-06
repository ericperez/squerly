<?php 
/**
 * Contains the most low-level helpers methods in Kohana:
 *
 * - Environment initialization
 * - Locating files within the cascading filesystem
 * - Auto-loading and transparent extension of classes
 * - Variable and path debugging
 *
 * @package    Kohana
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana {

	
	/**
	 * @var  string  character set of input and output
	 */
	public static $charset = 'utf-8';

	/**
	 * @var  string  base URL to the application
	 */
	public static $base_url = '/';

	
} // End Kohana
