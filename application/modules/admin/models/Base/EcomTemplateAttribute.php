<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_EcomTemplateAttribute', 'admin');

/**
 * Admin_Model_Base_EcomTemplateAttribute
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $ecom_attribute_id
 * @property integer $ecom_display_template_id
 * @property string $label
 * @property integer $sequence
 * @property Admin_Model_EcomAttribute $EcomAttribute
 * @property Admin_Model_EcomDisplayTemplate $EcomDisplayTemplate
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_EcomTemplateAttribute extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('ecom_template_attribute');
        $this->hasColumn('ecom_attribute_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('ecom_display_template_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('label', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));
        $this->hasColumn('sequence', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_EcomAttribute as EcomAttribute', array(
             'local' => 'ecom_attribute_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_EcomDisplayTemplate as EcomDisplayTemplate', array(
             'local' => 'ecom_display_template_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
        $this->actAs($softdelete0);
    }
}