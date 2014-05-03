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
     *   ["error" => true, "details" => "details"
     */
    static function status($target) {
        $details = send_command("status $target");

        if ($details["error"] == true) {
            return $details;
        } else {
            $detailarray = explode(";", $details);
            return [ "error" => false,
                     "target" => array_shift($detailarray),
                     "state" => array_shift($detailarray),
                     "details" => implode(";",$detailarray)
                     ];
        }
    }

    static function check($target) {
    }

    static function enable_notifications($target) {
    }

    static function disable_notifications($target) {
    }

    static function acknowledge($target, $comment = "") {
    }

    static function unacknowledge($target) {
    }

    static function downtime($target, $minutes=60, $comment="") {
    }

    static function problems($targetgroup, $state=null) {
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
            while (!feof($fp)) {
                $ret .= fgets($fp, 1024);
            }
            fclose($fp);

            return ["error" => false, "details" => $ret];

        }
    }

}
