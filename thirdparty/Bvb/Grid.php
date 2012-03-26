<?php

/**
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   $Id: Grid.php 1567 2011-01-18 04:48:20Z bento.vilas.boas@gmail.com $
 * @link      http://zfdatagrid.com
 */

/**
 *
 * This class will abstract results from a data source for descendants
 *
 *
 * @package   Bvb_Grid
 * @author    Bento Vilas Boas <geral@petala-azul.com>
 * @copyright 2010 ZFDatagrid
 * @license   http://www.petala-azul.com/bsd.txt   New BSD License
 * @version   Release: @package_version@
 * @category  Bvb_Grid
 */
abstract class Bvb_Grid {
    /**
     * Current Revision
     * @var string
     */
    const VERSION = '$Rev: 1567 $';


    /**
     * Default Configuration to be applied to all grids
     *
     * @var array
     * @static
     */
    protected static $_defaultConfig = array();
    /**
     * Char encoding
     *
     * @var string
     */
    protected $_charEncoding = 'UTF-8';
    /**
     * DBRM server name
     *
     * @var string
     */
    private $_server = null;
    /**
     * Fields order
     *
     * @var array
     */
    private $_fieldsOrder;
    /**
     * The path where we can find the library
     * Usually is lib or library
     *
     * @var string
     */
    protected $_libraryDir = 'library';
    /**
     * templates type to be used
     *
     * @var array
     */
    protected $_templates;
    /**
     * dir and prefix list to be used when formatting fields
     *
     */
    protected $_formatter;
    /**
     * Number of results per page
     *
     * @var int
     */
    protected $_recordsPerPage = 15;
    /**
     * Number of results to per page
     *
     * @var array
     */
    protected $_paginationInterval = array();
    /**
     * Type of export available
     *
     * @var array
     */
    protected $_export = array('pdf',
        'word',
        'wordx',
        'excel',
        'print',
        'xml',
        'csv',
        'ods',
        'odt',
        'json');
    /**
     * All info that is not directly related to the database
     */
    protected $_info = array();
    /**
     * URL to prefix in case of routes
     *
     * @var bool
     */
    protected $_routeUrl = false;
    /**
     * Baseurl
     *
     * @var string
     */
    protected $_baseUrl;
    /**
     * Array containing the query result from table(s)
     *
     * @var array
     */
    protected $_result;
    /**
     * Total records from db query
     *
     * @var int
     */
    protected $_totalRecords;
    /**
     * Array containing field titles
     *
     * @var array
     */
    protected $_titles;
    /**
     * Array containing table(s) fields
     *
     * @var array
     */
    protected $_fields = array();
    /**
     * Filters list
     *
     * @var array
     */
    protected $_filters;
    /**
     * Filters Render
     * @var
     */
    protected $_filtersRenders;
    /**
     * External fielters to be applied
     *
     * @var array
     */
    protected $_externalFilters = array();
    /**
     * Filters values inserted by the user
     *
     * @var array
     */
    protected $_filtersValues;
    /**
     * All information database related
     *
     * @var array
     */
    protected $_data = array();
    /**
     * URL params
     *
     * @var string
     */
    protected $_ctrlParams = array();
    /**
     * Extra fields array
     *
     * @var array
     */
    protected $_extraFields = array();
    /**
     * Final fields list (after all procedures).
     *
     * @var array
     */
    protected $_finalFields;
    /**
     * Use cache or not.
     * @var bool
     */
    protected $_cache = false;
    /**
     * The field to set order by, if we have a horizontal row
     *
     * @var string
     */
    private $_fieldHorizontalRow;
    /**
     * Template instance
     *
     * @var object
     */
    protected $_temp;
    /**
     * Result untouched
     *
     * @var array
     */
    private $_resultRaw;
    /**
     * Check if all columns have been added by ->query()
     *
     * @var bool
     */
    private $_allFieldsAdded = false;
    /**
     * If the user manually sets the query limit
     *
     * @var int|bool
     */
    protected $_forceLimit = false;
    /**
     * Default filters to be applied
     *
     * @var array
     * @return array
     */
    protected $_defaultFilters;
    /**
     * Instead throwing an exception,
     * we queue the field list and call this in
     * getFieldsFromQuery()
     *
     * @var array
     */
    protected $_updateColumnQueue = array();
    /**
     * List of callback functions to apply
     * on grid deploy and ajax
     *
     * @var array
     */
    protected $_configCallbacks = array();
    /**
     * Treat hidden fields as 'remove'
     *
     * @var bool
     */
    protected $_removeHiddenFields = false;
    /**
     * Functions to be applied on every fields before display
     *
     * @var string
     */
    protected $_escapeFunction = 'htmlspecialchars';
    /**
     * Grid Options.
     *
     * @var array
     */
    protected $_options = array();
    /**
     * Id used for multiples instances on the same page
     *
     * @var string
     */
    protected $_gridId;
    /**
     * Colspan for table
     *
     * @var int
     */
    protected $_colspan;
    /**
     * User defined INFO for templates
     *
     * @var array
     */
    protected $_templateParams = array();
    /**
     * To let a user know if the grid will be displayed or not
     *
     * @var bool
     */
    protected $_showsGrid = false;
    /**
     * Array of fields that should appear on detail view
     *
     * @var array
     */
    protected $_gridColumns = null;
    /**
     * Array of columns that should appear on detail view
     *
     * @var array
     */
    protected $_detailColumns = null;
    /**
     * If we are on detail or grid view
     *
     * @var bool
     */
    protected $_isDetail = false;
    /**
     * @var Zend_View_Interface
     */
    protected $_view;
    /**
     * Information from FORM
     *
     * @var object
     */
    protected $_crud = null;
    /**
     *
     * @var Bvb_Grid_Source_SourceInterface
     */
    private $_source = null;
    /**
     * Last name from deploy class (table|pdf|csv|etc...)
     *
     * @var string
     */
    protected $_deployName = null;
    /**
     * What is being done with this request
     *
     * @var array
     */
    protected $_willShow = array();
    /**
     * Print class based on conditions
     *
     * @var array
     */
    protected $_classRowCondition = array();
    /**
     * Result to apply to every <tr> based on condition
     *
     * @var $_classRowConditionResult array
     */
    protected $_classRowConditionResult = array();
    /**
     * CSS classes to be used
     *
     * @var array
     */
    protected $_cssClasses = array('odd' => 'alt', 'even' => '');
    /**
     * Condition to apply a CSS class to a table cell <td>
     *
     * @var array
     */
    protected $_classCellCondition = array();
    /**
     * Order setted by adapter
     *
     * @var string
     */
    protected $_order;
    /**
     * custom translate instance
     *
     * @var Zend_Translate
     */
    protected $_translator;
    /**
     * If filters by user will be showed when exporting
     *
     * @var bool
     */
    protected $_showFiltersInExport = false;
    /**
     * If whe should save filters in session
     *
     * @var bool
     */
    protected $_paramsInSession = false;
    /**
     * Session Params Zend_Session
     *
     * @var array
     */
    protected $_sessionParams = false;
    /**
     * Hold definitions from configurations
     *
     * @var array
     */
    protected $_deploy = array();
    /**
     * Mass Actions
     *
     * @var array
     */
    protected $_massActions = array();
    /**
     * Columns that should be return when submiting the form
     *
     * @var array
     */
    protected $_massActionsFields = array();
    /**
     * The defaulf separator for mass actions post values
     * @var string
     */
    protected $_massActionsSeparator = ',';
    /**
     * Contains URL's for edit and delete records
     * Can be called from a decorator using
     * {{detailUrl}}
     * {{deleteUrl}}
     * {{editUrl}}
     * {{addUrl}}
     *
     * @var array
     */
    protected $_actionsUrls = array('add' => '',
        'edit' => '',
        'delete' => '',
        'detail' => '');
    /**
     * Permission to add records
     *
     * @var bool
     */
    protected $_allowAdd = false;
    /**
     * Options for adition
     *
     * @var array
     */
    protected $_allowAddButton = array();
    /**
     * Permission to edit records
     *
     * @var bool
     */
    protected $_allowEdit = false;
    /**
     * Permission to delete records
     *
     * @var bool
     */
    protected $_allowDelete = false;

    /**
     * Makes shure that config callbacks will be used once
     *
     * @var boolean
     */
    protected $_runCallbacks = true;

    /**
     * Backwards compatibility
     *
     * @param mixed $object A Zend_Db object
     *
     * @return Bvb_Grid
     * @deprecated Use setSource()
     */
    public function query($object)
    {
        if ($object instanceof Zend_Db_Select) {
            $this->setSource(new Bvb_Grid_Source_Zend_Select($object));
        } elseif ($object instanceof Zend_Db_Table_Abstract) {
            $this->setSource(new Bvb_Grid_Source_Zend_Table($object));
        } else {
            throw new Bvb_Grid_Exception('Please use the setSource() method instead');
        }

        return $this;
    }

    /**
     * Sets the source to be used
     *
     * Bvb_Grid_Source_*
     *
     * @param Bvb_Grid_Source_SourceInterface $source A valid interface
     *
     * @return Bvb_Grid
     */
    public function setSource(Bvb_Grid_Source_SourceInterface $source)
    {
        $this->_source = $source;

        $this->getSource()->setCache($this->getCache());

        $tables = $this->getSource()->getMainTable();

        $this->_data['table'] = $tables['table'];
        if (isset($tables['schema']))
            $this->_data['schema'] = $tables['schema'];
        $this->_crudTable = $this->_data['table'];

        $fields = $this->getSource()->buildFields();

        foreach ($fields as $key => $field) {
            $this->updateColumn($key, $field);
        }

        $this->_allFieldsAdded = true;
        //Apply options to the fields
        $this->_applyOptionsToFields();

        return $this;
    }

    /**
     * The path where we can find the library
     * Usually is lib or library
     *
     * @param string $dir Zend Lib location
     *
     * @return Bvb_Grid
     */
    public function setLibraryDir($dir)
    {
        $this->_libraryDir = $dir;
        return $this;
    }

    /**
     * Returns the actual library path
     *
     * @return string
     */
    public function getLibraryDir()
    {
        return $this->_libraryDir;
    }

    /**
     * Sets grid cache
     *
     * @param mixed $cache Cache arguments
     *
     * @return mixed
     */
    public function setCache($cache)
    {
        if ($cache == false || (is_array($cache) && isset($cache['use']) && $cache['use'] == 0)) {
            $this->_cache = array('use' => 0);
            return $this;
        }

        if (is_array($cache) && isset($cache['use']) && isset($cache['instance']) && isset($cache['tag'])) {
            $this->_cache = $cache;
            return $this;
        }

        return false;
    }

    /**
     * Returns actual cache params
     *
     * @return Zend_Cache
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Returns the actual source object
     *
     * @return Bvb_Grid_Source_SourceInterface
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Defines a custom Translator
     *
     * @param Zend_Translate $translator Translator instance to be used
     *
     * @return Bvb_Grid
     */
    public function setTranslator(Zend_Translate $translator)
    {
        Bvb_Grid_Translator::getInstance()->setTranslator($translator);
        return $this;
    }

