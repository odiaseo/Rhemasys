<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_HelpType', 'admin');

/**
 * Admin_Model_Base_HelpType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $prefix
 * @property integer $level
 * @property integer $help_template_id
 * @property string $note
 * @property Admin_Model_HelpTemplate $HelpTemplate
 * @property Doctrine_Collection $Documents
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_HelpType extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('help_type');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 150, array(
             'type' => 'string',
             'length' => '150',
             ));
        $this->hasColumn('prefix', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('level', 'integer', 4, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '4',
             ));
        $this->hasColumn('help_template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
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
        $this->hasOne('Admin_Model_HelpTemplate as HelpTemplate', array(
             'local' => 'help_template_id',
             'foreign' => 'id'));

        $this->hasMany('Admin_Model_HelpDocument as Documents', array(
             'local' => 'id',
             'foreign' => 'help_type_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}