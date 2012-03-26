<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Admin_Model_AffiliateFeed', 'admin');

/**
 * Admin_Model_Base_AffiliateFeed
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property string $title
 * @property string $description
 * @property string $feed_url
 * @property string $field_mapping
 * @property integer $is_active
 * @property timestamp $downloaded_at
 * @property integer $affiliate_network_id
 * @property integer $affiliate_feed_type_id
 * @property Admin_Model_AffiliateNetwork $AffiliateNetwork
 * @property Admin_Model_AffiliateFeedType $AffiliateFeedType
 * @property Admin_Model_AffiliateProduct $AffiliateProduct
 * 
 * @package    ##PACKAGE##
 * @subpackage RhemaSys
 * @author     Pele Odiase <info@rhema-webdesign.com>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Admin_Model_Base_AffiliateFeed extends Rhema_Model_Abstract
{
    public function setTableDefinition()
    {
        $this->setTableName('affiliate_feed');
        $this->hasColumn('title', 'string', 150, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '150',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('feed_url', 'string', 512, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '512',
             ));
        $this->hasColumn('field_mapping', 'string', null, array(
             'type' => 'string',
             ));
        $this->hasColumn('is_active', 'integer', 1, array(
             'type' => 'integer',
             'default' => 1,
             'length' => '1',
             ));
        $this->hasColumn('downloaded_at', 'timestamp', 25, array(
             'type' => 'timestamp',
             'length' => '25',
             ));
        $this->hasColumn('affiliate_network_id', 'integer', 8, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '8',
             ));
        $this->hasColumn('affiliate_feed_type_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'default' => 1,
             'length' => '4',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_general_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Admin_Model_AffiliateNetwork as AffiliateNetwork', array(
             'local' => 'affiliate_network_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AffiliateFeedType as AffiliateFeedType', array(
             'local' => 'affiliate_feed_type_id',
             'foreign' => 'id'));

        $this->hasOne('Admin_Model_AffiliateProduct as AffiliateProduct', array(
             'local' => 'id',
             'foreign' => 'affiliate_feed_id'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $rhema_model_template_subsite0 = new Rhema_Model_Template_Subsite();
        $softdelete0 = new Doctrine_Template_SoftDelete();
        $blameable0 = new Doctrine_Template_Blameable();
        $this->actAs($timestampable0);
        $this->actAs($rhema_model_template_subsite0);
        $this->actAs($softdelete0);
        $this->actAs($blameable0);
    }
}