<?php
require_once("lib/general_functions.php");

class PHPDbMigrate {
    var $config = NULL;
    var $commands_list = array('add_column',
                                'remove_column',
                                'change_column',
                                'rename_column',
                                'sql',
                                'create_table',
                                'drop_table',
                                'rename_table',
                                'remove_index',
                                'add_index',
                                'code');
    var $db = NULL;

    function PHPDbMigrate($config) {
        $this->config = $config;
    }

    function run($opt_version=NULL, $environment="development") {
        $error = FALSE;
        //By default Driver is ucfirst!
        $this->config[$environment]['db']['driver']=ucfirst(strtolower($this->config[$environment]['db']['driver']));
        require_once("databases/".$this->config[$environment]['db']['driver'].".php");
        $this->db = new $this->config[$environment]['db']['driver']($this->config[$environment]['db']);
        $version = $this->db->get_version();

        $files_list = $this->_build_files_list($this->config['migrations_path']);

        if ((is_null($opt_version) && $version == (int)$files_list[sizeof($files_list) - 1]['number']) ||
            (!is_null($opt_version) && (int)$opt_version == $version)) {            
            $this->_exit("Database is currently at version {$version}");
        }

        //determine stop version
        $control = $this->_determine_stop_version($version, $opt_version, $files_list);

        // Run Files
        $this->_run_files($files_list, $control, $environment);
        $this->_exit("Migration Finished");
    }

    protected function _run_files($files_list, $control, $environment) {
        for ($i = 0; $i < sizeof($files_list); $i++) {

            try {
                if ($this->_run_version_up($control, $i, $files_list)) {
                    $this->_execute_file("up", "down", $i, $files_list, $files_list[$i]['number'], $environment);
                } else if ($this->_run_version_down($control, $i, $files_list) && (int)$control['stop'] >= (int)$files_list[sizeof($files_list) - 1]['number']) {
                    $this->_execute_file("down", "up", $i, $files_list, $files_list[$i + 1]['number'], $environment);
                } else if ($this->_run_version_down($control, $i, $files_list) && (int)$control['stop'] < (int)$files_list[sizeof($files_list) - 1]['number']) {
                    $this->_execute_file("down", "up", $i, $files_list, $control['stop'], $environment);
                }
            } catch(Exception $e) {
                error_log($e->getMessage());
                $this->_exit("There an error in file: {$files_list[$i]['file']}. Ending migration");                
                throw new Exception("Ending");
            }
        }
    }

    protected function _run_version_up($control, $i, $files_list) {
        if ($control['walk'] == "up" && $control['start'] < (int)$files_list[$i]['number'] && $control['stop'] >= (int)$files_list[$i]['number']) {
            return TRUE;
        }

        return FALSE;
    }

    protected function _run_version_down($control, $i, $files_list) {
        if ($control['walk'] == "down" && $control['start'] >= (int)$files_list[$i]['number'] && $control['stop'] < (int)$files_list[$i]['number']) {
            return TRUE;
        }

        return FALSE;
    }

    protected function _execute_file($up, $down, $i, $files_list, $version, $environment) {
        $this->_print_file_info($files_list[$i]['file']);
        $commands = $this->_parse_file("[{$up}]", "[{$down}]", $files_list[$i]['file'], $environment);
        $executables = array();

        foreach($commands as $command) {
            error_log("Parsing ".$command['command']."\n");
            $this->_load_executables($command, $executables);
        }

        foreach($executables as $executable) {
            try {
                $executable->execute($this->config["return_type"]);
                error_log("Done");
            } catch(Exception $e) {
                if ($up == "up") {
                    $old_version = $this->db->get_version();
                    $this->db->update_schema($old_version);
                } else {
                    $this->db->update_schema($files_list[$i]['number']);
                }
                $executable->logException($e->getMessage());
                throw new Exception("database fail: ".$e->getMessage());
            }
        }

        if ((int)$version == 0 && sizeof($files_list) - 1 == $i) {
            $this->db->update_schema($version);
        } else {
            if ($up == "up") {
                $this->db->update_schema($files_list[$i]['number']);
            } else {
                if (($i - 1) >= 0) {
                    $this->db->update_schema($files_list[$i - 1]['number']);
                } else {
                    $this->db->update_schema($files_list[$i + 1]['number']);
                }
            }
        }
    }

    protected function _load_executables($command, &$executables) {
        $tmp = "";
        if (strpos($command['command'], "_") !== False) {
            $tmp = explode("_", $command['command']);
            $tmp = ucfirst(strtolower($tmp[0])) . ucfirst(strtolower($tmp[1]));
        } else {
            $tmp = ucfirst(strtolower($command['command']));
        }
        require_once("lib/" . strtolower($command['command']) . ".php");

        $executables[] = new $tmp($this->db, $command['data']);
    }

