<?php

/**
 * @version     3.0.0
 * @package     com_imc
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Imc.
 */
class ImcViewComments extends JViewLegacy {

    protected $items;
    protected $pagination;
    protected $state;
    protected $canManageComments;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $canDo = ImcHelper::getActions();
        $this->canManageComments = $canDo->get('imc.manage.comments');
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        ImcHelper::addSubmenu('comments');

        $this->addToolbar();

        if(!$this->canManageComments){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_IMC_ACTION_NOT_ALLOWED'), 'error');
            return;
        }

        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        if(!$this->canManageComments){
            JToolBarHelper::title(JText::_('COM_IMC_TITLE_COMMENTS'), 'comments-2');
            //JToolBarHelper::back();
            $bar = JToolBar::getInstance('toolbar');
            $bar->appendButton('Link', 'leftarrow', 'COM_IMC_BACK', JRoute::_('index.php?option=com_imc', false));
            return;
        }
        require_once JPATH_COMPONENT . '/helpers/imc.php';

        $state = $this->get('State');
        $canDo = ImcHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_IMC_TITLE_COMMENTS'), 'comments-2');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/comment';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
                JToolBarHelper::addNew('comment.add', 'JTOOLBAR_NEW');
            }

            if ($canDo->get('core.edit') && isset($this->items[0])) {
                JToolBarHelper::editList('comment.edit', 'JTOOLBAR_EDIT');
            }
        }

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('comments.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
                JToolBarHelper::custom('comments.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'comments.delete', 'JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('comments.archive', 'JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('comments.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'comments.delete', 'JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else if ($canDo->get('core.edit.state')) {
                JToolBarHelper::trash('comments.trash', 'JTOOLBAR_TRASH');
                JToolBarHelper::divider();
            }
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_imc');
        }

        //Set sidebar action - New in 3.0
        JHtmlSidebar::setAction('index.php?option=com_imc&view=comments');

        $this->extra_sidebar = '';
                //Filter for the field ".issueid;
        jimport('joomla.form.form');
        $options = array();
        JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
        $form = JForm::getInstance('com_imc.comment', 'comment');

        $field = $form->getField('issueid');

        $query = $form->getFieldAttribute('filter_issueid','query');
        $translate = $form->getFieldAttribute('filter_issueid','translate');
        $key = $form->getFieldAttribute('filter_issueid','key_field');
        $value = $form->getFieldAttribute('filter_issueid','value_field');

        // Get the database object.
        $db = JFactory::getDBO();

        // Set the query and get the result list.
        $db->setQuery($query);
        $items = $db->loadObjectlist();

        // Build the field options.
        if (!empty($items))
        {
            foreach ($items as $item)
            {
                if ($translate == true)
                {
                    $options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
                }
                else
                {
                    $options[] = JHtml::_('select.option', $item->$key, $item->$value);
                }
            }
        }

        JHtmlSidebar::addFilter(
            'Issue',
            'filter_issueid',
            JHtml::_('select.options', $options, "value", "text", $this->state->get('filter.issueid')),
            true
        );
		JHtmlSidebar::addFilter(

			JText::_('JOPTION_SELECT_PUBLISHED'),

			'filter_published',

			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)

		);

    }

	protected function getSortFields()
	{
		return array(
		'a.id' => JText::_('JGRID_HEADING_ID'),
		'a.issueid' => JText::_('COM_IMC_COMMENTS_ISSUEID'),
		'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
		'a.state' => JText::_('JSTATUS'),
		'a.checked_out' => JText::_('COM_IMC_COMMENTS_CHECKED_OUT'),
		'a.checked_out_time' => JText::_('COM_IMC_COMMENTS_CHECKED_OUT_TIME'),
		'a.created_by' => JText::_('COM_IMC_COMMENTS_CREATED_BY'),
		);
	}

}
