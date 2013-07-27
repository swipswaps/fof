<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

if (!class_exists('JFormFieldList'))
{
	require_once JPATH_LIBRARIES . '/joomla/form/fields/list.php';
}

/**
 * Form Field class for FOF
 * Supports a generic list of options.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFFormFieldActions extends JFormFieldList implements FOFFormField
{
	protected $static;

	protected $repeatable;

	/** @var int A monotonically increasing number, denoting the row number in a repeatable view */
	public $rowid;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->static;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the field configuration
	 *
	 * @return  array
	 */
	protected function getConfig()
	{
		// If no custom options were defined let's figure out which ones of the
		// defaults we shall use...
		$config = array(
			'published'		 => 1,
			'unpublished'	 => 1,
			'archived'		 => 1,
			'trash'			 => 1,
			'all'			 => 0,
		);

		$stack = array();

		if ($this->element['show_published'] == 'false')
		{
			$config['published'] = 0;
		}

		if ($this->element['show_unpublished'] == 'false')
		{
			$config['unpublished'] = 0;
		}

		if ($this->element['show_archived'] == 'true')
		{
			$config['archived'] = 1;
		}

		if ($this->element['show_trash'] == 'true')
		{
			$config['trash'] = 1;
		}

		if ($this->element['show_all'] == 'true')
		{
			$config['all'] = 1;
		}

		return $config;
	}

	/**
	 * Method to get the field options.
	 *
	 * @since 2.0
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		return null;
	}

	/**
	 * Method to get a
	 *
	 * @param   string  $enabledFieldName  Name of the enabled/published field
	 *
	 * @return  FOFFormFieldPublished  Field
	 */
	protected function getPublishedField($enabledFieldName)
	{
		$attributes = array(
			'name' => $enabledFieldName,
			'type' => 'published',
		);

		if ($this->element['publish_up'])
		{
			$attributes['publish_up'] = (string) $this->element['publish_up'];
		}

		if ($this->element['publish_down'])
		{
			$attributes['publish_down'] = (string) $this->element['publish_down'];
		}

		foreach ($attributes as $name => $value)
		{
			if (!is_null($value))
			{
				$renderedAttributes[] = $name . '="' . $value . '"';
			}
		}

		$publishedXml = new SimpleXMLElement('<field ' . implode(' ', $renderedAttributes) . ' />');

		$publishedField = new FOFFormFieldPublished($this->form);

		// Pass required objects to the field
		$publishedField->item = $this->item;
		$publishedField->rowid = $this->rowid;
		$publishedField->setup($publishedXml, $this->item->{$enabledFieldName});

		return $publishedField;
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		throw new Exception(__CLASS__ . ' cannot be used in single item display forms');
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		if (!($this->item instanceof FOFTable))
		{
			throw new Exception(__CLASS__ . ' needs a FOFTable to act upon');
		}

		$config = $this->getConfig();

		// Initialise
		$prefix       = '';
		$checkbox     = 'cb';
		$publish_up   = null;
		$publish_down = null;
		$enabled      = true;

		$html = '<div class="btn-group">';

		// Render a published field
		if ($publishedFieldName = $this->item->getColumnAlias('enabled'))
		{
			// Generate a FOFFormFieldPublished field
			$publishedField = $this->getPublishedField($publishedFieldName);

			// Render the publish button
			$html .= $publishedField->getRepeatable();

			if ($config['archived'])
			{
				$archived	= $this->item->{$publishedFieldName} == 2 ? true : false;

				// Create dropdown items
				$action = $archived ? 'unarchive' : 'archive';
				JHtml::_('actionsdropdown.' . $action, 'cb' . $this->rowid, $prefix);
			}

			if ($config['trash'])
			{
				$trashed	= $this->item->{$publishedFieldName} == -2 ? true : false;

				$action = $trashed ? 'untrash' : 'trash';
				JHtml::_('actionsdropdown.' . $action, 'cb' . $this->rowid, $prefix);
			}

			// Render dropdown list
			if ($config['archived'] && $config['trash'])
			{
				$html .= JHtml::_('actionsdropdown.render', $this->item->title);
			}
		}

		$html .= '</div>';

		return $html;
	}
}