    protected function _parse_file($start, $end, $file, $environment) {
        $record = FALSE;
        $queries = array();

        $doc = fopen($this->config['migrations_path'] . "/" . $file, "r");

        if ($doc) {
           while (!feof($doc)) {
               $line = fgets($doc);
               if ($this->_start_file_commands_recording($start, $line)) {
                    $record = TRUE;
                } else if ($this->_stop_file_commands_recording($end, $line)) {
                    $record = FALSE;
                }
                if ($record == TRUE) {
                    $this->_build_queries_list($queries, $line);
                }
           }
           fclose($doc);
        } else {
            throw new Exception("Could not open file: {$file}");
        }

        return $queries;
    }

    protected function _build_queries_list(&$queries, $line) {
        $temp_line = trim($line);
        $code_line = $line;
        $command_data = $this->_parse_command($temp_line);
        $head = trim($command_data[0]);
        $tail = trim($command_data[1]);

        if (in_array($head, $this->commands_list) && (strpos($temp_line, "#") === FALSE)) {
            $queries[] = array('command' => $head, "data" => $tail);
        } else if ((sizeof($queries) - 1) >= 0 && $temp_line != "" && (strpos($temp_line, "#") === FALSE || strpos($temp_line, "#") != 0) && !(endswith($queries[sizeof($queries) - 1]['command'], 'code') || endswith($queries[sizeof($queries) - 1]['command'],'sql'))) {
            $queries[sizeof($queries) - 1]['data'] .= ",{$temp_line}";
        } else if ((sizeof($queries) - 1) >= 0 && $temp_line != "" && (strpos($temp_line, "#") === FALSE || strpos($temp_line, "#") != 0) && (endswith($queries[sizeof($queries) - 1]['command'], 'code') || endswith($queries[sizeof($queries) - 1]['command'],'sql'))) {
            $queries[sizeof($queries) - 1]['data'] .= " {$code_line}";
        } else if (endswith($head, 'code') || endswith($head, 'sql') && (sizeof($queries) - 1) < 0 && (strpos($temp_line, "#") === FALSE)) {
            $queries[] = array('command' => $head, "data" => $tail);
        }
    }

    protected function _parse_command($line) {
        $start = strpos($line, ":");
        if ($start === false) {
            return array($line, "");
        }

        return array(trim(substr($line,0,$start)), trim(substr($line,$start + 1)));
    }

    protected function _start_file_commands_recording($start, $line) {
        $found = FALSE;
        if (trim(strtolower($line)) == $start) {
            $found = TRUE;
        }
        return $found;
    }

    protected function _stop_file_commands_recording($end, $line) {
        $found = FALSE;
        if (trim(strtolower($line)) == $end) {
            $found = TRUE;
        }
        return $found;
    }

    protected function _print_file_info($file) {
        error_log("Loading file {$file}:");        
    }

    protected function _exit($msg="") {
        $msg="Exit Called".($msg==""?"":": $msg");
        if(!is_null($this->db)) {
            $this->db->close();
        }
        error_log($msg);
        throw new Exception($msg);
        //exit();
    }

    protected function _determine_stop_version($version, $opt_version, &$files_list) {
        $control = array("start" => $version, "stop" => "", "walk" => "up");
        if (is_null($opt_version)) {
            $control['stop'] = (int)$files_list[sizeof($files_list) - 1]['number'];
        } else if (!is_null($opt_version)) {
            foreach ($files_list as $file) {
                if ((int)$file['number'] <= (int)$opt_version) {
                    $control['stop'] = (int)$file['number'];
                }
            }

            if ($version > (int)$opt_version) {
                $control['walk'] = "down";
                if ($control['stop'] == '') {
                    $control['stop'] = (int)$opt_version;
                }
            }
        }

        if ($control['walk'] == 'down') {
            $files_list = array_reverse($files_list);
        }

        return $control;
    }

    protected function _build_files_list($path) {
        $files = array();
        if (is_dir($path)){
            $dir = opendir($path);

            while (($file = readdir($dir)) !== false) {
                if((int)$file > 0) {
                 $files[] = $file;
                }
            }         
            closedir($dir);
        }
        if (empty($files)) {           
            $this->_exit("No updates to run");
        }

        sort($files, SORT_STRING);

        try {
            $this->_check_duplicates($files);
        } catch(Exception $e) {
            $this->_exit($e);
        }

        $files = $this->_assemble_files_array($files);
        return $files;
    }

    protected function _assemble_files_array($files) {
        $file_array = array();
        foreach($files as $file) {
            $file_array[] = array('number' => $this->_get_file_number($file), 'file' => $file);
        }

        return $file_array;
    }

    protected function _check_duplicates($files) {
        $file_numbers = array();
        foreach($files as $file) {
            $file_numbers[] = (int)$this->_get_file_number($file);
        }

        $counts = array_count_values($file_numbers);
        foreach($counts as $k => $v) {
            if ($v > 1) {
                throw new Exception("There are duplicate migration versions. Please check the versioning numbers and try again");
            }
        }

        return TRUE;
    }

    protected function _get_file_number($file) {
        $end = strpos($file, "_");
        return substr($file, 0, $end);
    }
}