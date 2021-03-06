<?php /* This file encoded by Raizlabs PHP Obfuscator http://www.raizlabs.com/software */ ?>
<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Help_Model_Type', 'con2');

/**
 * Help_Model_Base_Type
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $prefix
 * @property integer $level
 * @property integer $template_id
 * @property string $note
 * @property Help_Model_Template $Template
 * @property Doctrine_Collection $Documents
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webesign.com>
 * @version    SVN: $Id: Builder.php 6716 2009-11-12 19:26:28Z jwage $
 */
abstract class Help_Model_Base_Type extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('type');
        $this->hasColumn('title', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('prefix', 'string', null, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '',
             ));
        $this->hasColumn('level', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('template_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('note', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));

        $this->option('type', 'INNODB');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Help_Model_Template as Template', array(
             'local' => 'template_id',
             'foreign' => 'id'));

        $this->hasMany('Help_Model_Document as Documents', array(
             'local' => 'id',
             'foreign' => 'type_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($softdelete0);
    }
}