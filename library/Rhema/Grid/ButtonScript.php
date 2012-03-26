<?php
class Rhema_Grid_ButtonScript{
	
	protected $_scriptTemplate;
	protected $_toolbarId;
	protected $_gridId;
	protected $_buttonId;
	protected $_buttonTitle;
	protected $_script = '';
	protected $_postUrl;
	protected $_title;
	protected $_link;
	protected $_menuId;
	protected $_tableId;
	
	public $view = null;
	
	public function __construct(){		
		if(! $this->view){
			$this->view = Zend_Layout::getMvcInstance()->getView();
		}
	}
	public function getScript($type, $options = array()){
		Rhema_Util::setClassOptions($this, $options); 
		$script  = array();
		$filter = new Zend_Filter_Word_DashToCamelCase();
		$method = $filter->filter('set-' . $type . '-script');
		if(method_exists($this, $method)){
			$this->$method($options );
					
		}else{
			$filename  = basename(__FILE__) ;
			$message   = "Class Method {$method} not found in {$filename} " ; 
			$this->_script = "alert('{$message}');";			
		}
		$script[] = $this->mergeScriptAndVariables($options['alone']);
		return implode(PHP_EOL, $script);
	}
	
	public static function getErrorDialogScript(){
		$default[]  =  'var lastsel;';
		$default[]  =  'var noRow = rms.initDialog("No Selection","Please select row");';
		return  implode(PHP_EOL, $default);
		//Zend_Layout::getMvcInstance()->getView()->headScript()->appendScript($script);
	}
	 
	public function mergeScriptAndVariables($standAlone = false){				
		$default[]  = "jQuery('#{$this->_toolbarId}').append(\"<div id='{$this->_buttonId}' title='{$this->_buttonTitle}' class='ui-pg-div ui-corner-all ui-section'>"
		 			. "<span class='ui-icon ui-icon-newwin ui-toolbar-icon'></span>{$this->_buttonTitle}</div>\"); ";
		$default[] = " jQuery('#{$this->_buttonId}', '#{$this->_toolbarId}').click(function(){ "; 
    				
		
		if($standAlone){			
			$default[] = $this->_script ;			
		}else{
    		$default[] = "var gsr = grid.getGridParam('selrow');"; 
    		$default[] = "if(gsr){"; 
		    $default[] = "var parm 	= {};"; 
			$default[] = "var rowdata 	= grid.getRowData(gsr);"; 
			$default[] = $this->_script;
		    $default[] = "}else {"; 
		    $default[] = "jQuery(noRow).dialog('open');"; 
		    $default[] = "} ";
		}
		$default[] = '});';
		return implode(PHP_EOL, $default) ;
	}
	
	/**
	 * Display the multiselect window to manage subsite licences
	 */
	public function setAddLicenceScript($options){
		//$this->_postUrl = $this->view->url(array(), 'admin-grid-licence');
		//$this->_link = $this->view->url(array('task' => 'save'), 'admin-grid-licence');
		$this->_script = "parm.ssid 	= rowdata.id;  
				parm.method	= 'site-licence';				 
				jQuery.post('{$options['postUrl']}',parm,function(data, status){
					rms.showSiteLicence(data, '{$options['link']}', parm.ssid);
				},'html'); ";
	}
	
	/**
	 * Display the multiselect options to manage template sections
	 */
	public function setSectionsScript(){
		//$this->_postUrl = $this->view->url(array(), 'admin-grid-section');
	//	$this->_link = $this->view->url(array('task' => 'save'), 'admin-grid-section');
		$this->_script = "  parm.template_id 	= rowdata.id;  
							parm.method			= 'template-sections';							 
							jQuery.post('{$this->_postUrl}',parm,function(data, status){
								rms.showSections(data, '{$this->_postUrl}');
							},'html'); ";
	}
	
