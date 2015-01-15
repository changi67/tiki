<?php
// (c) Copyright 2002-2011 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: CallbackTest.php 33195 2011-03-02 17:43:40Z changi67 $

/** 
 * @group unit
 * 
 */

class TikiFilter_CallbackTest extends TikiTestCase
{
	function testSimple()
	{
		$filter = new TikiFilter_Callback( 'strtoupper' );

		$this->assertEquals( 'HELLO', $filter->filter( 'hello' ) );
	}
}