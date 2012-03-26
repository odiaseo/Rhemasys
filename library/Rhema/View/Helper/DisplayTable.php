<?php
	/**
	 * This view helps is used to transforms table columns into
	 * forms dynamically based on column information retrieve
	 * using Doctrine
	 *
	 */
	class Rhema_View_Helper_DisplayTable extends Zend_View_Helper_Abstract {

		protected $_form;
		protected $_aLegentTitle = array(
			'CheckBox'		=>  ' Display Options'
		);

		public function getNameFromModel($model){
 			$parts = explode('_', $model);
 			$len   = count($parts);
 			$name  = 'frm' . $parts[$len - 1];
 			return $name;
 		}



		public function displayTable($model){
			$this->_form		 = new Rhema_Form_Table_Abstract();
			$this->_form->setName($this->getNameFromModel($model));
			$table   			 = Doctrine_Core::getTable($model);
			$elmGroups           = array();

			$global['cols'] 	 = $table->getColumns();
			$global['rel'] 		 = $table->getRelations();

			//sort table columns by data type

			foreach($global['cols'] as $col => $arr){
				$param      = array();
				$filter     = array('StringTrim');
				$label      = ucfirst($col);

				if(substr($col,-2) == 'id'){
					//$related[$col]      = $arr;
					$element    		= 'Select';
				}else{
					$dataType 	= $arr['type'];

					switch ($dataType){
						case 'integer':
							$element = 'Checkbox';
							break;
						case 'timestamp':
							$element = 'DatePicker';
							break;
						case 'string':
							if($col == 'image'){
								$element = 'File';
								$label   = 'Image file upload';
								$this->_form->setEnctype('multipart/form-data');
							}else{
								$element   = $arr['length'] > 100 ? 'Textarea' : 'Text';
							}

							break;
						default:
							$element   = 'Text';
					}
				}
				$param['filter']   = $filter;
				$param['label']    = $label;
				$param['value']    = '';
				$param['required'] = isset($param['notnull']) ? $param['notnull'] : false ;

				$elmGroups[$element][]   = $col;
				$this->_form->addElement($element, $col, $param);

			}

			foreach($elmGroups as $grp => $data){
				$legend = isset($this->_aLegentTitle[$grp]) ? $this->_aLegentTitle[$grp] : $grp;
				$this->_form->addDisplayGroup($data, $grp, array('legend' => $legend));
				$$grp   = $this->_form->getDisplayGroup($grp);
				$$grp->setDecorators(array(
					'FormElements',
					'FieldSet',
					array('HtmlTag', array('tag' => 'div', 'class' => strtolower("$grp-div")))
				));
			}

			$this->_form->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'div', 'style' => 'display:block')), 'Form'
			));

			return $this->_form->render();
		}



	}