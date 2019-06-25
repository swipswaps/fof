<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 3 or later
 */

namespace  FOF40\Render;

use FOF40\Container\Container;
use Joomla\Registry\Registry;
use stdClass;

defined('_JEXEC') or die;

/**
 * Base class for other render classes
 */
abstract class RenderBase implements RenderInterface
{

	/** @var   Container|null  The container we are attached to */
	protected $container = null;

	/** @var   bool  Is this renderer available under this execution environment? */
	protected $enabled = false;

	/** @var   int  The priority of this renderer in case we have multiple available ones */
	protected $priority = 0;

	/** @var   Registry  A registry object holding renderer options */
	protected $optionsRegistry = null;

	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;

		$this->optionsRegistry = new Registry();
	}

	/**
	 * Set a renderer option (depends on the renderer)
	 *
	 * @param   string $key   The name of the option to set
	 * @param   string $value The value of the option
	 *
	 * @return  void
	 */
	function setOption(string $key, string $value): void
	{
		$this->optionsRegistry->set($key, $value);
	}

	/**
	 * Set multiple renderer options at once (depends on the renderer)
	 *
	 * @param   array $options The options to set as key => value pairs
	 *
	 * @return  void
	 */
	function setOptions(array $options): void
	{
		foreach ($options as $key => $value)
		{
			$this->setOption($key, $value);
		}
	}

	/**
	 * Get the value of a renderer option
	 *
	 * @param   string $key     The name of the parameter
	 * @param   mixed  $default The default value to return if the parameter is not set
	 *
	 * @return  mixed  The parameter value
	 */
	function getOption(string $key, ?string $default = null): string
	{
		return $this->optionsRegistry->get($key, $default);
	}

	/**
	 * Returns the information about this renderer
	 *
	 * @return  stdClass
	 */
	function getInformation(): stdClass
	{
		$classParts = explode('\\', get_class($this));

		return (object) [
			'enabled'  => $this->enabled,
			'priority' => $this->priority,
			'name'     => strtolower(array_pop($classParts)),
		];
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string $view The current view
	 * @param   string $task The current task
	 *
	 * @return  void
	 */
	function preRender(string $view, string $task): void
	{
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string $view The current view
	 * @param   string $task The current task
	 *
	 * @return  void
	 */
	function postRender(string $view, string $task): void
	{
	}

	/**
	 * Renders the submenu (link bar) for a category view when it is used in a
	 * extension
	 *
	 * Note: this function has to be called from the addSubmenu function in
	 *         the ExtensionNameHelper class located in
	 *         administrator/components/com_ExtensionName/helpers/Extensionname.php
	 *
	 * @return  void
	 */
	function renderCategoryLinkbar(): void
	{
		throw new \LogicException(sprintf('Renderer class %s must implement the %s method', get_class($this), __METHOD__));
	}

	/**
	 * Opens a wrapper DIV. Our component's output will be inside this wrapper.
	 *
	 * @param   array  $classes  An array of additional CSS classes to add to the outer page wrapper element.
	 *
	 * @return  void
	 */
	protected function openPageWrapper(array $classes): void
	{
		$removeClasses = $this->getOption('remove_wrapper_classes', []);

		if (!is_array($removeClasses))
		{
			$removeClasses = explode(',', $removeClasses);
		}

		$removeClasses = array_map('trim', $removeClasses);

		foreach ($removeClasses as $class)
		{
			$x = array_search($class, $classes);

			if ($x !== false)
			{
				unset($classes[$x]);
			}
		}

		// Add the following classes to the wrapper div
		$addClasses = $this->getOption('add_wrapper_classes', '');

		if (!is_array($addClasses))
		{
			$addClasses = explode(',', $addClasses);
		}

		$addClasses = array_map('trim', $addClasses);

		$customClasses = implode(array_unique(array_merge($classes, $addClasses)), ' ');

		echo <<< HTML
<div class="$customClasses">

HTML;
	}

	/**
	 * Outputs HTML which closes the page wrappers opened with openPageWrapper.
	 *
	 * @return  void
	 */
	protected function closePageWrapper(): void
	{
		echo "</div>\n";
	}
}
