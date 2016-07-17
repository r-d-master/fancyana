<?php
  require_once '../api/include/DbHandler.php';

  function add_vendor(){
    $actionOk = false;
    if(count($_POST)>0) {

      $vendor_name = $_POST["add_new_vendor_name"];
      $vendor_url = $_POST["add_new_vendor_url"];
      $vendor_image = substr($_POST["image_name"], 0, -4);
      $response = array();
      $db = new DbHandler();
      $result = $db->addVendor($vendor_name, $vendor_url, $vendor_image);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['vendor_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;    
  }

  function add_banner(){
    $actionOk = false;
    if(count($_POST)>0) {

      $banner_image = substr($_POST["image_name"], 0, -4);
      $response = array();
      $db = new DbHandler();
      $result = $db->addBanner($banner_image);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['banner_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;    
  }

  function add_measurement_garment(){
    $actionOk = false;
    if(count($_POST)>0) {

      $user_id = $_POST["user_id"];
      $clothing_id = $_POST["clothing_id"];
      $measurements = $_POST["mgarment_measurements"];
      $measurement_set_name = $_POST["mgarment_name"];
      $measurement_set_image = substr($_POST["image_name"], 0, -4);
      $response = array();
      $db = new DbHandler();
      $result = $db->addGarmentMeasurementSet($user_id, $clothing_id, $measurements, $measurement_set_name, $measurement_set_image);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['mgarment_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;    
  }

  function redirect_with_message($message_type, $message_text) {
    header('Location:'.$_POST["callback_url"].'?message='.$message_type.'&message_text='.$message_text);
  }

  function call_action_function($action_function) {
    switch ($action_function) {
      case 'add_vendor': $actionWentOk = add_vendor(); return $actionWentOk;
      case 'add_banner': $actionWentOk = add_banner(); return $actionWentOk;
      case 'add_measurement_garment': $actionWentOk = add_measurement_garment(); return $actionWentOk;
      default:
        $message = "Sorry, there was an error uploading data. Action does not exist";
        redirect_with_message(0, $message);
        break;
    }
  }

  // print_r($_POST);
  // print_r($_FILES);
  $target_dir = "../uploadedimages/" . $_POST["folder"] . "/";
  $target_file = $target_dir . $_POST["image_name"];
  $uploadOk = 1;
  $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
  $message = "";
  // Check if image file is a actual image or fake image
  if(isset($_POST["submit"])) {
      $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
      if($check !== false) {
          // echo "File is an image - " . $check["mime"] . ".";
          $uploadOk = 1;
      } else {
          $message = "File is not an image.";
          $uploadOk = 0;
          redirect_with_message($uploadOk, $message);
      }
  }
  // // Check if file already exists
  // if (file_exists($target_file)) {
  //     $message = "Sorry, file already exists.";
  //     $uploadOk = 0;
  //     redirect_with_message($uploadOk, $message);
  // }
  // Check file size
  if ($_FILES["fileToUpload"]["size"] > 15000000) {
      $message = "Sorry, your file is too large.";
      $uploadOk = 0;
      redirect_with_message($uploadOk, $message);
  }
  // Allow certain file formats
  if($imageFileType != "jpg") {
      $message = "Sorry, only JPG files are allowed.";
      $uploadOk = 0;
      redirect_with_message($uploadOk, $message);
  }
  // Check if $uploadOk is set to 0 by an error
  if ($uploadOk == 0) {
      $message = "Error! Please ensure all fields were filled properly";
      redirect_with_message($uploadOk, $message);
  // if everything is ok, try to upload file
  } else {
    $callToActionWentOk = call_action_function($_POST["action_function"]);
    if ($callToActionWentOk) {
      if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message = "Success!";
            redirect_with_message($uploadOk, $message);
        } else {
            $message = "Sorry, there was an error uploading your file.";
            redirect_with_message($uploadOk, $message);
        }
    } else {
      $uploadOk = 0;
      $message = "Data Error! Please ensure all fields were filled properly";
      redirect_with_message($uploadOk, $message);      
    }
  }
?>