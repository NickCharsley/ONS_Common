<?php
require_once('base.php');

class Code extends Base {
    function Code($db, $data) {
        parent::Base();
        $this->query = $data;
    }

    function execute($linefeed = "\n") {
        error_log("Running Code");
        eval($this->query);
    }
}
?>
