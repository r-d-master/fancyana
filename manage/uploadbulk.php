<?php
  require_once '../api/include/DbHandler.php';

  function add_clothing($imageFileName, $imageMainName){
    $actionOk = false;
    if(count($_POST)>0) {

      $clothing_name = $imageMainName;
      $clothing_image = $imageFileName;
      $is_for_women = $_POST["add_new_dress_gender"];
      $price=$_POST["add_new_dress_price"];
      $response = array();
      $db = new DbHandler();
      $result = $db->addClothing($clothing_name, $clothing_image, $is_for_women, $price);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['clothing_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;
  }

  function add_fabric($imageFileName, $imageMainName){
    $actionOk = false;
    if(count($_POST)>0) {

      $clothing_id = $_POST["add_new_fabric_clothing_id"];
      $fabric_name = $imageMainName;
      $fabric_image = $imageFileName;
      $fabric_price = $_POST["add_new_fabric_price"];
      $response = array();
      $db = new DbHandler();
      $result = $db->addFabric($clothing_id, $fabric_name, $fabric_image, $fabric_price);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['fabric_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;
  }

  function add_design($imageFileName, $imageMainName){
    $actionOk = false;
    if(count($_POST)>0) {

      $design_group_id = $_POST["add_new_design_design_group_id"];
      if ($design_group_id == 0) {
        return false;
      }
      $design_name = $imageMainName;
      $design_image = $imageFileName;
      $response = array();
      $db = new DbHandler();
      $result = $db->addDesign($design_group_id, $design_name, $design_image);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['design_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;
  }

  function add_addon($imageFileName, $imageMainName){
    $actionOk = false;
    if(count($_POST)>0) {

      $addon_name = $imageMainName;
      $addon_image = $imageFileName;
      $clothing_id = $_POST["add_new_addon_clothing_id"];
      $addon_price = $_POST["add_new_addon_price"];
      $response = array();
      $db = new DbHandler();
      $result = $db->addAddon($addon_name, $addon_image, $clothing_id, $addon_price);
      if ($result != NULL) {
        $actionOk = true;
        $response["error"] = false;
        $response['addon_id'] = $result;
      } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = "An error occurred. Please try again";
      }
    }
    return $actionOk;
  }

  function add_vendor($imageFileName, $imageMainName){
    $actionOk = false;
    if(count($_POST)>0) {

      $vendor_name = $imageMainName;
      $vendor_url = $_POST["add_new_vendor_url"];
      $vendor_image = $imageFileName;
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

  function redirect_with_message($message_type, $message_text) {
    header('Location:'.$_POST["callback_url"].'?message='.$message_type.'&message_text='.$message_text);
  }

  function call_action_function($action_function, $imageFileName, $imageMainName) {
    switch ($action_function) {
      case 'add_clothing': $actionWentOk = add_clothing($imageFileName, $imageMainName); return $actionWentOk;
      case 'add_fabric': $actionWentOk = add_fabric($imageFileName, $imageMainName); return $actionWentOk;
      case 'add_design': $actionWentOk = add_design($imageFileName, $imageMainName); return $actionWentOk;
      case 'add_addon': $actionWentOk = add_addon($imageFileName, $imageMainName); return $actionWentOk;
      case 'add_vendor': $actionWentOk = add_vendor($imageFileName, $imageMainName); return $actionWentOk;
      default:
        $message = "Sorry, there was an error uploading data. Action does not exist";
        redirect_with_message(0, $message);
        break;
    }
  }

  // print_r($_POST);
  print_r($_FILES);
  if(count($_FILES['filesToUpload']['name'])) {
    $i = 0;
    foreach ($_FILES['filesToUpload']['name'] as $file) {

      $target_dir = "../uploadedimages/" . $_POST["folder"] . "/" . $_POST['image_name'];
      $target_file = $target_dir . $file;
      $uploadOk = 1;
      $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
      $imageFileName = pathinfo($target_file,PATHINFO_FILENAME);
      $imageMainName = pathinfo($file,PATHINFO_FILENAME);
      $message = "";
      // Check if image file is a actual image or fake image
      if(isset($_POST["submit"])) {
          $check = getimagesize($_FILES["filesToUpload"]["tmp_name"][$i]);
          if($check !== false) {
              // echo "File is an image - " . $check["mime"] . ".";
              $uploadOk = 1;
          } else {
              $message = "File is not an image.";
              $uploadOk = 0;
              // redirect_with_message($uploadOk, $message);
          }
      }
      // // Check if file already exists
      // if (file_exists($target_file)) {
      //     $message = "Sorry, file already exists.";
      //     $uploadOk = 0;
      //     redirect_with_message($uploadOk, $message);
      // }
      // Check file size
      if ($_FILES["filesToUpload"]["size"][$i] > 15000000) {
          $message = "Sorry, your file is too large.";
          $uploadOk = 0;
          // redirect_with_message($uploadOk, $message);
      }
      // Allow certain file formats
      if($imageFileType != "jpg") {
          $message = "Sorry, only JPG files are allowed.";
          $uploadOk = 0;
          // redirect_with_message($uploadOk, $message);
      }
      // Check if $uploadOk is set to 0 by an error
      if ($uploadOk == 0) {
          $message = "Error! Please ensure all fields were filled properly";
          // redirect_with_message($uploadOk, $message);
      // if everything is ok, try to upload file
      } else {
        $callToActionWentOk = call_action_function($_POST["action_function"], $imageFileName, $imageMainName);
        if ($callToActionWentOk) {
          if (move_uploaded_file($_FILES['filesToUpload']['tmp_name'][$i], $target_file)) {
              // redirect_with_message($uploadOk, $message);
          } else {
              $message = "Sorry, there was an error uploading your file.";
              // redirect_with_message($uploadOk, $message);
          }                    
        } else {
          $uploadOk = 0;
          $message = "Data Error! Please ensure all fields were filled properly";
        }
      }
      $i++;
    }
    if($uploadOk == 1) {
      $message = "Success!";
    }
    redirect_with_message($uploadOk, $message);
  }
?>