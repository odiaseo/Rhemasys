<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_HelpTemplate', 'admin');

/**
 * Admin_Model_Base_HelpTemplate
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $note
 * @property Doctrine_Collection $HelpField
 * @property Doctrine_Collection $HelpBoilerPlate
 * @property Doctrine_Collection $Plates
 * @property Doctrine_Collection $Layout
 * @property Doctrine_Collection $TemplateFields
 * @property Doctrine_Collection $Documents
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_HelpTemplate extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('help_template');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('note', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Admin_Model_HelpField as HelpField', array(
             'refClass' => 'Admin_Model_HelpTemplateField',
             'local' => 'help_template_id',
             'foreign' => 'help_field_id'));

        $this->hasMany('Admin_Model_HelpBoilerPlate as HelpBoilerPlate', array(
             'refClass' => 'Admin_Model_HelpTemplateBoilerPlate',
             'local' => 'help_template_id',
             'foreign' => 'help_boiler_plate_id'));

        $this->hasMany('Admin_Model_HelpTemplateBoilerPlate as Plates', array(
             'local' => 'id',
             'foreign' => 'help_template_id'));

        $this->hasMany('Admin_Model_HelpTemplateLayout as Layout', array(
             'local' => 'id',
             'foreign' => 'help_template_id'));

        $this->hasMany('Admin_Model_HelpTemplateField as TemplateFields', array(
             'local' => 'id',
             'foreign' => 'help_template_id'));

        $this->hasMany('Admin_Model_HelpType as Documents', array(
             'local' => 'id',
             'foreign' => 'help_template_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}