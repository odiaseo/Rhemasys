<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Help_Model_HelpBoilerPlate', 'con1');

/**
 * Help_Model_Base_HelpBoilerPlate
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property clob $content
 * @property integer $is_active
 * @property Doctrine_Collection $Plates
 * @property Doctrine_Collection $Templates
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
abstract class Help_Model_Base_HelpBoilerPlate extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('help_boiler_plate');
        $this->hasColumn('title', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('content', 'clob', null, array(
             'type' => 'clob',
             'notnull' => true,
             ));
        $this->hasColumn('is_active', 'integer', 1, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '1',
             ));

        $this->option('type', 'INNODB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Help_Model_HelpTemplateBoilerPlate as Plates', array(
             'local' => 'id',
             'foreign' => 'help_boiler_plate_id'));

        $this->hasMany('Help_Model_HelpTemplate as Templates', array(
             'refClass' => 'Help_Model_HelpTemplateBoilerPlate',
             'local' => 'help_boiler_plate_id',
             'foreign' => 'help_template_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}