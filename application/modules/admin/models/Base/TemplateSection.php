<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_TemplateSection', 'admin');

/**
 * Admin_Model_Base_TemplateSection
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $admin_section_id
 * @property integer $template_id
 * @property integer $sequence
 * @property Admin_Model_AdminSection $AdminSection
 * @property Admin_Model_Template $Template
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_TemplateSection extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('template_section');
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
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));


        $this->index('templateindex', array(
             'fields' => 
             array(
              0 => 'template_id',
             ),
             ));
        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_AdminSection as AdminSection', array(
             'local' => 'admin_section_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_Template as Template', array(
             'local' => 'template_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}