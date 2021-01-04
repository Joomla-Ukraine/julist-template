<?php
/**
 * JU List
 *
 * @package          Joomla.Site
 * @subpackage       JU List
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2017-2021 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see _LICENSE.php
 */

defined('_JEXEC') or die;

global $user;

$app      = JFactory::getApplication();
$path_lib = JPATH_SITE . '/libraries/cck/rendering/rendering.php';
$user     = JCck::getUser();

if(!file_exists($path_lib))
{
	print('/libraries/cck/rendering/rendering.php file is missing.');

	die;
}

require_once($path_lib);