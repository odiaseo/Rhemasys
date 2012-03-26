<?php
	class Rhema_Auth_Adapter_Model extends Zend_Auth_Adapter_DbTable  {
	
		protected $_modelClassName;
	 
		
	    public function __construct($modelClassName = 'Admin_Model_User',
	    					$identityColumn = null,$credentialColumn = null)  {
	        
	
	        if(null !== $modelClassName){
	        	$this->setModelClassName($modelClassName);
	        }	        
 
	        if (null !== $identityColumn) {
	            $this->setIdentityColumn($identityColumn);
	        }
	
	        if (null !== $credentialColumn) {
	            $this->setCredentialColumn($credentialColumn);
	        }
	    }	 

		public function setModelClassName($className){
			$this->_modelClassName = $className;
			return $this;
		}
		
		public function authenticate(){ 
			$this-> _authenticateSetup();
			$resultIdentities   = $this->_authenticateModelCredentials();	
			return $this->_validateResult($resultIdentities); 
		}
		
		protected function _authenticateModelCredentials(){
			try{
				//$userTable 			= Doctrine_Core::getTable($this->_modelClassName);
				$username  			= $this->_identity;
				//$functionName 		= 'findBy'.$this->_identityColumn;
				 
				$filter = new Rhema_Dao_Filter(); 
				
				$filter->setModel($this->_modelClassName)
				       ->addJoin('Role', Rhema_Dao_Filter::LEFT_JOIN, array('title', 'sequence'))
				       ->addCondition($this->_identityColumn, $username)
				       ->addCondition($this->_credentialColumn, $this->_credential)
				       ->addFields(array('firstname','lastname','image_file','visits'))
				       //->setDebug(true)
				      // ->setHydrationMode(Doctrine_Core::HYDRATE_RECORD)
				       ->setLimit(2);
				       //pd(Rhema_Model_Service::createQuery($filter));
				$resultIdentities = Rhema_Model_Service::createQuery($filter)->execute();
				
/*				$query = Doctrine_Query::create()
							->select('u.id,u.firstname,u.lastname,u.image_file,u.visits,r.title, r.sequence')
							->from("$this->_modelClassName u")	
							->leftJoin('u.Role r')						
							->where("u.$this->_identityColumn = ?" , $username)
							//->setHydrationMode(Doctrine_Core::HYDRATE_ARRAY)
							->andWhere("u.$this->_credentialColumn =?",$this->_credential);
							
				$resultIdentities   = $query->execute();	*/
			}catch (Exception $e){
	            throw new Zend_Auth_Adapter_Exception('The supplied parameters to ' . __CLASS__ .' failed to '
	                                                . 'produce a valid sql statement, please check table and column names '
	                                                . 'for validity.');				
			}
			return $resultIdentities;		
		}
		
	    protected function _validateResult($result){ 
	        if (!$result or count($result) < 1) {
	        	
	            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
	            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
	        
	        } elseif (count($result) > 1) {
	            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
	            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
 
	        }else{ 
		        $user         = current($result); 
		        $filter       = new Rhema_Dao_Filter();
		        
		        $filter->setModel($this->_modelClassName)
		        	   ->addToUpdateList(array('visits' => $user['visits'] + 1))
		        	   ->addCondition('id', $user['id'])
		        	   ->setQueryType(Rhema_Dao_Filter::QUERY_TYPE_UPDATE);
		        	   
		       	$done = Rhema_Model_Service::createQuery($filter)->execute();
		       	 
		        $this->_resultRow = $user;		 
				 
				$this->_authenticateResultInfo['identity'] 		= $this->_resultRow;
		        $this->_authenticateResultInfo['code'] 			= Zend_Auth_Result::SUCCESS;
		        $this->_authenticateResultInfo['messages'][] 	= 'Authentication successful.';
     

		       /* $query = Doctrine_Query::create()
		        			->update("$this->_modelClassName u")
		        			->set('u.visits', '?', 'u.visits + 1')
		        			->where('u.id =?', $this->_resultRow['id']);
		        $query->execute();
		         */
	        }
	        
	        return $this->_authenticateCreateAuthResult();
	    }
	       /**
     * _authenticateSetup() - This method abstracts the steps involved with making sure
     * that this adapter was indeed setup properly with all required peices of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup(){     
        $exception = null;

        if ($this->_modelClassName == '') {
            $exception = 'A model must be supplied for the ' . __CLASS__ .' authentication adapter.';
        } elseif ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }

        $this->_authenticateResultInfo = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        return true;
    }		
	}