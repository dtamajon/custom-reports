<?php
/**
 * Report Grid Config Model
 * @author Rolando Granadino <beeplogic@magenation.com>
 */
class Clean_SqlReports_Model_Report_GridConfig extends Varien_Object
{
    /**
     * get list of filterable columns
     * @return array
     */
    public function getFilterable()
    {
        $filterable = $this->getData('filterable');
        if (is_array($filterable)) {
            return $filterable;
        }
        return array();
    }

    public function getLabels()
    {
        $labels = $this->getData('labels');
        if (is_array($labels)) {
            return $labels;
        }
        return array();
    }

    /**
     * get list of clickable columns
     * @return array
     */
    public function getClickable()
    {
        $clickable = $this->getData('clickable');
        if (is_array($clickable)) {
            return $clickable;
        }
        return array();
    }

    /**
     * get list of columns types
     * @return array
     */
    public function getType()
    {
        $type = $this->getData('type');
        if (is_array($type)) {
            return $type;
        }
        return array();
    }

    /**
     * get list of columns alignment options
     * @return array
     */
    public function getAlignment()
    {
        $alignment = $this->getData('alignment');
        if (is_array($alignment)) {
            return $alignment;
        }
        return array();
    }

}
