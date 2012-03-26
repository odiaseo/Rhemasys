<?php
				
$default       = "jQuery('#TOOLBAR_ID').append(\"<div id='BTN_ID' title='TITLE' class='ui-pg-div ui-corner-all ui-section'>";	 
$default      .= "<span class='ui-icon ui-icon-newwin ui-toolbar-icon'></span>TITLE</div>\"); ";    
	     		 
	     
 $default     .= " jQuery('#BTN_ID', '#TOOLBAR_ID').click(function(){  
    				var gsr = jQuery('#GRID_ID').jqGrid('getGridParam','selrow'); 
    				if(gsr){ 
    					var parm 		= {};
	    				var rowdata 	= jQuery('#GRID_ID').jqGrid('getRowData',gsr);
						SCRIPT
    				} else { 
    					jQuery(noRow).dialog('open');
    				} 
    			}); ";		
 //================================= add subsite license ==================================					
$button['add-licence']    =   " parm.ssid 	= gsr;  
							parm.method			= 'site-licence';
							 
							jQuery.get('URL',parm,function(data, status){
								rms.showSiteLicence(data, 'LINK', gsr);
							},'html'); "; 	
//================================= view template sections ==================================					
$button['sections']    =   "parm.template_id 	= gsr;  
							parm.method			= 'template-sections';
							 
							jQuery.get('URL',parm,function(data, status){
								rms.showSections(data, 'LINK');
							},'html'); ";  
//================================ template fields ===========================================								 
$button['fields']    = "  	parm.title		= rowdata.title;
	    					parm.id         = gsr;
	    					parm.method		= 'template-fields';
	    		            parm.nb         = rms.poison();
	    					jQuery.get('URL', parm, function(data,status){
	    			 			data.title = 'Fields -' + rowdata.title;	
	    			 			rms.showFields(data); 	    			 		    			 	
	    					}, 'json'); ";
	    					
//============================== help field layout ========================================
$button['layout']  = " 	parm.template_id    = gsr; 
						parm.title          = rowdata.title; 
					    parm.nb             = rms.poison();
						jQuery.get('URL',parm,function(result, status){					
							rms.setupLayout(result);
						}, 'json');	 ";
					
//============================ page layout ===============================================
$button['page-layout'] = "  parm.title         = rowdata.title;
						    parm.template_id   = rowdata.template_id;
						    parm.page_id       = gsr;
						    parm.nb            = rms.poison();
							jQuery.get('URL',parm,function(result, status){									
								rms.setupPageLayout(result,rowdata);
							}, 'json');	 ";
						
//=============================  Assigne product to categories ============================================
 $button['product-category']	   = " parm.product_id       = gsr; 					    
						jQuery.get('URL',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.showCategory(result, gsr, 'LINK');							 
						}, 'json');	"; 
//============================= preview page ============================================
 $button['preview']	= " parm.page_id       = gsr;
					    parm.bypass		= 1;
					    parm.method		= 'page-preview';
					    
						jQuery.get('URL',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.previewPage(result);							 
						}, 'json');	"; 
 
//========================================================================================
 $button['attributes'] = "  parm.title         = rowdata.title; 
						    parm.template_id   = gsr;
						    parm.tm            = rms.poison();
							jQuery.get('URL',parm,function(result, status){									
								rms.assignAttributes(result,rowdata);
							}, 'json');	 ";
 
 //=============================  Manage Form Layout ============================================
 $button['manage-form']	   = " parm.web_form_id       = gsr; 
 						var rowdata 		= jQuery('#GRID_ID').jqGrid('getRowData',gsr);	
 						parm.table_id		= rowdata.admin_table_id;				    
						jQuery.get('URL',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.manageForm(result);							 
						}, 'json');	"; 
 
 //=============================  Affiliate Network data import  ==========================
 $button['field-mapping']	   = " parm.affiliate_network_id       = gsr; 
 						var rowdata 		= jQuery('#GRID_ID').jqGrid('getRowData',gsr);	
 						parm.table_id		= rowdata.admin_table_id;				    
						jQuery.get('URL',parm,function(result, status){	
						      result.title = rowdata.title;							      		
						      rms.manageForm(result);							 
						}, 'json');	"; 
//============================ initialise menu tree ====================================					
 $button['tree-setup'] = " rms.setupMenuTree('#MENU_ID'); ";