<?php

declare(strict_types=1);

namespace Atk4\Chart;

use Atk4\Core\Exception;
use Atk4\Data\Model;
use Atk4\Ui\JsExpression;

class PieChart extends Chart
{
    /** @var string Type of chart */
    public $type = 'pie';
    

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
    
    /**
     * Specify data source for this chart. The column must contain
     * the textual column first followed by sumber of data columns:
     * setModel($month_report, ['month', 'total_sales', 'total_purchases']);.
     *
     * This component will automatically figure out name of the chart,
     * series titles based on column captions etc.
     */
    public function setModel(Model $model, array $columns = []): Model
    {
        if (!$columns) {
            throw new Exception('Second argument must be specified to Chart::setModel()');
        }

        $this->dataSets = [];
        $colors = [];

        // Initialize data-sets
        foreach ($columns as $key => $column) {
            $colors[$column] = $this->nice_colors;

            if ($key === 0) {
                $title_column = $column;

                continue; // skipping labels
            }

            $this->dataSets[$column] = [
                //'label' => $model->getField($column)->getCaption(),
                'data' => [],
                'backgroundColor' => [], //$colors[0],
                //'borderColor' => [], //$colors[1],
                //'borderWidth' => 1,
            ];
        }

        // Prepopulate data-sets
        foreach ($model as $row) {
            $this->labels[] = $this->makeExpressable($row, $title_column);
            foreach ($this->dataSets as $key => &$dataset) {
                $dataset['data'][] = $this->makeExpressable($row, $key);
                $color = array_shift($colors[$key]);
                $dataset['backgroundColor'][] = $color[0];
                $dataset['borderColor'][] = $color[1];
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
    public function withCurrency(string $char = '€', string $axis = 'y')
    {
        $options['tooltips'] = [
            //'enabled' => true,
            //'mode'    => 'single',
            'callbacks' => [
                'label' => new JsExpression('{}', [
                    'function(item, data, bb) {
                        var val = data.datasets[item.datasetIndex].data[item.index];
                        return "' . $char . '" +  val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }',
                ]),
            ],
        ];

        $this->setOptions($options);

        return $this;
    }
}
