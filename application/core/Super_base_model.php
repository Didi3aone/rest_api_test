<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * A Super Parent class for all models.
 * YOU DON'T CHANGE ANYTHING IN THIS CLASS!, change base_model or xxx_model instead.
 */
abstract class Super_base_model extends CI_Model
{
    protected $_table = '';
    protected $_table_alias = '';
    protected $_pk_field = '';

    /**
     * implement this in child class to extend _get_row function.
     */
    abstract protected function _extend_get_row($result);
    /**
     * implement this in child class to extend _get_array function.
     */
    abstract protected function _extend_get_array($result);
    /**
     * implement this in child class to extend insert function.
     */
    abstract protected function _extend_insert($datas);
    /**
     * implement this in child class to extend update function.
     */
    abstract protected function _extend_update($datas, $condition);
    /**
     * implement this in child class to extend delete function.
     */
    abstract protected function _extend_delete($condition);

    /**
     * Constructor, use child implementation to set protected class variables.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * $params (Array):
     * select (optional, default all) - array().
     * status (optional, default true) - boolean or -1 to show all.
     * order_by (optional , default id asc) - array("column" => "order").
     * find_by_pk (optional, default false) - array ("id1", "id2").
     * limit (optional default 0) - int.
     * start (optional default 0) - int.
     * conditions (optional default false) - array().
     * filter (optional default empty array) - array() : filter is different than keywords as it is column-based and
     *   it uses "like" not "=" like in conditions, but it's all "AND" not "OR".
     * filter_or (optional default empty array) - array() : same as filter but with "OR".
     * row_array (optional default false) - boolean.
     * count_all_first (optional default false) - boolean : if true, will count all data first before adding limit and start.
     * joined (optional default null) - array() : array of joined table,...
     *   ex. array("tbl_customer" => array("tbl_customer.id" => "tbl_main.customer_id"));
     * left_joined (optional default null) - array() : array of joined table,... (BUT LEFT JOINED) the same with joined.
     * from (optional default null) - string : a string for overwrite "from", ex: "tbl_customer tcus, tbl_country tco".
     * group_by (optional default null) - array : an array for group by something, ex: array("col1", "col2"), will make GROUP BY col1, col2;
     * debug (optional default false) - boolean : if true, will not execute the query, but instead will show pq($this->db);exit;
     * distinct (optional default false) - boolean : if true, will add "distinct" to the query.
     */
    public function get_all_data($params = array())
    {
        //default values.
        (isset($params["select"])) ? $select = $params["select"] : $select = "*";
        (isset($params["status"])) ? $status = $params["status"] : $status = IS_ACTIVE_ALL;
        (isset($params["order_by"]) && $params["order_by"] != null) ? $orderBy = $params["order_by"] : $orderBy = array($this->_pk_field => "asc");
        (isset($params["find_by_pk"])) ? $findByPK = $params["find_by_pk"]: $findByPK = array();
        (isset($params["limit"])) ? $limit = $params["limit"] : $limit = 0;
        (isset($params["start"])) ? $start = $params["start"] : $start = 0;
        (isset($params["conditions"])) ? $conditions = $params["conditions"] : $conditions = "";
        (isset($params["filter"])) ? $filter = $params["filter"] : $filter = array();
        (isset($params["filter_or"])) ? $filter_or = $params["filter_or"] : $filter_or = array();
        (isset($params["row_array"])) ? $row_array = $params["row_array"] : $row_array = false;
        (isset($params["count_all_first"])) ? $count_all_first = $params["count_all_first"] : $count_all_first = false;
        (isset($params["joined"])) ? $joined = $params["joined"] : $joined = null;
        (isset($params["left_joined"])) ? $left_joined = $params["left_joined"] : $left_joined = null;
        (isset($params["from"])) ? $from = $params["from"] : $from = $this->_table." ".$this->_table_alias;
        (isset($params["group_by"])) ? $group_by = $params["group_by"] : $group_by = null;
        (isset($params["debug"])) ? $debug = $params["debug"] : $debug = false;
        (isset($params["distinct"])) ? $distinct = $params["distinct"] : $distinct = false;

        if ($distinct == true) {
            $this->db->distinct();
        }

        $this->db->select($select);

        //status == -1 will show all.
        if ($status != -1) {
            $this->db->where($this->_table_alias.".is_active", $status);
        }

        //for search for PK "id" as array.
        if (count($findByPK) > 0) {
            $this->db->where_in($this->_pk_field, $findByPK);
        }

        //for where conditions.
        if ($conditions != "") {
            $this->db->where($conditions);
        }

        //for filters.
        if (is_array($filter) && count($filter) > 0) {
            $this->db->group_start();
            $keys = array_keys($filter);
            for ($i = 0; $i < count($keys); $i++) {
                $column = $keys[$i];
                $value = $filter[$keys[$i]];
                $this->db->like($column, $value);
            }
            $this->db->group_end();
        }
        //or filters.
        if (is_array($filter_or) && count($filter_or) > 0) {
            $this->db->group_start();
            $keys = array_keys($filter_or);
            for ($i = 0; $i < count($keys); $i++) {
                $column = $keys[$i];
                $value = $filter_or[$keys[$i]];
                if ($i == 0) {
                    $this->db->like($column, $value);
                } else {
                    $this->db->or_like($column, $value);
                }
            }
            $this->db->group_end();
        }

        //for ordering.
        foreach ($orderBy as $column => $order) {
            $this->db->order_by($column, $order);
        }

        //for join table.
        if ($joined != null) {
            foreach ($joined as $table_name => $connector) {
                $this->db->join($table_name, key($connector)." = ".$connector[key($connector)]);
            }
        }

        //for left joined table.
        if ($left_joined != null) {
            foreach ($left_joined as $table_name => $connector) {
                $this->db->join($table_name, key($connector)." = ".$connector[key($connector)], "left");
            }
        }

        //for group by
        if ($group_by != null) {
            $this->db->group_by($group_by);
        }

        //before adding limit and start, count all first.
        if ($count_all_first) {
            $result['total'] = $this->db->count_all_results($from, false);
        } else {
            $result['total'] = 0;
            $this->db->from($from);
        }

        //limit and start.
        $this->db->limit($limit, $start);

        //debug.
        if ($debug) {
            pq($this->db);
            exit;
        }

        //decide if the result is a single row or array of rows.
        if ($row_array === true) {
            $result['datas'] = $this->_get_row();
        } else {
            $result['datas'] = $this->_get_array();
        }

        //return it.
        return $result;
    }

