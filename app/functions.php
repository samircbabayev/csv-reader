<?php

// Json Response
if (!function_exists("jsonRes")) {
  function jsonRes($data){
    header('Content-Type: application/json');

    if(!$data) {
      echo json_encode(restRes(409,"No data")); die;
    }

    if (!is_array($data)) die($data);

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

    echo json_encode($data);
    die;
  }
}

// Rest Response
if (!function_exists('restRes')) {
  function restRes($code = null,$message = null,$data = []){
    return [
      "code" => $code,
      "message" => $message,
      "data" => $data
    ];
  }
}
