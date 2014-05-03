<?php
/**
 * Wrapper around nagioseasier to interact with nagios instances
 */

class Nagioseasier {


    /**
     * get the status of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "target" => "target", "status" => "status",
     *   "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function status($target) {
        $details = Nagioseasier::send_command("status $target");

        if ($details["error"] == true) {
            return $details;
        } else {
            if (strpos($target, "/") !== false) {
                // we have a service check
                $detailarray = explode(";", $details["details"]);
                return [ "error" => false,
                        "target" => array_shift($detailarray),
                        "state" => array_shift($detailarray),
                        "details" => explode("\n", trim(implode(";",$detailarray)))
                        ];
            } else {
                $services = [];
                $status = "OK";
                foreach(explode("\n", $details["details"]) as $detail) {
                    if (empty($detail)) {
                        continue;
                    }
                    $detailarray = explode(";", $detail);
                    if (strtolower($detailarray[0]) == strtolower("$target/ping")) {
                        $status = $detailarray[1];
                    }
                    $services[] =  [
                            "target" => array_shift($detailarray),
                            "state" => array_shift($detailarray),
                            "details" => explode("\n", trim(implode(";",$detailarray)))
                        ];
                }
                return [ "error" => false,
                        "target" => $target,
                        "state" => $status,
                        "details" => $services
                        ];
            }
        }
    }

    /**
     * check the status of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function check($target) {
        return Nagioseasier::send_command("check $target");
    }

    /**
     * enable notifications of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function enable_notifications($target) {
        return Nagioseasier::send_command("enable_notifications $target");
    }

    /**
     * disable notifications of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function disable_notifications($target) {
        return Nagioseasier::send_command("disable_notifications $target");
    }

    /**
     * acknowledge a problem of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *   $comment - comment for action
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function acknowledge($target, $comment = "") {
        return Nagioseasier::send_command("acknowledge $target $comment");
    }

    /**
     * remove acknowledgement of a problem for a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function unacknowledge($target) {
        return Nagioseasier::send_command("unacknowledge $target");
    }

    /**
     * schedule downtime of a host or service
     *
     * Parameters:
     *   $target  - hostname or hostname/service
     *   $minutes - minutes of downtime to set
     *   $comment - comment for action
     *
     * Returns an array of either
     *   ["error" = false, "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function downtime($target, $minutes=60, $comment="") {
        return Nagioseasier::send_command("status $target");
    }

    /**
     * get the status of a host or service
     *
     * Parameters:
     *   $target - hostname or hostname/service
     *
     * Returns an array of either
     *   ["error" = false, "target" => "target", "status" => "status",
     *   "details" => "details"]
     *
     *   or
     *
     *   ["error" => true, "details" => "details"]
     */
    static function problems($targetgroup=null, $state=null) {
        if (!empty($targetgroup)) {
            $targetgroup = " $targetgroup";
        }
        if (!empty($state)) {
            $state = " $state";
        }
        $details = Nagioseasier::send_command("problems$targetgroup$state");

        if ($details["error"] == true) {
            return $details;
        } else {
            $services = [];
            foreach(explode("\n", $details["details"]) as $detail) {
                if (empty($detail)) {
                    continue;
                }
                $detailarray = explode(";", $detail);
                $services[] =  [
                        "target" => array_shift($detailarray),
                        "state" => array_shift($detailarray),
                        "details" => explode("\n", trim(implode(";",$detailarray)))
                    ];
            }
            return [ "error" => false,
                    "target" => $target,
                    "details" => $services
                    ];
        }
    }

    /**
     * helper function to send a command to the nagios query handler
     *
     * Parameters:
     *   $cmd - command to execute
     *
     * Returns ["error" => false, "details" => "output"] or ["error" => true, "details" => "errormessage"]
     */
    static function send_command($cmd) {
        $fp = stream_socket_client("unix:///var/spool/nagios/rw/nagios.qh", $errno, $errstr, 30);
        if (!$fp) {
            return ["error" => true, "details" => "$errstr ($errno)"];
        } else {
            fwrite($fp, "#nagioseasier $cmd\0");
            $ret = "";
            $errors = false;
            while (!feof($fp)) {
                $msg = fgets($fp, 1024);
                switch (trim($msg)) {
                case "200: OK":
                    break;
                case "404: Not found":
                    $errors = true;
                    break;
                default:
                    $ret .= $msg;
                }
            }
            fclose($fp);

            if ($errors == true) {
                return ["error" => $errors, "details" => $ret];
            } else {
                return ["error" => false, "details" => $ret];
            }

        }
    }

}
