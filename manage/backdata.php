<?php
  require_once '../api/include/DbHandler.php';

  function redirect_with_message($message_type, $message_text) {
    header('Location:'.$_POST["callback_url"].'?message='.$message_type.'&message_text='.$message_text);
  }

  function post_design_group_add(){
    if(count($_POST)>0) {
      $design_group_name = $_POST["add_new_design_group_name"];
      $clothing_id = $_POST["add_new_design_group_clothing_id"];
      $db = new DbHandler();
      $result = $db->addDesignGroup($design_group_name, $clothing_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function post_state_add(){
    if(count($_POST)>0) {
      $state_name = $_POST["add_new_state_name"];
      $country_id = $_POST["add_new_state_country_id"];
      $db = new DbHandler();
      $result = $db->addState($state_name, $country_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function post_country_add(){
    if(count($_POST)>0) {
      $country_name = $_POST["add_new_country_name"];
      $db = new DbHandler();
      $result = $db->addCountry($country_name);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function post_measurement_type_add(){
    if(count($_POST)>0) {
      $measurement_type_name = $_POST["add_new_measurement_type_name"];
      $measurement_type_max = $_POST["add_new_measurement_type_max"];
      $measurement_type_unit = $_POST["add_new_measurement_type_unit"];
      $db = new DbHandler();
      $result = $db->addMeasurementType($measurement_type_name, $measurement_type_max, $measurement_type_unit);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function post_status_type_add(){
    if(count($_POST)>0) {
      $status_type_text = $_POST["add_new_status_type_text"];
      $db = new DbHandler();
      $result = $db->addStatusType($status_type_text);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function post_measurement_matrix_update(){
    if(count($_POST)>0) {
      $clothing_id = $_POST["measurement_matrix_clothing_id"];
      $measurement_types = $_POST["measurement_matrix_measurement_types"];
      $db = new DbHandler();
      $result = $db->updateMeasurementMatrix($clothing_id, $measurement_types);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function call_action_function($action_function) {
    switch ($action_function) {
      case 'design_group_add': post_design_group_add(); break;
      case 'state_add': post_state_add(); break;
      case 'country_add': post_country_add(); break;
      case 'measurement_type_add': post_measurement_type_add(); break;
      case 'status_type_add': post_status_type_add(); break;
      case 'measurement_matrix_update': post_measurement_matrix_update(); break;
      default:
        $message = "Sorry, there was an error uploading data. Action does not exist";
        redirect_with_message("0", $message);
        break;
    }
  }

  print_r($_POST);
  call_action_function($_POST["action_function"]);
?>