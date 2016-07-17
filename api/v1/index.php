<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require_once 'Mail.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * User Registration
 * url - /register
 * method - POST
 * params - name, mobile, email, password
 */
$app->post('/register', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'mobile', 'email', 'password', 'subscribe'));

            $response = array();

            // reading post params
            $name = $app->request->post('name');
            $mobile = $app->request->post('mobile');
            $email = $app->request->post('email');
            $password = $app->request->post('password');
            $subscribe = $app->request->post('subscribe');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createUser($name, $mobile, $email, $password);

            if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["error_code"] = 1;
                $response["message"] = "Oops! An error occurred while registering";
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["error_code"] = 2;
                $response["message"] = "Sorry, this email already existed";
            } else if (!!$res) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
                $response["api_key"] = $res["api_key"];
                $response["user_id"] = $res["user_id"];
                if($subscribe == "true") {
                    $subscribe_result = $db->addSubscriber($email);
                    $response["subscribed"] = "true";
                } else {
                    $response["subscribed"] = "false";
                }
                $verificationMail = emailVerificationPEAR($email, $res["api_key"], $name);
                $response["verificationMail"] = $verificationMail;
            }
            // echo json response
            echoRespnse(201, $response);
        });

$app->post('/facebookregisterorlogin', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'fbid'));
            // reading post params
            $name = $app->request->post('name');
            $fbid = $app->request->post('fbid');
            $response = array();
            $db = new DbHandler();
            $res = $db->createFBUser($name, $fbid);

            if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["error_code"] = 1;
                $response["message"] = "Oops! An error occurred while registering";
            } else if (!!$res) {
                $response["error"] = false;
                $response["email"] = $fbid;
                $response["name"] = $name;
                $response["user_id"] = $res["user_id"];
                $response["api_key"] = $res["api_key"];
                $response["is_fb_user"] = true;
                $response["user_is_new"] = $res["user_is_new"];
                $response["message"] = "Logged in via Facebook";
            } else if (!$res) {
                $response["error"] = true;
                $response["email"] = $fbid;
                $response["name"] = $name;
                $response["message"] = "Unexpected Error!";
            }
            // echo json response
            echoRespnse(201, $response);
        });


$app->post('/googleregisterorlogin', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'email', 'gid'));
            // reading post params
            $name = $app->request->post('name');
            $email = $app->request->post('email');
            $gid = $app->request->post('gid');
            $response = array();
            $db = new DbHandler();
            $res = $db->createGUser($name, $email, $gid);

            if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["error_code"] = 1;
                $response["message"] = "Oops! An error occurred while registering";
            } else if (!!$res) {
                $response["error"] = false;
                $response["email"] = $email;
                $response["name"] = $name;
                $response["user_id"] = $res["user_id"];
                $response["api_key"] = $res["api_key"];
                $response["is_g_user"] = true;
                $response["user_is_new"] = $res["user_is_new"];
                $response["message"] = "Logged in via Google";
            } else if (!$res) {
                $response["error"] = true;
                $response["email"] = $email;
                $response["name"] = $name;
                $response["message"] = "Unexpected Error!";
            }
            // echo json response
            echoRespnse(201, $response);
        });

