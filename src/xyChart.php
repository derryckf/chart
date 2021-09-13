<?php

declare(strict_types=1);

namespace Atk4\Chart;

use Atk4\Core\Exception;
use Atk4\Data\Model;
use Atk4\Ui\JsExpression;

class xyChart extends Chart
{
    public $type = 'line';
    
    /**
     * Specify data source for this chart. The column must contain
     * the textual column first followed by sumber of data columns:
     * setModel($month_report, ['month', 'total_sales', 'total_purchases']);.
     *
     * This component will automatically figure out name of the chart,
     * series titles based on column captions etc.
     */
    protected function makeExpressable($row, $key)
    {
        switch($row->getField($key)->type){
            case 'time':
                return $row->get($key)->format('h:i:s');
                break;
            case 'date':
                 return $row->get($key)->format('Y-m-d');
                break;
            default:
                 return $row->get($key);
        }
    }
    
    public function setModel(Model $model, array $columns = []): Model
    {
        if (!$columns) {
            throw new Exception('Second argument must be specified to Chart::setModel()');
        }

        $this->dataSets = [];

        // Initialize data-sets
        foreach ($columns as $key => $column) {
            if ($key === 0) {
                $title_column = $column;
                    continue; // skipping labels
            }

            $colors = array_shift($this->nice_colors);

            $this->dataSets[$column] = [
                'label' => $model->getField($column)->getCaption(),
                'backgroundColor' => $colors[0],
                'borderColor' => $colors[1],
                'borderWidth' => 1,
                'data' => [],
            ];
        }
        $this->labels=[];
        // Prepopulate data-sets
        foreach ($model as $row) {
            $this->labels[] = $this->makeExpressable($row, $title_column);
            foreach ($this->dataSets as $key => &$dataset) {
                $dataset['data'][] = ['x' => $this->makeExpressable($row, $title_column),
                                      'y' => $this->makeExpressable($row, $key)
                    ];
            }
        }

        return $model;
    }
    
   

    /**
     * Add currency label.
     *
     * @param string $char Currency symbol
     * @param string $axis y or x
     *
     * @return $this
     */
    public function withTimeSeries( string $yLabel = '', string $unit = 'week')
    {
        //$this->labels=[];
        /*
        $options['spanGaps'] = 1000 * 60 * 60 * 24 * 7;   
        
        $options['interaction'] = [
                       'mode' => 'nearest',
        ]; 
        */
        $options['responsive'] = true; 
        $options['scales'] = 
        [
            'xAxes'=> [[
                    'type' => 'time',
                    'time'  => [
                                'unit' => $unit
                                ]
            ]],
   /*
    *          $axis =>[
    
                    'type' => 'time',
                    'display'=> true,
                    'time'  => [
                                'unit' => $unit
                                ]
            ], 
    * 

            'ticks' => [
                    'autoSkip' => false,
                    'maxRotation'=> 0,
                    'major' => [
                                 'enabled' => true
                               ],
                    'minor' => [
                                 'enabled' => false
                               ],
            ],
    * 
    */
            'yAxes' =>[[
               //'type' => 'time',
               'display' => true,
                'scaleLabel' => [
                    'display' => true,
                    'labelString' => $yLabel,
                ]
            ]]
       
        ];

        $this->setOptions($options);

        return $this;
    }
}
