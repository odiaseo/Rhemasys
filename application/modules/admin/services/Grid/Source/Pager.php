<?php
	class Admin_Service_Grid_Source_Pager extends Bvb_Grid_Source_Doctrine {
		
		protected $_pager;
		
	    public function __construct(Doctrine_Pager $pager)    { 
	    	
	        if (!$pager instanceof Doctrine_Pager) { 
	            throw new Bvb_Grid_Source_Doctrine_Exception(
	                "Please provide only an instance of Doctrine_Pager"
	                 . "or a valid Doctrine_Record instance"
	            );
	        }else{
	        	$q            = $pager->getQuery();
	        	$this->_pager = $pager;
	        	$this->_query = $q;
	        	$this->_setFromParts();
	       	    $this->_setSelectParts();
	        }
	    }
	    
	    public function getTotalRecords()    {
	        return (int) $this->_pager->getNumResults();
	    }
	    
	    public function execute(){
	    	$this->_pager->getQuery()->setHydrationMode(Doctrine::HYDRATE_SCALAR);	    	 
	        $results  = $this->_pager->execute();	        
	        $newArray = $this->_cleanQueryResults($results);
	        
	        return $newArray;
	    }	
	    
	    public function getSelectOrder()	    {
	        $newOrderBys = array();
	        $orderBy = $this->_query->getDqlPart('orderby');
	        
	        if (!empty($orderBy)) {
	            foreach ($orderBy as $anOrderby) {
	                $orderBys = explode(',', $anOrderby);
	                
	                foreach ($orderBys as $order) {
	                    $parts = explode(' ', trim($order));
	                    if (strtolower($parts[1]) != 'desc' && strtolower($parts[1]) != 'asc') {
	                        $parts[1] = '';
	                    }
	                    $newOrderBys[] = $parts;
	                }
	            }
	        }
	        
	        return count($newOrderBys) == 1 ? $newOrderBys[0] : $newOrderBys;
	    }
	}