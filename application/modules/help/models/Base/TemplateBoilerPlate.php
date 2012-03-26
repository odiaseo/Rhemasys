<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Help_Model_TemplateBoilerPlate', 'con2');

/**
 * Help_Model_Base_TemplateBoilerPlate
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $template_id
 * @property integer $boiler_plate_id
 * @property enum $position
 * @property integer $sequence
 * @property Help_Model_Template $Template
 * @property Help_Model_BoilerPlate $BoilerPlate
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
abstract class Help_Model_Base_TemplateBoilerPlate extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('template_boiler_plate');
        $this->hasColumn('template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('boiler_plate_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('position', 'enum', 4, array(
             'type' => 'enum',
             'length' => 4,
             'values' => 
             array(
              0 => 'Top',
              1 => 'Bottom',
             ),
             ));
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Help_Model_Template as Template', array(
             'local' => 'template_id',
             'foreign' => 'id'));

        $this->hasOne('Help_Model_BoilerPlate as BoilerPlate', array(
             'local' => 'boiler_plate_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}