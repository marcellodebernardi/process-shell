#!/usr/bin/php
<?php

/**
 * ECS518U January 2017
 * Lab 4 Running commands
 *
 *    COMMANDS          ERRORS CHECKED
 *    1. info XX         - check file/dir exists
 *    2. files
 *    ... omitted for simplicity
 *    8. Default is to run the program
 */


date_default_timezone_set('Europe/London');
$THEPATH = array("/bin/", "/usr/bin/", "/usr/local/bin/", "./");
declare(ticks = 1);

$prompt = "R0Shell>";
fwrite(STDOUT, "$prompt ");
while (1) {
    $line = trim(fgets(STDIN));
    $fields = preg_split("/\s+/", $line);
    // $numArgs = count($fields) - 1 ;
    // echo("The command is $fields[0] with $numArgs arguments\n") ;

    switch ($fields[0]) {
        case "files":
            filesCmd($fields);
            break;
        case "info":
            infoCmd($fields);
            break;
        case "exit":
            return;
        default:
            runCmd($fields);
    }

    fwrite(STDOUT, "$prompt ");
}

/**
 *  Run command
 *      Run an executable somewhere on the path
 *      Any number of arguments
 */
function runCmd($fields) {
    global $PID, $THEPATH;

    $cmd = $fields[0];

    // process arguments into more convenient form
    $cnt = 1;
    $args = array();
    while ($cnt < count($fields)) {
        $args[$cnt - 1] = $fields[$cnt];
        $cnt++;
    }
    $execname = addPath($cmd, $THEPATH);

    // run executable
    if (!$execname) {
        echo("Executable file $cmd not found\n");
    }
    else {

        $PID = pcntl_fork();

        // fork failed
        if ($PID == -1) {
            echo("Fork failed. \n");
        }
        // in parent
        else if ($PID) {
            pcntl_signal(SIGINT, function($signo) {
                // do nothing
            });

            pcntl_waitpid($PID, $status);
            $exitCode = pcntl_wexitstatus($status);
            // echo("Exit code: " . $exitCode . "\n");

            pcntl_signal(SIGINT, SIG_DFL);
        }
        // in child
        else {
            $status = pcntl_exec($execname, $args);
            if (!$status) {
                echo("Unable to run command $execname\n");
            }
        }
    }
}

function addPath($cmd, $path) {
    if (substr($cmd, 0, 1) != '/' and substr($cmd, 0, 1) != '.') {
        foreach ($path as $d) {
            $execname = $d . $cmd;
            if (file_exists($execname) and is_file($execname) and is_executable($execname)) {
                return $execname;
            }
        }
        return false;
    } else {
        return $cmd;
    }
}

/**
 * Lists directories and files in current directory. Lists directories first, in red text, followed
 * by files in white text.
 *
 * @param $fields
 */
function filesCmd($fields) {
    if (sizeof($fields) != 1) {
        echo("Incorrect usage of files command. Command does not support optional arguments.");
        return;
    }
    $directories = "\033[31m";
    $files = "\033[0m";
    foreach (glob("*") as $filename) {
        if (is_dir($filename)) $directories = $directories . $filename . "/\t";
        else $files = $files . $filename . "\t";
    }
    echo($directories . "" . $files);
}

/**
 * Displays information for indicated file. Checks if file exists.
 * @param $fields
 */
function infoCmd($fields) {
    if (sizeof($fields) != 2) {
        echo("Incorrect usage of info command. Correct usage: info [file or directory name]");
    }
    else if (!file_exists($fields[1])) {
        echo($fields[1] . ": no such file or directory.");
    }
    else {
        $fileOwner = posix_getpwuid(fileowner($fields[1]));
        $result = "" . $fileOwner['name'] . " " . date("d/m/Y", filemtime($fields[1]));
        if (is_dir($fields[1])) {
            $result = "\033[31m" . "directory\033[0m: " . $result;
        }
        else {
            $result = "file: " . $result . " " . filesize($fields[1]) . "B ";
            if (is_executable($fields[1])) $result = $result . "executable";
            else $result = $result . "not executable";
        }
        echo($result);
    }
}


//---------------------- 
// Other functions
//---------------------- 

function checkArgs($fields, $num) {
    $numArgs = count($fields) - 1;
    if ($numArgs == $num) return True;
    if ($numArgs > $num) {
        echo("  Unexpected argument ${fields[$num+1]} for command ${fields[0]}\n");
    } else {
        echo("  Missing argument for command ${fields[0]}\n");
    }
    return False;
}
?>
