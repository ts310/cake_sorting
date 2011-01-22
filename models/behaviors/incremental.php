<?php

/**
 * Automatically increment specified column value (default: 'sorting')
 *
 * @author Tsuyoshi Saito
 */
class IncrementalBehavior extends ModelBehavior {

    public function setup($model, $settings = array()) {
        if  (!isset($this->settings[$model->alias])) {
            $this->settings[$model->alias] = array(
                'field'  => 'sorting',
                'unique' => array()
            );
        }
        $this->settings[$model->alias] = array_merge($this->settings[$model->alias], (array) $settings);
    }

    public function beforeSave($model) {
        if ($model->id == null) {
            $settings = $this->settings[$model->alias];
            $field = $settings['field'];
            $conditions = array();
            if (!empty($settings['unique'])) {
                if  (!is_array($settings['unique'])) {
                    $settings['unique'] = array($settings['unique']);
                }
                foreach ($settings['unique'] as $item) {
                    if (isset($model->data[$model->alias][$item])) {
                        $conditions = am($conditions, array(
                            $model->alias . '.' . $item => $model->data[$model->alias][$item]
                        ));
                    }
                }
            }
            $model->data[$model->alias][$field] = $this->_nextIncrement($model, $field, $conditions);
        }
        return true;
    }

    /**
     * Get next max value for the field
     * @param string $field [optional]
     * @param array $conditions [optional]
     * @returno integer
     */
    private function _nextIncrement($model, $field = 'sorting', $conditions =  array()) {
        $data = $model->find('first', array(
            'conditions' => $conditions,
            'order' => array($model->alias . '.' . $field => 'desc')
        ));
        return $data[$model->alias][$field] + 1;
    }
}
