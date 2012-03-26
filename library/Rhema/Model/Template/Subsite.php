<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Rhema_Model_Template_Listener_Subsite
 *
 * Add subiste Ids to records specific to subsites
 *
 */
class Rhema_Model_Template_Subsite extends Doctrine_Template
{
    /**
     * Array of Blameable options
     *
     * @var string
     */
    protected $_options = array('listener'      => 'Rhema_Model_Template_Listener_Subsite',
                                'siteVar'       => 'subsiteId',
                                'default'       => false,
                                'params'        => array(),
                                'columns'       => array('created' =>  array('name'          =>  'admin_subsite_id',
                                                                             'alias'         =>  null,
                                                                             'type'          =>  'integer',
                                                                             'length'        =>  8,
                                                                             'disabled'      =>  false,
                                                                             'options'       =>  array('notnull' => true,)
                                                                            ),
                                                         'updated' =>  array('name'          =>  'admin_subsite_id',
                                                                             'alias'         =>  null,
                                                                             'type'          =>  'integer',
                                                                             'length'        =>  8,
                                                                             'disabled'      =>  false,
                                                                             'onInsert'      =>  true,
                                                                             'options'       =>  array('notnull' => true,)
                                                                            )
                                                        ),
                                'relations'       => array('created' => array('disabled'      => true,
                                                                              'name'          => 'SubsiteId',
                                                                              'class'         => 'AdminSubsite',
                                                                              'foreign'       => 'id', 
                                                                              ),
                                                           'updated' => array('disabled'      => true,
                                                                              'name'          => 'SubsiteId',
                                                                              'class'         => 'AdminSubsite',
                                                                              'foreign'       => 'id', 
                                                                              ),
                                                        ));
    

    /**
     * __construct
     *
     * @param array $options
     * @return void
     */
    public function __construct(array $options = array())
    {
    	
        if (!class_exists($this->_options['listener'], true)) {
            throw new Exception('Class: ' . $this->_options['listener'] . ' not found');
        }
        
        parent::__construct($options);
        
    }
    
    /**
     * Set table definition for Blameable behavior
     *
     * @return void
     */
    public function setTableDefinition()
    {
        if( ! $this->_options['columns']['created']['disabled']) {
            $name = $this->_options['columns']['created']['name'];
            if ($this->_options['columns']['created']['alias']) {
                $name .= ' as ' . $this->_options['columns']['created']['alias'];
            }
            $this->hasColumn($name, $this->_options['columns']['created']['type'],
                             $this->_options['columns']['created']['length'],
                             $this->_options['columns']['created']['options']);
        }

        if( ! $this->_options['columns']['updated']['disabled']) {
            $name = $this->_options['columns']['updated']['name'];
            if ($this->_options['columns']['updated']['alias']) {
                $name .= ' as ' . $this->_options['columns']['updated']['alias'];
            }
            
            if ($this->_options['columns']['updated']['onInsert'] !== true &&
              $this->_options['columns']['updated']['options']['notnull'] === true) {
                $this->_options['columns']['updated']['options']['notnull'] = false;
            }
            
            $this->hasColumn($name, $this->_options['columns']['updated']['type'],
                             $this->_options['columns']['updated']['length'],
                             $this->_options['columns']['updated']['options']);
        }

        $listener = new $this->_options['listener']($this->_options);
        
        if (get_class($listener) !== 'Rhema_Model_Template_Listener_Subsite' && 
            !is_subclass_of($listener, 'Rhema_Model_Template_Listener_Subsite')) {
            	throw new Exception('Invalid listener. Must be Rhema_Model_Template_Listener_Subsite or subclass');
        }
        $this->addListener($listener, 'Subsite');
    }
    
    /**
     * Setup the relations for the Blameable behavior
     *
     * @return void
     */
    public function setUp()
    {
     
      if( ! $this->_options['relations']['created']['disabled']) {
        $this->hasOne($this->_options['relations']['created']['class'] . ' as ' . $this->_options['relations']['created']['name'], 
          array('local' => $this->_options['columns']['created']['name'],
                'foreign' => $this->_options['relations']['created']['foreign'],
          )
        );
      }
      
      if( ! $this->_options['relations']['updated']['disabled'] && ! $this->_options['columns']['updated']['disabled']) {
        $this->hasOne($this->_options['relations']['updated']['class'] . ' as ' . $this->_options['relations']['updated']['name'], 
          array('local' => $this->_options['columns']['updated']['name'],
                'foreign' => $this->_options['relations']['updated']['foreign'],
          )
        );
      }
      
      
    }
}
