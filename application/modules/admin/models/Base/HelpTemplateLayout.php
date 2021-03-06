<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_HelpTemplateLayout', 'admin');

/**
 * Admin_Model_Base_HelpTemplateLayout
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $help_template_id
 * @property integer $help_section_id
 * @property integer $help_field_id
 * @property integer $section_seq
 * @property integer $item_seq
 * @property string $content_type
 * @property Admin_Model_HelpField $HelpField
 * @property Admin_Model_HelpTemplate $HelpTemplate
 * @property Admin_Model_HelpSection $HelpSection
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_HelpTemplateLayout extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('help_template_layout');
        $this->hasColumn('help_template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('help_section_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('help_field_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('section_seq', 'integer', 4, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '4',
             ));
        $this->hasColumn('item_seq', 'integer', 4, array(
             'type' => 'integer',
             'default' => 0,
             'length' => '4',
             ));
        $this->hasColumn('content_type', 'string', 4, array(
             'type' => 'string',
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_HelpField as HelpField', array(
             'local' => 'help_field_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_HelpTemplate as HelpTemplate', array(
             'local' => 'help_template_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_HelpSection as HelpSection', array(
             'local' => 'help_section_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}