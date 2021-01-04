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

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

require_once __DIR__ . '/config.php';

$cck = CCK_Rendering::getInstance($this->template);

if($cck->initialize() === false)
{
	return;
}

// Prepare
$attributes       = $cck->item_attributes ? ' ' . $cck->item_attributes : '';
$class            = trim($cck->getStyleParam('class'));
$custom_attr      = trim($cck->getStyleParam('attributes'));
$custom_attr      = $custom_attr ? ' ' . $custom_attr : '';
$display_mode     = (int) $cck->getStyleParam('list_display', '0');
$display_mode_tpl = (int) $cck->getStyleParam('list_display_tpl', '0');
$tags             = $cck->getStyleParam('tag', 'ul_li');
$tags             = explode('_', $tags);
$id_class         = $cck->id_class;
$items            = $cck->getItems();
$fieldnames       = $cck->getFields('element', '', false);
$multiple         = count($fieldnames) > 1;
$count            = count($items);
$auto_clean       = (int) $cck->getStyleParam('auto_clean', 0);

$loader_div = (int) $cck->getStyleParam('loader_div', 0);

// Ads
$ads       = (int) $cck->getStyleParam('ads', 0);
$ads_count = (int) $cck->getStyleParam('ads_count', 3);
$ads_class = trim($cck->getStyleParam('ads_class'));
$ads_file  = trim($cck->getStyleParam('ads_file', 'ads.php'));
$mod_top          = (int) $cck->getStyleParam('mod_top', 0);
$mod_top_position = trim($cck->getStyleParam('mod_top_position'));

// DateTime
$datetime           = (int) $cck->getStyleParam('use_dt', 0);
$datetime_icon      = trim($cck->getStyleParam('icon_dt'));
$datetime_class     = trim($cck->getStyleParam('class_dt'));
$datetime_datefield = trim($cck->getStyleParam('datefield_dt'));

// Alphabetic
$datetime           = (int) $cck->getStyleParam('use_dt', 0);
$datetime_icon      = trim($cck->getStyleParam('icon_dt'));
$datetime_class     = trim($cck->getStyleParam('class_dt'));
$datetime_datefield = trim($cck->getStyleParam('datefield_dt'));

// Auto clean
$isRaw = ($count == 1) ? $auto_clean : 0;
if($auto_clean == 2)
{
	$isRaw = 1;
}

// Set
$isMore = $cck->isLoadingMore();
if($cck->isGoingtoLoadMore())
{
	$class = trim($class . ' ' . 'cck-loading-more');
}

// CSS class
$class = str_replace('$total', $count, $class);
$class = $class ? ' class="' . $class . '"' : '';
if($id_class && !$isMore)
{
	echo '<div class="' . trim($cck->id_class) . '">';
}

// Raw
if(!($isRaw || $isMore || $display_mode_tpl) && $datetime == '0' && $count > 0)
{
	echo '<' . $tags[ 0 ] . $class . $custom_attr . '>';
}

// Module
if(($mod_top == 1) && $mods = ModuleHelper::getModules($mod_top_position))
{
	foreach($mods as $mod)
	{
		echo ModuleHelper::renderModule($mod, [ 'style' => 'raw' ]);
	}
}

$html = '';

