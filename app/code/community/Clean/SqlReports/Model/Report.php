<?php

/**
 * @method getCreatedAt()
 * @method Clean_SqlReports_Model_Report setCreatedAt($value)
 * @method getTitle()
 * @method Clean_SqlReports_Model_Report setTitle($value)
 * @method getOutputType()
 * @method Clean_SqlReports_Model_Report setOutputType($value)
 *
 * @method Clean_SqlReports_Model_Report setChartConfig($value)
 */
class Clean_SqlReports_Model_Report extends Mage_Core_Model_Abstract
{
    /**
     * @var Clean_SqlReports_Model_Report_GridConfig
     */
    protected $_gridConfig = null;

    /**
     * @var array
     */
    protected $_filterConfig = null;

    /**
     * @var array
     */
    protected $_filterData = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('cleansql/report');
    }

    public function getReportCollection()
    {
        $data = $this->getFilterData();
        $query  = $this->getData('sql_query');
        $config = $this->getFilterConfig();

        if (!empty($data) && !empty($config)) {
            $conditions = '';
            $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

            foreach($data as $name => $value) {
                $field_config = $config[$name];
                if (empty($field_config['field'])) {
                    continue;
                }

                if (!empty($conditions)) {
                    $conditions .= " AND ";
                }

                $is_date = $field_config['type'] == 'date';
                if ($is_date) {
                    $date = Mage::app()->getLocale()->date($value, $format, null, false);
                    $value = $date->toString('yyyy-MM-dd');

                    $conditions .= 'DATE(' . $field_config['field'] . ') ' . ($field_config['comparer'] ?: '==') . ' DATE(\'' . $value . '\')';
                }
                else {
                    $conditions .= $field_config['field'] . ' ' . ($field_config['comparer'] ?: '==') . ' \'' . $value . '\'';
                }
            }
            $query = str_replace('{filter}', ($conditions?: '1=1'), $query);
        }
        else {
            $query = str_replace('{filter}', '1=1', $query);
        }

        $connection = Mage::helper('cleansql')->getDefaultConnection();

        $collection = Mage::getModel('cleansql/reportCollection', $connection);
        $collection->getSelect()->from(new Zend_Db_Expr('(' . $query . ')'));

        return $collection;
    }

    public function getChartDiv()
    {
        return 'chart_' . $this->getId();
    }

    public function hasChart()
    {
        if (! $this->getOutputType()) {
            return false;
        }

        if ($this->getOutputType() == Clean_SqlReports_Model_Config_OutputType::TYPE_PLAIN_TABLE) {
            return false;
        }

        return true;
    }
    /**
     * @return Clean_SqlReports_Model_Report_GridConfig
     */
    public function getGridConfig()
    {
        if (!$this->_gridConfig) {
            $config = json_decode($this->getData('grid_config'), true);
            if (!is_array($config)) {
                $config = array();
            }
            $this->_gridConfig = Mage::getModel('cleansql/report_gridConfig', $config);
        }
        return $this->_gridConfig;
    }

    /**
     * @return boolean
     */
    public function hasFilterConfig() {
        return !empty($this->getFilterConfig());
    }

    /**
     * @return array
     */
    public function getFilterConfig()
    {
        if (!$this->_filterConfig) {
            $config = json_decode($this->getData('filter_config'), true);
            if (!is_array($config)) {
                $config = array();
            }
            $this->_filterConfig = $config; //Mage::getModel('cleansql/report_filterConfig', $config);
        }
        return $this->_filterConfig;
    }

    /**
     * @return array
     */
    public function getFilterData()
    {
        if (!$this->_filterData) {
            $data = array();
            $filter = Mage::app()->getRequest()->getParam('filter');
            if ($filter) {
                $filter = base64_decode($filter);
                parse_str(urldecode($filter), $data);
            }
            $this->_filterData = $data;
        }
        return $this->_filterData;
    }

    /**
     * Disallow TRUNCATE, DROP, DELETE statements & remove semicolon terminator
     */
    protected function _beforeSave() {

        $disallowedPatterns = array(
            'TRUNCATE TABLE',
            'DROP TABLE',
            'DROP TEMPORARY TABLE',
            'DELETE FROM'
        );

        $sqlQuery = $this->getSqlQuery();

        if (substr($sqlQuery, -1) === ';') {
            $this->setSqlQuery(substr($sqlQuery, 0, -1));
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('cleansql')->__('Do not include a semicolon terminator'));
        }
        foreach($disallowedPatterns as $pattern) {
            if (stripos($sqlQuery, $pattern) !== false) {
                $this->_dataSaveAllowed = false;
                Mage::getSingleton('core/session')->addError(Mage::helper('cleansql')->__('TRUNCATE, DROP or DELETE statemanets are not allowed'));
                return $this;
            }
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cleansql')->__('Saved report: %s', $this->getTitle()));

        return $this;
    }

}