	/**
	 * Help template fields
	 */
	public function setFieldsScript(){
	//	$this->_postUrl = $this->view->url(array(), 'admin-grid-section');
		$this->_script = "  parm.title		= rowdata.title;
	    					parm.id         = gsr;
	    					parm.method		= 'template-fields';
	    		            parm.nb         = rms.poison();
	    					jQuery.post('{$this->_postUrl}', parm, function(data,status){
	    			 			data.title = 'Fields -' + rowdata.title;	
	    			 			rms.showFields(data); 	    			 		    			 	
	    					}, 'json'); ";
	}
	/**
	 * Help template layout
	 */
	public function setLayoutScript(){
		$this->_script = " 	parm.template_id    = gsr; 
						parm.title          = rowdata.title; 
					    parm.nb             = rms.poison();
						jQuery.post('{$this->_postUrl}',parm,function(result, status){					
							rms.setupLayout(result);
						}, 'json');	 ";
	}
	public function setCategoryMetaScript(){
		$this->_script = " 	parm.id   = rowdata.id;   
					    parm.nb       = rms.poison();
					    parm.task     = 'preview';
					    parm.type     = 'category';
						jQuery.post('{$this->_postUrl}',parm,function(result, status){ 			      		
						      rms.mapColumnField(rowdata, result, true);							 
						}, 'json');	"	;	
	}

	public function setRetailerMetaScript(){
		$this->_script = " 	parm.id    = rowdata.id;  
					    parm.nb       = rms.poison();
					    parm.task     = 'preview';
					    parm.type     = 'retailer';
						jQuery.post('{$this->_postUrl}',parm,function(result, status){ 			      		
						      rms.mapColumnField(rowdata, result, true);							 
						}, 'json');	"	;		
	}
		
