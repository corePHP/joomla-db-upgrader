<?php
/**
 * @package	DB Upgrader
 * @version 1.5
 * @author 	Rafael Corral
 * @link 	http://www.corephp.com
 * @copyright Copyright (C) 2011 'corePHP' LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class JTableDBUpgrade extends JTable
{
	var $id          = 0;
	var $modified    = '0000-00-00 00:00:00';
	var $modified_by = null;
	var $name        = null;
	var $version     = null;

	function __construct( &$db, $key = 'id' )
	{
		parent::__construct( '#__dbupgrade', $key, $db );
	}

	/**
	 * Check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function store()
	{
		$user         =& JFactory::getUser();
		$config       =& JFactory::getConfig();
		$modifieddate =& JFactory::getDate();
		$modifieddate->setOffset( $config->getValue( 'config.offset' ) );

		$this->modified    = $modifieddate->toMySQL();
		$this->modified_by = $user->id;

		return parent::store();
	}
}
?>