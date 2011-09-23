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

jimport('joomla.plugin.plugin');

class plgSystemDBUpgrader extends JPlugin
{
	function onAfterRoute()
	{
		if ( $this->params->get( 'dodefault', false ) ) {
			DBUpgrader::getInstance( 'default' )->db_check();
		}
	}
}

class DBUpgrader extends JObject
{
	/**
	 * The type of extension we are tracking
	 *
	 * @var string
	 **/
	protected $type;

	function __construct( $type )
	{
		$this->type = $type;
	}

	function getInstance( $type )
	{
		static $instances = array();

		if ( !isset( $instances[$type] ) ) {
			$instances[$type] = new DBUpgrader( $type );
		}

		return $instances[$type];
	}

	/**
	 * Create config database
	 * 
	 * @since 1.0 
	 **/
	function create_config()
	{
		$db = JFactory::getDBO();

		$query = "DESCRIBE #__dbupgrade_config";
		$db->setQuery( $query );

		if ( !$db->loadResult() ) {
			$query = "
			CREATE TABLE IF NOT EXISTS `#__dbupgrade` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `modified` datetime NOT NULL,
			  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
			  `name` varchar(255) NOT NULL,
			  `version` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			$db->setQuery( $query );
			$db->query();
		}
	}

	function get_version()
	{
		jimport( 'joomla.database.table' );
		require_once JPATH_PLUGINS.'/system/dbupgrader/tables/dbupgrade.php';

		$db  = JFactory::getDBO();
		$row = new JTableDBUpgrade( $db, 'name' );
		$row->load( $this->type );
		$db_version = $row->get( 'version' );

		if ( !$db_version ) {
			$row = new JTableDBUpgrade( $db );
			$row->bind( array( 'name' => $this->type, 'version' => 1000 ) );
			$row->store();
			$db_version = 1000;
		}

		return $db_version;
	}

	function db_check()
	{
		$this->create_config();
		$db_version = $this->get_version();

		$new_version = $this->find_upgrades( $db_version );

		if ( $db_version == $new_version ) {
			return;
		}

		// Finally store new version to DB
		$db = JFactory::getDBO();
		$row = new JTableDBUpgrade( $db, 'name' );
		$row->load( $this->type );
		$row->version = $new_version;
		$row->store();
	}

	/**
	 * Find upgrades depending on DB version
	 * 
	 * @param int The current database version
	 * @return int The new database version
	 */
	function find_upgrades( $db_version )
	{
		jimport( 'joomla.filesystem.file' );

		$db = JFactory::getDBO();
		$no_more = false;
		$version_check = intval( $db_version ) + 1;

		while ( false == $no_more ) {
			$_file = JPATH_PLUGINS."/system/dbupgrader/sql/{$this->type}_{$version_check}.sql";
			if ( file_exists( $_file ) ) {
				$contents = JFile::read( $_file );

				if ( is_callable( array( $this, $this->type . '_' . $version_check ) ) ) {
					$_method = $this->type . '_' . $version_check;
					$this->$_method();
				}

				$queries = $db->splitSql( $contents );
				foreach ( (array) $queries as $query ) {
					$db->setQuery( $query );
					$db->query();
				}

				$version_check++;
			} else {
				$no_more = true;
			}
		}

		return --$version_check;
	}
}
