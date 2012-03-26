<?php
	interface Admin_Service_Grid_Adapter_Interface{
		public function getPager($table);
		public function getParam($field);
		public function getLabel($column);
		public function isRestricted($column, $model);
		public function getSortField($table, $default = 'title');
	}