<?php

/**
 * @version     3.0.0
 * @package     com_imc
 * @subpackage  mod_imc
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
defined('_JEXEC') or die;

$app = JFactory::getApplication();
$search = $app->getUserStateFromRequest('com_imc.issues.filter.search', 'filter_search');
$owned = $app->getUserStateFromRequest('com_imc.issues.filter.owned', 'filter_owned');
?>

<div class="imc_filters_search">
	<form class="form-search form-inline" action="<?php echo JRoute::_('index.php?option=com_imc&view=issues'); ?>" method="post" name="imc_filter_form" id="imc_filter_form">
	    <input type="text" class="input-medium search-query" name="filter_search" value="<?php echo $search; ?>">
		<?php if (JFactory::getUser()->id > 0) : ?>
			<p>
			<input type="hidden" id="filter_owned_hidden" name="filter_owned" value="no" />
		    <label class="checkbox inline">
				<input type="checkbox" id="filter_owned" name="filter_owned" value="yes" <?php echo ($owned == 'yes' ? 'checked="checked"' : ''); ?> > Show only my issues
			</label>
			</p>
		<?php endif; ?>
		<p></p>
	    <p><button type="submit" class="btn"><?php echo JText::_('MOD_IMCFILTERS_SEARCH'); ?> / <?php echo JText::_('MOD_IMCFILTERS_APPLY'); ?></button></p>
	</form>
</div>