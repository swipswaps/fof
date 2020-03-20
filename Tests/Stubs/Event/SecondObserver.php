<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Tests\Stubs\Event;

use FOF30\Event\Observable;

class SecondObserver extends FirstObserver
{
	function __construct(Observable &$subject)
	{
		parent::__construct($subject); // TODO: Change the autogenerated stub

		$this->myId = 'two';
	}

	public function onlySecond()
	{
		return 'only second';
	}
}
