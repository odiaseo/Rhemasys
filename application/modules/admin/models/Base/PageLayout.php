<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_PageLayout', 'admin');

/**
 * Admin_Model_Base_PageLayout
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $item
 * @property string $admin_content_type_id
 * @property integer $page_id
 * @property integer $admin_table_id
 * @property integer $admin_section_id
 * @property integer $template_id
 * @property integer $section_seq
 * @property integer $item_seq
 * @property Admin_Model_Template $Template
 * @property Admin_Model_Page $Page
 * @property Admin_Model_AdminContentType $AdminContentType
 * @property Admin_Model_AdminSection $AdminSection
 * @property Admin_Model_AdminTable $AdminTable
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_PageLayout extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('page_layout');
        $this->hasColumn('item', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('admin_content_type_id', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('page_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('admin_table_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('admin_section_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('section_seq', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('item_seq', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));


        $this->index('layoutindex', array(
             'fields' => 
             array(
              0 => 'page_id',
              1 => 'admin_table_id',
              2 => 'template_id',
             ),
             ));
        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_Template as Template', array(
             'local' => 'template_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_Page as Page', array(
             'local' => 'page_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AdminContentType as AdminContentType', array(
             'local' => 'admin_content_type_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AdminSection as AdminSection', array(
             'local' => 'admin_section_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AdminTable as AdminTable', array(
             'local' => 'admin_table_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}