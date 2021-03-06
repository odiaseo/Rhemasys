<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_Subject', 'admin');

/**
 * Admin_Model_Base_Subject
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property Admin_Model_Inquiry $Inquiry
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_Subject extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('subject');
        $this->hasColumn('title', 'string', 45, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '45',
             ));
        $this->hasColumn('description', 'string', 45, array(
             'type' => 'string',
             'length' => '45',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_Inquiry as Inquiry', array(
             'local' => 'id',
             'foreign' => 'subject_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
    }
}