    /**
     * this function is for private use only, to get query result as a single row only.
     */
    protected function _get_row()
    {
        $result = $this->db->get()->row_array();

        //execute extends in child class.
        $result = $this->_extend_get_row($result);

        return $result;
    }

    /**
     * this function is for private use only, to get query result as array.
     */
    protected function _get_array()
    {
        $result = $this->db->get()->result_array();

        //execute extends in child class.
        $result = $this->_extend_get_array($result);

        return $result;
    }

    /**
     * function insert.
     * @param $extra_param (array) if you need other configuration while inserting.
     * is_batch (optional, default false) - boolean, true if you want to do multiple insert.
     * is_direct (optional, default false) - boolean, true if you want to directly insert the items, and not using the standard "created_by", bla bla bla.
     */
    public function insert($datas, $extra_param = array())
    {
        //default values.
        (isset($extra_param["is_batch"])) ? $is_batch = $extra_param["is_batch"] : $is_batch = false;
        (isset($extra_param["is_direct"])) ? $is_direct = $extra_param["is_direct"] : $is_direct = false;

        //if not batches.
        if (!$is_batch) {
            if (!$is_direct) {
                $datas['created_date'] = strtotime("now");
            }

            $this->db->insert($this->_table, $datas);

            //execute extends in child class.
            $this->_extend_insert($datas);

            return $this->db->insert_id();
        } else {
            if (!$is_direct) {
                $datas = array_map(function ($value) {
                    $value['created_date'] = strtotime("now");
                    return $value;
                }, $datas);
            }

            //insert batch.
            $num_inserted = $this->db->insert_batch($this->_table, $datas);

            //execute extends in child class.
            $this->_extend_insert($datas);

            return $num_inserted;
        }
    }

    /**
     * function update.
     * @param $extra_param (array) if you need other configuration while updating.
     * is_direct (optional, default false) - boolean, true if you want to directly update the items, and not using the standard "updated_by", bla bla bla.
     */
    public function update($datas, $condition, $extra_param = array())
    {
        //default values.
        $is_direct = (isset($extra_param["is_direct"])) ? $extra_param["is_direct"] : false;

        if (!$is_direct) {
            $datas['updated_date'] = strtotime("now");
        }

        //execute extends in child class.
        $this->_extend_update($datas, $condition);

        return $this->db->update($this->_table, $datas, $condition);
    }

    /**
     * function delete.
     * @param $extra_param (array) if you need other configuration while deleting.
     * is_permanently (optional, default false) - boolean, true if you want to directly delete the items, and not using the standard "deleted_by", bla bla bla.
     */
    public function delete($condition, $extra_param = array())
    {
        //default values.
        (isset($extra_param["is_permanently"])) ? $is_permanently = $extra_param["is_permanently"] : $is_permanently = false;

        if (!$is_permanently) {
            //delete just change status.
            $datas = array(
                'status' => STATUS_DELETE,
                'deactivated_date'  => strtotime("now"),
            );

            //execute extends in child class.
            $this->_extend_delete($condition);

            return $this->db->update($this->_table, $datas, $condition);
        } else {
            //delete will delete row permanently.

            //execute extends in child class.
            $this->_extend_delete($condition);

            return $this->db->delete($this->_table, $condition);
        }
    }
}
