<?php
ob_start ();
  require_once '../api/include/DbHandler.php';

  function redirect_with_message($message_type, $message_text) {
    header('Location:'.$_POST["callback_url"].'?message='.$message_type.'&message_text='.$message_text);
    ob_flush ();
  }

  function add_design_group(){
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

  function add_alteration_type(){
    if(count($_POST)>0) {
      $alteration_type_title = $_POST["add_new_alteration_type_title"];
      $alteration_type_price = $_POST["add_new_alteration_type_price"];
      $clothing_id = $_POST["add_new_alteration_type_clothing_id"];
      $db = new DbHandler();
      $result = $db->addAlterationType($alteration_type_title, $alteration_type_price, $clothing_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function add_state(){
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

  function add_country(){
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

  function add_measurement_type(){
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

  function add_status_type(){
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

  function add_order_status(){
    if(count($_POST)>0) {
      $order_id = $_POST["order_id"];
      $status_text_id = $_POST["status_text_id"];
      $db = new DbHandler();
      $result = $db->addOrderStatus($order_id, $status_text_id);
      if ($result != NULL) {
        $result2 = $db->updateOrderStatusColumn($order_id, $status_text_id);
        if ($result2 != NULL) {
          redirect_with_message("1", "Success!");
        } else {
          redirect_with_message("0", "An error occurred. Please try again.");
        }
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function add_promo(){
    if(count($_POST)>0) {
      $promo_code = $_POST["promo_code"];
      $promo_type = $_POST["promo_type"];
      $promo_discount = $_POST["promo_discount"];
      $promo_minimum_amount = $_POST["promo_minimum_amount"];
      $db = new DbHandler();
      $result = $db->addPromo($promo_code, $promo_type, $promo_discount, $promo_minimum_amount);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_measurement_matrix(){
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

  function update_ts_standard(){
    if(count($_POST)>0) {
      $user_id = $_POST["user_id"];
      $ts_standard = $_POST["ts_standard"];
      $db = new DbHandler();
      $result = $db->updateTSStandard($user_id, $ts_standard);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_clothing(){
    if(count($_POST)>0) {
      $clothing_id = $_POST["clothing_id"];
      $clothing_name = $_POST["clothing_name"];
      $price = $_POST["price"];
      $db = new DbHandler();
      $result = $db->updateClothing($clothing_id, $clothing_name, $price);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_fabric(){
    if(count($_POST)>0) {
      $fabric_id = $_POST["fabric_id"];
      $fabric_name = $_POST["fabric_name"];
      $fabric_price = $_POST["fabric_price"];
      $db = new DbHandler();
      $result = $db->updateFabric($fabric_id, $fabric_name, $fabric_price);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_design(){
    if(count($_POST)>0) {
      $design_id = $_POST["design_id"];
      $design_name = $_POST["design_name"];
      $db = new DbHandler();
      $result = $db->updateDesign($design_id, $design_name);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_design_group(){
    if(count($_POST)>0) {
      $design_group_id = $_POST["design_group_id"];
      $design_group_name = $_POST["design_group_name"];
      $db = new DbHandler();
      $result = $db->updateDesignGroup($design_group_id, $design_group_name);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_alteration_type(){
    if(count($_POST)>0) {
      $alteration_type_id = $_POST["alteration_type_id"];
      $alteration_type_title = $_POST["alteration_type_title"];
      $alteration_type_price = $_POST["alteration_type_price"];
      $db = new DbHandler();
      $result = $db->updateAlterationType($alteration_type_id, $alteration_type_title, $alteration_type_price);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_addon(){
    if(count($_POST)>0) {
      $addon_id = $_POST["addon_id"];
      $addon_name = $_POST["addon_name"];
      $addon_price = $_POST["addon_price"];
      $db = new DbHandler();
      $result = $db->updateAddon($addon_id, $addon_name, $addon_price);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_vendor(){
    if(count($_POST)>0) {
      $vendor_id = $_POST["vendor_id"];
      $vendor_name = $_POST["vendor_name"];
      $vendor_url = $_POST["vendor_url"];
      $db = new DbHandler();
      $result = $db->updateVendor($vendor_id, $vendor_name, $vendor_url);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_state(){
    if(count($_POST)>0) {
      $state_id = $_POST["state_id"];
      $state_name = $_POST["state_name"];
      $db = new DbHandler();
      $result = $db->updateState($state_id, $state_name);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_country(){
    if(count($_POST)>0) {
      $country_id = $_POST["country_id"];
      $country_name = $_POST["country_name"];
      $db = new DbHandler();
      $result = $db->updateCountry($country_id, $country_name);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_status_type(){
    if(count($_POST)>0) {
      $status_text_id = $_POST["status_text_id"];
      $status_text = $_POST["status_text"];
      $db = new DbHandler();
      $result = $db->updateStatusType($status_text_id, $status_text);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function activate_user_via_admin(){
    if(count($_POST)>0) {
      $email = $_POST["email"];
      $db = new DbHandler();
      $result = $db->activateUserByEmail($email);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function update_promo(){
    if(count($_POST)>0) {
      $promo_id = $_POST["promo_id"];
      $promo_discount = $_POST["promo_discount"];
      $promo_minimum_amount = $_POST["promo_minimum_amount"];
      $db = new DbHandler();
      $result = $db->updatePromo($promo_id, $promo_discount, $promo_minimum_amount);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function activate_promo(){
    if(count($_POST)>0) {
      $promo_id = $_POST["promo_id"];
      $db = new DbHandler();
      $result = $db->activatePromo($promo_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function deactivate_promo(){
    if(count($_POST)>0) {
      $promo_id = $_POST["promo_id"];
      $db = new DbHandler();
      $result = $db->deactivatePromo($promo_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_clothing(){
    if(count($_POST)>0) {
      $clothing_id = $_POST["clothing_id"];
      $db = new DbHandler();
      $result = $db->voidClothing($clothing_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_fabric(){
    if(count($_POST)>0) {
      $fabric_id = $_POST["fabric_id"];
      $db = new DbHandler();
      $result = $db->voidFabric($fabric_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_design(){
    if(count($_POST)>0) {
      $design_id = $_POST["design_id"];
      $db = new DbHandler();
      $result = $db->voidDesign($design_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_design_group(){
    if(count($_POST)>0) {
      $design_group_id = $_POST["design_group_id"];
      $db = new DbHandler();
      $result = $db->voidDesignGroup($design_group_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_alteration_type(){
    if(count($_POST)>0) {
      $alteration_type_id = $_POST["alteration_type_id"];
      $db = new DbHandler();
      $result = $db->voidAlterationType($alteration_type_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_addon(){
    if(count($_POST)>0) {
      $addon_id = $_POST["addon_id"];
      $db = new DbHandler();
      $result = $db->voidAddon($addon_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_vendor(){
    if(count($_POST)>0) {
      $vendor_id = $_POST["vendor_id"];
      $db = new DbHandler();
      $result = $db->voidVendor($vendor_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_state(){
    if(count($_POST)>0) {
      $state_id = $_POST["state_id"];
      $db = new DbHandler();
      $result = $db->voidState($state_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_country(){
    if(count($_POST)>0) {
      $country_id = $_POST["country_id"];
      $db = new DbHandler();
      $result = $db->voidCountry($country_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_status_type(){
    if(count($_POST)>0) {
      $status_text_id = $_POST["status_text_id"];
      $db = new DbHandler();
      $result = $db->voidStatusType($status_text_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function void_banner(){
    if(count($_POST)>0) {
      $banner_id = $_POST["banner_id"];
      $db = new DbHandler();
      $result = $db->voidBanner($banner_id);
      if ($result != NULL) {
        redirect_with_message("1", "Success!");
      } else {
        redirect_with_message("0", "An error occurred. Please try again.");
      }
    }
  }

  function call_action_function($action_function) {
    switch ($action_function) {
      case 'add_design_group': add_design_group(); break;
      case 'add_alteration_type': add_alteration_type(); break;
      case 'add_state': add_state(); break;
      case 'add_country': add_country(); break;
      case 'add_measurement_type': add_measurement_type(); break;
      case 'add_status_type': add_status_type(); break;
      case 'add_order_status': add_order_status(); break;
      case 'add_promo': add_promo(); break;
      case 'update_measurement_matrix': update_measurement_matrix(); break;
      case 'update_ts_standard': update_ts_standard(); break;
      case 'update_clothing': update_clothing(); break;
      case 'update_fabric': update_fabric(); break;
      case 'update_design': update_design(); break;
      case 'update_design_group': update_design_group(); break;
      case 'update_alteration_type': update_alteration_type(); break;
      case 'update_addon': update_addon(); break;
      case 'update_vendor': update_vendor(); break;
      case 'update_state': update_state(); break;
      case 'update_country': update_country(); break;
      case 'update_status_type': update_status_type(); break;
      case 'activate_user_via_admin': activate_user_via_admin(); break;
      case 'update_promo': update_promo(); break;
      case 'activate_promo': activate_promo(); break;
      case 'deactivate_promo': deactivate_promo(); break;
      case 'void_clothing': void_clothing(); break;
      case 'void_fabric': void_fabric(); break;
      case 'void_design': void_design(); break;
      case 'void_design_group': void_design_group(); break;
      case 'void_alteration_type': void_alteration_type(); break;
      case 'void_addon': void_addon(); break;
      case 'void_vendor': void_vendor(); break;
      case 'void_state': void_state(); break;
      case 'void_country': void_country(); break;
      case 'void_status_type': void_status_type(); break;
      case 'void_banner': void_banner(); break;
      default:
        $message = "Sorry, there was an error. Action does not exist";
        redirect_with_message("0", $message);
        break;
    }
  }

  print_r($_POST);
  call_action_function($_POST["action_function"]);
?>