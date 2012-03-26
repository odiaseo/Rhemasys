<?php
/**
 * Interface to be implemented for backend admin menus
 */
Interface Rhema_Menu_Interface  {
	public function __construct();
	/**
	 * Checks if the logged in user's acces to the current menu
	 * @return unknown_type
	 */
	public function checkAccess();
	public function render();
	public function getAttributes();
}