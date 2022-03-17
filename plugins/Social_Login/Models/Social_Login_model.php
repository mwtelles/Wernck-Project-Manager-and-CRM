<?php

namespace Social_Login\Models;

use App\Models\Crud_model;

class Social_Login_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'users';
        parent::__construct($this->table);
    }

    function authenticate_user($email) {
        $email = $this->db->escapeString($email);

        $this->db_builder->select("id,user_type,client_id");
        $result = $this->db_builder->getWhere(array('email' => $email, 'status' => 'active', 'deleted' => 0, 'disable_login' => 0));

        if (count($result->getResult()) !== 1) {
            return false;
        }

        $user_info = $result->getRow();

        if ($this->_client_can_login($user_info) !== false) {
            $session = \Config\Services::session();
            $session->set('user_id', $user_info->id);
            return true;
        }
    }

    private function _client_can_login($user_info) {
        //check client login settings
        if ($user_info->user_type === "client" && get_setting("disable_client_login")) {
            return false;
        } else if ($user_info->user_type === "client") {
            //user can't be loged in if client has deleted
            $clients_table = $this->db->prefixTable('clients');

            $sql = "SELECT $clients_table.id
                    FROM $clients_table
                    WHERE $clients_table.id = $user_info->client_id AND $clients_table.deleted=0";
            $client_result = $this->db->query($sql);

            if ($client_result->resultID->num_rows !== 1) {
                return false;
            }
        }
    }

}
