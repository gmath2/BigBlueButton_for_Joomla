<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    17th July, 2018
 * @author     Jibon L. Costa <https://www.hoicoimasti.com>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
 * @copyright  Copyright (C) 2019 Hoicoi Extension. All Rights Reserved
 * @license    MIT
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Event View class
 */
class BigbluebuttonViewEvent extends JViewLegacy
{
	/**
	 * display method of View
	 * @return void
	 */
	public function display($tpl = null)
	{
		// set params
		$this->params = JComponentHelper::getParams('com_bigbluebutton');
		// Assign the variables
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');
		$this->state = $this->get('State');
		// get action permissions
		$this->canDo = BigbluebuttonHelper::getActions('event', $this->item);
		// get input
		$jinput = JFactory::getApplication()->input;
		$this->ref = $jinput->get('ref', 0, 'word');
		$this->refid = $jinput->get('refid', 0, 'int');
		$return = $jinput->get('return', null, 'base64');
		// set the referral string
		$this->referral = '';
		if ($this->refid && $this->ref)
		{
			// return to the item that referred to this item
			$this->referral = '&ref=' . (string)$this->ref . '&refid=' . (int)$this->refid;
		}
		elseif($this->ref)
		{
			// return to the list view that referred to this item
			$this->referral = '&ref=' . (string)$this->ref;
		}
		// check return value
		if (!is_null($return))
		{
			// add the return value
			$this->referral .= '&return=' . (string)$return;
		}

		// Set the toolbar
		$this->addToolBar();
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}


	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId	= $user->id;
		$isNew = $this->item->id == 0;

		JToolbarHelper::title( JText::_($isNew ? 'COM_BIGBLUEBUTTON_EVENT_NEW' : 'COM_BIGBLUEBUTTON_EVENT_EDIT'), 'pencil-2 article-add');
		// Built the actions for new and existing records.
		if (BigbluebuttonHelper::checkString($this->referral))
		{
			if ($this->canDo->get('event.create') && $isNew)
			{
				// We can create the record.
				JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
			}
			elseif ($this->canDo->get('event.edit'))
			{
				// We can save the record.
				JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
			}
			if ($isNew)
			{
				// Do not creat but cancel.
				JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				// We can close it.
				JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		else
		{
			if ($isNew)
			{
				// For new records, check the create permission.
				if ($this->canDo->get('event.create'))
				{
					JToolBarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
					JToolBarHelper::custom('event.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				};
				JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CANCEL');
			}
			else
			{
				if ($this->canDo->get('event.edit'))
				{
					// We can save the new record
					JToolBarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
					JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
					// We can save this record, but check the create permission to see
					// if we can return to make a new one.
					if ($this->canDo->get('event.create'))
					{
						JToolBarHelper::custom('event.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
					}
				}
				if ($this->canDo->get('event.create'))
				{
					JToolBarHelper::custom('event.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
				if ($this->canDo->get('event.resent_email'))
				{
					// add Resent Email button.
					JToolBarHelper::custom('event.reSendEmail', 'reply', '', 'COM_BIGBLUEBUTTON_RESENT_EMAIL', false);
				}
				JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
			}
		}
		JToolbarHelper::divider();
		// set help url for this view if found
		$help_url = BigbluebuttonHelper::getHelpUrl('event');
		if (BigbluebuttonHelper::checkString($help_url))
		{
			JToolbarHelper::help('COM_BIGBLUEBUTTON_HELP_MANAGER', false, $help_url);
		}
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if(strlen($var) > 30)
		{
    		// use the helper htmlEscape method instead and shorten the string
			return BigbluebuttonHelper::htmlEscape($var, $this->_charset, true, 30);
		}
		// use the helper htmlEscape method instead.
		return BigbluebuttonHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		$isNew = ($this->item->id < 1);
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_($isNew ? 'COM_BIGBLUEBUTTON_EVENT_NEW' : 'COM_BIGBLUEBUTTON_EVENT_EDIT'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_bigbluebutton/assets/css/event.css", (BigbluebuttonHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
		$this->document->addScript(JURI::root() . $this->script, (BigbluebuttonHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript');
		$this->document->addScript(JURI::root() . "administrator/components/com_bigbluebutton/views/event/submitbutton.js", (BigbluebuttonHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/javascript'); 
		$this->document->addStyleSheet(JURI::root() . "media/com_bigbluebutton/css/jquery.datetimepicker.min.css");
		$this->document->addScript(JURI::root() . "media/com_bigbluebutton/js/jquery.datetimepicker.full.min.js");
		JText::script('view not acceptable. Error');
	}
}
