<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Help_Model_Section', 'con2');

/**
 * Help_Model_Base_Section
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property integer $sequence
 * @property Doctrine_Collection $Layout
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
abstract class Help_Model_Base_Section extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('section');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'default' => '0',
             'unique' => true,
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Help_Model_TemplateLayout as Layout', array(
             'local' => 'id',
             'foreign' => 'section_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}