<?php

class Clean_SqlReports_Block_Adminhtml_Customreport_View extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_customreport_view';
        $this->_blockGroup = 'cleansql';
        $this->_headerText = Mage::helper('core')->__($this->_getReport()->getTitle());

        parent::__construct();
        $this->setTemplate('clean_sqlreports/grid/container.phtml');
        $this->_removeButton('add');
        $this->_removeButton('search');
    }

    protected function _prepareLayout()
    {
        return Mage_Adminhtml_Block_Widget_Container::_prepareLayout();
    }

    /**
     * @return Clean_SqlReports_Model_Report
     */
    protected function _getReport()
    {
        return Mage::registry('current_report');
    }

    /**
     * Get filter url
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/viewtable', array('_current' => true));
    }
}
