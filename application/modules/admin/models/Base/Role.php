<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_Role', 'admin');

/**
 * Admin_Model_Base_Role
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property integer $sequence
 * @property integer $is_admin
 * @property Admin_Model_BlogPost $BlogPost
 * @property Admin_Model_HelpDocument $HelpDocument
 * @property Admin_Model_User $User
 * @property Admin_Model_Portfolio $Portfolio
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_Role extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('role');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('is_admin', 'integer', 1, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '1',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_BlogPost as BlogPost', array(
             'local' => 'id',
             'foreign' => 'role_id'));

        $this->hasOne('Admin_Model_HelpDocument as HelpDocument', array(
             'local' => 'id',
             'foreign' => 'role_id'));

        $this->hasOne('Admin_Model_User as User', array(
             'local' => 'id',
             'foreign' => 'role_id'));

        $this->hasOne('Admin_Model_Portfolio as Portfolio', array(
             'local' => 'id',
             'foreign' => 'role_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $blameable0 = new Doctrine_Template_Blameable();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
        $this->actAs($blameable0);
    }
}