    /**
     * The __construct function receives the db adapter. All information related to the
     * URL is also processed here
     *
     * @param array $options An Array or Zend_Config object.
     *
     * @return void
     */
    public function __construct($options)
    {
        if (!$this instanceof Bvb_Grid_Deploy_DeployInterface) {
            throw new Bvb_Grid_Exception(get_class($this) . ' needs to implement Bvb_Grid_Deploy_DeployInterface');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = array_merge_recursive(self::getDefaultConfig(), $options);

        // get the controller params and baseurl to use with filters
        if (isset($this->_options['grid']['requestParams'])) {
            // use from configuration, remove it from _options to enforce correct usage
            $this->setParams($this->_options['grid']['requestParams']);
            unset($this->_options['grid']['requestParams']);
        } else {
            // use the request parameters
            $this->setParams($this->getRequest()->getParams());
        }
        if (isset($this->_options['grid']['baseUrl'])) {
            // use from configuration, remove it from _options to enforce correct usage
            $this->_baseUrl = $this->_options['grid']['baseUrl'];
            unset($this->_options['grid']['baseUrl']);
        } else {
            // use controllers value
            $this->_baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        }

        foreach (array('massActionsAll_', 'gridAction_', 'send_') as $value) {
            $this->removeParam($value);
        }

        foreach ($this->_ctrlParams as $key => $value) {
            if (is_array($value)) {
                $this->removeParam($key);
            }
        }

        /**
         * plugins loaders
         */
        $this->_formatter = new Zend_Loader_PluginLoader();

        //Templates loading
        if (is_array($this->_export)) {
            foreach ($this->_export as $key => $temp) {
                if (is_array($temp)) {
                    $export = $key;
                } else {
                    $export = $temp;
                }
                $this->_templates[$export] = new Zend_Loader_PluginLoader(array());
            }
        }

        // Add the formatter fir for fields content
        $this->addFormatterDir('Bvb/Grid/Formatter', 'Bvb_Grid_Formatter');

        $deploy = explode('_', get_class($this));
        $this->_deployName = strtolower(end($deploy));

        $renderDir = ucfirst($this->_deployName);

        $this->_filtersRenders = new Zend_Loader_PluginLoader();
        $this->addFiltersRenderDir('Bvb/Grid/Filters/Render/' . $renderDir, 'Bvb_Grid_Filters_Render_' . $renderDir);


        if (!defined('E_USER_DEPRECATED')) {
            define('E_USER_DEPRECATED', E_USER_WARNING);
        }


        $this->_sessionParams = new Zend_Session_Namespace('ZFDG_FILTERS' . $this->getGridId(true));
    }

    /**
     * Returns current request from Zend_Controller_Front
     *
     * @return Zend_Controller_Front::getInstance()->getReguest();
     */
    public function getRequest()
    {
        return Zend_Controller_Front::getInstance()->getRequest();
    }

    /**
     * Set view object
     *
     * @param Zend_View_Interface $view view object to use
     *
     * @return Bvb_Grid
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface
     */
    public function getView()
    {
        if (null === $this->_view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    /**
     * Sets the functions to be used to apply to each value
     * before display
     *
     * @param array $functions Default functions to escape values
     *
     * @return Bvb_Grid
     */
    public function setDefaultEscapeFunction($functions)
    {
        $this->_escapeFunction = $functions;
        return $this;
    }

    /**
     * Returns the active escape functions
     *
     * @return string
     */
    public function getDefaultEscapeFunction()
    {
        return $this->_escapeFunction;
    }

    /**
     * Character encoding
     *
     * @param string $encoding Encoding to be used
     *
     * @return Bvb_Grid
     */
    public function setcharEncoding($encoding)
    {
        $this->_charEncoding = $encoding;
        return $this;
    }

    /**
     * Returns the actual encoding
     *
     * @return string
     */
    public function getCharEncoding()
    {
        return $this->_charEncoding;
    }

    /**
     * The translator
     *
     * @param string $message Message to be translated
     *
     * @return string
     */
    protected function __($message)
    {
        if (strlen($message) == 0) {
            return $message;
        }

        if ($this->getTranslator()) {
            return $this->getTranslator()->translate($message);
        }

        return $message;
    }

    /**
     * Gets the translator instance
     *
     * @return Zend_Translate
     */
    public function getTranslator()
    {
        return Bvb_Grid_Translator::getInstance()->getTranslator();
    }

    /**
     * Check if a string is available
     *
     * @param string $message Message to check if it's translated
     *
     * @return bool
     */
    protected function isTranslated($message)
    {
        return Bvb_Grid_Translator::getInstance()->isTranslated($message);
    }

    /**
     * Use the overload function so we can return an object
     *
     * @param string $name  Mehtod name
     * @param string $value Mehtod args
     *
     * @return Bvb_Grid
     */
    public function __call($name, $value)
    {
        if (substr(strtolower($name), 0, 6) == 'source') {

            $meth = substr($name, 6);
            $meth[0] = strtolower($meth[0]);

            if (is_object($this->getSource()) && method_exists($this->getSource(), $meth)) {
                $this->getSource()->$meth();
                return $this;
            }
        }

        $class = $this->_deployName;

        if ($name == 'set' . ucfirst($class) . 'GridColumns') {
            $this->setGridColumns($value[0]);
            return $this;
        }

        if ($name == 'set' . ucfirst($class) . 'DetailColumns') {
            $this->setDetailColumns($value[0]);
            return $this;
        }

        if (substr(strtolower($name), 0, strlen($class) + 3) == 'set' . $class) {
            $name = substr($name, strlen($class) + 3);
            $name[0] = strtolower($name[0]);
            $this->_deploy[$name] = $value[0];
            return $this;
        }

        if (substr(strtolower($name), 0, 3) == 'set') {
            $name = substr($name, 3);

            if (!isset($value[0])) {
                $value[0] = null;
            }
            $this->__set($name, $value[0]);
        } else {
            throw new Bvb_Grid_Exception("call to unknown function $name");
        }

        return $this;
    }

    /**
     * Magic function handling
     *
     * @param string $var   Variable name
     * @param string $value Variable value
     *
     * @return Bvb_Grid
     */
    public function __set($var, $value)
    {
        $var[0] = strtolower($var[0]);
        $this->_info[$var] = $value;
        return $this;
    }

    /**
     * Update data from a column
     *
     * @param string $field   Field Name
     * @param array  $options Associative array of options to be applyied to the field
     *
     * @return Bvb_Grid
     */
    public function updateColumn($field, array $options = array())
    {
        if (null == $this->getSource()
            || ($this->_allFieldsAdded == true && !array_key_exists($field, $this->_data['fields']))
        ) {
            /**
             * Add to the queue and call it from the getFieldsFromQuery() method
             * @var $_updateColumnQueue
             */
            if (isset($this->_updateColumnQueue[$field])) {
                $this->_updateColumnQueue[$field] = array_merge($this->_updateColumnQueue[$field], $options);
            } else {
                $this->_updateColumnQueue[$field] = $options;
            }

            return $this;
        }

        if ($this->_allFieldsAdded == false) {
            $this->_data['fields'][$field] = $options;
        } elseif (array_key_exists($field, $this->_data['fields'])) {

            if (isset($options['hRow']) && $options['hRow'] == 1) {
                $this->_fieldHorizontalRow = $field;
                $this->_info['hRow'] = array('field' => $field, 'title' => $options['title']);
            }

            $this->_data['fields'][$field] = array_merge($this->_data['fields'][$field], $options);
        }

        return $this;
    }

    /**
     * Set option hidden=1 on several columns
     *
     * @param array $columns Array of columns that will be hidden
     *
     * @return Bvb_Grid
     */
    public function setColumnsHidden(array $columns)
    {
        foreach ($columns as $column) {
            $this->updateColumn($column, array('hidden' => 1));
        }
        return $this;
    }

    /**
     * Add a new dir to look for when formating a field
     *
     * @param string $dir    Classes Location
     * @param string $prefix Classes prefix
     *
     * @return Bvb_Grid
     */
    public function addFormatterDir($dir, $prefix)
    {
        $this->_formatter->addPrefixPath(trim($prefix, '_'), trim($dir, '/') . '/');
        return $this;
    }

    /**
     * Format a field
     *
     * @param string $value     Value to be formatted
     * @param mixed  $formatter Formatter to be used
     *
     * @return mixed
     */
    protected function _applyFormat($value, $formatter)
    {
        if (is_array($formatter)) {
            $result = reset($formatter);
            if (!isset($formatter[1])) {
                $formatter[1] = array();
            }

            $options = (array) $formatter[1];
        } else {
            $result = $formatter;
            $options = array();
        }

        $class = $this->_formatter->load($result);

        $t = new $class($options);

        if (!$t instanceof Bvb_Grid_Formatter_FormatterInterface) {
            throw new Bvb_Grid_Exception("$class must implement the Bvb_Grid_Formatter_FormatterInterface");
        }

        return $t->format($value);
    }

    /**
     * Number of records to show per page
     *
     * @param array $pagination key=>pair array of possible values of records per page for user to choose from
     *
     * @return Bvb_Grid
     */
    public function setPaginationInterval(array $pagination)
    {
        $this->_paginationInterval = $pagination;
        return $this;
    }

    /**
     * Number of records to show per page
     *
     * @param int $number Records to show
     *
     * @return Bvb_Grid
     */
    public function setRecordsPerPage($number = 15)
    {
        $this->_recordsPerPage = (int) $number;
        return $this;
    }

    /**
     * Default values for filters.
     * This will be applied before displaying. However the user can still remove them.
     *
     * @param array $filters Associative array with fields=>Values to define default filters values
     *
     * @return Bvb_Grid
     */
    public function setDefaultFiltersValues(array $filters)
    {
        $this->_defaultFilters = $filters;
        return $this;
    }

    /**
     * Get filters values
     *
     * @return Bvb_Grid
     */
    protected function _buildFiltersValues()
    {
        //Build an array to know filters values
        $filtersValues = array();
        $fields = $this->getFields();

        $filters = array();
        foreach ($this->_ctrlParams as $key => $value) {
            // only build filter if search is enabled
            if (isset($this->_data['fields'][$key]['search']) && $this->_data['fields'][$key]['search'] == false)
                continue;

            //This happens when we have range filters and the url look like this:
            // /action/field[from]/100/field[to]/500
            if (stripos($key, '[')) {
                $name = explode('[', $key);

                //lets check if there ir a grid id, so we can remove it
                if (strlen($this->getGridId()) > 0) {
                    $name[0] = substr($name[0], 0, - strlen($this->getGridId()));
                }

                //check if this param is in fact a field we set
                if (in_array($name[0], $fields)) {
                    $filters[$name[0]][substr($name[1], 0, - 1)] = $value;
                }
            } else {

                //check if this param is in fact a field we set
                if (in_array($key, $fields)) {

                    $filters[$key] = $value;
                    //Can have a grid id, so we also need to check for that situation
                } elseif (in_array(substr($key, 0, - strlen($this->getGridId())), $fields)) {
                    if ($this->getGridId() != ''
                        && substr($key, - strlen($this->getGridId())) == $this->getGridId()
                    ) {
                        $key = substr($key, 0, - strlen($this->getGridId()));
                    }

                    $filters[$key] = $value;
                }
            }
        }

        if (count($filters) > 0) {

            //let's set the range filters as an array
            foreach ($filters as $key => $value) {
                if (is_array($value)) {
                    $this->setParam($key, $value);
                }
            }


            $fieldsRaw = $this->_data['fields'];

            //final check for allowed fields
            foreach ($filters as $key => $filter) {

                if (!is_array($filter) && (strlen($filter) == 0 || !in_array($key, $this->_fields))) {

                    unset($filters[$key]);
                } elseif (!is_array($filter)) {

                    if (isset($fieldsRaw[$key]['searchField'])) {
                        $key = $fieldsRaw[$key]['searchField'];
                    }

                    //Copy the current filter value so we can perform the vairous options and not loosing the
                    //orignal value. This happens with transform, callbacks, etc,
                    $oldFilter = $filter;

                    //Check fi user has defined a transform option for the value
                    //The transform option is used to normalise vallues, like date, currency, etc
                    if (isset($this->_filters[$key]['transform']) && is_callable($this->_filters[$key]['transform'])) {
                        $filter = call_user_func($this->_filters[$key]['transform'], $filter);
                    }

                    //A callback is set? If yes, let's call it
                    if (isset($this->_filters[$key]['callback']) && is_array($this->_filters[$key]['callback'])) {
                        if (!is_callable($this->_filters[$key]['callback']['function'])) {
                            throw new Bvb_Grid_Exception($this->_filters[$key]['callback']['function']
                                    . ' is not callable');
                        }

                        if (!isset($this->_filters[$key]['callback']['params'])
                            || !is_array($this->_filters[$key]['callback']['params'])
                        ) {
                            $this->_filters[$key]['callback']['params'] = array();
                        }

                        $this->_filters[$key]['callback']['params'] = array_merge(
                            $this->_filters[$key]['callback']['params'],
                            array('field' => $key,
                                  'value' => $filter,
                                  'select' => $this->getSource()->getSelectObject())
                        );

                        $result = call_user_func($this->_filters[$key]['callback']['function'],
                            $this->_filters[$key]['callback']['params']
                        );
                        } elseif (isset($this->_data['fields'][$key]['search'])
                          && is_array($this->_data['fields'][$key]['search'])
                          && $this->_data['fields'][$key]['search']['fulltext'] == true
                             ){

                        //Fulltext search activated by user. Only possible in MySQL server
                        $this->getSource()->addFullTextSearch($filter, $this->_data['fields'][$key]);
                    } else {

                        //Nothing "special" needs to be performed. So we continue with the normal procedure
                        //Let's check if there is a special symbol in user's input
                        //Some exaemples: =valu, >value, r:regexp
                        $op = $this->getFilterOp($key, $filter);

                        $this->getSource()->addCondition($op['filter'], $op['op'], $this->_data['fields'][$key]);
                    }

                    //We assign the filter value so it can be filled properlly.
                    //Even if we perform any transform to the field, the original user's input must be showed
                    $filtersValues[$key] = $oldFilter;
                }

                if (is_array($filter)) {

                    //Load filter render
                    $render = $this->loadFilterRender($this->_filters[$key]['render']);

                    $render->setFieldName($key);

                    if ($render->hasConditions()) {
                        $cond = $render->getConditions();
                        $render->setSelect($this->getSource()->getSelectObject());

                        foreach ($filter as $nkey => $value) {
                            if (strlen($value) > 0) {
                                $oldValue = $value;
                                $value = $render->normalize($value, $nkey);
                                $this->getSource()->addCondition($value, $cond[$nkey], $this->_data['fields'][$key]);
                                $filtersValues[$key][$nkey] = $oldValue;
                            }
                        }
                    } else {
                        $render->buildQuery($filter);
                    }
                }
            }
        }

        $this->_filtersValues = $filtersValues;

        $this->_applyExternalFilters();

        //If needed put current filters values in session
        if (count($this->_filtersValues) > 0 && $this->_paramsInSession === true) {
            $this->_sessionParams->filters = $this->_filtersValues;
        }

        return $this;
    }

    /**
     * Adds external filters to the grid
     *
     * @return void
     */
    protected function _applyExternalFilters()
    {
        if (count($this->_externalFilters) == 0)
            return false;

        foreach ($this->_externalFilters as $id => $callback) {

            if ($this->getParam($id)) {
                call_user_func_array($callback, array($id, $this->getParam($id), $this->getSelect()));
                $this->_filtersValues[$id] = $this->getParam($id);
            }
        }
    }

    /**
     * Returns the operand to be used in filters
     * This value comes from the user input
     * but can be override
     *
     * @param string $field  Field name
     * @param string $filter Filter to apply to the field
     *
     * @return array
     */
    public function getFilterOp($field, $filter)
    {
        if (!isset($this->_data['fields'][$field]['searchType'])) {
            $this->_data['fields'][$field]['searchType'] = 'like';
        }

        $op = strtolower($this->_data['fields'][$field]['searchType']);

        if ($this->_data['fields'][$field]['searchType'] == 'sqlExp'
            && isset($this->_data['fields'][$field]['searchSqlExp'])
        ) {

            $op = 'sqlExp';
            $sqlExp = $this->_data['fields'][$field]['searchSqlExp'];
            $filter = str_replace('{{value}}', $this->getSource()->quoteValue($filter), $sqlExp);
        } elseif (substr(strtolower($filter), 0, 6) == ':empty') {
            $op = 'empty';
            $filter = substr($filter, 2);
        } elseif (substr(strtolower($filter), 0, 10) == ':isnotnull') {
            $op = 'isnotnull';
            $filter = substr($filter, 2);
        } elseif (substr(strtolower($filter), 0, 7) == ':isnull') {
            $op = 'isnull';
            $filter = substr($filter, 2);
        } elseif (substr(strtoupper($filter), 0, 2) == 'R:') {
            $op = 'REGEX';
            $filter = substr($filter, 2);
        } elseif (strpos($filter, '<>') !== false && substr($filter, 0, 2) != '<>') {
            $op = 'range';
        } elseif (substr($filter, 0, 1) == '=') {
            $op = '=';
            $filter = substr($filter, 1);
        } elseif (substr($filter, 0, 2) == '>=') {
            $op = '>=';
            $filter = substr($filter, 2);
        } elseif ($filter[0] == '>') {
            $op = '>';
            $filter = substr($filter, 1);
        } elseif (substr($filter, 0, 2) == '<=') {
            $op = '<=';
            $filter = substr($filter, 2);
        } elseif (substr($filter, 0, 2) == '<>' || substr($filter, 0, 2) == '!=') {
            $op = '<>';
            $filter = substr($filter, 2);
        } elseif ($filter[0] == '<') {
            $op = '<';
            $filter = substr($filter, 1);
        } elseif ($filter[0] == '*' and substr($filter, - 1) == '*') {
            $op = 'like';
            $filter = substr($filter, 1, - 1);
        } elseif ($filter[0] == '*' and substr($filter, - 1) != '*') {
            $op = 'llike';
            $filter = substr($filter, 1);
        } elseif ($filter[0] != '*' and substr($filter, - 1) == '*') {
            $op = 'rlike';
            $filter = substr($filter, 0, - 1);
        } elseif (stripos($filter, ',') !== false) {
            $op = 'IN';
        }

        if (isset($this->_data['fields']['searchTypeFixed'])
            && $this->_data['fields']['searchTypeFixed'] === true
            && $op != $this->_data['fields']['searchType']
        ) {

            $op = $this->_data['fields']['searchType'];
        }
        return array('op' => $op, 'filter' => $filter);
    }

    /**
     * Build query.
     *
     * @return bool
     */
    protected function _buildQueryOrderAndLimit()
    {
        $start = (int) $this->getParam('start');
        $order = $this->getParam('order');
        $order1 = explode('_', $order);
        $orderf = strtoupper(end($order1));


        if ($orderf == 'DESC'
            || $orderf == 'ASC'
            || ($this->_paramsInSession === true
            && is_array($this->_sessionParams->order))
        ) {

            array_pop($order1);
            $orderField = implode('_', $order1);

            if ($this->_paramsInSession === true) {
                if ($this->getParam('noOrder')) {
                    $this->_sessionParams->order = null;
                }

                if (is_array($this->_sessionParams->order) && !$this->getParam('order')) {
                    $orderField = $this->_sessionParams->order['field'];
                    $orderf = $this->_sessionParams->order['order'];
                    $this->setParam('order' . $this->getGridId(), $orderField . '_' . $orderf);
                }
            }

            if (in_array($orderField, $this->_fieldsOrder)) {
                $this->getSource()->buildQueryOrder($orderField, $orderf, true);

                if ($this->_paramsInSession === true) {
                    $this->_sessionParams->order = array('field' => $orderField, 'order' => $orderf);
                }
            }
        }

        if (strlen($this->_fieldHorizontalRow) > 0) {
            $this->getSource()->buildQueryOrder($this->_fieldHorizontalRow, 'ASC', true);
        }

        if (false === $this->_forceLimit) {
            $this->getSource()->buildQueryLimit($this->getResultsPerPage(), $start);
        }

        return true;
    }

    /**
     * Returns the number of records to show per page
     *
     * @return integer
     */
    public function getResultsPerPage()
    {
        $perPage = (int) $this->getParam('perPage', 0);

        if ($this->_paramsInSession === true && $this->getParam('perPage') === false) {
            $perPage = (int) $this->_sessionParams->perPage;
            $this->setParam('perPage' . $this->getGridId(), $perPage);
        }

        if ($perPage > 0 && array_key_exists($perPage, $this->_paginationInterval)) {
            if ($this->_paramsInSession === true) {
                $this->_sessionParams->perPage = $perPage;
            }
            return $perPage;
        } else {
            if ($this->_paramsInSession === true) {
                $this->_sessionParams->perPage = $this->_recordsPerPage;
            }
            return $this->_recordsPerPage;
        }
    }

    /**
     * Returns the url, without the param(s) specified
     *
     * @param mixed $situation Array of params to be removed
     * @param bool  $allowAjax If this URL will be called using ajax
     *
     * @todo Use a view helper to build url
     *
     * @return string
     */
    public function getUrl($situation = '', $allowAjax = true)
    {
        $situation = (array) $situation;

        //this array the a list of params that name changes
        //based on grid id. The id is prepended to the name
        $paramsGet = array('perPage',
                           'order',
                           'start',
                           'filters',
                           'noFilters',
                           '_exportTo',
                           'add',
                           'edit',
                           'noOrder',
                           'comm',
                           'gridDetail',
                           'gridRemove');

        $params = $this->getAllParams();

        if (in_array('filters', $situation)) {
            $fields = array_merge($this->getFields(), array_keys($this->_externalFilters));

            foreach ($fields as $field) {
                if (isset($params[$field . $this->getGridId()])) {
                    unset($params[$field . $this->getGridId()]);
                }
            }

            foreach ($params as $key => $value) {

                if (stripos($key, '[') !== false) {
                    $fl = explode('[', $key);

                    if (in_array(rtrim($fl[0], $this->getGridId()), $fields)) {

                        unset($params[rtrim($fl[0]) . '[' . $fl[1]]);
                    }
                }
            }
        }



        foreach ($situation as $value) {
            if (in_array($value, $paramsGet)) {
                $value = $value . $this->getGridId();
            }
            unset($params[$value]);
        }

        $paramsClean = $params;
        unset($paramsClean['controller']);
        unset($paramsClean['module']);
        unset($paramsClean['action']);
        unset($paramsClean['_zfgid']);
        unset($paramsClean['gridmod' . $this->getGridId()]);

        $this->removeParam('gridmod');


        if (is_array($this->_filters)) {
            foreach ($this->_filters as $key => $value) {
                if (is_array($key) && isset($key['render'])) {
                    unset($paramsClean[$key]);
                }
            }
        }


        $url = '';
        foreach ($paramsClean as $key => $param) {
            if (is_array($param)
                || ($key == 'perPage' && $this->_recordsPerPage == $param)
                || ($key == 'perPage' && (int) $param == 0)
                || ($key == 'start' && (int) $param == 0)
            ) {
                continue;
            }

            $url .= '/' . $this->getView()->escape($key) . '/' . $this->getView()->escape($param);
        }

        $action = '';
        if (isset($params['action'])) {
            $action = '/' . $params['action'];
        }

        if ($this->getRouteUrl() !== false) {
            $finalUrl = $this->getRouteUrl();
        } else {
            $finalUrl = $params['module'] . '/' . $params['controller'] . $action;
        }

        // Remove the action e controller keys, they are not necessary (in fact they aren't part of url)
        if (array_key_exists('ajax', $this->_info) && $this->getInfo('ajax') !== false && $allowAjax == true) {
            return $finalUrl . $url . '/gridmod' . $this->getGridId() . '/ajax' . '/_zfgid/' . $this->getGridId();
        } else {
            //$ur = new Zend_View_Helper_Url();
            //return $ur->url($params_clean, null, true);
            return $this->_baseUrl . '/' . $finalUrl . $url;
        }
    }

    /**
     * Return variable stored in info. Return default if value is not stored.
     *
     * @param string $param   Param to look for in the info var
     * @param mixed  $default Return this value if param does not exist
     *
     * @return string
     */
    public function getInfo($param, $default = false)
    {
        if (isset($this->_info[$param]))
            return $this->_info[$param];

        if (strpos($param, ',')) {
            $params = explode(',', $param);
            $param = array_map('trim', $params);

            $final = $this->_info;

            foreach ($params as $check) {
                if (!isset($final[$check])) {
                    return $default;
                }
                $final = $final[$check];
            }

            return $final;
        }

        return $default;
    }

    /**
     * Build Filters. If defined put the values
     * Also check if the user wants to hide a field
     *
     * @return mixed
     */
    protected function _buildFilters()
    {
        $return = array();
        if ($this->getInfo('noFilters') == 1) {
            return false;
        }


        $data = $this->_fields;

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'left') {

                $value['newrow'] = !isset($value['newrow']) ? false : $value['newrow'];
                $value['rowspan'] = !isset($value['rowspan']) ? null : $value['rowspan'];
                $value['colspan'] = !isset($value['colspan']) ? null : $value['colspan'];

                $return[$key] = array('type' => 'extraField',
                                      'position' => 'left',
                                      'newrow' => $value['newrow'],
                                      'rowspan' => $value['rowspan'],
                                      'colspan' => $value['colspan']);
            }
        }

        for ($i = 0; $i < count($data); $i++) {
            $nf = $this->_fields[$i];

            if (!isset($this->_data['fields'][$nf]['search'])) {
                $this->_data['fields'][$nf]['search'] = true;
            }

            if ($this->_displayField($nf)) {

                if (!isset($this->_data['fields'][$nf]['newrow'])) {
                    $newrow = false;
                } else {
                    $newrow = $this->_data['fields'][$nf]['newrow'];
                }

                if (!isset($this->_data['fields'][$nf]['rowspan'])) {
                    $rowspan = null;
                } else {
                    $rowspan = $this->_data['fields'][$nf]['rowspan'];
                }

                if (!isset($this->_data['fields'][$nf]['colspan'])) {
                    $colspan = null;
                } else {
                    $colspan = $this->_data['fields'][$nf]['colspan'];
                }




                if (is_array($this->_filters)
                    && array_key_exists($data[$i], $this->_filters)
                    && $this->_data['fields'][$nf]['search'] != false
                ) {


                    $filterValue = isset($this->_filtersValues[$data[$i]]) ? $this->_filtersValues[$data[$i]] : '';
                    $return[] = array('type' => 'field',
                                      'value' => $filterValue,
                                      'field' => $data[$i],
                                      'newrow' => $newrow,
                                      'rowspan' => $rowspan,
                                      'colspan' => $colspan);
                } else {
                    $return[] = array('type' => 'field',
                                      'field' => $data[$i],
                                      'newrow' => $newrow,
                                      'rowspan' => $rowspan,
                                      'colspan' => $colspan);
                }
            }
        }

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'right') {

                $value['newrow'] = !isset($value['newrow']) ? false : $value['newrow'];
                $value['rowspan'] = !isset($value['rowspan']) ? null : $value['rowspan'];
                $value['colspan'] = !isset($value['colspan']) ? null : $value['colspan'];

                $return[$key] = array('type' => 'extraField',
                                      'position' => 'right',
                                      'newrow' => $value['newrow'],
                                      'rowspan' => $value['rowspan'],
                                      'colspan' => $value['colspan']);
            }
        }

