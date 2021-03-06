<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Filter;

/**
 * Adds a new column to every row of a DataTable based on the result of callback.
 * 
 * **Basic usage example**
 * 
 *     $callback = function ($visits, $timeSpent) {
 *         return round($timeSpent / $visits, 2);
 *     };
 *     
 *     $dataTable->filter('ColumnCallbackAddColumn', array(array('nb_visits', 'sum_time_spent'), 'avg_time_on_site', $callback));
 *
 * @package Piwik
 * @subpackage DataTable
 * @api
 */
class ColumnCallbackAddColumn extends Filter
{
    /**
     * The names of the columns to pass to the callback.
     */
    private $columns;

    /**
     * The name of the column to add.
     */
    private $columnToAdd;

    /**
     * The callback to apply to each row of the DataTable. The result is added as
     * the value of a new column.
     */
    private $functionToApply;

    /**
     * Extra parameters to pass to the callback.
     */
    private $functionParameters;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered.
     * @param array|string $columns The names of the columns to pass to the callback.
     * @param string $columnToAdd The name of the column to add.
     * @param callable $functionToApply The callback to apply to each row of a DataTable. The columns
     *                                  specified in `$columns` are passed to this callback.
     * @param array $functionParameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                                  instead.
     */
    public function __construct($table, $columns, $columnToAdd, $functionToApply, $functionParameters = array())
    {
        parent::__construct($table);

        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $this->columns = $columns;
        $this->columnToAdd = $columnToAdd;
        $this->functionToApply = $functionToApply;
        $this->functionParameters = $functionParameters;
    }

    /**
     * See [ColumnCallbackAddColumn](#).
     *
     * @param DataTable $table The table to filter.
     */
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
            $columnValues = array();
            foreach ($this->columns as $column) {
                $columnValues[] = $row->getColumn($column);
            }

            $parameters = array_merge($columnValues, $this->functionParameters);
            $value = call_user_func_array($this->functionToApply, $parameters);

            $row->setColumn($this->columnToAdd, $value);

            $this->filterSubTable($row);
        }
    }
}