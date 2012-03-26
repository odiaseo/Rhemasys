<?php
	/**
	 * Coverts result array obtained from a database query to a format
	 * that can be passed to jqGrid via ajax calls
	 *
	 */
	class Admin_Service_GridData extends Rhema_Util {
		
		public $_model;
		protected $_columnNames;
		protected $_result;
		protected $_returnData;
		protected $_pager;
		protected $_tableObject;
		public $_gridId    = 'list';
		public $_gridPager = 'pager';
		public $page_type;
		public $type_id;
		
		
		public 		$url;
		public 		$view;
		
		public    $gridParam = array(
			'datatype'		=>  'json', 
			'rowNum'		=> 	20, 
			'autowidth'		=>  'false', 
			'rownumbers'	=>  'true', 			 
			'sortname'		=>  'title', 
			'viewrecords'	=>  'true', 
			'sortorder'		=>  'asc', 
			'caption'		=> 'Table record',
			'forceFit' 		=>  'false',
			'multiselect'	=>  'true',
			'height'		=>  'auto',
			'gridview'    	=>  'true',
			'sortable'		=>  'true',
			'scroll'		=>  1,
			'treeGrid'	    =>  'false',
			'ExpandColumn'  => 'title',
			'width'			=> 646,
			'editHeight'	=> '100%',
			'editWidth'		=> 650,
			'toolbar'		=> '[false,"bottom"]',
			'editPos'		=> '',
			'toolbarScript'	=> '',
			'more'			=> ''
		);
	 
		
		public function __construct($model = null, &$params = array(), $type_id = '', $page_type = '', $ajax = false){
			if($model){
				$this->initGrid($model, $params, $type_id, $page_type, $ajax);
			}
		}
		
		public function initGrid($model = null, &$params = array(), $type_id = '', $page_type = '', $ajax = false){
			$this->page_type 				= $page_type;
			$this->type_id				    = $type_id;
			$this->gridParam['model']       = $model;					
			$this->gridParam['gridId']      = "$model-list"  . $type_id . $page_type;
			$this->gridParam['gridPager']   = "$model-pager" . $type_id . $page_type;
			
			//$request = Zend_Controller_Front::getInstance()->getRequest();
		 	if($ajax){
				$this->gridParam['gridId']      .= rand(1,300);
				$this->gridParam['gridPager']   .= rand(1,300);		 		
		 	}
			
			$this->gridParam['rowList']		= Zend_Json::encode(array(5,10,15,20,30,40,50,100));
			$baseUrl 						= Zend_Registry::isRegistered('baseUrl') ? Zend_Registry::get('baseUrl') : '';
			
			if($page_type){
				Zend_Registry::set('page_type', $page_type);
			}
			
			$colModel        				= $this->getColumnModel($model, $type_id, $this->gridParam['sortname']);	
			 	
			$this->gridParam['columnModelStr'] 	= Zend_Json::encode($colModel);
							
			if(!isset($params['editurl']) and isset($params['url'])){
				$params['editurl'] = $params['url'];
			}
 
			
			$toolbarUrl     = isset($params['toolbarUrl']) ? $params['toolbarUrl'] : '';
			$script  		= self::getToolbarScript($model, $this->gridParam['gridId'], $baseUrl, $toolbarUrl);
			
			if($script){
				$params['toolbarScript'] = $script;
				$params['toolbar']       = '[true, "bottom"]';				
			}
			
			if('User' == substr($model,-4)){
				$params['sortname']	= 'lastname';
			}
			
			$parts = explode('_', $model);
			$last  = strtolower($parts[count($parts) - 1]);
			switch($last){
				case 'component':{
						$params['editWidth'] 	= 950;
						$params['editPos']		= ' top: 150, left: 100, '; 
					break;
				}
			}
			foreach($params as $ind => $val){
				$this->gridParam[$ind] = $val;
			}
			 			
			$params['tableName'] = $this->gridParam['caption'];
			 
		}
		 
		
		public function getColumnModel($table, $type_id = null, &$sortField = 'title'){			
			$reqStr   	= '(*)' . str_repeat('&nbsp;', 2);
			$noReqStr 	= str_repeat('&nbsp;', 6);
			
			$row 		= 0;
			$models 	= array();
			$columns    = $type_id  ? $this->getHelpDocumentFields($table, $type_id) 
									: $this->getTableColumn($table);
			$isTree     = Rhema_Util::isTreeTable($table);
			
			if($isTree){
				//$this->gridParam['treeGrid']  = 'true';
				$this->gridParam['sortname']  = 'lft';
				//$this->gridParam['autowidth'] = 'false';
				//$this->gridParam['width']     = '680';
			}
			
			foreach($columns as $name => $columnData){
				$row++;				
				$colModel     				= $this->confirmDetails($name, $row, $order, $table, $isTree);
				$required   				= (isset($columnData['notnull']) and $columnData['notnull']);
				$colModel['required']      	= $required;
				$formOptions   				= "<span class='field-req req-$name'>" . ($required ? $reqStr : $noReqStr) . '</span>';
	 			$colModel['formoptions']   	= array('rowpos' => $order,'elmprefix' => $formOptions);
				$models[]   = $colModel;
			}
			
			$sortField = isset($columns[$sortField]) ? $sortField : 'id';
			return $models;
		}
		
			
		public static function getHelpDocumentFields($model, $type_id = null){
	    	$mandatory = array();
	    	$hold      = array();
	    	
	    	$allCols   = Doctrine_Core::getTable($model)->getColumns(); 
	    	if($type_id){	
	    		$res          = Help_Model_Field::getMandatoryColumns(); 
	    		$mandatory    = $res['title'];   		
		    	$row          = Help_Model_Type::getItem($type_id);

		    	$query        = Doctrine_Query::create()
		    	                ->select('tf.field_id,f.title')
		    					->from("Help_Model_TemplateField tf")
		    					->where('tf.template_id =?', $row['template_id'])
		    					->leftJoin('tf.Field f')
		    					//->leftJoin('tf.Field q')
		    					//->orderBy('f.id')
		    					->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY);
		    	$selectedCols = $query->execute(array()); 
		    	$len          = count($selectedCols);  
		    	
		    	if($len){
		    		for($i=0; $i<$len; $i++){
		    			$tit         = $selectedCols[$i]['Field']['title'];
		    			$hold[$tit ] = $allCols[$tit];
		    		}
		    	} 		    		
	    	} 
	    	 
	    	if(is_array($mandatory))
	    		$temp = array_flip($mandatory);
	    	foreach($allCols as $tit => $item){
	    		if(isset($temp[$tit]) or isset($hold[$tit])){
	    			$return[$tit] = $item;
	    		} 
	    	}
	    	
	    	 
	    	$return = count($return) ? $return : $allCols;
 
	    	return $return;
	    }	 
		
		public function getTableColumn($table){
			return Doctrine_Core::getTable($table)->getColumns(); 
		}
 
		public function getCellData($row){ 
			foreach($row as $key => $value){				
				if(Rhema_Util::dontEdit($key)){
					$end[$key] = $value;
				}else{
					$return[] = $value;
				}
			}
			$return[] = $end['level'];
			$return[] = $end['lft'];
			$return[] = $end['rgt'];
			$return[] = (($end['lft'] + 1) == $end['rgt']) ? 'true' : 'false';
			$return[] = 'false';
			return $return;
		}
		
		public function getHelpCellData($row,$type_id){
			$return = array();
			$model  = HELP_PREFIX . 'Document';
			$cols   = $this->getCached()->getHelpDocumentFields($model, $type_id);
			foreach($cols as $title => $temp){
				$return[] = isset($row[$title]) ? $row[$title] : '';
			}
			return $return;
		}
		
		public function format($type = 'json', $type_id = null){
			$return   = '';
			$isTree   = Rhema_Util::isTreeTable($this->gridParam['model']);
			$this->_returnData->page    = $this->_pager->getPage();
			$this->_returnData->total   = $this->_pager->getLastPage();
			$this->_returnData->records = $this->_pager->getNumResults();
			
			if(is_array($this->_result)){
				$records 			= count($this->_result);				 			
				for($i=0; $i < $records; $i++){		
					$this->_returnData->rows[$i]['id']	 = $this->_result[$i]['id'];
					$this->_returnData->rows[$i]['cell'] = $type_id ? $this->getHelpCellData($this->_result[$i],$type_id) : array_values($this->_result[$i]);	 
					//$this->_returnData->rows[$i]['cell'] = array_values($this->_result[$i]);	
				}
   						
    			switch($type){
					case 'json' :
					default:{
						$return = Zend_Json::encode($this->_returnData);
					}
				}
			}	
			
			if($isTree) $this->gridParam['treedatatype'] = 'json';
			
			return $return;
		}
		
		public function getGridData(){
			$front      = Zend_Controller_Front::getInstance();
			$request 	= $front->getRequest();
	    	$page 		= $request->getParam('page'); // get the requested page 
	    	$limit 		= $request->getParam('rows'); // get how many rows we want to have into the grid 
	    	$sidx 		= $request->getParam('sidx','id'); // get index row - i.e. user click to sort 
	    	$sord 		= $request->getParam('sord','ASC'); // get the direction    
	    	$root_id    = $request->getParam('root_id',null);
	    	$doSearch   = $request->getParam('_search');
	    	$type_id    = $request->getParam('type_id', null);
	    	$page_type  = $request->getParam('page_type', null);
	    	 
	    	
	    	$table		= $this->gridParam['model'];	    	
	    	$isTree     = Rhema_Util::isTreeTable($table);
	    	
	    	
	    		 
    		$query = Doctrine_Query::create()
    						->from($table . ' t')
    						->orderBy("t.$sidx $sord");
	    	if(substr($table,-4) == 'Menu'){
	    		$query->where('t.level <> ?', 0);
	    		if($root_id){
	    			$query->andWhere('t.root_id =?', $root_id);
	    			//$query->orWhere('t.root_id IS NULL');
	    		}
	    		 
	    	}
	    	
	    	if(ADMIN_PREFIX . 'AdminTable' == $table){
	    		$query->andWhere('t.is_hidden = ?', 0);
	    	}
	    	
	    	if(HELP_PREFIX . 'Document' == $table and $type_id){
	    		$query->andWhere('t.type_id = ?', $type_id);
	    	}

	    	if(MODEL_PREFIX . 'Page' == $table){
	    		if($page_type){
	    			$query->andWhere("t.$page_type = ?", 1);
	    		}else{
	    			$query->andWhere('t.is_header = ?', 0);
	    			$query->andWhere('t.is_footer = ?', 0);
	    		}
	    	}
	    	
	    	
	    	if($doSearch     == 'true'){
	    		$operator     = $request->getParam('searchOper');
	    		$searchField  = $request->getParam('searchField');
	    		$searchString = $request->getParam('searchString');
	    		
	    		$args         = array_filter(explode(',', $searchString));
	    		
	    		for($i=0; $i<count($args); $i++){	    		
	    			Rhema_Util::transformSearch($query, $searchField, $args[$i], $operator);
	    		}
	    	}
	    		    	
	    	$sidx       = $sidx  ? $sidx  : 'id';	    	
	    	$pager      = new Doctrine_Pager($query, $page, $limit);
	    	$pager->setCountQuery($query);
	    	
	    	$this->_result =  $pager->execute(array(),Doctrine::HYDRATE_ARRAY); 
	    	$this->_pager  = $pager;
	    	
	    	return $this;
		}	
 
		
		public function generateScript($ajaxCall = false){ 
			
			$prm     = $this->gridParam; 
			
			$script  = $ajaxCall ? '' : 'jQuery(document).ready(function(){';
				
				$script .= " var lastsel;
					var noRow = rms.initDialog('No Selection','Please select row');
					 jQuery('#$prm[gridId]').jqGrid({ 
						url			: '$prm[url]', 
						editurl		: '$prm[editUrl]',
						datatype	: '$prm[datatype]', 
						colModel	: $prm[columnModelStr], 
						rowNum		: $prm[rowNum], 
						autowidth	: $prm[autowidth], 
						rownumbers	: $prm[rownumbers], 
						rowList		: $prm[rowList], 
						pager		: '#$prm[gridPager]', 
						sortname	: '$prm[sortname]', 
						viewrecords	: $prm[viewrecords], 
						sortorder	: '$prm[sortorder]', 
						caption		: '$prm[caption]',
						forceFit 	: $prm[forceFit],
						multiselect	: $prm[multiselect],
						height		: 'auto',
						width		: '$prm[width]',
						gridview    : $prm[gridview],
						sortable	: $prm[sortable],
						toolbar	    : $prm[toolbar],
						editcell    : true,
						treeGrid	: $prm[treeGrid],
						loadui      : 'disable',
						viewsortcols: true,
						$prm[more]
						ExpandColumn:  'title',
						/*onSelectRow : function(id){ 
							if(id && id!==lastsel){ 
								jQuery('#$prm[gridId]').jqGrid('restoreRow',lastsel); 
								jQuery('#$prm[gridId]').jqGrid('editRow',id,true,pickdates); 
								lastsel = id; 
							} 
						},*/
						loadError : function(xhr,st,err) { 
							jQuery('#$prm[gridId]').html('Type: '+st+'; Response: '+ xhr.status + ' ' +xhr.statusText); 
						}  
					}); 
					
				 	jQuery('#$prm[gridId]').jqGrid('navGrid','#$prm[gridPager]',
						{view:true, refresh:true, search:true, add:true, edit:true, del:true}, //options 
						{	height:'auto',
							width:'$prm[editWidth]',
							reloadAfterSubmit:false, 
							jqModal:true, 
							closeOnEscape:false, 
							recreateForm: true,
							modal:true,
							$prm[editPos]
							bottominfo:'Fields marked with (*) are required',
							beforeShowForm: function(frmId){ 
 								rms.prepareForm(frmId, '#$prm[gridId]');
							},
							onClose: function(frmId){
								rms.cleanUpForm(frmId);
							} 
						}, // edit options 
						{	height:'auto',
							width:'$prm[editWidth]',
							reloadAfterSubmit:false,
							jqModal:true, 
							closeOnEscape:false,
							recreateForm: true,
							modal:true,
							bottominfo:'Fields marked with (*) are required', 
							closeAfterAdd: false,
							$prm[editPos]
							beforeShowForm: function(frmId){ 
 								rms.prepareForm(frmId,'#$prm[gridId]');
							},
							onClose: function(frmId){
								rms.cleanUpForm(frmId);
							}, 
						}, // add options 
						{	reloadAfterSubmit:false,
							jqModal:false, 
							closeOnEscape:true
						}, // del options 
						{
							closeOnEscape:true
						}, // search options 	
						{	multipleSearch:true,
							width:'$prm[editWidth]',
							height:'auto',
							$prm[editPos]
						}	
					); 
					
					
					
					jQuery('#$prm[gridId]').jqGrid('navButtonAdd','#$prm[gridPager]',
						{ caption: 'Columns', 
						title: 'Reorder Columns', 
						onClickButton : function (){ 
							jQuery('#$prm[gridId]').jqGrid('columnChooser'); } 
					}); 
					
					jQuery('#$prm[gridId]').jqGrid('gridResize',{
						minWidth:350,
						maxWidth:800,
						minHeight:80, 
						maxHeight:450
					});
				 
					$prm[toolbarScript]
					
					jQuery('.multiselect').multiselect();";
					
			 	$script .= $ajaxCall ? '' : "});
					
			 	 function pickdates(sel){
			 	 	jQuery('[id^='+sel+'][id$=_at]','#$prm[gridId]').datepicker({dateFormat:'yy-mm-dd'});
					//jQuery('#'+id+'_Created_at'','#$prm[gridId]').datepicker({dateFormat:'yy-mm-dd'});  
				 };"; 			
					
			 if($ajaxCall){
			   return $script;	
			 }else{			 
				$name     = '/temp/' . strtolower($prm['gridId'] . '.js');
			 	file_put_contents(getcwd() . SCRIPT_PATH . $name, $script);
			 	Rhema_Util::setHeaderFiles($name);
			 } 
		}
			    
		public static function getToolbarScript($table, $gridId, $baseUrl, $url){
			$return  = '';
			
			switch ($table){
				case ADMIN_PREFIX . 'AdminTemplate' :{
					$urlParm  = array('module'=>'admin','controller'=>'grid','action'=>'section');
					$aurl      = Zend_Controller_Front::getInstance()->getRouter()->assemble($urlParm,ADMIN_ROUTE);	
					$button = '<div id="toolbar-button" class="ui-pg-div ui-corner-all ui-section"><span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span> View Sections</div>';
					
					$return = "jQuery('#t_$gridId').append('$button'); 
					jQuery('.ui-section','#t_$gridId').click(function(){
					    var gr = jQuery('#$gridId').jqGrid('getGridParam','selrow');  				
						if( gr != null ){	
							var parm 			= {};
							parm.template_id 	= gr; 
							parm.bypass			= 1;	
							parm.method			= 'template-sections';
							 
							jQuery.get('$aurl',parm,function(data, status){
								jQuery('#show-sections').html(data).show('slow').draggable();
								jQuery('.multiselect').multiselect({ 
									dividerLocation: 0.5
								});
								
								jQuery('#btn-close').bind('click',function(){
									jQuery('#show-sections').hide('slow');
								});
								
								jQuery('#btn-ok').bind('click',function(){
									var sxns = jQuery('select[id=\"section_id\"]').val();
									var temp = jQuery('input[id=\"template_id\"]').val();
									 
									var parm = {'template_id': temp, 'section_id[]':sxns}; 
									
									jQuery.post('$baseUrl/admin/grid/section/task/save',parm,function(){},'json');
								});
								
							},'html'); 
						}else {
							alert('Please Select Row');
						}
					});"; 
		
				break;
				}
				case HELP_PREFIX .'Template':{	
					$urlParm  = array('module'=>'help','controller'=>'design','action'=>'assign');
					$furl      = Zend_Controller_Front::getInstance()->getRouter()->assemble($urlParm,ADMIN_ROUTE);					 
					$fields   = '<div id="view-fields" title="View template fields" class="ui-pg-div ui-corner-all ui-section">';
					$fields  .= '<span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span> Fields</div>';
						    
 
				    $return   .= "					 
				     jQuery('#t_$gridId').append('$fields'); 
				     jQuery('#view-fields', '#t_$gridId').click(function(){  
				    		var gsr = jQuery('#$gridId').jqGrid('getGridParam','selrow'); 
				    		if(gsr){ 
				    			var parm 		= {};
				    			parm.bypass		= 1;
				    			parm.id         = gsr;
				    			parm.method		= 'template-fields';
				    		     var rowdata = jQuery('#$gridId').jqGrid('getRowData',gsr);
				    			 jQuery.get('$furl',parm, function(data,status){
				    			 	data.title = 'Fields -' + rowdata.title;	
				    			 	rms.showFields(data); 	    			 		    			 	
				    			 }	, 'json');
				    		} else { 
				    			jQuery(noRow).dialog('open');
				    		} 
				    }); ";
		    
					//$fields   = '<div id="view-plates" title="Boiler Plates" class="ui-pg-div ui-corner-all ui-section">';
					//$fields  .= '<span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span> Boiler Plates</div>';
					
	 
					$layout   = '<div id="edit-layout-btn" class="ui-pg-div ui-corner-all ui-section" title="Edit template layout">';
					$layout  .= '<span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span> Layout</div>';
					
					$urlParm  = array('module'=>'help','controller'=>'design','action'=>'layout');
					$lurl     = Zend_Controller_Front::getInstance()->getRouter()->assemble($urlParm,ADMIN_ROUTE);
						
 					$return  .= " 
						jQuery('#t_$gridId').append('$layout'); 
						jQuery('#edit-layout-btn','#t_$gridId').click(function(){ 
						    var gsr = jQuery('#$gridId').jqGrid('getGridParam','selrow'); 
							if(gsr){ 
								var parm 			= {};
								parm.template_id    = gsr;
								parm.bypass		    = 1;
								parm.method         = 'help-layout';
								
							    var rowdata = jQuery('#$gridId').jqGrid('getRowData',gsr);
								jQuery.get('$lurl',parm,function(result, status){	
								    result.title = rowdata.title;								
									rms.setupLayout(result);
								}, 'json');	
							}else { 
			    				jQuery(noRow).dialog('open');
			    			} 
						});  "; 
 					break; 
				} 
				case MODEL_PREFIX . 'Page' :{	 
					$layout   = '<div id="edit-page-btn" class="ui-pg-div ui-corner-all ui-section" title="Edit page layout">';
					$layout  .= '<span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span>Edit Layout</div>';

 					$return  .= " 
						jQuery('#t_$gridId').append('$layout'); 
						jQuery('#edit-page-btn','#t_$gridId').click(function(){ 
						    var gsr = jQuery('#$gridId').jqGrid('getGridParam','selrow'); 
							if(gsr){ 
							    var rowdata = jQuery('#$gridId').jqGrid('getRowData',gsr);
							    var param           = {};
							    param.title         = rowdata.title;
							    param.template_id   = rowdata.admin_template_id;
							    param.page_id       = gsr;
							    param.bypass		= 1;
							    param.method        = 'page-layout';
								jQuery.get('$url',param,function(result, status){									
									rms.setupPageLayout(result,rowdata);
								}, 'json');	
							}else { 
			    				jQuery(noRow).dialog('open');
			    			} 
						});  "; 
 					 
					$preview   = '<div id="preview-page-btn" class="ui-pg-div ui-corner-all ui-section" title="Preview webpage">';
					$preview  .= '<span class="ui-icon ui-icon-newwin ui-toolbar-icon"></span>Preview</div>';
					 
					
 					$return  .= " 
						jQuery('#t_$gridId').append('$preview'); 
						jQuery('#preview-page-btn','#t_$gridId').click(function(){ 
						    var gsr = jQuery('#$gridId').jqGrid('getGridParam','selrow'); 
							if(gsr){ 
							    var rowdata = jQuery('#$gridId').jqGrid('getRowData',gsr);
							    var param           = {};
							    param.page_id       = gsr;
							    param.bypass		= 1;
							    param.method		= 'page-preview';
							    
								jQuery.get(baseUrl+'/cms/design/url',param,function(result, status){	
								      result.title = rowdata.title;							      		
								      rms.previewPage(result);							 
								}, 'json');					    
							
							}else { 
			    				jQuery(noRow).dialog('open');
			    			} 
						});  "; 
 					break; 
				}
			}
				
			return $return;
		}
	    		
	    public function confirmDetails($col, $cnt, &$order=0, $model = null, $isTree = false){ 
			$show    = false; // do not shot empty select as first option
			$default = array('index'  		=> $col,
							'name'   		=> $col,
							//'width'  		=> 120,
							'editable' 		=> true,
							'edittype'  	=> 'text',
							'required'  	=> true,
							'sortable'  	=> true,
							'readonly'  	=> false,
							'editrules'	    => array('edithidden' => true),
							'label'         => Rhema_Util::getLabel($col)
			);

			$order			            = $cnt+1;
			if('id' == $col){
				$default['editable']    = false;
				$default['readonly']    = true;
				$default['required']    = false; 				
				$default['key'] 		= true;		 
				$default['editable']    = false;
				$default['width']     	= 50;
			}elseif(Rhema_Util::dontEdit($col)){
	    		$default['hidden']  = true;
	    		$default['editrules']['edithidden']  = false;
	    		$default['editable'] = false;					
			}elseif('version' == $col){
	    		$default['editable']  	= false;
	    	}elseif(preg_match('/^(content|description|summary|keyword|note|related_item)$/i', $col)){
				$default['edittype']  = 'textarea';
				$default['width']     = 200;
				switch ($col){
	    			case 'content':
	    				$order          += 600;
	    			case 'note'   :    
	    				$order  		+= 300;
	    				$default['editrules']['edithidden']  = true;
	    				$default['hidden'] 		= true;
	    				$default['editable']  	= true;
	    				break; 
	    				
	    			default:
	    				$order          += 300;
	    				$default['hidden']      = false;
	    		}			
			}elseif('content' == $col){
				$default['hidden']      = true;
			}elseif('_at' == substr($col,-3)){
				$default['edittype']  = 'text';
				$default['formatter'] = 'date';
				if(preg_match('/^(created_at|updated_at|deleted_at)$/i', $col)){
					$default['editrules']['edithidden']  = false;
					$default['editable']    = false;
					$default['hidden']		= true;									
				}
			}elseif('_id' == substr($col,-3) and 'root_id' != $col){
				$field     = 'title';
				$key       = 'id';
				$table     = $col;
				$default['number']		    = true;
				if('admin_subsite_id' == $col){
					$default['editable'] = false;
				}elseif('user_id' == $col){
					$field 	=  'lastname';
				}elseif('admin_table_id' == $col){
					$key    = 'name';	
					$default['number']	= false;			
				}elseif(preg_match('/^(header_id|footer_id)$/i', $col)){
					$table    = 'page_id';
					$pageType = 'is_' . substr($col,0,-3);
					Zend_Registry::set('page_type', $pageType);
				}
				
				$default['edittype']  		= 'select';
				$default['editoptions']['value']     = Rhema_Util::getEditOptions($model,$table,$key, $field, $show, $col);
				$default['width']     		= 150;
				$order          		   += 40;
			}elseif(preg_match('/^(module|controller|action|module_dir)$/i', $col)){
				$default['edittype']  	= 'select';
				$options 				= array('' => 'select', 'index' => 'index');
				$select                 = true;
				$default['hidden']      = true;
				
				switch($col){
					case 'module_dir':
					case 'module':{
						$options =  Rhema_Util::getDir('/modules');
						break;
					} 
					case 'controller':{ 
						break;
					}
					case 'action':{
						if(MODEL_PREFIX . 'Menu' == $model){
							$default['edittype']  	= 'text';
							$select = false;
						}
						break;
					} 
				} 
				
				if($select){
					$default['editoptions']['value'] = $options;
				}
				
			}elseif('is_' == substr($col,0,3)){
				$default['formatter'] = 'checkbox';
				$default['edittype']  = 'checkbox';
				$default['width']     = 50;
				$default['editoptions']['value'] ="1:0";
				$default['align']		 = 'center'; 
	    		
				$order               += 20;
			}elseif('image' == $col){
				//$default['edittype']  = 'file';
				$default['width']     = 60;
				$order          	 += 50;
			}elseif(preg_match('/email$/i', $col)){
				$default['editrules']['email']     = true;
			}elseif(substr($col,-3) == '_by'){
	    		$default['editable']  	= false;
	    		$default['editrules']['edithidden']  = false;
	    	}elseif('sequence' == $col){
	    		$default['editrules']['minValue']  = 1;
	    		$default['editrules']['integer']   = true;
	    		$default['editrules']['number']    = true;
	    	}elseif('level' == $col){
	    		$default['width'] = 50;
	    		$default['align'] = 'center';
	    		$default['editable']  	= false;
	    	}elseif('question' == $col){
	    		$default['edittype']  = 'textarea';
	    		$order          	 += 111; 
	    	}elseif('answer' == $col){
	    		$default['edittype']  = 'textarea';
	    		$order          	 += 152;
	    	}
	    	 
		    return $default	; 
		}
	}