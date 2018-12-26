<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * An implementation for super_base_model, every other xxxx_model must use this model instead.
 * any other converter, and statics can be put in here.
 */
abstract class Base_model extends Super_base_model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * OVERRIDE PARENT METHOD.
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
     * OVERRIDE PARENT METHOD.
     * this function is for private use only, to get query result as array.
     */
    protected function _get_array()
    {
        $result = $this->db->get()->result_array();

        //untuk ganti kode sediaan di obat_jadi. 
        $this->load->model('obat_jadi/Report_model');

        if (count($result) > 0) {
            foreach ($result as $key => $data) {
                
                //untuk ganti kode sediaan di obat_jadi.
                 if (isset($data['sediaan']) && array_key_exists($data['sediaan'], Report_model::$static_bentuk_sediaan) === TRUE) {
                    $result[$key]['sediaan_name'] = Report_model::$static_bentuk_sediaan[$data['sediaan']];
                } else {
                    $result[$key]['sediaan_name'] = "Bentuk Sediaan TIdak Ditemukan.";
                }
            }
        }

        //execute extends in child class.
        $result = $this->_extend_get_array($result);

        return $result;
    }
}
