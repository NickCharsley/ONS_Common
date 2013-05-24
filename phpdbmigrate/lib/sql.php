<?php
require_once('base.php');

class Sql extends Base {
    function Sql($db, $data) {
        parent::Base();
        $this->db = $db;
        $this->query = $data;
    }

    function execute($linefeed = "\n") {
        error_log("Running sql");
        $this->db->execute_raw_query($this->query);
        return True;
    }
}
?>
