<?php

namespace Anano;

abstract class Controller
{
    protected $filters;
    
    /**
     * Add a filter to a controller with optional conditions.
     * These will be tested during the routing phase.
     *
     * @param   string  $filter         Name of the filter as defined in config/filters.php
     * @param   array   $conditions     Optional list of conditions in which to apply the filter.
     * @param   array   $data           Any other configuration data the filter might need.
     */
    
    protected function filter($filter, $conditions=array(), $data=array())
    {
        $this->filters[$filter] = array(
            'function' => Config::get('filters.' . $filter),
            'conditions' => $conditions + array('on' => null, 'only' => null, 'except' => null),
            'data' => $data
        );
    }
    
    /**
     * Run a filter immediately and return the result. This can be used in a controller method
     * if you don't want to use the class constructor to apply filters.
     */
    
    protected function check($filter, $data=array())
    {
        $function = Config::get('filters.' . $filter);
        return $function($data);
    }
    
    /**
     * Run through all the applied filters for the current route and return
     * the first negative result or TRUE if all succeeded.
     */
    
    public function _filters_run($method_name)
    {
        if ($this->filters)
        {
            foreach ($this->filters as $filter)
            {
                $verbs = array_map('strtoupper', (array)$filter['conditions']['on'] );
                if (!empty($verbs) && !in_array($_SERVER['REQUEST_METHOD'], $verbs))
                    continue;
                
                $methods = (array)$filter['conditions']['only'];
                if (!empty($methods) && !in_array($method_name, $methods))
                    continue;
                
                if (in_array($method_name, (array)$filter['conditions']['except']))
                    continue;
                
                $rv = $filter['function']($filter['data']);
                if ($rv !== true)
                    return $rv;
            }
        }
        return true;
    }
}