	/**
	 * Display window to add page items to layout
	 */
	public function setPageLayoutScript(){
		$this->_script = "  var currentRow =  jQuery('tr#' + gsr);;
							parm.title         = rowdata.title;
						    parm.template_id   = jQuery('#hidden_template_id', currentRow).val() ;
						    // rowdata.template_id;
						    parm.page_id       = gsr;
						    parm.nb            = rms.poison();
							jQuery.post(\"{$this->_postUrl}\",parm,function(result, status){									
								rms.setupPageLayout(result,rowdata); 
							}, 'json');	 ";
	}

	public function setLayoutManagerScript(){
		$this->_script = "  rms.setPageData(\"{$this->_postUrl}\", gsr, grid); ";
	}	
 
	public function setPreviewMappingScript(){
		$this->_script = " parm.id           = rowdata.id; 
		                   parm.task         = 'preview';
		                   parm.type         = 'feed';
		                   parm.nb           = rms.poison();
						jQuery.post('{$this->_postUrl}',parm,function(result, status){ 	
							rms.mapColumnField(rowdata, result, false);							 
						}, 'json');	"	;	
	}
	/**
	 * Display inteface to map databvase columns to feed columns for affiliate marketing
	 */
	public function setFieldMappingScript(){
		$this->_script = " parm.id    = rowdata.id; 
						   parm.task  = 'map-field';
						   parm.nb    = rms.poison();
						jQuery.post('{$this->_postUrl}',parm,function(result, status){ 			      		
						      rms.mapColumnField(rowdata, result, true);							 
						}, 'json');	"	;	
	}
	/**
	 * Screen to assign products to categories.
	 * A product can be in multiple categories in a one-to-many relationship
	 */
	public function setProductCategoryScript(){
		$this->_script = " parm.product_id     = gsr;
						   parm.nb             = rms.poison(); 					    
						jQuery.post('{$this->_postUrl}',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.showCategory(result, gsr, '$this->_link');							 
						}, 'json');	";
	}
	
	/**
	 * On the page layout admin page, gives the option to preview the
	 * page from the backend without having to switch to site view
	 */
	public function setPreviewScript(){
		$this->_script = " parm.page_id       = gsr;
					    parm.bypass		= 1;
					    parm.method		= 'page-preview';
					    parm.nb           = rms.poison();
						jQuery.post('{$this->_postUrl}',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.previewPage(result);							 
						}, 'json');	";
	}
	
	/**
	 * Set product display template attributes in design a customised
	 * page layout on product detail view
	 */
	public function setAttributesScript(){
		$this->_script = "  parm.title        = rowdata.title; 
						    parm.template_id   = gsr;
						    parm.tm            = rms.poison();
							jQuery.post('{$this->_postUrl}',parm,function(result, status){									
								rms.assignAttributes(result,rowdata);
							}, 'json');	 ";
	}
	
	/**
	 * Manage Form Layout
	 */
	public function setManageForm(){
		$this->_script = " parm.web_form_id       = gsr; 
 						var rowdata 		= jQuery('#{$this->_gridId}').jqGrid('getRowData',gsr);	
 						parm.table_id		= rowdata.admin_table_id;				    
						jQuery.post('{$this->_postUrl}',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.manageForm(result);							 
						}, 'json');	";
	}
	
	/**
	 * This setup the page menu in a tree like structure in the menu layout screen
	 */
	public function setTreeSetupScript(){
		$this->_script = " rms.setupMenuTree('#{$this->_menuId}'); ";
	}
	
	public function setUpdateAclScript(){
		$this->_script = " rms.updateAcl('{$this->_postUrl}', '#{$this->_tableId}'); "; 
	}
	/**
	 * @return the $_scriptTemplate
	 */
	public function getScriptTemplate(){
		return $this->_scriptTemplate;
	}
	
	/**
	 * @return the $_toolbarId
	 */
	public function getToolbarId(){
		return $this->_toolbarId;
	}
	
	public function getTitle(){
		return $this->_title;
	}
	
	public function setTitle($_title){
		$this->_title = $_title;
		return $this;
	}
	/**
	 * @return the $_gridId
	 */
	public function getGridId(){
		return $this->_gridId;
	}
	
	/**
	 * @return the $_buttonId
	 */
	public function getButtonId(){
		return $this->_buttonId;
	}
	
	/**
	 * @return the $_buttonTitle
	 */
	public function getButtonTitle(){
		return $this->_buttonTitle;
	}
	
	/**
	 * @return the $_postUrl
	 */
	public function getPostUrl(){
		return $this->_postUrl;
	}
	
	/**
	 * @param field_type $_scriptTemplate
	 */
	public function setScriptTemplate($_scriptTemplate){
		$this->_scriptTemplate = $_scriptTemplate;
	}
	
	/**
	 * @param field_type $_toolbarId
	 */
	public function setToolbarId($_toolbarId){
		$this->_toolbarId = $_toolbarId;
	}
	
	/**
	 * @param field_type $_gridId
	 */
	public function setGridId($_gridId){
		$this->_gridId = $_gridId;
	}
	
	/**
	 * @param field_type $_buttonId
	 */
	public function setButtonId($_buttonId){
		$this->_buttonId = $_buttonId;
	}
	
	/**
	 * @param field_type $_buttonTitle
	 */
	public function setButtonTitle($_buttonTitle){
		$this->_buttonTitle = $_buttonTitle;
	}
	
	/**
	 * @param field_type $_postUrl
	 */
	public function setPostUrl($_postUrl){
		$this->_postUrl = $_postUrl;
	}
	/**
	 * @return the $_menuId
	 */
	public function getMenuId(){
		return $this->_menuId;
	}
	
	/**
	 * @param field_type $_menuId
	 */
	public function setMenuId($_menuId){
		$this->_menuId = $_menuId;
	}
	/**
	 * @return the $_tableId
	 */
	public function getTableId(){
		return $this->_tableId;
	}

	/**
	 * @param field_type $_tableId
	 */
	public function setTableId($_tableId){
		$this->_tableId = $_tableId;
	}


}