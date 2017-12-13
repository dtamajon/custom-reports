<?php

class Clean_SqlReports_Block_Adminhtml_Customreport_View_Filter extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @return Clean_SqlReports_Model_Report
     */
    protected function _getReport()
    {
        return Mage::registry('current_report');
    }


    /**
     * Report type options
     */
    protected $_reportTypeOptions = array();

    /**
     * Report field visibility
     */
    protected $_fieldVisibility = array();

    /**
     * Report field opions
     */
    protected $_fieldOptions = array();

    /**
     * Set field visibility
     *
     * @param string Field id
     * @param bool Field visibility
     */
    public function setFieldVisibility($fieldId, $visibility)
    {
        $this->_fieldVisibility[$fieldId] = (bool)$visibility;
    }

    /**
     * Get field visibility
     *
     * @param string Field id
     * @param bool Default field visibility
     * @return bool
     */
    public function getFieldVisibility($fieldId, $defaultVisibility = true)
    {
        if (!array_key_exists($fieldId, $this->_fieldVisibility)) {
            return $defaultVisibility;
        }
        return $this->_fieldVisibility[$fieldId];
    }

    /**
     * Set field option(s)
     *
     * @param string $fieldId Field id
     * @param mixed $option Field option name
     * @param mixed $value Field option value
     */
    public function setFieldOption($fieldId, $option, $value = null)
    {
        if (is_array($option)) {
            $options = $option;
        } else {
            $options = array($option => $value);
        }
        if (!array_key_exists($fieldId, $this->_fieldOptions)) {
            $this->_fieldOptions[$fieldId] = array();
        }
        foreach ($options as $k => $v) {
            $this->_fieldOptions[$fieldId][$k] = $v;
        }
    }

    /**
     * Add report type option
     *
     * @param string $key
     * @param string $value
     * @return Mage_Adminhtml_Block_Report_Filter_Form
     */
    public function addReportTypeOption($key, $value)
    {
        $this->_reportTypeOptions[$key] = $this->__($value);
        return $this;
    }

    /**
     * Add fieldset with general report fields
     *
     * @return Mage_Adminhtml_Block_Report_Filter_Form
     */
    protected function _prepareForm()
    {
       /** @var Clean_SqlReports_Model_Report_FilterConfig $config */
        $config     = $this->_getReport()->getFilterConfig();
        if (empty($config)) {
            return parent::_prepareForm();
        }

        $data     = $this->_getReport()->getFilterData();

        $actionUrl = $this->getUrl('*/*/viewtable/report_id/'.$this->_getReport()->getReportId());
        $form = new Varien_Data_Form(
            array('id' => 'filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'clean_sqlreport_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Filter')));

        $elements_to_ignore = array('field', 'comparer');
        foreach($config as $index => $options) {
            $field_options = array();
            $name = '';
            $type = '';
            foreach($options as $option => $value) {
                if (in_array($option, $elements_to_ignore)) {
                    continue;
                }

                if ($option == 'name') {
                    $name = $value;
                }

                if ($option == 'type' and $value == 'date') {
                    $type = $value;
                    $field_options['image'] = $this->getSkinUrl('images/grid-cal.gif');
                    $field_options['format'] = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                }
                elseif ($option == 'type') {
                    $type = $value;
                }
                elseif ($option == 'label' || $option == 'title') {
                    $field_options[$option] = Mage::helper('reports')->__($value);
                }
                else {
                    $field_options[$option] = $value;
                }
            }
            if (empty($name)) {
                $name = $index;
                $field_options['name'] = $name;
            }

            if ($data[$name]) {
                $field_options['value'] = $data[$name];
            }

            $fieldset->addField($name, $type, $field_options);
        }

        /*$fieldset->addField('filter_form_submit', 'button', array(
            'value'   => Mage::helper('reports')->__('Show Report'),
            'onclick' => 'filterFormSubmit()'
        )); */

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues()
    {
        /*$data = $this->getFilterData()->getData();
        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value[0])) {
                $data[$key] = explode(',', $value[0]);
            }
        }
        $this->getForm()->addValues($data);*/
        return parent::_initFormValues();
    }

    /**
     * This method is called before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();

        if (empty($this->getForm())) {
            return $result;
        }

        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
            // apply field visibility
            foreach ($fieldset->getElements() as $field) {
                if (!$this->getFieldVisibility($field->getId())) {
                    $fieldset->removeField($field->getId());
                }
            }
            // apply field options
            foreach ($this->_fieldOptions as $fieldId => $fieldOptions) {
                $field = $fieldset->getElements()->searchById($fieldId);
                /** @var Varien_Object $field */
                if ($field) {
                    foreach ($fieldOptions as $k => $v) {
                        $field->setDataUsingMethod($k, $v);
                    }
                }
            }
        }

        return $result;
    }

}
