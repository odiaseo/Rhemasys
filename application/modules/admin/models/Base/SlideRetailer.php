<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_SlideRetailer', 'admin');

/**
 * Admin_Model_Base_SlideRetailer
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $slide_id
 * @property integer $affiliate_retailer_id
 * @property Admin_Model_Slide $Slide
 * @property Admin_Model_AffiliateRetailer $AffiliateRetailer
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_SlideRetailer extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('slide_retailer');
        $this->hasColumn('slide_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('affiliate_retailer_id', 'integer', 4, array(
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
        $this->hasOne('Admin_Model_Slide as Slide', array(
             'local' => 'slide_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AffiliateRetailer as AffiliateRetailer', array(
             'local' => 'affiliate_retailer_id',
             'foreign' => 'id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
        $this->actAs($softdelete0);
    }
}