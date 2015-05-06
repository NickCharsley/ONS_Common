<?php
require_once('base.php');

class DropTable extends Base {
    function DropTable($db,$data) {
        parent::Base();
        $this->_clear_attributes();
        $this->db = $db;
        $this->query = $this->_determine_attributes($data);
    }

    function execute($linefeed = "\n") {
        error_log("Running drop table: {$this->table}");
        $this->db->execute_raw_query($this->query);
        return True;
    }

    private function _determine_attributes($query) {
        $query = $this->_trim_table($query);
        return $this->db->format_drop_table($this->table);
    }
}
?>