        return $return;
    }

    /**
     * Checks if a field should be displayed or is setted as 'remove'
     *
     * @param string $field Field Name
     *
     * @return bool
     */
    protected function _displayField($field)
    {
        if (!isset($this->_data['fields'][$field]['remove'])) {
            $this->_data['fields'][$field]['remove'] = false;
        }
        if (!isset($this->_data['fields'][$field]['hidden'])) {
            $this->_data['fields'][$field]['hidden'] = false;
        }

        if ($this->_data['fields'][$field]['remove'] == 0
            && (($this->_data['fields'][$field]['hidden'] == 0)
            || ($this->_data['fields'][$field]['hidden'] == 1 && $this->_removeHiddenFields !== true))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Replaces the brackets in fields
     *
     * @param array $fields Array of fields
     *
     * @return array
     */
    protected function _prepareReplace($fields)
    {
        // Make an array of field names in format {{$fieldname}}
        $map = array_map(create_function('$value', 'return "{{{$value}}}";'), $fields);
        if (isset($this->_options['grid']['enableUnmodifiedFieldPlaceholders'])
            && $this->_options['grid']['enableUnmodifiedFieldPlaceholders'] == true
        ) {
            // Enable placeholders for unmodified fields: Make an array of field names in format {{=$fieldname}}
            $map2 = array_map(create_function('$value', 'return "{{={$value}}}";'), $fields);
            $map = array_merge($map, $map2);
        }
        return $map;
    }

    /**
     * Build the titles with the order links (if wanted)
     *
     * @return array
     */
    protected function _buildTitles()
    {
        $return = array();
        $url = $this->getUrl(array('order', 'start', 'comm', 'noOrder'));

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'left') {

                $value['newrow'] = !isset($value['newrow']) ? false : $value['newrow'];
                $value['rowspan'] = !isset($value['rowspan']) ? null : $value['rowspan'];
                $value['colspan'] = !isset($value['colspan']) ? null : $value['colspan'];

                if ($this->__(isset($value['title']))) {
                    $fieldValue = $value['title'];
                } else {
                    $fieldValue = $value['name'];
                }

                $title = $return[$key] = array('type' => 'extraField',
                    'value' => $fieldValue,
                    'position' => 'left',
                    'newrow' => $value['newrow'],
                    'rowspan' => $value['rowspan'],
                    'colspan' => $value['colspan']);
            }
        }

        $titles = $this->_fields;

        if (!$this->getParam('noOrder')) {
            $selectOrder = $this->getSource()->getSelectOrder();

            if (count($selectOrder) == 1) {
                $this->setParam('order' . $this->getGridId(), $selectOrder[0] . '_' . strtoupper($selectOrder[1]));
            }
        }

        for ($i = 0; $i < count($this->_fields); $i++) {
            if ($this->getParam('order')) {
                $explode = explode('_', $this->getParam('order'));
                $name = str_replace('_' . end($explode), '', $this->getParam('order'));
                $this->_order[$name] = strtoupper(end($explode)) == 'ASC' ? 'DESC' : 'ASC';
            }

            $fieldsToOrder = array_values($this->_data['fields']);

            if (isset($fieldsToOrder[$i]['orderField']) && strlen($fieldsToOrder[$i]['orderField']) > 0) {
                $orderFinal = $fieldsToOrder[$i]['orderField'];
            } else {
                $orderFinal = $titles[$i];
            }

            if (is_array($this->_order)) {
                $order = $orderFinal == key($this->_order) ? $this->_order[$orderFinal] : 'ASC';
            } else {
                $order = 'ASC';
            }

            if ($this->_displayField($titles[$i])) {

                if (!isset($this->_data['fields'][$titles[$i]]['newrow'])) {
                    $newrow = false;
                } else {
                    $newrow = $this->_data['fields'][$titles[$i]]['newrow'];
                }

                if (!isset($this->_data['fields'][$titles[$i]]['rowspan'])) {
                    $rowspan = null;
                } else {
                    $rowspan = $this->_data['fields'][$titles[$i]]['rowspan'];
                }

                if (!isset($this->_data['fields'][$titles[$i]]['colspan'])) {
                    $colspan = null;
                } else {
                    $colspan = $this->_data['fields'][$titles[$i]]['colspan'];
                }


                $noOrder = $this->getInfo('noOrder') ? $this->getInfo('noOrder') : '';

                if ($this->isTranslated($titles[$i]) === true) {
                    $fieldTitle = $this->__($titles[$i]);
                } else {
                    $fieldTitle = $this->__($this->_titles[$titles[$i]]);
                }


                if ($noOrder == 1) {
                    $return[$titles[$i]] = array('type' => 'field',
                        'name' => $titles[$i],
                        'field' => $titles[$i],
                        'value' => $fieldTitle,
                        'newrow' => $newrow,
                        'rowspan' => $rowspan,
                        'colspan' => $colspan);
                } else {
                    $return[$titles[$i]] = array('type' => 'field',
                        'name' => $titles[$i],
                        'field' => $orderFinal,
                        'simpleUrl' => $url,
                        'url' => "$url/order{$this->getGridId()}/{$orderFinal}_$order",
                        'value' => $fieldTitle,
                        'newrow' => $newrow,
                        'rowspan' => $rowspan,
                        'colspan' => $colspan);
                }
            }
        }

        foreach ($this->_extraFields as $key => $value) {
            if ($value['position'] == 'right') {

                $value['newrow'] = !isset($value['newrow']) ? false : $value['newrow'];
                $value['rowspan'] = !isset($value['rowspan']) ? null : $value['rowspan'];
                $value['colspan'] = !isset($value['colspan']) ? null : $value['colspan'];

                $return[$key] = array('type' => 'extraField',
                    'value' => $this->__(isset($value['title']) ? $value['title'] : $value['name']),
                    'position' => 'right',
                    'newrow' => $value['newrow'],
                    'rowspan' => $value['rowspan'],
                    'colspan' => $value['colspan']);
            }
        }

        $this->_finalFields = $return;

        return $return;
    }

    /**
     * Replaces {{field}} for the actual field value
     *
     * @param string &$item Item to apply function
     * @param string $key   Value to search
     * @param string $text  Value to replace
     *
     * @return void
     */
    protected function _replaceSpecialTags(&$item, $key, $text)
    {
        $item = str_replace($text['find'], $text['replace'], $item);
    }

    /**
     * Applies the format option to a field
     *
     * @param string $newValue Value generated
     * @param string $value    Field Name
     * @param array  $search   Variables to search for
     * @param array  $replace  Replace variable with these values
     *
     * @return mixed
     */
    protected function _applyFieldFormat($newValue, $value, $search, $replace)
    {
        if (is_array($value)) {
            array_walk($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        return $this->_applyFormat($newValue, $value);
    }

    /**
     * Applies the callback option to a field
     *
     * @param string $newValue Value generated
     * @param string $value    Field Name
     * @param array  $search   Variables to search for
     * @param array  $replace  Replace variable with these values
     *
     * @return mixed
     */
    protected function _applyFieldCallback($newValue, $value, $search, $replace)
    {
        if (!is_callable($value['function'])) {
            throw new Bvb_Grid_Exception($value['function'] . ' not callable');
        }

        if (isset($value['params']) && is_array($value['params'])) {
            $toReplace = $value['params'];
            $toReplaceArray = array();
            $toReplaceObj = array();

            foreach ($toReplace as $key => $rep) {
                if (is_scalar($rep) || is_array($rep)) {
                    $toReplaceArray[$key] = $rep;
                } else {
                    $toReplaceObj[$key] = $rep;
                }
            }
        } else {
            return call_user_func($value['function']);
        }

        if (is_array($toReplace)) {
                array_walk(
                    $toReplaceArray,
                    array($this, '_replaceSpecialTags'),
                    array('find' => $search,'replace' => $replace)
                );
        }

        for ($i = 0; $i <= count($toReplace); $i++) {
            if (isset($toReplaceArray[$i])) {
                $toReplace[$i] = $toReplaceArray[$i];
            } elseif (isset($toReplaceObj[$i])) {
                $toReplace[$i] = $toReplaceObj[$i];
            }
        }

        return call_user_func_array($value['function'], $toReplace);
    }

    /**
     * Applies the decorator to a fields
     *
     * @param array  $find    Variables to search for
     * @param array  $replace Replace variable with these values
     * @param string $value   Field Name
     *
     * @return string
     */
    protected function _applyFieldDecorator(array $find, array $replace, $value)
    {
        return str_replace($find, $replace, $value);
    }

    /**
     * Applies escape functions to a field
     *
     * @param string $value Field Value
     *
     * @return string
     */
    protected function _applyFieldEscape($value)
    {
        if ($this->_escapeFunction === false) {
            return $value;
        }

        if (!is_callable($this->_escapeFunction)) {
            throw new Bvb_Grid_Exception($this->_escapeFunction . ' not callable');
        }

        $value = call_user_func($this->_escapeFunction, $value);

        return $value;
    }

    /**
     * Apply escape functions to column
     *
     * @param string $field Field Name
     * @param string $value Field Value
     *
     * @return mixed
     */
    private function _escapeField($field, $value)
    {
        if (!isset($this->_data['fields'][$field]['escape'])) {
            $this->_data['fields'][$field]['escape'] = 1;
        }

        if (($this->_data['fields'][$field]['escape'] ? 1 : 0) == 0) {
            return $value;
        }

        if ($this->_data['fields'][$field]['escape'] == 1) {
            return $this->_applyFieldEscape($value);
        }

        if (!is_callable($this->_data['fields'][$field]['escape'])) {
            throw new Bvb_Grid_Exception($this->_data['fields'][$field]['escape'] . ' not callable');
        }

        return call_user_func($this->_data['fields'][$field]['escape'], $value);
    }

    /**
     * Applies the view helper to the field
     *
     * @param string $newValue Value generated
     * @param string $value    Field Name
     * @param array  $search   Variables to search for
     * @param array  $replace  Replace variable with these values
     *
     * @return string
     */
    protected function _applyFieldHelper($newValue, $value, array $search, array $replace)
    {
        if (is_array($value)) {
            array_walk($value, array($this, '_replaceSpecialTags'), array('find' => $search, 'replace' => $replace));
        }

        $name = $value['name'];
        $t = $this->getView()->getHelper($name);
        $re = new ReflectionMethod($t, $name);

        if (isset($value['params']) && is_array($value['params'])) {
            $newValue = $re->invokeArgs($t, $value['params']);
        } else {
            $newValue = $re->invoke($t);
        }

        return $newValue;
    }

    /**
     * The loop for the results.
     * Check the extra-fields,
     *
     * @return array
     */
    protected function _buildGrid()
    {
        $return = array();

        $search = $this->_prepareReplace($this->_fields);

        $fields = $this->_fields;

        $i = 0;

        $classConditional = array();
        foreach ($this->_result as $row) {


            // Create a map of field values with which to replace special field placeholders (ex. {{field_name}})
            $replace = array();
            foreach ($fields as $field) {
                $row[$field] = isset($row[$field]) ? $row[$field] : null;
                $replace[$field] = $row[$field];
            }

            if (isset($this->_options['grid']['enableUnmodifiedFieldPlaceholders'])
                && $this->_options['grid']['enableUnmodifiedFieldPlaceholders'] == true
            ) {
                // Enable placeholders for unmodified fields:
                // Append a second set of fields to the replacement map, with field names prefixed by '='
                // (ex. {{=field_name}})
                // These will allow access to the original field values unmodified by formatters, etc.
                foreach ($fields as $field) {
                    $row[$field] = isset($row[$field]) ? $row[$field] : null;
                    $replace['=' . $field] = $row[$field];
                }
            }

            $replace['editUrl'] = str_replace($search, $replace, $this->_actionsUrls['edit']);
            $replace['addUrl'] = str_replace($search, $replace, $this->_actionsUrls['add']);
            $replace['deleteUrl'] = str_replace($search, $replace, $this->_actionsUrls['delete']);
            $replace['detailUrl'] = str_replace($search, $replace, $this->_actionsUrls['detail']);

            if (!in_array('{{editUrl}}', $search)) {
                $search[] = '{{editUrl}}';
            }
            if (!in_array('{{addUrl}}', $search)) {
                $search[] = '{{addUrl}}';
            }
            if (!in_array('{{deleteUrl}}', $search)) {
                $search[] = '{{deleteUrl}}';
            }
            if (!in_array('{{detailUrl}}', $search)) {
                $search[] = '{{detailUrl}}';
            }

            $this->_classRowConditionResult[$i] = '';
            if (isset($this->_classRowCondition[0]) && is_array($this->_classRowCondition[0])) {
                foreach ($this->_classRowCondition as $value) {
                    $cond = str_replace($search, $replace, $value['condition']);
                    $final = call_user_func(create_function('', "if($cond){return true;}else{return false;}"));
                    $this->_classRowConditionResult[$i] .= $final == true ? $value['class'] . ' ' : $value['else'] . '';
                }
            }

            $this->_classRowConditionResult[$i] .= ( $i % 2) ? $this->_cssClasses['even'] : $this->_cssClasses['odd'];
            if (count($this->_classCellCondition) > 0) {
                foreach ($this->_classCellCondition as $key => $value) {
                    $classConditional[$key] = '';
                    foreach ($value as $condF) {
                        $cond = str_replace($search, $replace, $condF['condition']);
                        $final = call_user_func(create_function('', "if($cond){return true;}else{return false;}"));
                        $classConditional[$key] .= $final == true ? $condF['class'] . ' ' : $condF['else'] . ' ';
                    }
                }
            }

            /**
             * Deal with extrafield from the left
             */
            foreach ($this->_getExtraFields('left') as $field) {
                $return[$i][] = $this->_buildExtraField($field, $search, $replace);
            }

            /**
             * Deal with the grid itself
             */
            foreach ($fields as $field) {
                $row[$field] = isset($row[$field]) ? $row[$field] : null;

                $newValue = $this->_escapeField($field, $row[$field]);

                if (isset($this->_data['fields'][$field]['callback']['function'])) {
                    $newValue = $this->_applyFieldCallback(
                        $newValue,
                        $this->_data['fields'][$field]['callback'],
                        $search,
                        $replace
                    );
                    $replace[$field] = $newValue;

                    $search[] = '{{callback}}';
                    $replace[] = $newValue;
                }

                if (isset($this->_data['fields'][$field]['format'])) {
                    $newValue = $this->_applyFieldFormat(
                        $newValue,
                        $this->_data['fields'][$field]['format'],
                        $search,
                        $replace
                    );
                    $replace[$field] = $newValue;

                    $search[] = '{{format}}';
                    $replace[] = $newValue;
                }

                if (isset($this->_data['fields'][$field]['helper'])) {
                    $newValue = $this->_applyFieldHelper(
                        $newValue,
                        $this->_data['fields'][$field]['helper'],
                        $search,
                        $replace
                    );
                    $replace[$field] = $newValue;

                    $search[] = '{{helper}}';
                    $replace[] = $newValue;
                }

                if (isset($this->_data['fields'][$field]['decorator'])) {
                    $newValue = $this->_applyFieldDecorator(
                        $search,
                        $replace,
                        $this->_data['fields'][$field]['decorator']
                    );
                }

                if ($this->_displayField($field)) {
                    if (isset($this->_data['fields'][$field]['translate'])
                        && $this->_data['fields'][$field]['translate'] == true
                    ) {
                        $newValue = $this->__($newValue);
                    }

                    if (!isset($this->_data['fields'][$field]['style'])) {
                        $style = '';
                    } else {
                        $style = $this->_data['fields'][$field]['style'];
                    }

                    if (isset($this->_data['fields'][$field]['class'])) {
                        $fieldClass = $this->_data['fields'][$field]['class'];
                    } else {
                        $fieldClass = '';
                    }

                    if (isset($classConditional[$field])) {
                        $finalClassConditional = $classConditional[$field];
                    } else {
                        $finalClassConditional = '';
                    }

                    if (!isset($this->_data['fields'][$field]['newrow'])) {
                        $newrow = false;
                    } else {
                        $newrow = $this->_data['fields'][$field]['newrow'];
                    }

                    if (!isset($this->_data['fields'][$field]['rowspan'])) {
                        $rowspan = null;
                    } else {
                        $rowspan = $this->_data['fields'][$field]['rowspan'];
                    }

                    if (!isset($this->_data['fields'][$field]['colspan'])) {
                        $colspan = null;
                    } else {
                        $colspan = $this->_data['fields'][$field]['colspan'];
                    }


                    $return[$i][] = array('class' => $fieldClass . ' ' . $finalClassConditional,
                                          'value' => $newValue,
                                          'field' => $field,
                                          'style' => $style,
                                          'newrow' => $newrow,
                                          'rowspan' => $rowspan,
                                          'colspan' => $colspan);
                }
            }

            /**
             * Deal with extra fields from the right
             */
            foreach ($this->_getExtraFields('right') as $field) {
                $return[$i][] = $this->_buildExtraField($field, $search, $replace);
            }
            $i++;
        }

        return $return;
    }

    /**
     * Get the extra fields for a give position
     *
     * @param string $position Postion name
     *
     * @return array
     */
    protected function _getExtraFields($position = 'left')
    {
        if (!is_array($this->_extraFields)) {
            return array();
        }

        $final = array();

        foreach ($this->_extraFields as $value) {
            if ($value['position'] == $position) {
                $final[] = $value;
            }
        }

        return $final;
    }

    /**
     * Build extra fields (apply callbacks, helpers, etc)
     *
     * @param string $field   Field Name
     * @param array  $search  Variables to search for
     * @param array  $replace Replace variable with these values
     *
     * @return array
     */
    protected function _buildExtraField($field, $search, $replace)
    {
        $originalArray = array('class' => '', 'style' => '', 'newrow' => '', 'rowspan' => '', 'colspan' => '');
        $field = array_merge($originalArray, $field);

        $value = '';
        if (isset($field['format'])) {
            $value = $this->_applyFieldFormat($value, $field['format'], $search, $replace);
            $search[] = '{{format}}';
            $replace[] = $value;
        }

        if (isset($field['callback'])) {
            $value = $this->_applyFieldCallback($value, $field['callback'], $search, $replace);
            $search[] = '{{callback}}';
            $replace[] = $value;
        }

        if (isset($field['helper'])) {
            $value = $this->_applyFieldHelper($value, $field['helper'], $search, $replace);
            $search[] = '{{helper}}';
            $replace[] = $value;
        }

        if (isset($field['decorator'])) {
            $value = $this->_applyFieldDecorator($search, $replace, $field['decorator']);
        }


        return array('class' => $field['class'],
            'value' => $value,
            'style' => $field['style'],
            'newrow' => $field['newrow'],
            'rowspan' => $field['rowspan'],
            'colspan' => $field['colspan']);
    }

    /**
     * Apply SQL Functions
     *
     * @param array $where Where condition to be applied
     *
     * @return array
     */
    protected function _buildSqlExp($where = array())
    {
        $final = $this->getInfo('sqlexp') ? $this->getInfo('sqlexp') : '';

        if (!is_array($final)) {
            return false;
        }

        $result = array();
        foreach ($final as $key => $value) {
            if (!array_key_exists($key, $this->_data['fields']))
                continue;

            if (!isset($value['value'])) {
                $value['value'] = $key;
            }

            $value['newrow'] = !isset($value['newrow']) ? false : $value['newrow'];
            $value['rowspan'] = !isset($value['rowspan']) ? null : $value['rowspan'];
            $value['colspan'] = !isset($value['colspan']) ? null : $value['colspan'];

            $resultExp = $this->getSource()->getSqlExp($value, $where);

            if (!isset($value['format']) && isset($this->_data['fields'][$key]['format'])) {
                $resultExp = $this->_applyFormat($resultExp, $this->_data['fields'][$key]['format']);
            } elseif (isset($value['format']) && strlen(isset($value['format'])) > 2 && false !== $value['format']) {
                $resultExp = $this->_applyFormat($resultExp, $value['format']);
            }

            $result[$key] = $resultExp;
        }

        if (!$result)
            return array();

        $return = array();
        foreach ($this->_finalFields as $key => $value) {
            $class = $this->getInfo("sqlexp,$key,class") ? ' ' . $this->getInfo("sqlexp,$key,class") : '';
            $value = (array_key_exists($key, $result)) ? $result[$key] : '';
            $newrow = isset($value['newrow']) ? $value['newrow'] : '';
            $rowspan = isset($value['rowspan']) ? $value['rowspan'] : '';
            $colspan = isset($value['colspan']) ? $value['colspan'] : '';

            $return[] = array('class' => $class,
                              'value' => $value,
                              'field' => $key,
                              'newrow' => $newrow,
                              'rowspan' => $rowspan,
                              'colspan' => $colspan);
        }
        return $return;
    }

    /**
     * Make sure the fields exists on the database, if not remove them from the array
     *
     * @param array $fields Array of fields to be validated
     *
     * @return void
     */
    protected function _validateFields(array $fields)
    {
        $hidden = array();
        $show = array();
        $titles = array();
        foreach ($fields as $key => $value) {
            if (!isset($value['order']) || $value['order'] == 1) {
                if (isset($value['orderField'])) {
                    $orderFields[$key] = $value['orderField'];
                } else {
                    $orderFields[$key] = $key;
                }
            }

            if (isset($value['title'])) {
                $titles[$key] = $value['title'];
            } else {
                $titles[$key] = ucwords(str_replace('_', ' ', $key));
            }

            if (isset($this->_data['fields'][$key]['hidden']) && $this->_data['fields'][$key]['hidden'] == 1) {
                $hidden[$key] = $key;
            } else {
                $show[$key] = $key;
            }
        }

        $fieldsFinal = array();
        $lastIndex = 1;
        $norder = 0;
        foreach ($show as $key => $value) {
            $value = $this->_data['fields'][$value];

            if (isset($value['position']) && (!isset($value['hidden']) || $value['hidden'] == 0)) {
                if ($value['position'] == 'last') {
                    $fieldsFinal[($lastIndex + 100)] = $key;
                } elseif ($value['position'] == 'first') {
                    $fieldsFinal[($lastIndex - 100)] = $key;
                } else {
                    if ($value['position'] == 'next') {
                        $norder = $lastIndex + 1;
                    } else {
                        $norder = (int) $value['position'];
                    }

                    if (array_key_exists($norder, $fieldsFinal)) {
                        for ($i = count($fieldsFinal); $i >= $norder; $i--) {
                            $fieldsFinal[($i + 1)] = $fieldsFinal[$i];
                        }
                        $fieldsFinal[$norder] = $key;
                    }

                    $fieldsFinal[$norder] = $key;
                }
            } elseif (!isset($value['hidden']) || $value['hidden'] == 0) {
                while (true) {
                    if (array_key_exists($lastIndex, $fieldsFinal)) {
                        $lastIndex++;
                    } else {
                        break;
                    }
                }
                $fieldsFinal[$lastIndex] = $key;
            }
        }

        ksort($fieldsFinal);

        $fieldsFinal = array_values($fieldsFinal);

        //Put the hidden fields on the end of the array
        foreach ($hidden as $value) {
            $fieldsFinal[] = $value;
        }

        $this->_fields = $fieldsFinal;
        $this->_titles = $titles;
        $this->_fieldsOrder = $orderFields;
    }

    /**
     * Make sure the filters exists, they are the name from the table field.
     * If not, remove them from the array
     * If we get an empty array, we then create a new one with all the fields specified
     * in $this->_fields method
     *
     * @return array
     */
    protected function _validateFilters()
    {
        if ($this->getInfo('noFilters') == 1)
            return false;

        if (is_array($this->_filters))
            return $this->_filters;

        return array_combine($this->_fields, $this->_fields);
    }

    /**
     * Checks if there are any filters
     *
     * @return bool
     */
    public function hasFilters()
    {
        if (count(array_intersect_key(array_combine($this->getFields(), $this->getFields()), $this->_ctrlParams)) > 0)
            return true;

        return false;
    }

    /**
     * Build user defined filters
     *
     * @return Bvb_Grid
     */
    protected function _buildDefaultFiltersValues()
    {
        if ($this->_paramsInSession === true) {
            if ($this->getParam('noFilters')) {
                $this->_sessionParams->filters = null;
            }
        }

        if ((is_array($this->_defaultFilters) || $this->_paramsInSession === true)
            && !$this->hasFilters()
            && !$this->getParam('noFilters')
        ) {

            foreach ($this->_data['fields'] as $key => $value) {
                if (!$this->_displayField($key)) {
                    continue;
                }

                if ($this->_paramsInSession === true) {
                    if (isset($this->_sessionParams->filters[$key])) {
                        if (is_array($this->_sessionParams->filters[$key])) {
                            foreach ($this->_sessionParams->filters[$key] as $skey => $svalue) {
                                if (!isset($this->_ctrlParams[$key . $this->getGridId() . '[' . $skey . ']'])) {
                                    $this->_ctrlParams[$key . $this->getGridId() . '[' . $skey . ']'] = $svalue;
                                }
                            }
                        } else {
                            $this->_ctrlParams[$key . $this->getGridId()] = $this->_sessionParams->filters[$key];
                        }
                        continue;
                    }
                }

                if (is_array($this->_defaultFilters) && array_key_exists($key, $this->_defaultFilters)) {
                    $this->_ctrlParams[$key] = $this->_defaultFilters[$key];
                }
            }
        }

        return $this;
    }

    /**
     * Deploys
     *
     * @return Bvb_Grid
     */
    public function deploy()
    {
        if ($this->getSource() === null) {
            throw new Bvb_Grid_Exception('Please specify your source');
        }


        //Disable ajax for CRUD operations
        if (!is_null($this->_crud)) {
            $this->setAjax(false);
        }

        //Add columns in queue
        foreach ($this->_updateColumnQueue as $field => $options) {
            $this->updateColumn($field, $options);
        }

        // apply additional configuration
        $this->runConfigCallbacks();

        if ($this->getParam('gridDetail') == 1
            && $this->_deployName == 'table'
            && (is_array($this->_detailColumns)
            || $this->getParam('gridRemove'))
        ) {
            $this->_isDetail = true;
        }

        if ($this->_isDetail === true && is_array($this->_detailColumns)) {
            if (count($this->_detailColumns) > 0) {
                $finalColumns = array_intersect($this->_detailColumns, array_keys($this->_data['fields']));

                foreach ($this->_data['fields'] as $key => $value) {
                    if (!in_array($key, $finalColumns)) {
                        $this->updateColumn($key, array('remove' => 1));
                    }
                }
            }
        }

        if ($this->_isDetail === false && is_array($this->_gridColumns)) {
            $finalColumns = array_intersect($this->_gridColumns, array_keys($this->_data['fields']));
            foreach ($this->_data['fields'] as $key => $value) {
                if (!in_array($key, $finalColumns)) {
                    $this->updateColumn($key, array('remove' => 1));
                }
            }

            foreach (array_keys($this->_extraFields) as $value) {
                if ($value == 'ZFG_MASS_ACTIONS')
                    continue;

                if (!in_array($value, $this->_gridColumns)) {
                    unset($this->_extraFields[$value]);
                }
            }
        }

        if ($this->_isDetail == true) {
            $result = $this->getSource()->fetchDetail($this->getIdentifierColumnsFromUrl());
            if ($result == false) {
                $this->_gridSession->message = $this->__('Record Not Found');
                $this->_gridSession->_noForm = 1;
                $this->_gridSession->correct = 1;
                $this->_redirect($this->getUrl(array('comm', 'gridDetail', 'gridRemove')));
            }
        }

        if (count($this->getSource()->getSelectOrder()) == 1 && !$this->getParam('order')) {
            $norder = $this->getSource()->getSelectOrder();

            if (!$norder instanceof Zend_Db_Expr) {
                $this->setParam('order' . $this->getGridId(), $norder[0] . '_' . strtoupper($norder[1]));
            }
        }

        $this->_buildDefaultFiltersValues();

        // Validate table fields, make sure they exist...
        $this->_validateFields($this->_data['fields']);

        // Filters. Not required that every field as filter.
        $this->_filters = $this->_validateFilters($this->_filters);

        $this->_buildFiltersValues();

        if ($this->_isDetail == false) {
            $this->_buildQueryOrderAndLimit();
        }

        if ($this->getParam('noOrder') == 1) {
            $this->getSource()->resetOrder();
        }

        $result = $this->getSource()->execute();

        if ($this->_forceLimit === false) {
            $resultCount = $this->getSource()->getTotalRecords();
        } else {
            $resultCount = $this->_forceLimit;
            if (count($result) < $resultCount) {
                $resultCount = count($result);
            }
        }

        $this->_totalRecords = $resultCount;
        $this->_result = $result;

        $this->_colspan();
        return $this;
    }

    /**
     * Get details about a column
     *
     * @param string $column Column name to be returned
     *
     * @return mixed
     */
    protected function _getColumn($column)
    {
        return isset($this->_data['fields'][$column]) ? $this->_data['fields'][$column] : null;
    }

    /**
     * Convert Object to Array
     *
     * @param object $data Obejct to be converted to array
     *
     * @return array
     */
    protected function _object2array($data)
    {
        if (!is_object($data) && !is_array($data))
            return $data;

        if (is_object($data))
            $data = get_object_vars($data);

        return array_map(array($this, '_object2array'), $data);
    }

    /**
     * set template locations
     *
     * @param string $dir    Classes Location
     * @param string $prefix Classes Prefix
     * @param string $type   Template Type
     *
     * @return Bvb_Grid
     */
    public function addTemplateDir($dir, $prefix, $type)
    {
        if (!isset($this->_templates[$type])) {
            $this->_templates[$type] = new Zend_Loader_PluginLoader();
        }

        $this->_templates[$type]->addPrefixPath(trim($prefix, '_'), trim($dir, '/') . '/', $type);
        return $this;
    }

    /**
     * Define the template to be used
     *
     * @param string $template Template Name
     * @param string $output   Outpute type. It's the deploy class name
     * @param array  $options  Template Options
     *
     * @return void
     */
    public function setTemplate($template, $output = 'table', $options = array())
    {
        $tmp = $options;
        $options['userDefined'] = $tmp;

        $class = $this->_templates[$output]->load($template, $output);

        if (isset($this->_options['template'][$output][$template])) {
            $tpOptions = array_merge($this->_options['template'][$output][$template], $options);
        } else {
            $tpOptions = $options;
        }

        $tpInfo = array('colspan' => $this->_colspan,
            'charEncoding' => $this->getCharEncoding(),
            'name' => $template, 'dir' => $this->_templates[$output]->getClassPath($template, $output),
            'class' => $this->_templates[$output]->getClassName($template, $output));

        $this->_temp[$output] = new $class();

        $this->_temp[$output]->options = array_merge($tpInfo, $tpOptions);

        return $this->_temp[$output];
    }

    /**
     * Add multiple columns at once
     *
     * @return void
     */
    public function updateColumns()
    {
        $fields = func_get_args();

        foreach ($fields as $value) {
            if (!$value instanceof Bvb_Grid_Column) {
                throw new Bvb_Grid_Exception('Instance of Bvb_Grid_Column must be provided');
            }

            foreach ($value as $field) {
                $finalField = $field['field'];
                unset($field['field']);
                $this->updateColumn($finalField, $field);
            }
        }
    }

    /**
     * Calculate colspan for pagination and top
     *
     * @return int
     */
    protected function _colspan()
    {
        $row = 0;
        $totalFields[$row] = 0;


        // add the extra left fields
        foreach ($this->_extraFields as $value) {
            if ($value['position'] != 'left') {
                continue;
            }

            $rows = 1;
            $cols = 1;
            if (isset($value['newrow']) && $value['newrow']) {
                $row++;
            }
            if (isset($value['rowspan']) && $value['rowspan'] !== null) {
                $rows = $value['rowspan'];
            }
            if (isset($value['colspan']) && $value['colspan'] !== null) {
                $cols = $value['colspan'];
            }
            if ($cols < 0) {
                $cols = 1;
            }

            // add the appropriate number of columns for the relevant rows
            for ($a = 0; $a < $rows; $a++) {
                $totalFields[$row + $a] += $cols;
            }
        }


        // loop through the fields
        foreach ($this->_fields as $nf) {
            $rows = 1;
            $cols = 1;

            if (isset($this->_data['fields'][$nf])) {
                // skip certain types of fields
                $value = $this->_data['fields'][$nf];
                if (isset($value['remove']) && $value['remove'] == 1) {
                    continue;
                } elseif (isset($value['hidden']) && $value['hidden'] == 1 && $this->_removeHiddenFields === true) {
                    continue;
                }
                if (isset($value['hRow']) && $value['hRow'] == 1) {
                    continue;
                }

                if (isset($value['newrow']) && $value['newrow']) {
                    $row++;
                }
                if (isset($value['rowspan']) && $value['rowspan'] !== null) {
                    $rows = $value['rowspan'];
                }
                if (isset($value['colspan']) && $value['colspan'] !== null) {
                    $cols = $value['colspan'];
                }
                if ($cols < 0) {
                    $cols = 1;
                }
            }

            // add the appropriate number of columns for the relevant rows
            for ($a = 0; $a < $rows; $a++) {
                if (!isset($totalFields[$row + $a])) {
                    $totalFields[$row + $a] = 0;
                }
                $totalFields[$row + $a] += $cols;
            }
        }

        // add the extra right fields
        foreach ($this->_extraFields as $value) {
            if ($value['position'] != 'right') {
                continue;
            }

            $rows = 1;
            $cols = 1;
            if (isset($value['newrow']) && $value['newrow']) {
                $row++;
            }
            if (isset($value['rowspan']) && $value['rowspan'] !== null) {
                $rows = $value['rowspan'];
            }
            if (isset($value['colspan']) && $value['colspan'] !== null) {
                $cols = $value['colspan'];
            }
            if ($cols < 0) {
                $cols = 1;
            }

            // add the appropriate number of columns for the relevant rows
            for ($a = 0; $a < $rows; $a++) {
                $totalFields[$row + $a] += $cols;
            }
        }


        if ($this->_allowDelete == 1) {
            $totalFields[$row]++;
        }

        if ($this->_allowEdit == 1) {
            $totalFields[$row]++;
        }

        if (is_array($this->_detailColumns) && $this->_isDetail == false) {
            $totalFields[$row]++;
        }

        $this->_colspan = max($totalFields);

        return $this->_colspan;
    }

    /**
     * Returns a field and its options
     *
     * @param string $field Field Name
     *
     * @return mixed
     */
    public function getField($field)
    {
        return isset($this->_data['fields'][$field]) ? $this->_data['fields'][$field] : false;
    }

    /**
     * Return fields list.
     * Optional param returns also fields options
     *
     * @param bool $returnOptions If grid should return all options or only fields names
     *
     * @return array
     */
    public function getFields($returnOptions = false)
    {
        if (false !== $returnOptions) {
            return $this->_data['fields'];
        }

        return array_keys($this->_data['fields']);
    }

    /**
     * Add filters
     *
     * @param object $filters Filters object to be added to the grid
     *
     * @return Bvb_Grid
     */
    public function addFilters($filters)
    {
        $filtersObj = $filters;

        $filters = $this->_object2array($filters);
        $filters = $filters['_filters'];

        foreach ($filtersObj->_filters as $key => $value) {
            if (isset($filters[$key]['callback'])) {
                $filters[$key]['callback'] = $value['callback'];
            }
            if (isset($filters[$key]['transform'])) {
                $filters[$key]['transform'] = $value['transform'];
            }
        }

        $this->_filters = $filters;

        foreach ($filters as $key => $filter) {
            if (isset($filter['searchType'])) {
                $this->updateColumn($key, array('searchType' => $filter['searchType']));
            }
        }

        $unspecifiedFields = array_diff($this->getFields(), array_keys($this->_filters));

        foreach ($unspecifiedFields as $value) {
            $this->updateColumn($value, array('search' => false));
        }

        return $this;
    }

    /**
     * Add extra columns
     *
     * @return Bvb_Grid
     */
    public function addExtraColumns()
    {
        $extraFields = func_get_args();

        if (is_array($this->_extraFields)) {
            $final = $this->_extraFields;
        } else {
            $final = array();
        }

        foreach ($extraFields as $value) {
            if (!$value instanceof Bvb_Grid_Extra_Column) {
                throw new Bvb_Grid_Exception($value . ' must be a instance of Bvb_Grid_Extra_Column');
            }

            if (!isset($value->_field['name']) || !is_string($value->_field['name'])) {
                throw new Bvb_Grid_Exception('You need to define the column name');
            }

            if (isset($value->_field['title']) && !is_string($value->_field['title'])) {
                throw new Bvb_Grid_Exception('title option must be a string');
            }

            $final[$value->_field['name']] = $value->_field;
        }

        $this->_extraFields = $final;
        return $this;
    }

    /**
     * Returns the grid version
     *
     * @return string
     * @static
     */
    public static function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Return number records found
     *
     * @return integer
     */
    public function getTotalRecords()
    {
        return (int) $this->_totalRecords;
    }

    /**
     * Automates export functionality
     *
     * @param string        $defaultClass
     * @param mixed         $options
     * @param mixed         $id
     * @param array|array   $classCallbacks key should be lowercase, functions to call once before deploy() or ajax()
     * @param array|boolean $requestParams  request parameters will be used if FALSE
     * @static
     *
     * @return $object
     */
    public static function factory($defaultClass, $options = array(), $id = '', $classCallbacks = array(), $requestParams = false)
    {
        if (!is_string($id)) {
            $id = '';
        }

        if (strpos($defaultClass, '_') === false) {
            $defaultClass = 'Bvb_Grid_Deploy_' . ucfirst(strtolower($defaultClass));
        }

        if (false === $requestParams) {
            // use request parameters
            $requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        // use this as request parameters
        if (!isset($options['grid'])) {
            $options['grid'] = array('requestParams' => $requestParams);
        } else {
            $options['grid']['requestParams'] = $requestParams;
        }

        if (!isset($requestParams['_exportTo' . $id])) {
            // return instance of the main Bvb object, because this is not and export request
            $grid = new $defaultClass($options);
            $lClass = $defaultClass;
        } else {
            $lClass = strtolower($requestParams['_exportTo' . $id]);
            // support translating of parameters specifig for the export initiator class
            if (isset($requestParams['_exportFrom'])) {
                // TODO support translating of parameters specifig for the export initiator class
                $requestParams = $requestParams;
            }

            // now we need to find and load the right Bvb deploy class
            // TODO support user defined classes
            $className = 'Bvb_Grid_Deploy_' . ucfirst($requestParams['_exportTo' . $id]);

            if (Zend_Loader_Autoloader::autoload($className)) {
                $grid = new $className($options);
            } else {
                $grid = new $defaultClass($options);
                $lClass = $defaultClass;
            }
        }

        // add the powerfull configuration callback function
        if (isset($classCallbacks[$lClass])) {
            $grid->_configCallbacks = $classCallbacks[$lClass];
        }

        if (is_string($id)) {
            $grid->setGridId($id);
        }

        return $grid;
    }


    /**
     * Runs configuration callbacks
     *
     * @return void
     */
    public function runConfigCallbacks()
    {
        if (!$this->_runCallbacks) {
            // makes shure that config callbacks will be used once
            return;
        }
        $this->_runCallbacks = false;

        if (!is_array($this->_configCallbacks)) {
            call_user_func($this->_configCallbacks, $this);
        } elseif (count($this->_configCallbacks) == 0) {
            // no callback
            return;
        } elseif (count($this->_configCallbacks) > 1 && is_array($this->_configCallbacks[0])) {
            die("multi");
            // TODO maybe fix
            // ordered list of callback functions defined
            foreach ($this->_configCallbacks as $func) {

            }
            break;
        } else {
            // only one callback function defined
            call_user_func($this->_configCallbacks, $this);
        }
        // run it only once
        $this->_configCallbacks = array();
    }

    /**
     * Build list of exports with options
     *
     * Options:
     * caption   - mandatory
     * img       - (default null)
     * cssClass   - (default ui-icon-extlink)
     * newWindow - (default true)
     * url       - (default actual url)
     * onClick   - (default null)
     * _class    - (reserved, used internaly)
     *
     * @return array
     */
    public function getExports()
    {
        $res = array();
        foreach ($this->_export as $name => $defs) {
            if (!is_array($defs)) {
                // only export name is passed, we need to get default option
                $name = $defs;
                $className = 'Bvb_Grid_Deploy_' . ucfirst($name); // TODO support user defined classes



                if (Zend_Loader_Autoloader::autoload($className) && method_exists($className, 'getExportDefaults')) {
                    // learn the defualt values
                    $defs = call_user_func(array($className, 'getExportDefaults'));
                } else {
                    // there are no defaults, we need at least some caption
                    $defs = array('caption' => $name);
                }

                $defs['_class'] = $className;
            }
            $res[$name] = $defs;
        }

        return $res;
    }

    /**
     * This is useful if the deploy class has no intention of using hidden fields
     *
     * @param bool $value Field Name
     *
     * @return Bvb_Grid
     */
    protected function _setRemoveHiddenFields($value)
    {
        $this->_removeHiddenFields = (bool) $value;
        return $this;
    }

    /**
     * Adds more options to the grid
     *
     * @param mixed $options Associative array of options to be applied
     *
     * @return Bvb_Grid
     */
    public function updateOptions($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        $this->_options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Defines options to the grid
     *
     * @param array $options Associative array of options to be applied
     *
     * @return Bvb_Grid
     */
    public function setOptions(array $options)
    {
        $this->_options = array_merge_recursive($options, $this->_options);
        return $this;
    }

    /**
     * Apply the options to the fields
     *
     * @return void
     */
    protected function _applyOptionsToFields()
    {
        if (isset($this->_options['fields']) && is_array($this->_options['fields'])) {
            foreach ($this->_options['fields'] as $field => $options) {
                if (isset($options['format']['function'])) {
                    if (!isset($options['format']['params'])) {
                        $options['format']['params'] = array();
                    }
                    $options['format'] = array($options['format']['function'], $options['format']['params']);
                }

                if (isset($options['callback'])) {
                    if (!isset($options['callback']['params'])) {
                        $options['callback']['params'] = array();
                    }

                    if (isset($options['callback']['function']) && isset($options['callback']['class'])) {
                        $options['callback'] = array('function' => array($options['callback']['class'],
                                $options['callback']['function']),
                            'params' => $options['callback']['params']);
                    } else {
                        $options['callback'] = array('function' => $options['callback']['function'],
                            'params' => $options['callback']['params']);
                    }
                }

                $this->updateColumn($field, $options);
            }
        }

        $deploy = explode('_', get_class($this));
        $name = strtolower(end($deploy));

        if (isset($this->_options['deploy'][$name]) && is_array($this->_options['deploy'][$name])) {
            if (method_exists($this, '_applyConfigOptions')) {
                $this->_applyConfigOptions($this->_options['deploy'][$name], true);
            } else {
                $this->_deploy = $this->_options['deploy'][$name];
            }
        }

        if (isset($this->_options['template'][$name]) && is_array($this->_options['template'][$name])) {
            $this->addTemplateParams($this->_options['template'][$name]);
        }

        if (isset($this->_options['grid']['formatter'])) {
            $this->_options['grid']['formatter'] = (array) $this->_options['grid']['formatter'];

            foreach ($this->_options['grid']['formatter'] as $formatter) {
                $temp = $formatter;
                $temp = str_replace('_', '/', $temp);
                $this->addFormatterDir($temp, $formatter);
            }
        }

        if (isset($this->_options['grid']['recordsPerPage'])) {
            $this->setRecordsPerPage($this->_options['grid']['recordsPerPage']);
        }


        if (isset($this->_options['grid']['paginationInterval'])) {
            $this->setPaginationInterval($this->_options['grid']['paginationInterval']);
        }
    }

    /**
     * Sets the grid id, to allow multiples instances per page
     *
     * @param string $id Grid to be used in grid
     *
     * @return Bvb_Grid
     */
    public function setGridId($id)
    {
        $this->_gridId = trim(preg_replace("/[^a-zA-Z0-9_]/", '_', $id), '_');
        return $this;
    }

    /**
     * Returns the current id.
     *
     * ""=>emty string is a valid value
     *
     * @param bool $forceId If we should force an id to be returned in case no one is set
     *
     * @return string
     */
    public function getGridId($forceId = false)
    {
        if ($forceId === true && strlen($this->_gridId) == 0) {
            return $this->getRequest()->getActionName()
            . '_' . $this->getRequest()->getControllerName()
            . '_' . $this->getRequest()->getModuleName();
        }
        return $this->_gridId;
    }

    /**
     * Set user defined params for templates.
     *
     * @param array $options Associative array o options to pass to template
     *
     * @return Bvb_Grid
     */
    public function setTemplateParams(array $options)
    {
        $this->_templateParams = $options;
        return $this;
    }

    /**
     * Set user defined params for templates.
     *
     * @param string $name  Name of the variable
     * @param mixed  $value value of the variable
     *
     * @return Bvb_Grid
     */
    public function addTemplateParam($name, $value)
    {
        $this->_templateParams[$name] = $value;
        return $this;
    }

    /**
     * Adds user defined params for templates.
     *
     * @param array $options Options to be passed to the template
     *
     * @return Bvb_Grid
     */
    public function addTemplateParams(array $options)
    {
        $this->_templateParams = array_merge($this->_templateParams, $options);
        return $this;
    }

    /**
     * Returns template info defined by the user
     *
     * @return array
     */
    public function getTemplateParams()
    {
        return $this->_templateParams;
    }

    /**
     * Reset options for column
     *
     * @param string $column Column which should have options reseted
     *
     * @return Bvb_Grid
     */
    public function resetColumn($column)
    {
        $support = array();
        $support[] = $this->_data['fields']['title'];
        $support[] = $this->_data['fields']['field'];
        $this->updateColumn($column, $support);
        return $this;
    }

    /**
     * Reset options for several columns
     *
     * @param array $columns Columns which should have options reseted
     *
     * @return Bvb_Grid
     */
    public function resetColumns(array $columns)
    {
        foreach ($columns as $column) {
            $this->resetColumn($column);
        }

        return $this;
    }

    /**
     * Defines which columns will be available to user
     *
     * @param array $columns Columns to be showed
     *
     * @return Bvb_Grid
     */
    public function setGridColumns(array $columns)
    {
        $this->_gridColumns = $columns;
        return $this;
    }

    /**
     * Adds more columns to be showed
     *
     * @param array $columns Columns to be showed
     *
     * @return Bvb_Grid
     */
    public function addGridColumns(array $columns)
    {
        $this->_gridColumns = array_merge($this->_gridColumns, $columns);
        return $this;
    }

    /**
     * Defines which columns will be available on detail view
     *
     * @param array $columns Columns to be showed
     *
     * @return Bvb_Grid
     */
    public function setDetailColumns($columns = array())
    {
        $this->_detailColumns = $columns;
        return $this;
    }

    /**
     * Adds more columns that will be available on detail view
     *
     * @param array $columns Array of columns to be showed within detail view
     *
     * @return Bvb_Grid
     */
    public function addDetailColumns(array $columns)
    {
        $this->_detailColumns = array_merge($this->_detailColumns, $columns);
        return $this;
    }

    /**
     * Get the list of primary keys from the URL
     *
     * @return array
     */
    public function getIdentifierColumnsFromUrl()
    {
        if (!$this->getParam('comm')) {
            return array();
        }

        $param = $this->getParam('comm');
        $explode = explode(';', $param);
        $param = end($explode);
        $param = substr($param, 1, - 1);

        $paramF = explode('-', $param);
        $param = '';

        $returnArray = array();
        foreach ($paramF as $value) {
            $f = explode(':', $value);
            $returnArray[$f[0]] = $f[1];
        }
        return $returnArray;
    }

    /**
     * Let the user know what will be displayed.
     *
     * @return array
     */
    public function willShow()
    {
        return $this->_willShow;
    }

    /**
     * Get a param from the $this->_ctrlParams appending the grid id
     *
     * @param string $param   Param Name
     * @param mixed  $default Default value to be returned if param does not exists
     *
     * @return mixed
     */
    public function getParam($param, $default = false)
    {
        $param = $param . $this->getGridId();
        return isset($this->_ctrlParams[$param]) ? $this->_ctrlParams[$param] : $default;
    }

    /**
     * Returns all params received from Zend_Controller
     *
     * @return array
     */
    public function getAllParams()
    {
        return $this->_ctrlParams;
    }

    /**
     * Redirects a user to a give URL and exits
     *
     * @param string $url  URL to redirect
     * @param int    $code URL Response code
     *
     * @return void
     */
    protected function _redirect($url, $code = 302)
    {
        $response = Zend_Controller_Front::getInstance()->getResponse();
        $response->setRedirect($url, $code);
        $response->sendResponse();
        die();
    }

    /**
     * Set a param to be used by controller.
     *
     * @param string $param Param Name
     * @param mixed  $value Param value
     *
     * @return Bvb_Grid
     */
    public function setParam($param, $value)
    {
        $this->_ctrlParams[$param] = $value;
        return $this;
    }

    /**
     * Remove a param
     *
     * @param string $param Param Name
     *
     * @return Bvb_Grid
     */
    public function removeParam($param)
    {
        unset($this->_ctrlParams[$param]);
        return $this;
    }

    /**
     * Unsets all params received from controller
     *
     * @return Bvb_Grid
     */
    public function removeAllParams()
    {
        $this->_ctrlParams = array();
        return $this;
    }

    /**
     * Defines a new set of params
     *
     * @param array $params Associative array of params to use
     *
     * @return Bvb_Grid
     */
    public function setParams(array $params)
    {
        $this->_ctrlParams = $params;
        return $this;
    }

    /**
     * Add one more para to the grid
     *
     * @param string $key   Param name
     * @param string $value Param Value
     *
     * @return Bvb_Grid
     */
    public function addParam($key, $value)
    {
        $this->_ctrlParams[$key] = $value;
        return $this;
    }

    /**
     * Use this method to add more params to the grid.
     *
     * @param array $params Associative array of params to be added
     *
     * @return Bvb_Grid
     */
    public function addParams(array $params)
    {
        $this->_ctrlParams = array_merge($this->_ctrlParams, $params);
        return $this;
    }

    /**
     * Defines which export options are available
     * Ex: array('word','pdf');
     *
     * @param array $export Array of key/pairs to be available when exporting
     *
     * @return Bvb_Grid
     */
    public function setExport(array $export)
    {
        $this->_export = $export;
        return $this;
    }

    /**
     * Adds a new export option
     *
     * @param string $name    Deploy classe name to be available when exporting
     * @param array  $options Options to be applyied
     *
     * @return Bvb_Grid
     */
    public function addExport($name, $options)
    {
        $this->_export[$name] = $options;
        return $this;
    }

    /**
     * Returns the currently setted export methods
     *
     * @return array
     */
    public function getExport()
    {
        return $this->_export;
    }

    /**
     * Defines SQL expressions
     *
     * @param array $exp Array of experessions to be built
     *
     * @return Bvb_Grid
     */
    public function setSqlExp(array $exp)
    {
        $this->_info['sqlexp'] = $exp;
        return $this;
    }

    /**
     * Defines the route URL to be applied
     *
     * @param string $url string to be prefixed on every URL
     *
     * @return Bvb_Grid
     */
    public function setRouteUrl($url)
    {
        $this->_routeUrl = (string) ltrim($url, '/');
        return $this;
    }

    /**
     * Returns the current route URL
     *
     * @return string
     */
    public function getRouteUrl()
    {
        return $this->_routeUrl;
    }

    /**
     * Loads the filter to be rendered
     *
     * @param mixed $render Type of render to be used
     *
     * @return mixed
     */
    public function loadFilterRender($render)
    {
        if (is_array($render)) {
            $toRender = key($render);
        } else {
            $toRender = $render;
        }


        $renderExists = $this->_filtersRenders->getPaths();


        $renderInfo = 'Bvb_Grid_Render_' . ucfirst($this->_deployName) . '_' . ucfirst($toRender);
        if (!array_key_exists($renderInfo, $renderExists)) {
            $this->addFiltersRenderDir('Bvb/Grid/Filters/Render/Table', 'Bvb_Grid_Filters_Render_Table');
        }

        $classname = $this->_filtersRenders->load(ucfirst($toRender));


        if (is_array($render)) {
            $class = new $classname($render[$toRender]);
        } else {
            $class = new $classname();
        }

        if (!$class instanceof Bvb_Grid_Filters_Render_RenderInterface) {
            throw new Bvb_Grid_Exception("$classname must implement Bvb_Grid_Filters_Render_RenderInterface");
        }

        $class->setGridId($this->getGridId());

        return $class;
    }

    /**
     * Adds a new dir to check for filters
     *
     * @param string $dir    Class Name
     * @param string $prefix Dir where classes are located
     *
     * @return Bvb_Grid
     */
    public function addFiltersRenderDir($dir, $prefix)
    {
        $this->_filtersRenders->addPrefixPath(trim($prefix, '_'), trim($dir, '/') . '/');
        return $this;
    }

    /**
     * Checks if the active request is a export
     *
     * @return bool
     */
    public function isExport()
    {
        return in_array($this->getParam('_exportTo'), $this->_export);
    }

    /**
     * Returns the select object from source
     *
     * @return Bvb_Grid_Source_SourceInterface
     */
    public function getSelect()
    {
        return $this->getSource()->getSelectObject();
    }


    /**
     * Adds a new external filters
     *
     * @param string $fieldId  Field id to be used
     * @param string $callback Callback to be called. Will receive. $id,$value,$select
     *
     * @throws Bvb_Grid_Exception
     * @return Bvb_Grid
     */
    public function addExternalFilter($fieldId, $callback)
    {
        if (!is_callable($callback)) {
            throw new Bvb_Grid_Exception($callback . ' not callable');
        }

        $this->_externalFilters[$fieldId] = $callback;

        return $this;
    }

    /**
     * Clears all external filters
     *
     * @return Bvb_Grid
     */
    public function clearExternalFilters()
    {
        $this->_externalFilters = array();
        return $this;
    }

    /**
     * Removes a specified filter
     *
     * @param string $fieldId Field to be removed
     *
     * @return Bvb_Grid
     */
    public function removeExternalFilter($fieldId)
    {
        if (isset($this->_externalFilters[$fieldId])) {
            unset($this->_externalFilters[$fieldId]);
        }

        return $this;
    }

    /**
     * Defines if filters should be showned in export
     *
     * @param bool $show If we should show filters or not when exporting
     *
     * @return Bvb_Grid
     */
    public function setShowFiltersInExport($show)
    {
        $this->_showFiltersInExport = $show;

        return $this;
    }

    /**
     * Whetever to save or not in session filters and order
     * This is based on gridId, if not provided, action_controller_module
     *
     * @param bool $status The status to be setted
     *
     * @return Bvb_Grid
     */
    public function saveParamsInSession($status)
    {
        $this->_paramsInSession = (bool) $status;
        return $this;
    }

    /**
     * Defines options for deployment
     *
     * @param array $options Asociative array with options for deploy
     *
     * @return Bvb_Grid
     */
    public function setDeployOptions(array $options)
    {
        foreach ($options as $option => $value) {
            $this->setDeployOption($option, $value);
        }
        return $this;
    }

    /**
     * Defines option for deployment
     *
     * @param string $option Options name
     * @param string $value  Option value
     *
     * @return Bvb_Grid
     */
    public function setDeployOption($option, $value)
    {
        $this->_deploy[$option] = $value;
        return $this;
    }

    /**
     * Retrieve a value and return $default if there is no element set.
     *
     * @param string $name    The key name to de fetched
     * @param mixed  $default The value to be returned if key does not exist
     *
     * @return mixed
     */
    public function getDeployOption($name, $default = null)
    {
        return (array_key_exists($name, $this->_deploy)) ? $this->_deploy[$name] : $default;
    }

    /**
     * Reset Deploy Options
     *
     * @return Bvb_Grid
     */
    public function clearDeployOptions()
    {
        $this->_deploy = array();
        return $this;
    }

    /**
     * retrieve deploy options
     *
     * @return array
     */
    public function getDeployOptions()
    {
        return $this->_deploy;
    }

    /**
     * Checks if there are any mass actions registered
     *
     * @return bool
     */
    public function hasMassActions()
    {
        return count($this->_massActions) > 0;
    }

    /**
     * Returns the active mass options
     *
     * @return array
     */
    public function getMassActionsOptions()
    {
        if (!$this->hasMassActions()) {
            return array();
        }

        return (array) $this->_massActions;
    }

    /**
     * Defines mass actions, overriden any previous
     *
     * @param array $options Options to be made available to user
     * @param array $fields  Fields to be used instead field identifier
     *
     * @return string
     */
    public function setMassActions(array $options, array $fields)
    {
        $this->_massActions = $options;
        $this->_massActionsFields = $fields;

        foreach ($options as $value) {
            if (!isset($value['url']) || !isset($value['caption'])) {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        if (count($fields) == 0 && count($this->getSource()->getIdentifierColumns($this->_data['table'])) == 0) {
            throw new Bvb_Grid_Exception('No primary key defined in table. Mass actions not available');
        }

        $pk = '';
        foreach ($this->getSource()->getIdentifierColumns($this->_data['table']) as $value) {
            $aux = explode('.', $value);
            $pk .= end($aux) . '-';
        }

        return rtrim($pk, '-');
    }

    /**
     * Adds a new mass action option
     *
     * @param array $options Options to be made available to user
     * @param mixed $fields  Fields to be used instead field identifier
     *
     * @return Bvb_Grid
     */
    public function addMassActions(array $options, $fields = null)
    {
        if (!$this->hasMassActions()) {
            return $this->setMassActions($options, (array) $fields);
        }

        foreach ($options as $value) {
            if (!isset($value['url']) || !isset($value['caption'])) {
                throw new Bvb_Grid_Exception('Options url and caption are required for each action');
            }
        }

        $this->_massActions = array_merge($options, $this->_massActions);

        if (is_array($fields)) {
            $this->_massActionsFields = $fields;
        }

        return $this;
    }

    /**
     * Checks if the user has the right to export for the defined format
     *
     * @throws Bvb_Grid_Exception
     *
     * @return void
     */
    public function checkExportRights()
    {
        if (!in_array($this->_deployName, $this->_export) && !array_key_exists($this->_deployName, $this->_export)) {
            throw new Bvb_Grid_Exception($this->__("You don't have permission to export the results to this format"));
        }
    }

    /**
     * Build an array based on the given key name (why this never made it to PHP core I'll never know).
     *
     * @param array  $array Array to be used
     * @param string $key   Key to be
     *
     * @see http://www.php.net/manual/en/function.array-map.php#96269
     *
     * @return array
     */
    public function arrayPluck($array, $key = 'value')
    {
        if (!is_array($array))
            return array();

        $result = array();
        foreach ($array as $row) {
            if (array_key_exists($key, $row))
                $result[] = $row[$key];
        }

        return $result;
    }

    /**
     * Defines the default grid configuration
     *
     * @param Zend_Config|array $options Config Options
     *
     * @throws Bvb_Grid_Exception
     * @static
     *
     * @return void
     */
    public static function setDefaultConfig($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            throw new Bvb_Grid_Exception('options must be an instance from Zend_Config or an array');
        }

        self::$_defaultConfig = $options;
    }

    /**
     * Returns the current default configuration
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return self::$_defaultConfig;
    }

    /**
     * Defines the separator for primary keys or fields for mass actions post values
     *
     * @param string $separator Separator to be used in post fields
     *
     * @return Bvb_Grid
     */
    public function setMassActionsSeparator($separator)
    {

        if (0 == strlen($separator)) {
            throw new Bvb_Grid_Exception('Please provide a Mass Actions separator');
        }

        $this->_massActionsSeparator = (string) $separator;

        return $this;
    }

    /**
     * Returns the current Mass Action separator for post values
     *
     * @return string
     */
    public function getMassActionsSeparator()
    {
        return $this->_massActionsSeparator;
    }

    /**
     * Reutrns fields alias
     *
     * @param string $field Field alias
     *
     * @return string
     */
    public function getFieldAlias($field)
    {
        foreach ($this->getFields(true) as $alias => $value) {
            if ($value['field'] == $field)
                return $alias;
        }
    }

}
