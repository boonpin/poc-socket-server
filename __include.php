<?php

$SRC_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;

const INCLUDE_FOLDERS = ['logger', 'utils'];

foreach (scandir($SRC_DIR) as $file) {
    if (substr($file, -4) === ".php") {
        require_once $SRC_DIR . $file;
    }
}

foreach (INCLUDE_FOLDERS as $folder) {
    if (file_exists($SRC_DIR . $folder)) {
        foreach (scandir($SRC_DIR . $folder) as $file) {
            if (substr($file, -4) === ".php") {
                require_once $SRC_DIR . $folder . DIRECTORY_SEPARATOR . $file;
            }
        }
    }
}