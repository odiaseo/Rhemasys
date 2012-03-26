<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_AdminModule', 'admin');

/**
 * Admin_Model_Base_AdminModule
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $label
 * @property string $code
 * @property string $thumb
 * @property string $image_file
 * @property integer $admin_menu_id
 * @property integer $admin_licence_id
 * @property integer $sequence
 * @property string $content
 * @property Admin_Model_AdminMenu $AdminMenu
 * @property Admin_Model_AdminLicence $AdminLicence
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_AdminModule extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('admin_module');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 150, array(
             'type' => 'string',
             'length' => '150',
             ));
        $this->hasColumn('label', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('code', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('thumb', 'string', 145, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '145',
             ));
        $this->hasColumn('image_file', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('admin_menu_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('admin_licence_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('content', 'string', null, array(
             'type' => 'string',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_AdminMenu as AdminMenu', array(
             'local' => 'admin_menu_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AdminLicence as AdminLicence', array(
             'local' => 'admin_licence_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}