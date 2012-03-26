<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_AdminGroup', 'admin');

/**
 * Admin_Model_Base_AdminGroup
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property integer $is_active
 * @property Admin_Model_HelpDocument $HelpDocument
 * @property Admin_Model_User $User
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_AdminGroup extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('admin_group');
        $this->hasColumn('title', 'string', 50, array(
             'type' => 'string',
             'notnull' => true,
             'unique' => true,
             'length' => '50',
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('is_active', 'integer', 1, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '1',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_HelpDocument as HelpDocument', array(
             'local' => 'id',
             'foreign' => 'admin_group_id'));

        $this->hasOne('Admin_Model_User as User', array(
             'local' => 'id',
             'foreign' => 'admin_group_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}