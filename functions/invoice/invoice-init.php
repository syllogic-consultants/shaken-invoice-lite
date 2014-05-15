<?php
/*
 * This is a customized version of "WordPress 3 Invoice" by Elliot Condon.
 * You can view the original plugin at http://wordpress.org/extend/plugins/wordpress3-invoice/
 *
 * Version: 1.0.0
 * License: GPL
*/

define('SH_INVOICE_DIR', get_template_directory_uri().'/functions/invoice/');

// Basic
require_once('core/functions.php');
require_once('core/invoice.php');
require_once('core/client.php');
require_once('admin/options.php');

global $sh_invoice;
$sh_invoice = new sh_invoice();

class sh_invoice
{ 
	var $name;
	var $dir;
	var $path;
	var $siteurl;
	var $wpadminurl;
	var $version;
	
	var $invoice;
	var $client;
	var $stats;
	var $options;
	
	function __construct()
	{
		
		// set class variables
		$this->name = __('Shaken Invoice','sh_invoice');
		$this->path = dirname(__FILE__).'/';
		$this->dir = SH_INVOICE_DIR;
		$this->siteurl = home_url();
		$this->wpadminurl = admin_url();
		$this->version = '1.0.0';
		
		$this->invoice = new Invoice($this);
		$this->client = new Client($this);
		$this->options = new Shaken_Options($this);
		
		add_action('admin_head', array($this,'admin_head'));
		add_action('admin_menu', array($this,'create_menu'));
		
		// Prevent canonical URL auto direct - bad for encrypted invoices
		remove_filter('template_redirect', 'redirect_canonical');
		
		return true;
	}

	/**
	 * Adds Style + Javascript to admin head
	 *
	 * @author Sawyer
	 * @since 1.0.0
	 * @Todo - only add to sh_invoice admin pages
	 * 
	 **/
	function admin_head()
	{
		global $post;
		// 1. add style + jquery to all invoice related pages
		if(get_post_type($post->ID) == 'invoice' || $_GET['post_type'] == 'invoice') 
		{
			echo '<link rel="stylesheet" href="'.$this->dir.'admin/style.css" type="text/css" media="all" />';	
			echo '<script type="text/javascript" src="'.$this->dir.'admin/admin-jquery.js" ></script>';
		}
	}
	
	/**
	 * Creates Admin Menu
	 *
	 * @author Sawyer
	 * @since 1.0.0
	 *
	 **/
	function create_menu() {
		add_submenu_page('edit.php?post_type=invoice', __('Options','sh_invoice'), __('Options','sh_invoice'), 'manage_options','options', array($this->options,'admin_page'));
	}
	
	/**
	 * Copy a folder
	 *
	 * @author Clay Lua: http://hungred.com/how-to/prevent-wordpress-plugin-update-deleting-important-folder-plugin/
	 * @since 1.0.0
	 *
	 **/
	function sh_invoice_copy($source, $dest)
	{
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}
	
		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}
	
		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}
	
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
	
			// Deep copy directories
			$this->sh_invoice_copy("$source/$entry", "$dest/$entry");
		}
	
		// Clean up
		$dir->close();
		return true;
	}
	
	/**
	 * Remove a folder
	 *
	 * @author Aidan Lister: http://putraworks.wordpress.com/2006/02/27/php-delete-a-file-or-a-folder-and-its-contents/
	 * @since 1.0.0
	 *
	 **/
	function sh_invoice_remove($dirname)
	{
		// Sanity check
		if (!file_exists($dirname)) {
			return false;
		}
		
		// Simple delete for a file
		if (is_file($dirname)) {
			return unlink($dirname);
		}
		
		// Loop through the folder
		$dir = dir($dirname);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			
			// Recurse
			$this->sh_invoice_remove("$dirname/$entry");
		}
		
		// Clean up
		$dir->close();
		return rmdir($dirname);
		
	}
	/**
	 * Backup Gateway folder on auto update
	 *
	 * @since 1.0.0
	 *
	 **/	
	function sh_invoice_backup()
	{
		$to = $this->path.'../sh_invoice_backup/';
		$from = $this->path.'gateways/';
		if(is_dir($from))
		{
			$this->sh_invoice_copy($from, $to);
		}
	}
	/**
	 * Restore Gateway folder on auto update
	 *
	 * @since 1.0.0
	 *
	 **/	
	function sh_invoice_recover()
	{
		$from = $this->path.'../sh_invoice_backup/';
		$to = $this->path.'gateways/';
		if(is_dir($from))
		{
			$this->sh_invoice_copy($from, $to);
			$this->sh_invoice_remove($from);
		}
			
	}
	
	/**
	 * Setup the table for additonal fields in the client taxonomy
	 *
	 * @since 1.0.0
	 *
	 **/
	function sh_client_metadata_setup() 
	{
	
		global $wpdb;
		$charset_collate = '';  
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";
	  
		$tables = $wpdb->get_results("show tables like '{$wpdb->prefix}taxonomymeta'");
		if (!count($tables))
			$wpdb->query("CREATE TABLE {$wpdb->prefix}taxonomymeta (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			taxonomy_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY taxonomy_id (taxonomy_id),
			KEY meta_key (meta_key)
			) 	$charset_collate;");
	}
	
} // end: sh_invoice