/**
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'password'));

            // reading post params
            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();
            // check for correct email and password
            if ($db->checkLogin($email, $password)) {
                // get the user by email
                $user = $db->getUserByEmail($email);

                if ($user != NULL) {
                    $response["error"] = false;
                    $response['user_id'] = $user['user_id'];
                    $response['name'] = $user['name'];
                    $response['mobile'] = $user['mobile'];
                    $response['email'] = $user['email'];
                    $response['apiKey'] = $user['api_key'];
                    $response['active'] = $user['active'];
                    $response["is_fb_user"] = false;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // user credentials are wrong
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoRespnse(200, $response);
        });

$app->post('/getallpromos', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $results = $db->getAllPromos();
                if ($results != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();
                    while ($result = $results->fetch_assoc()) {
                        $tmp = array();
                        $tmp['promo_id'] = $result['promo_id'];
                        $tmp['promo_code'] = $result['promo_code'];
                        $tmp['promo_type'] = $result['promo_type'];
                        $tmp['promo_discount'] = $result['promo_discount'];
                        $tmp['promo_minimum_amount'] = $result['promo_minimum_amount'];
                        $tmp['active'] = $result['active'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getpromobycode', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('promo_code'));
        // reading post params
        $promo_code = $app->request()->post('promo_code');
        $response = array();
        $db = new DbHandler();
        $promo = $db->getPromoByCode($promo_code);
        if ($promo != NULL) {
            $response["error"] = false;
            $response['promo_id'] = $promo["promo_id"];
            $response['promo_code'] = $promo["promo_code"];
            $response['promo_type'] = $promo["promo_type"];
            $response['promo_discount'] = $promo["promo_discount"];
            $response['promo_minimum_amount'] = $promo["promo_minimum_amount"];
        } else {
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
        echoRespnse(200, $response);
    });

$app->post('/getallclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllClothing();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["men"] = array();
                    $response["women"] = array();
                    while ($clothing = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['clothing_id'] = $clothing['clothing_id'];
                        $tmp['clothing_name'] = $clothing['clothing_name'];
                        $tmp['clothing_image'] = $clothing['clothing_image'];
                        $tmp['is_for_women'] = $clothing['is_for_women'];
                        $tmp['price'] = $clothing['price'];
                        if($tmp['is_for_women'] == 0){
                            array_push($response["men"], $tmp);
                        } else {
                            array_push($response["women"], $tmp);
                        }
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalladdons', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllAddons();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["addons"] = array();
                    while ($addon = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['addon_id'] = $addon['addon_id'];
                        $tmp['clothing_id'] = $addon['clothing_id'];
                        $tmp['clothing_name'] = $addon['clothing_name'];
                        $tmp['is_for_women'] = $addon['is_for_women'];
                        $tmp['addon_name'] = $addon['addon_name'];
                        $tmp['addon_image'] = $addon['addon_image'];
                        $tmp['addon_price'] = $addon['addon_price'];
                        array_push($response["addons"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalladdonsandclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllAddons();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["addons"] = array();
                    while ($addon = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['addon_id'] = $addon['addon_id'];
                        $tmp['clothing_id'] = $addon['clothing_id'];
                        $tmp['clothing_name'] = $addon['clothing_name'];
                        $tmp['is_for_women'] = $addon['is_for_women'];
                        $tmp['addon_name'] = $addon['addon_name'];
                        $tmp['addon_image'] = $addon['addon_image'];
                        $tmp['addon_price'] = $addon['addon_price'];
                        array_push($response["addons"], $tmp);
                    }
                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();
                        while ($clothing = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $clothing['clothing_id'];
                            $tmp['clothing_name'] = $clothing['clothing_name'];
                            $tmp['clothing_image'] = $clothing['clothing_image'];
                            $tmp['is_for_women'] = $clothing['is_for_women'];
                            $tmp['price'] = $clothing['price'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }                    
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getaddonsbyclothing', function() use ($app) {
            verifyRequiredParams(array('clothing_id'));
            $clothing_id = $app->request()->post('clothing_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getAddonsByClothing($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["addons"] = array();
                    while ($addon = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['addon_id'] = $addon['addon_id'];
                        $tmp['addon_name'] = $addon['addon_name'];
                        $tmp['addon_image'] = $addon['addon_image'];
                        $tmp['addon_price'] = $addon['addon_price'];
                        array_push($response["addons"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallmeasurementmatrices', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllMeasurementTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["measurement_types"] = array();
                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                        $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                        $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                        $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                        if($tmp['measurement_type_id'] == 1 || $tmp['measurement_type_id'] == 2) {

                        } else {
                            array_push($response["measurement_types"], $tmp);
                        }
                    }

                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }

                        $result = $db->getAllMeasurementMatrices();
                        if ($result != NULL) {
                            $response["error"] = false;
                            $response["measurement_matrices"] = array();

                            while ($resultrow = $result->fetch_assoc()) {
                                $tmp = array();
                                $tmp['clothing_id'] = $resultrow['clothing_id'];
                                $tmp['measurement_types'] = $resultrow['measurement_types'];
                                array_push($response["measurement_matrices"], $tmp);
                            }
                        } else {
                            // unknown error occurred
                            $response['error'] = true;
                            $response['message'] = "An error occurred. Please try again";
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallmeasurementmatricesandmgarments', function() use ($app) {
            verifyRequiredParams(array('user_id'));
            $user_id = $app->request()->post('user_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllMeasurementTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["measurement_types"] = array();
                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                        $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                        $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                        $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                        if($tmp['measurement_type_id'] == 1 || $tmp['measurement_type_id'] == 2) {

                        } else {
                            array_push($response["measurement_types"], $tmp);
                        }
                    }

                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }

                        $result = $db->getAllMeasurementMatrices();
                        if ($result != NULL) {
                            $response["error"] = false;
                            $response["measurement_matrices"] = array();

                            while ($resultrow = $result->fetch_assoc()) {
                                $tmp = array();
                                $tmp['clothing_id'] = $resultrow['clothing_id'];
                                $tmp['measurement_types'] = $resultrow['measurement_types'];
                                array_push($response["measurement_matrices"], $tmp);
                            }
                            $result = $db->getGarmentMeasurementSetsByUser($user_id);
                            if ($result != NULL) {
                                $response["error"] = false;
                                $response["measurement_garments"] = array();

                                while ($resultrow = $result->fetch_assoc()) {
                                    $tmp = array();
                                    $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                                    $tmp['clothing_id'] = $resultrow['clothing_id'];
                                    $tmp['clothing_name'] = $resultrow['clothing_name'];
                                    $tmp['is_for_women'] = $resultrow['is_for_women'];
                                    $tmp['measurements'] = $resultrow['measurements'];
                                    $tmp['measurement_set_name'] = $resultrow['measurement_set_name'];
                                    $tmp['measurement_set_image'] = $resultrow['measurement_set_image'];
                                    $tmp['measurement_set_create_date'] = $resultrow['measurement_set_create_date'];
                                    array_push($response["measurement_garments"], $tmp);
                                }
                            } else {
                                // unknown error occurred
                                $response['error'] = true;
                                $response['message'] = "An error occurred. Please try again";
                            }                            
                        } else {
                            // unknown error occurred
                            $response['error'] = true;
                            $response['message'] = "An error occurred. Please try again";
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getmeasurementmatrixbyclothing', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('clothing_id'));
            // reading post params
            $clothing_id = $app->request()->post('clothing_id');
            $response = array();
            $db = new DbHandler();
            $result = $db->getAllMeasurementTypes();
            if ($result != NULL) {
                $response["error"] = false;
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                    $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                    $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                    $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                    if(!!$resultrow['measurement_type_description']){
                        $tmp['measurement_type_description_present'] = true;
                        $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                    } else {
                        $tmp['measurement_type_description_present'] = false;                            
                    }                    
                    if($tmp['measurement_type_id'] == 1 || $tmp['measurement_type_id'] == 2) {

                    } else {
                        array_push($response["measurement_types"], $tmp);
                    }
                }
                $result = $db->getMeasurementMatrixByClothing($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["measurement_matrix"] = "";
                    while ($resultrow = $result->fetch_assoc()) {
                        $response["measurement_matrix"] = $resultrow['measurement_types'];
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            echoRespnse(200, $response);
        });

$app->post('/getmeasurementmatrixbyclothinganduser', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('clothing_id', 'user_id'));
            // reading post params
            $clothing_id = $app->request()->post('clothing_id');
            $user_id = $app->request()->post('user_id');
            $response = array();
            $db = new DbHandler();
            $result = $db->getAllMeasurementTypes();
            if ($result != NULL) {
                $response["error"] = false;
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                    $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                    $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                    $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                    if(!!$resultrow['measurement_type_description']){
                        $tmp['measurement_type_description_present'] = true;
                        $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                    } else {
                        $tmp['measurement_type_description_present'] = false;                            
                    }                    
                    if($tmp['measurement_type_id'] == 1 || $tmp['measurement_type_id'] == 2) {

                    } else {
                        array_push($response["measurement_types"], $tmp);
                    }
                }
                $result = $db->getMeasurementMatrixByClothing($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["measurement_matrix"] = "";
                    while ($resultrow = $result->fetch_assoc()) {
                        $response["measurement_matrix"] = $resultrow['measurement_types'];
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }


            $result = $db->getGarmentMeasurementSetsByUserAndClothing($user_id, $clothing_id);
            if ($result != NULL) {
                $response["garment_measurement_sets"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                    $tmp['measurement_set_name'] = $resultrow['measurement_set_name'];
                    $tmp['measurement_set_image'] = $resultrow['measurement_set_image'];
                    $tmp['measurement_set_create_date'] = $resultrow['measurement_set_create_date'];
                    $tmp['measurements'] = $resultrow['measurements'];
                    array_push($response["garment_measurement_sets"], $tmp);
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }

            echoRespnse(200, $response);
        });

$app->post('/getallmeasurementtypes', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllMeasurementTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();
                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                        $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                        $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                        $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                        if(!!$resultrow['measurement_type_description']){
                            $tmp['measurement_type_description_present'] = true;
                            $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                        } else {
                            $tmp['measurement_type_description_present'] = false;                            
                        }
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getusermeasurements', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('user_id'));
        // reading post params
        $user_id = $app->request()->post('user_id');
        $response = array();
        $db = new DbHandler();
        $result = $db->getAllMeasurementTypes();
        if ($result != NULL) {
            $response["error"] = false;
            $response["measurement_types"] = array();
            while ($resultrow = $result->fetch_assoc()) {
                $tmp = array();
                $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                if(!!$resultrow['measurement_type_description']){
                    $tmp['measurement_type_description_present'] = true;
                    $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                } else {
                    $tmp['measurement_type_description_present'] = false;                            
                }
                array_push($response["measurement_types"], $tmp);
            }

            $result = $db->getTSStandardByUser($user_id);
            if ($result != NULL) {
                while ($resultrow = $result->fetch_assoc()) {
                    $response["ts_standard"] = $resultrow['ts_standard'];
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }

            $result = $db->getCustomTSMeasurementSetsByUser($user_id);
            if ($result != NULL) {
                $response["measurement_sets"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                    $tmp['measurement_set_name'] = $resultrow['measurement_set_name'];
                    $tmp['measurement_set_create_date'] = $resultrow['measurement_set_create_date'];
                    $tmp['measurements'] = $resultrow['measurements'];
                    array_push($response["measurement_sets"], $tmp);
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
        echoRespnse(200, $response);
    });

$app->post('/getusermeasurementsbyclothing', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('user_id', 'clothing_id'));
        // reading post params
        $user_id = $app->request()->post('user_id');
        $clothing_id = $app->request()->post('clothing_id');
        $response = array();
        $db = new DbHandler();
        $result = $db->getAllMeasurementTypes();
        if ($result != NULL) {
            $response["error"] = false;
            $response["measurement_types"] = array();
            while ($resultrow = $result->fetch_assoc()) {
                $tmp = array();
                $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                if(!!$resultrow['measurement_type_description']){
                    $tmp['measurement_type_description_present'] = true;
                    $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                } else {
                    $tmp['measurement_type_description_present'] = false;                            
                }
                array_push($response["measurement_types"], $tmp);
            }

            $result = $db->getTSStandardByUser($user_id);
            if ($result != NULL) {
                while ($resultrow = $result->fetch_assoc()) {
                    $response["ts_standard"] = $resultrow['ts_standard'];
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }

            $response["measurement_sets"] = array();
            $result = $db->getCustomTSMeasurementSetsByUser($user_id);
            if ($result != NULL) {
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_set_type'] = 0;
                    $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                    $tmp['measurement_set_name'] = $resultrow['measurement_set_name'];
                    $tmp['measurement_set_create_date'] = $resultrow['measurement_set_create_date'];
                    $tmp['measurements'] = $resultrow['measurements'];
                    array_push($response["measurement_sets"], $tmp);
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            
            $result = $db->getCustomMeasurementSetsByUserAndClothing($user_id, $clothing_id);
            if ($result != NULL) {
                while ($resultrow = $result->fetch_assoc()) {
                    $tmp = array();
                    $tmp['measurement_set_type'] = 2;
                    $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                    $tmp['measurement_set_name'] = $resultrow['measurement_set_name'];
                    $tmp['measurement_set_create_date'] = $resultrow['measurement_set_create_date'];
                    $tmp['measurements'] = $resultrow['measurements'];
                    array_push($response["measurement_sets"], $tmp);
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
        echoRespnse(200, $response);
    });

$app->post('/getalluserprofiles', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllUserProfiles();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['name'] = $resultrow['name'];
                        $tmp['mobile'] = $resultrow['mobile'];
                        $tmp['email'] = $resultrow['email'];
                        $tmp['user_create_date'] = $resultrow['user_create_date'];
                        $tmp['active'] = $resultrow['active'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/gettsmeasurementtypes', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllMeasurementTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();
                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                        $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                        $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                        $tmp['measurement_type_unit'] = $resultrow['measurement_type_unit'];
                        if(!!$resultrow['measurement_type_description']){
                            $tmp['measurement_type_description_present'] = true;
                            $tmp['measurement_type_description'] = $resultrow['measurement_type_description'];                           
                            array_push($response["results"], $tmp);
                        } else {
                            
                        }
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/gettsstandardbyemail', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('email'));
        // reading post params
        $email = $app->request()->post('email');
        $response = array();
        $db = new DbHandler();
        $user = $db->getUserByEmail($email);
        if ($user != NULL) {
            $response["error"] = false;
            $response['user_id'] = $user['user_id'];
            $response['name'] = $user['name'];
            $response['mobile'] = $user['mobile'];
            $response['email'] = $user['email'];
            $result = $db->getTSStandardByUser($user['user_id']);
            while ($resultrow = $result->fetch_assoc()) {
                $response["ts_standard"] = $resultrow['ts_standard'];
            }
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
        echoRespnse(200, $response);
    });

$app->post('/getmeasurementgarmentsuserbyemail', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('email'));
        // reading post params
        $email = $app->request()->post('email');
        $response = array();
        $db = new DbHandler();
        $user = $db->getUserByEmail($email);
        if ($user != NULL) {
            $response["error"] = false;
            $response['user_id'] = $user['user_id'];
            $response['name'] = $user['name'];
            $response['email'] = $user['email'];
        } else {
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
        echoRespnse(200, $response);
    });

$app->post('/getallstatustypes', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllStatusTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['status_text_id'] = $resultrow['status_text_id'];
                        $tmp['status_text'] = $resultrow['status_text'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallbanners', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllBanners();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['banner_id'] = $resultrow['banner_id'];
                        $tmp['banner_image'] = $resultrow['banner_image'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallsubscribers', function() use ($app) {
            verifyRequiredParams(array('authorized_access_identifier'));
            $authorized_access_identifier = $app->request()->post('authorized_access_identifier');
            $response = array();
            $db = new DbHandler();
                if(validateAuthorizedAccessIdentifier($authorized_access_identifier)) {
                    $result = $db->getAllSubscribers();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["results"] = array();
                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['email'] = $resultrow['subscriber_email'];
                            array_push($response["results"], $tmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Forbidden Access! You are not authorized to make this query";
                    $response["results"] = array();
                }
            echoRespnse(200, $response);
        });

$app->post('/getalluseremails', function() use ($app) {
            verifyRequiredParams(array('authorized_access_identifier'));
            $authorized_access_identifier = $app->request()->post('authorized_access_identifier');
            $response = array();
            $db = new DbHandler();
                if(validateAuthorizedAccessIdentifier($authorized_access_identifier)) {
                    $result = $db->getAllUserEmails();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["results"] = array();
                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['email'] = $resultrow['email'];
                            array_push($response["results"], $tmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = "Forbidden Access! You are not authorized to make this query";
                    $response["results"] = array();
                }
            echoRespnse(200, $response);
        });

$app->post('/getallstates', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllStates();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['state_id'] = $resultrow['state_id'];
                        $tmp['state_name'] = $resultrow['state_name'];
                        $tmp['country_name'] = $resultrow['country_name'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallstatesandcountries', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllStates();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["states"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['state_id'] = $resultrow['state_id'];
                        $tmp['state_name'] = $resultrow['state_name'];
                        $tmp['country_name'] = $resultrow['country_name'];
                        array_push($response["states"], $tmp);
                    }

                    $result = $db->getAllCountries();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["countries"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['country_id'] = $resultrow['country_id'];
                            $tmp['country_name'] = $resultrow['country_name'];
                            array_push($response["countries"], $tmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallcountries', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllCountries();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['country_id'] = $resultrow['country_id'];
                        $tmp['country_name'] = $resultrow['country_name'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallvendors', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllVendors();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['vendor_id'] = $resultrow['vendor_id'];
                        $tmp['vendor_name'] = $resultrow['vendor_name'];
                        $tmp['vendor_url'] = $resultrow['vendor_url'];
                        $tmp['vendor_image'] = $resultrow['vendor_image'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallfabrics', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllFabrics();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['fabric_id'] = $resultrow['fabric_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        $tmp['fabric_name'] = $resultrow['fabric_name'];
                        $tmp['fabric_image'] = $resultrow['fabric_image'];
                        $tmp['fabric_price'] = $resultrow['fabric_price'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallfabricsandclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllFabrics();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["fabrics"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['fabric_id'] = $resultrow['fabric_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        $tmp['fabric_name'] = $resultrow['fabric_name'];
                        $tmp['fabric_image'] = $resultrow['fabric_image'];
                        $tmp['fabric_price'] = $resultrow['fabric_price'];
                        array_push($response["fabrics"], $tmp);
                    }

                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getvendorsandfabricsbyclothing', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('clothing_id'));
            // reading post params
            $clothing_id = $app->request()->post('clothing_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getFabricsByClothingId($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["fabrics"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['fabric_id'] = $resultrow['fabric_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['fabric_name'] = $resultrow['fabric_name'];
                        $tmp['fabric_image'] = $resultrow['fabric_image'];
                        $tmp['fabric_price'] = $resultrow['fabric_price'];
                        array_push($response["fabrics"], $tmp);
                    }

                    $result = $db->getAllVendors();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["vendors"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['vendor_id'] = $resultrow['vendor_id'];
                            $tmp['vendor_name'] = $resultrow['vendor_name'];
                            $tmp['vendor_url'] = $resultrow['vendor_url'];
                            $tmp['vendor_image'] = $resultrow['vendor_image'];
                            array_push($response["vendors"], $tmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalldesigngroups', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllDesignGroups();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['design_group_id'] = $resultrow['design_group_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        $tmp['design_group_name'] = $resultrow['design_group_name'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalldesigngroupsandclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllDesignGroups();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["design_groups"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['design_group_id'] = $resultrow['design_group_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        $tmp['design_group_name'] = $resultrow['design_group_name'];
                        array_push($response["design_groups"], $tmp);
                    }

                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallalterationtypesandclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllAlterationTypes();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["alteration_types"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['alteration_type_id'] = $resultrow['alteration_type_id'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        $tmp['alteration_type_title'] = $resultrow['alteration_type_title'];
                        $tmp['alteration_type_price'] = $resultrow['alteration_type_price'];
                        array_push($response["alteration_types"], $tmp);
                    }

                    $result = $db->getAllClothing();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["clothing_men"] = array();
                        $response["clothing_women"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            if($tmp['is_for_women'] == 0){
                                array_push($response["clothing_men"], $tmp);
                            } else {
                                array_push($response["clothing_women"], $tmp);
                            }
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalterationtypesbyclothing', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('clothing_id'));
            // reading post params
            $clothing_id = $app->request()->post('clothing_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getAlterationTypesByClothingId($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["alteration_types"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['alteration_type_id'] = $resultrow['alteration_type_id'];
                        $tmp['alteration_type_title'] = $resultrow['alteration_type_title'];
                        $tmp['alteration_type_price'] = $resultrow['alteration_type_price'];
                        array_push($response["alteration_types"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalldesigns', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllDesigns();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['design_id'] = $resultrow['design_id'];
                        $tmp['design_name'] = $resultrow['design_name'];
                        $tmp['design_image'] = $resultrow['design_image'];
                        $tmp['design_group_name'] = $resultrow['design_group_name'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        array_push($response["results"], $tmp);
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalldesignsanddesigngroupsbyclothing', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('clothing_id'));
            // reading post params
            $clothing_id = $app->request()->post('clothing_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getDesignGroupsByClothingId($clothing_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["design_groups"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['design_group_id'] = $resultrow['design_group_id'];
                        $tmp['design_group_name'] = $resultrow['design_group_name'];
                        array_push($response["design_groups"], $tmp);
                    }
                    $result = $db->getDesignsByClothingId($clothing_id);
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["designs"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['design_id'] = $resultrow['design_id'];
                            $tmp['design_name'] = $resultrow['design_name'];
                            $tmp['design_image'] = $resultrow['design_image'];
                            $tmp['design_group_id'] = $resultrow['design_group_id'];
                            array_push($response["designs"], $tmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getalldesignsanddesigngroupsandclothing', function() use ($app) {
            $response = array();
            $db = new DbHandler();
                $result = $db->getAllDesigns();
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["designs"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['design_id'] = $resultrow['design_id'];
                        $tmp['design_name'] = $resultrow['design_name'];
                        $tmp['design_image'] = $resultrow['design_image'];
                        $tmp['design_group_name'] = $resultrow['design_group_name'];
                        $tmp['clothing_id'] = $resultrow['clothing_id'];
                        $tmp['clothing_name'] = $resultrow['clothing_name'];
                        $tmp['is_for_women'] = $resultrow['is_for_women'];
                        array_push($response["designs"], $tmp);
                    }
                    $result = $db->getAllDesignGroups();
                    if ($result != NULL) {
                        $response["error"] = false;
                        $response["design_groups"] = array();

                        while ($resultrow = $result->fetch_assoc()) {
                            $tmp = array();
                            $tmp['design_group_id'] = $resultrow['design_group_id'];
                            $tmp['clothing_id'] = $resultrow['clothing_id'];
                            $tmp['clothing_name'] = $resultrow['clothing_name'];
                            $tmp['is_for_women'] = $resultrow['is_for_women'];
                            $tmp['design_group_name'] = $resultrow['design_group_name'];
                            array_push($response["design_groups"], $tmp);
                        }

                        $result = $db->getAllClothing();
                        if ($result != NULL) {
                            $response["error"] = false;
                            $response["clothing_men"] = array();
                            $response["clothing_women"] = array();

                            while ($resultrow = $result->fetch_assoc()) {
                                $tmp = array();
                                $tmp['clothing_id'] = $resultrow['clothing_id'];
                                $tmp['clothing_name'] = $resultrow['clothing_name'];
                                $tmp['is_for_women'] = $resultrow['is_for_women'];
                                if($tmp['is_for_women'] == 0){
                                    array_push($response["clothing_men"], $tmp);
                                } else {
                                    array_push($response["clothing_women"], $tmp);
                                }
                            }
                        } else {
                            // unknown error occurred
                            $response['error'] = true;
                            $response['message'] = "An error occurred. Please try again";
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getuseraddresses', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id'));
            // reading post params
            $user_id = $app->request()->post('user_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getAddressesByUserId($user_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['address_id'] = $resultrow['address_id'];
                        $tmp['address_name'] = $resultrow['address_name'];
                        $tmp['address_person_name'] = $resultrow['address_person_name'];
                        $tmp['address_line1'] = $resultrow['address_line1'];
                        $tmp['address_line2'] = $resultrow['address_line2'];
                        $tmp['address_city'] = $resultrow['address_city'];
                        $tmp['state_name'] = $resultrow['state_name'];
                        $tmp['address_pincode'] = $resultrow['address_pincode'];
                        $tmp['country_name'] = $resultrow['country_name'];
                        $tmp['address_mobile'] = $resultrow['address_mobile'];
                        array_push($response["results"], $tmp);
                    }
                    $result = $db->getAllStates();
                    if ($result != NULL) {
                          $response["states"] = array();
                      while ($resultrow = $result->fetch_assoc()) {
                          $tmp = array();
                          $tmp['state_id'] = $resultrow['state_id'];
                          $tmp['state_name'] = $resultrow['state_name'];
                          array_push($response["states"], $tmp);
                      }
                      $result = $db->getAllCountries();
                      if ($result != NULL) {
                          $response["countries"] = array();

                          while ($resultrow = $result->fetch_assoc()) {
                              $tmp = array();
                              $tmp['country_id'] = $resultrow['country_id'];
                              $tmp['country_name'] = $resultrow['country_name'];
                              array_push($response["countries"], $tmp);
                          }
                      } else {
                          // unknown error occurred
                          $response['error'] = true;
                          $response['message'] = "An error occurred. Please try again";
                      }
                    } else {
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getstatusbyorder', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('order_id'));
            // reading post params
            $order_id = $app->request()->post('order_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getStatusByOrderId($order_id);
                if ($result != NULL) {
                  $response["error"] = false;
                  $response["order_status"] = array();

                  while ($resultrow = $result->fetch_assoc()) {
                      $tmp = array();
                      $tmp['status_id'] = $resultrow['status_id'];
                      $tmp['status_text_id'] = $resultrow['status_text_id'];
                      $tmp['status_text'] = $resultrow['status_text'];
                      $tmp['status_create_date'] = $resultrow['status_create_date'];
                      array_push($response["order_status"], $tmp);
                  }
                  $result = $db->getAllStatusTypes();
                  if ($result != NULL) {
                    $response["status_types"] = array();
                    while ($resultrow = $result->fetch_assoc()) {
                      $tmp = array();
                      $tmp['status_text_id'] = $resultrow['status_text_id'];
                      $tmp['status_text'] = $resultrow['status_text'];
                      array_push($response["status_types"], $tmp);
                    }
                  }
                } else {
                  // unknown error occurred
                  $response['error'] = true;
                  $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/getallorders', function() use ($app) {
          $response = array();
          $db = new DbHandler();
          $result = $db->getAllOrders();
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['user_id'] = $resultrow['user_id'];
                  $tmp['name'] = $resultrow['name'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['fabric_method'] = $resultrow['fabric_method'];
                  $tmp['fabric_id'] = $resultrow['fabric_id'];
                  $tmp['designs'] = $resultrow['designs'];
                  $tmp['measurement_method'] = $resultrow['measurement_method'];
                  $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status'] = $resultrow['status'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  if($tmp['measurement_method'] == "1" || $tmp['measurement_method'] == "3" || $tmp['measurement_method'] == "6") {
                    $measurements = $db->getMeasurementsByMeasurementSetId($tmp['measurement_set_id']);
                    if ($measurements != NULL) {
                      $tmp['measurements'] = $measurements;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getordersandextrasfors', function() use ($app) {
        verifyRequiredParams(array('status'));
        // reading post params
        $status_param = $app->request()->post('status');
        $response = array();
        $db = new DbHandler();
        if ($status_param == "0") {
            $result = $db->getAllSOrders();
        } else {
            $status_params = json_decode('[' . $status_param . ']', true);
            $result = $db->getAllSOrdersByStatus($status_params);
        }
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['user_id'] = $resultrow['user_id'];
                  $tmp['name'] = $resultrow['name'];
                  $tmp['mobile'] = $resultrow['mobile'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['fabric_method'] = $resultrow['fabric_method'];
                  $tmp['fabric_id'] = $resultrow['fabric_id'];
                  $tmp['designs'] = $resultrow['designs'];
                  $tmp['addons'] = $resultrow['addons'];
                  $tmp['measurement_method'] = $resultrow['measurement_method'];
                  $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status'] = $resultrow['status'];
                  $tmp['remarks'] = $resultrow['remarks'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  if($tmp['measurement_method'] == "1" || $tmp['measurement_method'] == "3" || $tmp['measurement_method'] == "6") {
                    $measurements = $db->getMeasurementsByMeasurementSetId($tmp['measurement_set_id']);
                    if ($measurements != NULL) {
                      $tmp['measurements'] = $measurements;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
              $result = $db->getAllMeasurementTypes();
              if ($result != NULL) {
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                  $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                  $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                  array_push($response["measurement_types"], $tmp);
                }
              }
              $result = $db->getAllDesignGroups();
              if ($result != NULL) {
                $response["design_groups"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['design_group_id'] = $resultrow['design_group_id'];
                  $tmp['design_group_name'] = $resultrow['design_group_name'];
                  array_push($response["design_groups"], $tmp);
                }
              }
              $result = $db->getAllStatusTypes();
              if ($result != NULL) {
                $response["status_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['status_text_id'] = $resultrow['status_text_id'];
                  $tmp['status_text'] = $resultrow['status_text'];
                  array_push($response["status_types"], $tmp);
                }
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getordersandextrasfora', function() use ($app) {
        verifyRequiredParams(array('status'));
        // reading post params
        $status_param = $app->request()->post('status');
        $response = array();
        $db = new DbHandler();
        if ($status_param == "0") {
            $result = $db->getAllAOrders();
        } else {
            $status_params = json_decode('[' . $status_param . ']', true);
            $result = $db->getAllAOrdersByStatus($status_params);
        }
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['user_id'] = $resultrow['user_id'];
                  $tmp['name'] = $resultrow['name'];
                  $tmp['mobile'] = $resultrow['mobile'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['fabric_method'] = $resultrow['fabric_method'];
                  $tmp['alteration_method'] = $resultrow['fabric_id'];
                  $tmp['alteration_method_string'] = $resultrow['alteration_type_title'];
                  $tmp['measurement_method'] = $resultrow['measurement_method'];
                  $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status'] = $resultrow['status'];
                  $tmp['remarks'] = $resultrow['remarks'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  if($tmp['measurement_method'] == "1" || $tmp['measurement_method'] == "3" || $tmp['measurement_method'] == "6") {
                    $measurements = $db->getMeasurementsByMeasurementSetId($tmp['measurement_set_id']);
                    if ($measurements != NULL) {
                      $tmp['measurements'] = $measurements;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
              $result = $db->getAllMeasurementTypes();
              if ($result != NULL) {
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                  $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                  $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                  array_push($response["measurement_types"], $tmp);
                }
              }
              $result = $db->getAllStatusTypes();
              if ($result != NULL) {
                $response["status_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['status_text_id'] = $resultrow['status_text_id'];
                  $tmp['status_text'] = $resultrow['status_text'];
                  array_push($response["status_types"], $tmp);
                }
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getordersandextrasbyuser', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('user_id'));
          // reading post params
          $user_id = $app->request()->post('user_id');
          $response = array();
          $db = new DbHandler();
          $result = $db->getOrdersByUser($user_id);
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_code'] = $resultrow['order_code'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['clothing_image'] = $resultrow['clothing_image'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status_text'] = $resultrow['status_text'];
                  $tmp['remarks'] = $resultrow['remarks'];
                  $tmp['discount'] = $resultrow['discount'];
                  $tmp['final_total'] = $resultrow['final_total'];
                  $tmp['order_date'] = $resultrow['order_date'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getorderandextrasbyuserandordercode', function() use ($app) {
        // check for required params
        verifyRequiredParams(array('user_id', 'order_code'));
        // reading post params
        $user_id = $app->request()->post('user_id');
        $order_code = $app->request()->post('order_code');
        $response = array();
        $db = new DbHandler();
        $result = $db->getOrderByUserAndOrderCode($user_id, $order_code);
        if ($result != NULL) {
            $response["error"] = false;
            $response["results"] = array();

            while ($resultrow = $result->fetch_assoc()) {
                $tmp = array();
                $tmp['order_id'] = $resultrow['order_id'];
                $tmp['order_code'] = $resultrow['order_code'];
                $tmp['order_type'] = $resultrow['order_type'];
                $tmp['clothing_id'] = $resultrow['clothing_id'];
                $tmp['clothing_name'] = $resultrow['clothing_name'];
                $tmp['clothing_image'] = $resultrow['clothing_image'];
                $tmp['is_for_women'] = $resultrow['is_for_women'];
                $tmp['clothing_price'] = $resultrow['price'];
                $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                $tmp['pickup_required'] = $resultrow['pickup_required'];
                $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                $tmp['pickup_date'] = $resultrow['pickup_date'];
                $tmp['total_price'] = $resultrow['total_price'];
                $tmp['status'] = $resultrow['status'];
                $tmp['status_text'] = $resultrow['status_text'];
                $tmp['remarks'] = $resultrow['remarks'];
                $tmp['promo_id'] = $resultrow['promo_id'];
                $tmp['discount'] = $resultrow['discount'];
                $tmp['final_total'] = $resultrow['final_total'];
                $tmp['order_date'] = $resultrow['order_date'];

                $tmp['fabric_method'] = $resultrow['fabric_method'];
                $tmp['fabric_id'] = $resultrow['fabric_id'];
                $tmp['designs'] = $resultrow['designs'];
                $tmp['addons'] = $resultrow['addons'];
                $tmp['measurement_method'] = $resultrow['measurement_method'];
                $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];

                $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                if ($delivery_address != NULL) {
                  $tmp['delivery_address'] = $delivery_address;
                }
                if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                  $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                  if($pickup_address != NULL) {
                    $tmp['pickup_address'] = $pickup_address;
                  }
                }

                $response["results"] = $tmp;
                $response["fabric_exists"] = false;                        

                if ($tmp['order_type'] == "1") {
                    $designsObject = json_decode($tmp['designs'], true);
                    $design_ids = array();
                    foreach ($designsObject as $dgId=>$dgDesignId) {
                        array_push($design_ids, $dgDesignId);
                    }
                    if (count($design_ids) > 0) {
                        $designs = $db->getDesignsByDesignIds($design_ids);
                    } else {
                        $designs = NULL;
                    }
                    if ($designs != NULL) {
                        $response["error"] = false;
                        $response["designs"] = array();
                        while ($dresultrow = $designs->fetch_assoc()) {
                            $dtmp = array();
                            $dtmp['design_id'] = $dresultrow['design_id'];
                            $dtmp['design_group_id'] = $dresultrow['design_group_id'];
                            $dtmp['design_group_name'] = $dresultrow['design_group_name'];
                            $dtmp['design_name'] = $dresultrow['design_name'];
                            $dtmp['design_image'] = $dresultrow['design_image'];

                            array_push($response["designs"], $dtmp);
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }

                    $addonsObject = json_decode($tmp['addons'], true);
                    $addons_count = $addonsObject["num"];
                    $response["addons_count"] = $addons_count;

                    if ($addons_count > 0) {
                        $response["addons_exist"] = true;
                        $addon_ids = array();
                        foreach ($addonsObject as $adIndex=>$adId) {
                            if ($adIndex !== "num") {
                                array_push($addon_ids, $adId);                                
                            }
                        }
                        $addons = $db->getAddonsByAddonIds($addon_ids);
                        if ($addons != NULL) {
                            $response["error"] = false;
                            $response["addons"] = array();
                            while ($aresultrow = $addons->fetch_assoc()) {
                                $atmp = array();
                                $atmp['addon_id'] = $aresultrow['addon_id'];
                                $atmp['addon_name'] = $aresultrow['addon_name'];
                                $atmp['addon_image'] = $aresultrow['addon_image'];
                                $atmp['addon_price'] = $aresultrow['addon_price'];

                                array_push($response["addons"], $atmp);
                            }
                        } else {
                            // unknown error occurred
                            $response['error'] = true;
                            $response['message'] = "An error occurred. Please try again";
                        }
                    } else {                        
                        $response["addons_exist"] = false;
                    }

                    $fabric_id = $tmp['fabric_id'];

                    if ($fabric_id != 0) {
                        $response["fabric_exists"] = true;
                        $fabricDetails = $db->getFabricByFabricId($fabric_id);
                        if ($fabricDetails != NULL) {
                            $response["error"] = false;
                            $response["fabric"] = NULL;
                            while ($fresultrow = $fabricDetails->fetch_assoc()) {
                                $ftmp = array();
                                $ftmp['fabric_name'] = $fresultrow['fabric_name'];
                                $ftmp['fabric_image'] = $fresultrow['fabric_image'];
                                $ftmp['fabric_price'] = $fresultrow['fabric_price'];
                                $response["fabric"] = $ftmp;
                            }
                        } else {
                            // unknown error occurred
                            $response['error'] = true;
                            $response['message'] = "An error occurred. Please try again";
                        }
                    }
                } else {
                    $alterationDetails = $db->getAlterationTypeByAlterationId($tmp['fabric_id']);
                    if ($alterationDetails != NULL) {
                        $response["error"] = false;
                        $response["design_groups"] = array();
                        while ($alresultrow = $alterationDetails->fetch_assoc()) {
                            $altmp = array();
                            $altmp['alteration_type_title'] = $alresultrow['alteration_type_title'];
                            $altmp['alteration_type_price'] = $alresultrow['alteration_type_price'];
                            $response["alteration"] = $altmp;
                        }
                    } else {
                        // unknown error occurred
                        $response['error'] = true;
                        $response['message'] = "An error occurred. Please try again";
                    }
                }
                // $designGroups = $db->getDesignGroupsByClothingId($tmp['clothing_id']);
                // if ($designGroups != NULL) {
                //     $response["error"] = false;
                //     $response["design_groups"] = array();
                //     while ($dgresultrow = $designGroups->fetch_assoc()) {
                //         $dgtmp = array();
                //         $dgtmp['id'] = $dgresultrow['design_group_id'];
                //         $dgtmp['name'] = $dgresultrow['design_group_name'];
                //         $dgtmp['design'] = $designObject[$dgtmp['id']];

                //         array_push($response["design_groups"], $dgtmp);
                //     }
                // } else {
                //     // unknown error occurred
                //     $response['error'] = true;
                //     $response['message'] = "An error occurred. Please try again";
                // }


              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getordersandextrasforsbyuser', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('user_id'));
          // reading post params
          $user_id = $app->request()->post('user_id');
          $response = array();
          $db = new DbHandler();
          $result = $db->getSOrdersByUser($user_id);
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['user_id'] = $resultrow['user_id'];
                  $tmp['name'] = $resultrow['name'];
                  $tmp['mobile'] = $resultrow['mobile'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['fabric_method'] = $resultrow['fabric_method'];
                  $tmp['fabric_id'] = $resultrow['fabric_id'];
                  $tmp['designs'] = $resultrow['designs'];
                  $tmp['addons'] = $resultrow['addons'];
                  $tmp['measurement_method'] = $resultrow['measurement_method'];
                  $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status'] = $resultrow['status'];
                  $tmp['remarks'] = $resultrow['remarks'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  if($tmp['measurement_method'] == "1" || $tmp['measurement_method'] == "3" || $tmp['measurement_method'] == "6") {
                    $measurements = $db->getMeasurementsByMeasurementSetId($tmp['measurement_set_id']);
                    if ($measurements != NULL) {
                      $tmp['measurements'] = $measurements;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
              $result = $db->getAllMeasurementTypes();
              if ($result != NULL) {
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                  $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                  $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                  array_push($response["measurement_types"], $tmp);
                }
              }
              $result = $db->getAllDesignGroups();
              if ($result != NULL) {
                $response["design_groups"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['design_group_id'] = $resultrow['design_group_id'];
                  $tmp['design_group_name'] = $resultrow['design_group_name'];
                  array_push($response["design_groups"], $tmp);
                }
              }
              $result = $db->getAllStatusTypes();
              if ($result != NULL) {
                $response["status_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['status_text_id'] = $resultrow['status_text_id'];
                  $tmp['status_text'] = $resultrow['status_text'];
                  array_push($response["status_types"], $tmp);
                }
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getordersandextrasforabyuser', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('user_id'));
          // reading post params
          $user_id = $app->request()->post('user_id');
          $response = array();
          $db = new DbHandler();
          $result = $db->getAOrdersByUser($user_id);
          if ($result != NULL) {
              $response["error"] = false;
              $response["results"] = array();

              while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['order_id'] = $resultrow['order_id'];
                  $tmp['order_type'] = $resultrow['order_type'];
                  $tmp['user_id'] = $resultrow['user_id'];
                  $tmp['name'] = $resultrow['name'];
                  $tmp['mobile'] = $resultrow['mobile'];
                  $tmp['clothing_id'] = $resultrow['clothing_id'];
                  $tmp['clothing_name'] = $resultrow['clothing_name'];
                  $tmp['is_for_women'] = $resultrow['is_for_women'];
                  $tmp['fabric_method'] = $resultrow['fabric_method'];
                  $tmp['alteration_method'] = $resultrow['fabric_id'];
                  $tmp['alteration_method_string'] = $resultrow['alteration_type_title'];
                  $tmp['measurement_method'] = $resultrow['measurement_method'];
                  $tmp['measurement_set_id'] = $resultrow['measurement_set_id'];
                  $tmp['delivery_address_id'] = $resultrow['delivery_address_id'];
                  $tmp['pickup_required'] = $resultrow['pickup_required'];
                  $tmp['pickup_address_id'] = $resultrow['pickup_address_id'];
                  $tmp['pickup_date'] = $resultrow['pickup_date'];
                  $tmp['total_price'] = $resultrow['total_price'];
                  $tmp['status'] = $resultrow['status'];
                  $tmp['remarks'] = $resultrow['remarks'];

                  $delivery_address = $db->getAddressByAddressId($tmp['delivery_address_id']);
                  if ($delivery_address != NULL) {
                    $tmp['delivery_address'] = $delivery_address;
                  }
                  if($tmp['pickup_required'] == "1" || $tmp['pickup_required'] == "2" || $tmp['pickup_required'] == "4" || $tmp['pickup_required'] == "5" || $tmp['pickup_required'] == "6") {
                    $pickup_address = $db->getAddressByAddressId($tmp['pickup_address_id']);
                    if($pickup_address != NULL) {
                      $tmp['pickup_address'] = $pickup_address;
                    }
                  }

                  if($tmp['measurement_method'] == "1" || $tmp['measurement_method'] == "3" || $tmp['measurement_method'] == "6") {
                    $measurements = $db->getMeasurementsByMeasurementSetId($tmp['measurement_set_id']);
                    if ($measurements != NULL) {
                      $tmp['measurements'] = $measurements;
                    }
                  }

                  array_push($response["results"], $tmp);
              }
              $result = $db->getAllMeasurementTypes();
              if ($result != NULL) {
                $response["measurement_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['measurement_type_id'] = $resultrow['measurement_type_id'];
                  $tmp['measurement_type_name'] = $resultrow['measurement_type_name'];
                  $tmp['measurement_type_max'] = $resultrow['measurement_type_max'];
                  array_push($response["measurement_types"], $tmp);
                }
              }
              $result = $db->getAllStatusTypes();
              if ($result != NULL) {
                $response["status_types"] = array();
                while ($resultrow = $result->fetch_assoc()) {
                  $tmp = array();
                  $tmp['status_text_id'] = $resultrow['status_text_id'];
                  $tmp['status_text'] = $resultrow['status_text'];
                  array_push($response["status_types"], $tmp);
                }
              }
          } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
          }
      echoRespnse(200, $response);
  });

$app->post('/getuserprofile', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id'));
            // reading post params
            $user_id = $app->request()->post('user_id');
            $response = array();
            $db = new DbHandler();
                $result = $db->getUserProfileByUserId($user_id);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response["results"] = array();

                    while ($resultrow = $result->fetch_assoc()) {
                        $tmp = array();
                        $tmp['name'] = $resultrow['name'];
                        $tmp['mobile'] = $resultrow['mobile'];
                        $tmp['email'] = $resultrow['email'];
                        $tmp['user_create_date'] = $resultrow['user_create_date'];
                        $isSubscribed = $db->isUserSubscribed($tmp['email']);
                        $tmp['is_subscribed'] = $isSubscribed;
                        array_push($response["results"], $tmp);
                    }


                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/addsubscriber', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email'));
            // reading post params
            $subscriber_email = $app->request()->post('email');
            $response = array();
            $db = new DbHandler();
                $result = $db->addSubscriber($subscriber_email);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response['subscriber_id'] = $result;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/voidsubscriber', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email'));
            // reading post params
            $subscriber_email = $app->request()->post('email');
            $response = array();
            $db = new DbHandler();
            $result = $db->voidSubscriber($subscriber_email);
            if ($result != NULL) {
                $response["error"] = false;
                $response['subscriber_voided'] = $result;
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            echoRespnse(200, $response);
        });

$app->post('/addusermeasurementset', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id', 'clothing_id', 'measurements', 'measurement_set_name'));
            // reading post params
            $user_id = $app->request()->post('user_id');
            $clothing_id = $app->request()->post('clothing_id');
            $measurements = $app->request()->post('measurements');
            $measurement_set_name = $app->request()->post('measurement_set_name');
            $response = array();
            $db = new DbHandler();
                $result = $db->addMeasurementSet($user_id, $clothing_id, $measurements, $measurement_set_name);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response['measurement_set_type_id'] = $result;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/addusermeasurementsetbyclothing', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id', 'clothing_id', 'measurements', 'measurement_set_name'));
            // reading post params
            $user_id = $app->request()->post('user_id');
            $clothing_id = $app->request()->post('clothing_id');
            $measurements = $app->request()->post('measurements');
            $measurement_set_name = $app->request()->post('measurement_set_name');
            $response = array();
            $db = new DbHandler();
                $result = $db->addMeasurementSetByClothing($user_id, $clothing_id, $measurements, $measurement_set_name);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response['measurement_set_type_id'] = $result;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/addaddress', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id', 'address_name', 'address_person_name', 'address_line1', 'address_line2', 'address_city', 'address_state_id', 'address_pincode', 'address_country_id', 'address_mobile'));
            // reading post params
            $user_id = $app->request()->post('user_id');
            $address_name = $app->request()->post('address_name');
            $address_person_name = $app->request()->post('address_person_name');
            $address_line1 = $app->request()->post('address_line1');
            $address_line2 = $app->request()->post('address_line2');
            $address_city = $app->request()->post('address_city');
            $address_state_id = $app->request()->post('address_state_id');
            $address_pincode = $app->request()->post('address_pincode');
            $address_country_id = $app->request()->post('address_country_id');
            $address_mobile = $app->request()->post('address_mobile');
            $response = array();
            $db = new DbHandler();
                $result = $db->addAddress($user_id, $address_name, $address_person_name, $address_line1, $address_line2, $address_city, $address_state_id, $address_pincode, $address_country_id, $address_mobile);
                if ($result != NULL) {
                    $response["error"] = false;
                    $response['address_id'] = $result;
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            echoRespnse(200, $response);
        });

$app->post('/requestordercancelorreturn', function() use ($app) {
          verifyRequiredParams(array('reason_type', 'order_id', 'reason_text'));
          $reason_type = $app->request()->post('reason_type');
          $order_id = $app->request()->post('order_id');
          $reason_text = $app->request()->post('reason_text');

          $status_text_id = 7;
          if ($reason_type == "1") {
            $status_text_id = 10;
          }

          $response = array();
          $db = new DbHandler();

          $reason = $db->addReason($reason_type, $order_id, $reason_text);
          if($reason != NULL) {
            $response['error'] = false;
            $response["reason_added"] = true;
          } else {
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
          }

          $status_update = $db->addOrderStatus($order_id, $status_text_id);
          if($status_update != NULL) {
            $status_column_update = $db->updateOrderStatusColumn($order_id, $status_text_id);
            if($status_column_update != NULL) {
              $response['error'] = false;
              $response["order_status"] = $status_text_id;
            } else {
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
            }            
          } else {
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
          }
          echoRespnse(200, $response);
        });

$app->post('/addorderstatus', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('order_id', 'status_text_id'));
          // reading post params
          $order_id = $app->request()->post('order_id');
          $status_text_id = $app->request()->post('status_text_id');
          $response = array();
          $db = new DbHandler();
          $status_update = $db->addOrderStatus($order_id, $status_text_id);
          if($status_update != NULL) {
            $status_column_update = $db->updateOrderStatusColumn($order_id, $status_text_id);
            if($status_column_update != NULL) {
              $response['error'] = false;
              $response["order_status"] = $status_text_id;
            } else {
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
            }            
          } else {
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
          }
          echoRespnse(200, $response);
        });

$app->post('/addorder', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('order_type', 'user_id', 'clothing_id', 'fabric_method', 'fabric_id', 'designs', 'addons', 'measurement_method', 'measurements', 'measurement_set_id', 'delivery_address_id', 'pickup_required', 'pickup_address_id', 'pickup_date', 'total_price', 'remarks', 'promo_id', 'discount', 'final_total'));
          // reading post params
          $order_type = $app->request()->post('order_type');
          $user_id = $app->request()->post('user_id');
          $clothing_id = $app->request()->post('clothing_id');
          $fabric_method = $app->request()->post('fabric_method');
          $fabric_id = $app->request()->post('fabric_id');
          $designs = $app->request()->post('designs');
          $addons = $app->request()->post('addons');
          $measurement_method = $app->request()->post('measurement_method');
          $measurements = $app->request()->post('measurements');
          $measurement_set_id = $app->request()->post('measurement_set_id');
          $delivery_address_id = $app->request()->post('delivery_address_id');
          $pickup_required = $app->request()->post('pickup_required');
          $pickup_address_id = $app->request()->post('pickup_address_id');
          $pickup_date = $app->request()->post('pickup_date');
          $total_price = $app->request()->post('total_price');
          $remarks = $app->request()->post('remarks');
          $promo_id = $app->request()->post('promo_id');
          $discount = $app->request()->post('discount');
          $final_total = $app->request()->post('final_total');
          $response = array();
          $db = new DbHandler();
          if ($measurement_set_id == 0) {
              if ($measurements == "0") {
                $measurement_set_id = 0;
              } else {
                $measurement_set_id = $db->addMeasurementSet($user_id, $clothing_id, $measurements, "");
              }            
          }
          if ($measurement_set_id != NULL) {
            $order_id = $db->addNewOrder($order_type, $user_id, $clothing_id, $fabric_method, $fabric_id, $designs, $addons, $measurement_method, $measurement_set_id, $delivery_address_id, $pickup_required, $pickup_address_id, $pickup_date, $total_price, $remarks, $promo_id, $discount, $final_total);
            if ($order_id != NULL) {
              $response["error"] = false;
              $response["measurement_set_id"] = $measurement_set_id;
              $response["order_id"] = $order_id;
              $order_code = $db->updateOrderCode($order_type, $clothing_id, $order_id);
              if($order_code != NULL) {
                $response["order_code"] = $order_code;
              }
              $status_update = $db->addOrderStatus($order_id, 1);
              if($status_update != NULL) {
                $status_column_update = $db->updateOrderStatusColumn($order_id, 1);
                if($status_column_update != NULL) {
                  $response['error'] = false;
                  $response["order_status"] = 1;
                } else {
                  $response['error'] = true;
                  $response['message'] = "An error occurred. Please try again";
                }
              } else {
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
              }
            } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
            }
          } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
          }
          echoRespnse(200, $response);
        });

$app->post('/addorderalt', function() use ($app) {
          // check for required params
          verifyRequiredParams(array('order_type', 'user_id', 'clothing_id', 'garment_method', 'alteration_method', 'measurement_method', 'measurements', 'measurement_set_id', 'delivery_address_id', 'pickup_required', 'pickup_address_id', 'pickup_date', 'total_price', 'remarks', 'promo_id', 'discount', 'final_total'));
          // reading post params
          $order_type = $app->request()->post('order_type');
          $user_id = $app->request()->post('user_id');
          $clothing_id = $app->request()->post('clothing_id');
          $fabric_method = $app->request()->post('garment_method');
          $fabric_id = $app->request()->post('alteration_method');
          $designs = "";
          $addons = "";
          $measurement_method = $app->request()->post('measurement_method');
          $measurements = $app->request()->post('measurements');
          $measurement_set_id = $app->request()->post('measurement_set_id');
          $delivery_address_id = $app->request()->post('delivery_address_id');
          $pickup_required = $app->request()->post('pickup_required');
          $pickup_address_id = $app->request()->post('pickup_address_id');
          $pickup_date = $app->request()->post('pickup_date');
          $total_price = $app->request()->post('total_price');
          $remarks = $app->request()->post('remarks');
          $promo_id = $app->request()->post('promo_id');
          $discount = $app->request()->post('discount');
          $final_total = $app->request()->post('final_total');
          $response = array();
          $db = new DbHandler();
          if ($measurement_set_id == 0) {
              if ($measurements == "0") {
                $measurement_set_id = 0;
              } else {
                $measurement_set_id = $db->addMeasurementSet($user_id, $clothing_id, $measurements, "");
              }            
          }
          if ($measurement_set_id != NULL) {
            $order_id = $db->addNewOrder($order_type, $user_id, $clothing_id, $fabric_method, $fabric_id, $designs, $addons, $measurement_method, $measurement_set_id, $delivery_address_id, $pickup_required, $pickup_address_id, $pickup_date, $total_price, $remarks, $promo_id, $discount, $final_total);
            if ($order_id != NULL) {
              $response["error"] = false;
              $response["measurement_set_id"] = $measurement_set_id;
              $response["order_id"] = $order_id;
              $order_code = $db->updateOrderCode($order_type, $clothing_id, $order_id);
              if($order_code != NULL) {
                $response["order_code"] = $order_code;
              }
              $status_update = $db->addOrderStatus($order_id, 1);
              if($status_update != NULL) {
                $status_column_update = $db->updateOrderStatusColumn($order_id, 1);
                if($status_column_update != NULL) {
                  $response['error'] = false;
                  $response["order_status"] = 1;
                } else {
                  $response['error'] = true;
                  $response['message'] = "An error occurred. Please try again";
                }
              } else {
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
              }
            } else {
              // unknown error occurred
              $response['error'] = true;
              $response['message'] = "An error occurred. Please try again";
            }
          } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
          }
          echoRespnse(200, $response);
        });

// $app->post('/generateordercode', function() use ($app) {
//             verifyRequiredParams(array('order_id'));
//             $order_id = $app->request()->post('order_id');
//             $response = array();
//             $db = new DbHandler();

//             $result = $db->getOrderCodeDetailsByOrderId($order_id);;
//             if ($result != NULL) {
//                 $response["error"] = false;
//                 $order_type = $result['order_type'];
//                 $clothing_id = $result['clothing_id'];
//                 $order_code = $db->updateOrderCode($order_type, $clothing_id, $order_id);
//                 if($order_code != NULL) {
//                     $response['error'] = false;
//                     $response["order_code"] = $order_code;
//                 } else {
//                     // unknown error occurred
//                     $response['error'] = true;
//                     $response['message'] = "An error occurred. Please try again";
//                 }
//         } else {
//                 // unknown error occurred
//                 $response['error'] = true;
//                 $response['message'] = "An error occurred. Please try again";
//             }

//                 echoRespnse(200, $response);
//         });

$app->post('/generateordertimestamp', function() use ($app) {
            verifyRequiredParams(array('order_id'));
            $order_id = $app->request()->post('order_id');
            $response = array();
            $db = new DbHandler();

            $result = $db->updateOrderTimestampByOrderId($order_id);;
            if ($result != NULL) {
                $response["error"] = false;
            } else {
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }

                echoRespnse(200, $response);
        });

$app->post('/voiduseraddress', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('address_id'));
            // reading post params
            $address_id = $app->request()->post('address_id');
            $response = array();
            $db = new DbHandler();
            $result = $db->voidUserAddress($address_id);
            if ($result != NULL) {
                $response["error"] = false;
                $response['address_voided'] = $result;
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }
            echoRespnse(200, $response);
        });

$app->get('/verifyaccount', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'key'));
            // reading get params
            $email = $app->request()->get('email');
            $key = $app->request()->get('key');
            $response = array();
            $db = new DbHandler();
            $user_id = $db->verifyEmailKeyCredentials($email, $key);
            if ($user_id != NULL) {
                $response["error"] = false;
                $result = $db->activateUser($user_id);
                if ($result != NULL) {
                    $response['user_id'] = $user_id;
                    $response['activated'] = true;
                    $app->response->redirect('http://tailorsquare.in/verify.php?user_id='.$user_id);
                } else {
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "Error! The Activation Link is invalid. Please Contact TailorSquare Support";
                $app->response->redirect('http://tailorsquare.in/verification_failed.php');
            }
            echoRespnse(200, $response);
        });

$app->post('/testemail', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email', 'key', 'name'));
            // reading post params
            $email = $app->request->post('email');
            $key = $app->request->post('key');
            $name = $app->request->post('name');

            $response = array();
            $result = emailVerificationPEAR($email, $key, $name);
            $response["message"] = $result;

            // echo json response
            echoRespnse(200, $response);
        });

$app->post('/placespecialorderrequest', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('name', 'mobile', 'email', 'gender', 'dress_info', 'order_type'));
            // reading post params
            $name = $app->request->post('name');
            $mobile = $app->request->post('mobile');
            $email = $app->request->post('email');
            $gender = $app->request->post('gender');
            $dress_info = $app->request->post('dress_info');
            $order_type = $app->request->post('order_type');

            $response = array();
            $result = emailSpecialOrderPEAR($name, $mobile, $email, $gender, $dress_info, $order_type);
            $response["message"] = $result;

            // echo json response
            echoRespnse(200, $response);
        });

$app->post('/resendverificationemail', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id'));
            // reading post params
            $user_id = $app->request->post('user_id');

            $response = array();
            $db = new DbHandler();
            $user = $db->getUserById($user_id);

            if ($user != NULL) {
                $result = emailVerificationPEAR($user['email'], $user['api_key'], $user['name']);
                $response['error'] = false;
                $response["message"] = $result;
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "An error occurred. Please try again";
            }

            // echo json response
            echoRespnse(200, $response);
        });

$app->post('/sendpasswordresetemail', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email'));
            // reading post params
            $email = $app->request->post('email');

            $response = array();
            $db = new DbHandler();
            $user = $db->getUserByEmail($email);

            if ($user != NULL) {
                $emailError = emailPasswordResetPEAR($email, $user['api_key'], $user['name']);
                if (!$emailError) {
                    $response['error'] = false;
                    $response['message'] = "Email sent successfully!";
                } else {
                    $response['error'] = true;                    
                    $response['error_code'] = 2;
                    $response["message"] = $emailError;                    
                }
            } else {
                // unknown error occurred
                $response['error'] = 1;
                $response['message'] = "An error occurred. Please try again";
            }

            // echo json response
            echoRespnse(200, $response);
        });

$app->get('/resetpasswordforaccount', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email','key'));
            // reading post params
            $email = $app->request->get('email');
            $key = $app->request->get('key');

            $response = array();
            $db = new DbHandler();
            $user_id = $db->verifyEmailKeyCredentials($email, $key);
            if ($user_id != NULL) {
                $response["error"] = false;
                $app->response->redirect('http://tailorsquare.in/resetpassword.php?email='.$email.'&key='.$key);
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "Error! The link is invalid. Please Contact TailorSquare Support";
                $app->response->redirect('http://tailorsquare.in/reset_failed.php');
            }
            echoRespnse(200, $response);
        });

$app->post('/changeuserpassword', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('email','api_key','password'));
            // reading post params
            $email = $app->request->post('email');
            $api_key = $app->request->post('api_key');
            $password = $app->request->post('password');

            $response = array();
            $db = new DbHandler();
            $result = $db->updateUserPassword($email, $api_key, $password);
            if (!!$result) {
                $response["error"] = false;
                $response["password_updated"] = true;
            } else {
                // unknown error occurred
                $response['error'] = true;
                $response['message'] = "User not found";
            }
            echoRespnse(200, $response);
        });

$app->post('/changeuserpasswordbyid', function() use ($app) {
            // check for required params
            verifyRequiredParams(array('user_id','api_key','old_password','new_password'));
            // reading post params
            $user_id = $app->request->post('user_id');
            $api_key = $app->request->post('api_key');
            $old_password = $app->request->post('old_password');
            $new_password = $app->request->post('new_password');

            $response = array();
            $db = new DbHandler();

            $isValidPassword = $db->checkLoginById($user_id, $old_password);
            if ($isValidPassword) {
                $result = $db->updateUserPasswordById($user_id, $api_key, $new_password);
                if (!!$result) {
                    $response["error"] = false;
                    $response["password_updated"] = true;
                    $response['message'] = "Password Changed Successfully";
                } else {
                    // unknown error occurred
                    $response['error'] = true;
                    $response['error_code'] = "1";
                    $response["password_updated"] = false;
                    $response['message'] = "Incorrect User ID or API Key";
                }                
            } else {
                $response['error'] = true;
                $response['error_code'] = "2";
                $response["password_updated"] = false;
                $response['message'] = "Incorrect Password";
            }

            echoRespnse(200, $response);
        });

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

function emailVerificationPEAR($email, $key, $name) {
    $from = "TailorSquare <mail@tailorsquare.in>";
    $to = $email;
    $subject = $name.', Verify your TailorSquare Account';
    $url = 'http://tailorsquare.in/api/v1/verifyaccount?email='.$email.'&key='.$key;
    $body =     '<p>Dear '.$name.',</p>
                <p>Thank you for signing up at TailorSquare - One stop for all your tailoring needs!</p>
                <p>Your account has been created, but needs to be verified before you can login.</p>
                <p>Please activate your account by visiting the link below:<br/></p>
                <p>--------------------------------------------------------------------------------------<br/></p>
                <p><a href="'.$url.'" target="_blank">'.$url.'</a><br/></p>
                <br/>
                <br/>
                <br/>
                <p>Cheers<br/>
                Tailor Square Team</p>';
    $host = "mail.tailorsquare.in";
    $username = "mail@tailorsquare.in";
    $password = "cR8s4@2j";
    $headers = array
        (   'From' => $from,
            'To' => $to,
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html'
        );
    $smtp = Mail::factory(
        'smtp',
        array (
            'host' => $host,
            'port' => 25,
            'auth' => true,
            'username' => $username,
            'password' => $password
            )
        );
    $mail = $smtp->send($to, $headers, $body);
        if (PEAR::isError($mail)) {
            return $mail->getMessage();
        } else {
            return "Message successfully sent!";
        }
}

function emailPasswordResetPEAR($email, $key, $name) {
    $from = "TailorSquare <mail@tailorsquare.in>";
    $to = $email;
    $subject = 'Reset Password of TailorSquare Account for '.$email;
    $url = 'http://tailorsquare.in/api/v1/resetpasswordforaccount?email='.$email.'&key='.$key;
    $body =     '<p>Dear '.$name.',</p>
                <p>Someone tried to reset your TailorSquare account password.</p>
                <p>If it was you, please visit the link below to reset it.<br/></p>
                <p>--------------------------------------------------------------------------------------<br/></p>
                <p><a href="'.$url.'" target="_blank">'.$url.'</a><br/></p>
                <br/>
                <br/>
                <p>If you did not make any such request, please ignore this email.</p>
                <br/>
                <br/>
                <br/>
                <p>Cheers<br/>
                Tailor Square Team</p>';
    $host = "mail.tailorsquare.in";
    $username = "mail@tailorsquare.in";
    $password = "cR8s4@2j";
    $headers = array
        (   'From' => $from,
            'To' => $to,
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html'
        );
    $smtp = Mail::factory(
        'smtp',
        array (
            'host' => $host,
            'port' => 25,
            'auth' => true,
            'username' => $username,
            'password' => $password
            )
        );
    $mail = $smtp->send($to, $headers, $body);
        if (PEAR::isError($mail)) {
            return $mail->getMessage();
        } else {
            return false;
        }
}

function emailVerification($email, $key) {
    $to      = $email; // Send email to our user
    $subject = 'Verify your TailorSquare Account'; 
    $url = 'http://tailorsquare.in/api/v1/verifyaccount?email='.$email.'&key='.$key;
    $message = '
     
    Thank you for signing up at TailorSquare - One stop for all your tailoring needs!
    Your account has been created please activate your account by pressing the link below.
     
    ------------------------
     
    Please click this link to activate your account:
    <a href="'.$url.'" target="_blank">'.$url.'</a>

    Cheers
    Tailor Square Team
     
    '; // Our message above including the link

    $headers = 'From:no-reply@tailorsquare.in' . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html\r\n";    
    mail($to, $subject, $message, $headers);
}

function emailSpecialOrderPEAR($name, $mobile, $email, $gender, $dress_info, $order_type) {
    $from = "TailorSquare <mail@tailorsquare.in>";
    $to = "TailorSquare <dress@tailorsquare.in>";
    $subject = 'Special Order Request from '.$name;
    $body =     '<p>A Special Order Request has been placed at TailorSquare.in</p>
                <p>Here are the order details:<br/></p>
                <p>--------------------------------------------------------------------------------------<br/></p>
                <table>
                    <tr>
                        <td>Name</td>
                        <td>'.$name.'</td>
                    </tr>
                    <tr>
                        <td>Mobile</td>
                        <td>'.$mobile.'</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>'.$email.'</td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td>'.$gender.'</td>
                    </tr>
                    <tr>
                        <td>Order Type</td>
                        <td>'.$order_type.'</td>
                    </tr>
                    <tr>
                        <td>Dress Info</td>
                        <td>'.$dress_info.'</td>
                    </tr>
                </table>
                <br/>
                <br/>
                <p>Cheers<br/>
                Tailor Square Team</p>';
    $host = "mail.tailorsquare.in";
    $username = "mail@tailorsquare.in";
    $password = "cR8s4@2j";
    $headers = array
        (   'From' => $from,
            'To' => $to,
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html'
        );
    $smtp = Mail::factory(
        'smtp',
        array (
            'host' => $host,
            'port' => 25,
            'auth' => true,
            'username' => $username,
            'password' => $password
            )
        );
    $mail = $smtp->send($to, $headers, $body);
        if (PEAR::isError($mail)) {
            return $mail->getMessage();
        } else {
            return false;
        }
}

// Special Order Email to multiple Email IDs - Changes
//     $to = "TailorSquare <hello@tailorsquare.in>, mohd asim ali <ali.mohdasim@gmail.com>, Priyadarshi Bhaskar <priyadarshi1000@gmail.com>";
//     $recipients = array(
//         'hello@tailorsquare.in',
//         'ali.mohdasim@gmail.com',
//         'priyadarshi1000@gmail.com'
//         );
//     $headers = array
//         (   'From' => $from,
//             'To' => $to,
//             'Subject' => $subject,
//             'MIME-Version' => '1.0',
//             'Content-type' => 'text/html'
//         );
//     $mail = $smtp->send($recipients, $headers, $body);

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating authorized access identifier
 */
function validateAuthorizedAccessIdentifier($authorized_access_identifier) {
    if ($authorized_access_identifier == "xWyZub6DCA4ARGWB") {
        return true;
    } else {
        return false;
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>