if($count)
{
	// ads
	if($ads == 1)
	{
		$ads_tmpl = __DIR__ . '/includes/ads/' . $ads_file;

		ob_start();
		require_once $ads_tmpl;
		$ads_html = ob_get_clean();
	}

	// datetime
	if($datetime == '1')
	{
		$tmp_date = '';
		$total    = count($items);
	}

	// modes
	if($display_mode == 2)
	{
		if($display_mode_tpl == 1)
		{
			$tmpl = $cck->path . '/positions/' . $cck->type . '/default.php';
			$html = $tmpl . ' not found';
			if(is_file($tmpl))
			{
				ob_start();
				require($tmpl);
				$html = ob_get_clean();
			}
		}
		else
		{
			$i = 0;
			foreach($items as $item)
			{
				$row = $item->renderPosition('element');
				if($datetime == '1')
				{
					$_ds = $cck->getItems();
					$out = [];
					foreach($_ds as $_d)
					{
						$out[] = [
							$_d->getValue($datetime_datefield)
						];
					}

					$outtime = $out[ $i ][ 0 ];
					if($tmp_date != HTMLHelper::_('date', $outtime, 'd.m'))
					{
						if($tmp_date)
						{
							$dateheader = HTMLHelper::_('date', $outtime, 'd F');
							if($datetime_icon)
							{
								$dateheader = $datetime_icon . '<span class="uk-text-middle">' . HTMLHelper::_('date', $outtime, 'd F') . '</span>';
							}

							$html .= '</' . $tags[ 0 ] . '><h3' . ($datetime_class ? ' class="' . $datetime_class . '"' : '') . '>' . $dateheader . '</h3>';
						}

						$tmp_date = HTMLHelper::_('date', $outtime, 'd.m');
						$html     .= '<' . $tags[ 0 ] . $class . $custom_attr . '>';
					}
				}

				if($row && !$isRaw)
				{
					$row = '<' . $tags[ 1 ] . $item->replaceLive($attributes) . '>' . $row . '</' . $tags[ 1 ] . '>';
				}

				if($ads == 1 && $i == $ads_count)
				{
					$html .= '<' . $tags[ 1 ] . ($ads_class ? ' class="' . $ads_class . '"' : '') . '>' . $ads_html . '</' . $tags[ 1 ] . '>';
				}

				$html .= $row;

				$i++;
			}
		}
	}
	elseif($display_mode == 1)
	{
		foreach($items as $pk => $item)
		{
			$row = $cck->renderItem($pk);
			if($row && !$isRaw)
			{
				$row = '<' . $tags[ 1 ] . $item->replaceLive($attributes) . '>' . $row . '</' . $tags[ 1 ] . '>';
			}

			$html .= $row;
		}
	}
	elseif($display_mode_tpl == 1)
	{
		$tmpl = $cck->path . '/positions/' . $cck->type . '/default.php';
		$html = $tmpl . ' not found';
		if(is_file($tmpl))
		{
			ob_start();
			require($tmpl);
			$html = ob_get_clean();
		}
	}
	else
	{
		foreach($items as $item)
		{
			$row = '';
			foreach($fieldnames as $fieldname)
			{
				$content = $item->renderField($fieldname);
				if($content != '')
				{
					if($item->getMarkup($fieldname) !== 'none' && ($multiple || $item->getMarkup_Class($fieldname)))
					{
						$row .= '<div class="cck-clrfix' . $item->getMarkup_Class($fieldname) . '">' . $content . '</div>';
					}
					else
					{
						$row .= $content;
					}
				}
			}

			if($row && !$isRaw)
			{
				$row = '<' . $tags[ 1 ] . $item->replaceLive($attributes) . '>' . $row . '</' . $tags[ 1 ] . '>';
			}

			$html .= $row;
		}
	}

	echo $html;
}
else
{
	$tmpl = $cck->path . '/positions/' . $cck->type . '/not_found.php';

	if(is_file($tmpl))
	{
		ob_start();
		require($tmpl);
		echo ob_get_clean();
	}
	else
	{
		echo JText::_('COM_CCK_NOT_FOUND_HTML');
	}
}

if(!($isRaw || $isMore || $display_mode_tpl) && $count > 0)
{
	echo '</' . $tags[ 0 ] . '>';
}

if($id_class && !$isMore)
{
	echo '</div>';
}

if($loader_div && $count > 1)
{
	?>
	<div class="status uk-margin-medium-top">
		<div class="loader uk-text-center">
			<span data-uk-spinner="ratio: 3"></span>
		</div>
		<div class="no-more uk-text-center uk-text-muted" hidden><?php echo JText::_('COM_CCK_NOT_FOUND'); ?></div>
	</div>
	<?php
}

$cck->finalize();