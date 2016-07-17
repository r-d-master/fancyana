<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */
    public function createUser($name, $mobile, $email, $password) {
        require_once 'PassHash.php';
        $response = array();
        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);
            // Generating API key
            $api_key = $this->generateApiKey();
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO user(name, mobile, email, password_hash, api_key) values(?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $mobile, $email, $password_hash, $api_key);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $record = array();
                $record["name"] = $name;
                $record["mobile"] = $mobile;
                $record["email"] = $email;
                $record["api_key"] = $api_key;
                $last_id = $this->conn->insert_id;
                $record["user_id"] = $last_id;

                return $record;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User with same email already existed in the db
            return USER_ALREADY_EXISTED;
        }
        return $response;
    }

    public function createFBUser($name, $fbid) {
        require_once 'PassHash.php';
        $response = array();
        // First check if user already existed in db
        if (!$this->isUserExists($fbid)) {
            $password_hash = PassHash::hash($fbid);
            $api_key = $this->generateApiKey();
            $stmt = $this->conn->prepare("INSERT INTO user(name, email, password_hash, api_key, active) values(?, ?, ?, ?, 2)");
            $stmt->bind_param("ssss", $name, $fbid, $password_hash, $api_key);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $record = array();
                $record["name"] = $name;
                $record["email"] = $fbid;
                $record["api_key"] = $api_key;
                $last_id = $this->conn->insert_id;
                $record["user_id"] = $last_id;
                $record["user_is_new"] = true;

                return $record;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User Exists
            $stmt = $this->conn->prepare("SELECT user_id, name, email, api_key FROM user WHERE email = ?");
            $stmt->bind_param("s", $fbid);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            if ($num_rows > 0) {
                $stmt->bind_result($user_id, $name, $fbid, $api_key);
                $stmt->fetch();
                $user = array();
                $user["user_id"] = $user_id;
                $user["name"] = $name;
                $user["email"] = $fbid;
                $user["api_key"] = $api_key;
                $user["user_is_new"] = false;
                $stmt->close();
                return $user;
            } else {
                return NULL;
            }
        }
        return $response;
    }

    public function createGUser($name, $email, $gid) {
        require_once 'PassHash.php';
        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            $password_hash = PassHash::hash($gid);
            $api_key = $this->generateApiKey();
            $stmt = $this->conn->prepare("INSERT INTO user(name, email, password_hash, api_key, active) values(?, ?, ?, ?, 3)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $record = array();
                $record["name"] = $name;
                $record["email"] = $email;
                $record["api_key"] = $api_key;
                $last_id = $this->conn->insert_id;
                $record["user_id"] = $last_id;
                $record["user_is_new"] = true;

                return $record;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
        } else {
            // User Exists
            $stmt = $this->conn->prepare("SELECT user_id, name, email, api_key FROM user WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $num_rows = $stmt->num_rows;
            if ($num_rows > 0) {
                $stmt->bind_result($user_id, $name, $email, $api_key);
                $stmt->fetch();
                $user = array();
                $user["user_id"] = $user_id;
                $user["name"] = $name;
                $user["email"] = $email;
                $user["api_key"] = $api_key;
                $user["user_is_new"] = false;
                $stmt->close();
                return $user;
            } else {
                return NULL;
            }
        }
    }

    public function updateUserPassword($email, $api_key, $password) {
        require_once 'PassHash.php';
        $password_hash = PassHash::hash($password);
        $stmt = $this->conn->prepare("UPDATE user SET password_hash = ? WHERE email = ? AND api_key = ?");
        $stmt->bind_param("sss", $password_hash, $email, $api_key);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUserPasswordById($user_id, $api_key, $password) {
        require_once 'PassHash.php';
        $password_hash = PassHash::hash($password);
        $stmt = $this->conn->prepare("UPDATE user SET password_hash = ? WHERE user_id = ? AND api_key = ?");
        $stmt->bind_param("sss", $password_hash, $user_id, $api_key);
        $stmt->execute();
        $stmt->store_result();
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($affected_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkLoginById($user_id, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $stmt->fetch();
            $stmt->close();
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $stmt->fetch();
            $stmt->close();
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
    public function checkAdminLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM admin_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $stmt->fetch();
            $stmt->close();
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
            // user not existed with the email
            return FALSE;
        }
    }

    public function checkTailorLogin($email, $password) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password_hash FROM tailor_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password
            $stmt->fetch();
            $stmt->close();
            if (PassHash::check_password($password_hash, $password)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();
            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT user_id from user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function verifyEmailKeyCredentials($email, $key) {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE email = ? AND api_key = ?");
        $stmt->bind_param("ss", $email, $key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    public function activateUserByEmail($email) {
        $stmt = $this->conn->prepare("UPDATE user SET active=1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function activateUser($user_id) {
        $stmt = $this->conn->prepare("UPDATE user SET active=1 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT name, email, api_key FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $stmt->bind_result($name, $email, $api_key);
            $stmt->fetch();
            $user = array();
            $user["name"] = $name;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT user_id, name, mobile, email, api_key, user_create_date, active FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        if ($num_rows > 0) {
            $stmt->bind_result($user_id, $name, $mobile, $email, $api_key, $user_create_date, $active);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $user_id;
            $user["name"] = $name;
            $user["mobile"] = $mobile;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $user["userCreateDate"] = $user_create_date;
            $user["active"] = $active;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getAdminUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email, api_key FROM admin_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($user_id, $name, $email, $api_key);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $user_id;
            $user["name"] = $name;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    public function getTailorUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT user_id, name, email, api_key FROM tailor_user WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($user_id, $name, $email, $api_key);
            $stmt->fetch();
            $user = array();
            $user["user_id"] = $user_id;
            $user["name"] = $name;
            $user["email"] = $email;
            $user["api_key"] = $api_key;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

    public function getAllClothing() {
        $stmt = $this->conn->prepare("SELECT clothing_id, clothing_name, clothing_image, is_for_women, price FROM clothing WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllAddons() {
        $stmt = $this->conn->prepare("SELECT addon_id, a.clothing_id, clothing_name, is_for_women, addon_name, addon_image, addon_price FROM addon AS a, clothing AS c WHERE a.clothing_id=c.clothing_id AND a.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllMeasurementTypes() {
        $stmt = $this->conn->prepare("SELECT measurement_type_id, measurement_type_name, measurement_type_max, measurement_type_unit, measurement_type_description FROM measurement_type");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllUserProfiles() {
        $stmt = $this->conn->prepare("SELECT name, mobile, email, user_create_date, active FROM user ORDER BY user_id DESC");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllUsers() {
        $stmt = $this->conn->prepare("SELECT user_id, name, mobile, email FROM user ORDER BY user_id DESC");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllStatusTypes() {
        $stmt = $this->conn->prepare("SELECT status_text_id, status_text FROM status_text WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllStates() {
        $stmt = $this->conn->prepare("SELECT state_id, state_name, country_name FROM address_state AS s, address_country AS c where s.country_id = c.country_id AND s.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllCountries() {
        $stmt = $this->conn->prepare("SELECT country_id, country_name FROM address_country WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllVendors() {
        $stmt = $this->conn->prepare("SELECT vendor_id, vendor_name, vendor_url, vendor_image FROM vendor WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllFabrics() {
        $stmt = $this->conn->prepare("SELECT fabric_id, f.clothing_id, clothing_name, is_for_women, fabric_name, fabric_image, fabric_price FROM fabric AS f, clothing AS c WHERE f.clothing_id=c.clothing_id AND f.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllDesignGroups() {
        $stmt = $this->conn->prepare("SELECT design_group_id, dg.clothing_id, clothing_name, is_for_women, design_group_name FROM design_group AS dg, clothing AS c WHERE dg.clothing_id=c.clothing_id AND dg.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllAlterationTypes() {
        $stmt = $this->conn->prepare("SELECT alteration_type_id, atp.clothing_id, clothing_name, is_for_women, alteration_type_title, alteration_type_price FROM alteration_type AS atp, clothing AS c WHERE atp.clothing_id=c.clothing_id AND atp.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllDesigns() {
        $stmt = $this->conn->prepare("SELECT design_id, design_name, design_image, d.design_group_id, design_group_name, dg.clothing_id, clothing_name, is_for_women FROM design AS d, design_group AS dg, clothing AS c WHERE d.design_group_id=dg.design_group_id AND dg.clothing_id=c.clothing_id AND d.voided=0 AND dg.voided=0 AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllBanners() {
        $stmt = $this->conn->prepare("SELECT banner_id, banner_image FROM banner WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllSubscribers() {
        $stmt = $this->conn->prepare("SELECT subscriber_email FROM subscriber WHERE voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllUserEmails() {
        $stmt = $this->conn->prepare("SELECT email FROM user WHERE active=1 OR active=3");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllMeasurementMatrices() {
        $stmt = $this->conn->prepare("SELECT mm.clothing_id, measurement_types FROM measurement_matrix AS mm, clothing AS c WHERE mm.clothing_id=c.clothing_id AND c.voided=0");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllPromos() {
        $stmt = $this->conn->prepare("SELECT promo_id, promo_code, promo_type, promo_discount, promo_minimum_amount, active FROM promo ORDER BY promo_id DESC");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllOrders() {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, o.designs, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status FROM order_details AS o, user AS u, clothing AS c WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllSOrders() {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, o.designs, o.addons, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.order_type=1 ORDER BY o.order_id DESC");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllSOrdersByStatus($status_params) {
        $params = array();
        $param_type = '';
        $param_holders = '';
        $n = count($status_params);
        for($i = 0; $i < $n; $i++) {
          $param_type .= "i";
          $param_holders .= '?, ';
        }
        $param_holders = substr($param_holders, 0, -2);
        $params[] = & $param_type;         
        for($i = 0; $i < $n; $i++) {
          $params[] = & $status_params[$i];
        }

        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, o.designs, o.addons, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.order_type=1 AND o.status IN (".$param_holders.") ORDER BY o.order_id DESC");
        /* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
        call_user_func_array(array($stmt, 'bind_param'), $params);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllAOrders() {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, atp.alteration_type_title, o.designs, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c, alteration_type AS atp WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.fabric_id=atp.alteration_type_id AND o.order_type=2 ORDER BY o.order_id DESC");
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAllAOrdersByStatus($status_params) {
        $params = array();
        $param_type = '';
        $param_holders = '';
        $n = count($status_params);
        for($i = 0; $i < $n; $i++) {
          $param_type .= "i";
          $param_holders .= '?, ';
        }
        $param_holders = substr($param_holders, 0, -2);
        $params[] = & $param_type;         
        for($i = 0; $i < $n; $i++) {
          $params[] = & $status_params[$i];
        }

        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, atp.alteration_type_title, o.designs, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c, alteration_type AS atp WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.fabric_id=atp.alteration_type_id AND o.order_type=2 AND o.status IN (".$param_holders.") ORDER BY o.order_id DESC");
        /* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
        call_user_func_array(array($stmt, 'bind_param'), $params);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getOrdersByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_code, o.order_type, o.clothing_id, c.clothing_name, c.clothing_image, c.is_for_women, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, st.status_text, o.remarks, o.discount, o.final_total, o.order_date FROM order_details AS o, clothing AS c, status_text AS st WHERE o.clothing_id=c.clothing_id AND o.status=st.status_text_id AND o.user_id= ? ORDER BY o.order_id DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getOrderByUserAndOrderCode($user_id, $order_code) {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_code, o.order_type, o.clothing_id, c.clothing_name, c.clothing_image, c.is_for_women, c.price, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, st.status_text, o.remarks, o.promo_id, o.discount, o.final_total, o.order_date, o.fabric_method, o.fabric_id, o.designs, o.addons, o.measurement_method, o.measurement_set_id FROM order_details AS o, clothing AS c, status_text AS st WHERE o.clothing_id=c.clothing_id AND o.status=st.status_text_id AND o.user_id= ? AND o.order_code = ?");
        $stmt->bind_param("is", $user_id, $order_code);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getSOrdersByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, o.fabric_id, o.designs, o.addons, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.order_type=1 AND o.user_id= ? ORDER BY o.order_id DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAOrdersByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT o.order_id, o.order_type, o.user_id, u.name, u.mobile, o.clothing_id, c.clothing_name, c.is_for_women, o.fabric_method, atp.alteration_type_title, o.fabric_id, o.designs, o.measurement_method, o.measurement_set_id, o.delivery_address_id, o.pickup_required, o.pickup_address_id, o.pickup_date, o.total_price, o.status, o.remarks FROM order_details AS o, user AS u, clothing AS c, alteration_type AS atp WHERE o.user_id=u.user_id AND o.clothing_id=c.clothing_id AND o.fabric_id=atp.alteration_type_id AND o.order_type=2 AND o.user_id= ? ORDER BY o.order_id DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAddonsByClothing($clothing_id) {
        $stmt = $this->conn->prepare("SELECT addon_id, addon_name, addon_image, addon_price FROM addon WHERE clothing_id = ? AND voided=0");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getPromoByCode($promo_code) {
        $stmt = $this->conn->prepare("SELECT promo_id, promo_code, promo_type, promo_discount, promo_minimum_amount FROM promo WHERE promo_code = ? AND active = 1");
        $stmt->bind_param("s", $promo_code);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        if ($num_rows > 0) {
            $stmt->bind_result($promo_id, $promo_code, $promo_type, $promo_discount, $promo_minimum_amount);
            $stmt->fetch();
            $promo = array();
            $promo["promo_id"] = $promo_id;
            $promo["promo_code"] = $promo_code;
            $promo["promo_type"] = $promo_type;
            $promo["promo_discount"] = $promo_discount;
            $promo["promo_minimum_amount"] = $promo_minimum_amount;
            $stmt->close();
            return $promo;
        } else {
            return NULL;
        }
    }

    public function getMeasurementMatrixByClothing($clothing_id) {
        $stmt = $this->conn->prepare("SELECT measurement_types FROM measurement_matrix WHERE clothing_id = ?");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getTSStandardByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT ts_standard FROM user_ts_standard WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getFabricsByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT fabric_id, clothing_id, fabric_name, fabric_image, fabric_price FROM fabric WHERE clothing_id = ? AND voided=0");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getFabricByFabricId($fabric_id) {
        $stmt = $this->conn->prepare("SELECT fabric_name, fabric_image, fabric_price FROM fabric WHERE fabric_id = ?");
        $stmt->bind_param("i", $fabric_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getDesignGroupsByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT design_group_id, clothing_id, design_group_name FROM design_group WHERE clothing_id = ? AND voided=0");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAlterationTypesByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT alteration_type_id, alteration_type_title, alteration_type_price FROM alteration_type WHERE clothing_id = ? AND voided=0");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAlterationTypeByAlterationId($alteration_type_id) {
        $stmt = $this->conn->prepare("SELECT alteration_type_id, alteration_type_title, alteration_type_price FROM alteration_type WHERE alteration_type_id = ?");
        $stmt->bind_param("i", $alteration_type_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getDesignsByDesignIds($design_ids) {
        $params = array();
        $param_type = '';
        $param_holders = '';
        $n = count($design_ids);
        for($i = 0; $i < $n; $i++) {
          $param_type .= "i";
          $param_holders .= '?, ';
        }
        $param_holders = substr($param_holders, 0, -2);
        $params[] = & $param_type;         
        for($i = 0; $i < $n; $i++) {
          $params[] = & $design_ids[$i];
        }

        $sql = "SELECT d.design_id, d.design_group_id, dg.design_group_name, d.design_name, d.design_image FROM design as d, design_group as dg WHERE design_id IN (".$param_holders.") AND d.design_group_id = dg.design_group_id";
        // echo $sql . "\n";
        // print_r($design_ids);
        $stmt = $this->conn->prepare($sql);
        /* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
        call_user_func_array(array($stmt, 'bind_param'), $params);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAddonsByAddonIds($addon_ids) {
        $params = array();
        $param_type = '';
        $param_holders = '';
        $n = count($addon_ids);
        for($i = 0; $i < $n; $i++) {
          $param_type .= "i";
          $param_holders .= '?, ';
        }
        $param_holders = substr($param_holders, 0, -2);
        $params[] = & $param_type;         
        for($i = 0; $i < $n; $i++) {
          $params[] = & $addon_ids[$i];
        }

        $sql = "SELECT addon_id, addon_name, addon_image, addon_price FROM addon WHERE addon_id IN (".$param_holders.")";
        $stmt = $this->conn->prepare($sql);
        /* use call_user_func_array, as $stmt->bind_param('s', $param); does not accept params array */
        call_user_func_array(array($stmt, 'bind_param'), $params);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getDesignsByDesignGroupId($design_group_id) {
        $stmt = $this->conn->prepare("SELECT design_id, design_group_id, design_name, design_image FROM design WHERE design_group_id = ?");
        $stmt->bind_param("i", $design_group_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getDesignsByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT design_id, d.design_group_id, design_name, design_image FROM design AS d, design_group AS dg WHERE dg.clothing_id = ? AND dg.design_group_id=d.design_group_id AND dg.voided=0 AND d.voided=0");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getClothingMeasurementTypesByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT clothing_id, measurement_type_id, measurement_type_name FROM clothing_measurement_type AS cmt, measurement_type AS mt WHERE clothing_id = ? AND cmt.measurement_type_id=mt.measurement_type_id");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getMeasurementTypesByClothingId($clothing_id) {
        $stmt = $this->conn->prepare("SELECT measurement_type_id, measurement_type_name, measurement_type_max, measurement_type_unit FROM measurement_type WHERE measurement_type_id IN (SELECT measurement_type_id FROM clothing_measurement_type WHERE clothing_id = ?)");
        $stmt->bind_param("i", $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getCustomTSMeasurementSetsByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT measurement_set_id, measurement_set_name, measurement_set_create_date, measurements FROM measurement_set WHERE user_id = ? AND clothing_id = 0 AND measurement_set_type = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getCustomMeasurementSetsByUserAndClothing($user_id, $clothing_id) {
        $stmt = $this->conn->prepare("SELECT measurement_set_id, measurement_set_name, measurement_set_create_date, measurements FROM measurement_set WHERE user_id = ? AND clothing_id = ? AND measurement_set_type = 2");
        $stmt->bind_param("ii", $user_id, $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getGarmentMeasurementSetsByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT measurement_set_id, m.clothing_id, clothing_name, is_for_women, measurement_set_create_date, measurements, measurement_set_name, measurement_set_image FROM measurement_set AS m, clothing AS c WHERE m.clothing_id = c.clothing_id AND user_id = ? AND measurement_set_type = 1 AND c.voided = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getGarmentMeasurementSetsByUserAndClothing($user_id, $clothing_id) {
        $stmt = $this->conn->prepare("SELECT measurement_set_id, measurement_set_name, measurement_set_create_date, measurements, measurement_set_image FROM measurement_set WHERE user_id = ? AND clothing_id = ? AND measurement_set_type = 1");
        $stmt->bind_param("ii", $user_id, $clothing_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getMeasurementsByMeasurementSetId($measurement_set_id) {
        $stmt = $this->conn->prepare("SELECT measurements FROM measurement_set WHERE measurement_set_id = ?");
        $stmt->bind_param("i", $measurement_set_id);
        if ($stmt->execute()) {
            $stmt->bind_result($measurements);
            $stmt->fetch();
            // $measurement_set = array();
            // $measurement_set["measurements"] = $measurements;
            $stmt->close();
            return $measurements;
        } else {
            return NULL;
        }
    }

    public function addClothing($clothing_name, $clothing_image, $is_for_women, $price) {
        $stmt = $this->conn->prepare("INSERT INTO clothing (clothing_name, clothing_image, is_for_women, price) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("ssii", $clothing_name, $clothing_image, $is_for_women, $price);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addFabric($clothing_id, $fabric_name, $fabric_image, $fabric_price) {
        $stmt = $this->conn->prepare("INSERT INTO fabric (clothing_id, fabric_name, fabric_image, fabric_price) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("issi", $clothing_id, $fabric_name, $fabric_image, $fabric_price);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addDesign($design_group_id, $design_name, $design_image) {
        $stmt = $this->conn->prepare("INSERT INTO design (design_group_id, design_name, design_image) VALUES(?, ?, ?)");
        $stmt->bind_param("iss", $design_group_id, $design_name, $design_image);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addDesignGroup($design_group_name, $clothing_id) {
        $stmt = $this->conn->prepare("INSERT INTO design_group (design_group_name, clothing_id) VALUES(?, ?)");
        $stmt->bind_param("si", $design_group_name, $clothing_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addAlterationType($alteration_type_title, $alteration_type_price, $clothing_id) {
        $stmt = $this->conn->prepare("INSERT INTO alteration_type (alteration_type_title, alteration_type_price, clothing_id) VALUES(?, ?, ?)");
        $stmt->bind_param("sii", $alteration_type_title, $alteration_type_price, $clothing_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addAddon($addon_name, $addon_image, $clothing_id, $addon_price) {
        $stmt = $this->conn->prepare("INSERT INTO addon (addon_name, addon_image, clothing_id, addon_price) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("ssii", $addon_name, $addon_image, $clothing_id, $addon_price);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addVendor($vendor_name, $vendor_url, $vendor_image) {
        $stmt = $this->conn->prepare("INSERT INTO vendor (vendor_name, vendor_url, vendor_image) VALUES(?, ?, ?)");
        $stmt->bind_param("sss", $vendor_name, $vendor_url, $vendor_image);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addState($state_name, $country_id) {
        $stmt = $this->conn->prepare("INSERT INTO address_state (state_name, country_id) VALUES(?, ?)");
        $stmt->bind_param("si", $state_name, $country_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addCountry($country_name) {
        $stmt = $this->conn->prepare("INSERT INTO address_country (country_name) VALUES(?)");
        $stmt->bind_param("s", $country_name);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addMeasurementType($measurement_type_name, $measurement_type_max, $measurement_type_unit) {
        $stmt = $this->conn->prepare("INSERT INTO measurement_type (measurement_type_name, measurement_type_max, measurement_type_unit) VALUES(?, ?, ?)");
        $stmt->bind_param("sis", $measurement_type_name, $measurement_type_max, $measurement_type_unit);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addMeasurementSet($user_id, $clothing_id, $measurements, $measurement_set_name) {
        $stmt = $this->conn->prepare("INSERT INTO measurement_set (user_id, clothing_id, measurements, measurement_set_name) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $clothing_id, $measurements, $measurement_set_name);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addMeasurementSetByClothing($user_id, $clothing_id, $measurements, $measurement_set_name) {
        $stmt = $this->conn->prepare("INSERT INTO measurement_set (user_id, clothing_id, measurements, measurement_set_name, measurement_set_type) VALUES(?, ?, ?, ?, 2)");
        $stmt->bind_param("iiss", $user_id, $clothing_id, $measurements, $measurement_set_name);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addGarmentMeasurementSet($user_id, $clothing_id, $measurements, $measurement_set_name, $measurement_set_image) {
        $stmt = $this->conn->prepare("INSERT INTO measurement_set (user_id, clothing_id, measurements, measurement_set_name, measurement_set_image, measurement_set_type) VALUES(?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("iisss", $user_id, $clothing_id, $measurements, $measurement_set_name, $measurement_set_image);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addStatusType($status_type_text) {
        $stmt = $this->conn->prepare("INSERT INTO status_text (status_text) VALUES(?)");
        $stmt->bind_param("s", $status_type_text);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addReason($reason_type, $reason_order_id, $reason_text) {
        $stmt = $this->conn->prepare("INSERT INTO reason (reason_type, reason_order_id, reason_text) VALUES(?, ?, ?)");
        $stmt->bind_param("iis", $reason_type, $reason_order_id, $reason_text);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addNewOrder($order_type, $user_id, $clothing_id, $fabric_method, $fabric_id, $designs, $addons, $measurement_method, $measurement_set_id, $delivery_address_id, $pickup_required, $pickup_address_id, $pickup_date, $total_price, $remarks, $promo_id, $discount, $final_total) {
        $stmt = $this->conn->prepare("INSERT INTO order_details (order_type, user_id, clothing_id, fabric_method, fabric_id, designs, addons, measurement_method, measurement_set_id, delivery_address_id, pickup_required, pickup_address_id, pickup_date, total_price, remarks, promo_id, discount, final_total) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiissiiiiisisiii", $order_type, $user_id, $clothing_id, $fabric_method, $fabric_id, $designs, $addons, $measurement_method, $measurement_set_id, $delivery_address_id, $pickup_required, $pickup_address_id, $pickup_date, $total_price, $remarks, $promo_id, $discount, $final_total);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function updateOrderTimestampByOrderId($order_id) {
        $stmt = $this->conn->prepare("SELECT status_create_date FROM status WHERE order_id = ? AND status_text_id = 1");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        if ($num_rows > 0) {
            $stmt->bind_result($status_create_date);
            $stmt->fetch();
            $result = $status_create_date;
            $stmt->close();
            return $this->setOrderTimestampByOrderId($order_id, $status_create_date);
        } else {
            return NULL;
        }
    }

    private function setOrderTimestampByOrderId($order_id, $timestamp) {
        $stmt = $this->conn->prepare("UPDATE order_details SET order_date = ? WHERE order_id = ?");
        $stmt->bind_param("si", $timestamp, $order_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getOrderCodeDetailsByOrderId($order_id) {
        $stmt = $this->conn->prepare("SELECT order_type, clothing_id FROM order_details WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        if ($num_rows > 0) {
            $stmt->bind_result($order_type, $clothing_id);
            $stmt->fetch();
            $result = array();
            $result["order_type"] = $order_type;
            $result["clothing_id"] = $clothing_id;
            $stmt->close();
            return $result;
        } else {
            return NULL;
        }

    }

    public function updateOrderCode($order_type, $clothing_id, $order_id) {
        $clen = strlen($clothing_id);
        $ilen = strlen($order_id);
        
        $clothing_id = substr($clothing_id, 0, 6);
        $order_id = substr($order_id, 0, 11);
        
        $order_code = $order_type;
        
        if ($clen < 10){
            $order_code .= "0";
        }

        $order_code .= $clen."-";
        
        for ($c=0; $c<6-$clen; $c++) {
            $paddingChar = mt_rand(0, 9);
            $order_code .= $paddingChar;
        }

        $order_code .= $clothing_id;
        
        if ($ilen < 10){
            $order_code .= "0";
        }

        $order_code .= $ilen."-";
        
        for ($i=0; $i<11-$ilen; $i++) {
            $paddingChar = mt_rand(0, 9);
            $order_code .= $paddingChar;
        }

        $order_code .= $order_id;

        $stmt = $this->conn->prepare("UPDATE order_details SET order_code = ? WHERE order_id = ?");
        $stmt->bind_param("si", $order_code, $order_id);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {
            return $order_code;
        } else {
            return NULL;
        }
    }

    public function addAddress($user_id, $address_name, $address_person_name, $address_line1, $address_line2, $address_city, $address_state_id, $address_pincode, $address_country_id, $address_mobile) {
        $stmt = $this->conn->prepare("INSERT INTO address (user_id, address_name, address_person_name, address_line1, address_line2, address_city, address_state_id, address_pincode, address_country_id, address_mobile) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssisis", $user_id, $address_name, $address_person_name, $address_line1, $address_line2, $address_city, $address_state_id, $address_pincode, $address_country_id, $address_mobile);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $last_id = $this->conn->insert_id;
            return $last_id;
        } else {
            return NULL;
        }
    }

    public function addBanner($banner_image) {
        $stmt = $this->conn->prepare("INSERT INTO banner (banner_image) VALUES(?)");
        $stmt->bind_param("s", $banner_image);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addSubscriber($subscriber_email) {
        if (!$this->doesSubscriberExist($subscriber_email)) {
            $stmt = $this->conn->prepare("INSERT INTO subscriber (subscriber_email) VALUES(?)");
            $stmt->bind_param("s", $subscriber_email);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $result;
            } else {
                return NULL;
            }
        } else {
            $stmt = $this->conn->prepare("UPDATE subscriber SET voided='0' WHERE subscriber_email = ?");
            $stmt->bind_param("s", $subscriber_email);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $result;
            } else {
                return NULL;
            }            
        }
    }

    private function doesSubscriberExist($subscriber_email) {
        $stmt = $this->conn->prepare("SELECT subscriber_id from subscriber WHERE subscriber_email = ?");
        $stmt->bind_param("s", $subscriber_email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function isUserSubscribed($email) {
        $stmt = $this->conn->prepare("SELECT subscriber_id from subscriber WHERE subscriber_email = ? AND voided = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function addPromo($promo_code, $promo_type, $promo_discount, $promo_minimum_amount) {
        if (!$this->doesPromoExist($promo_code)) {
            $stmt = $this->conn->prepare("INSERT INTO promo (promo_code, promo_type, promo_discount, promo_minimum_amount) VALUES(?, ?, ?, ?)");
            $stmt->bind_param("siii", $promo_code, $promo_type, $promo_discount, $promo_minimum_amount);
            $result = $stmt->execute();
            $stmt->close();
            if ($result) {
                return $result;
            } else {
                return NULL;
            }
        } else {
            return NULL;
        }
    }

    private function doesPromoExist($promo_code) {
        $stmt = $this->conn->prepare("SELECT promo_id from promo WHERE promo_code = ?");
        $stmt->bind_param("s", $promo_code);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    public function updateMeasurementMatrix($clothing_id, $measurement_types) {
        $stmt = $this->conn->prepare("INSERT INTO measurement_matrix (clothing_id, measurement_types) VALUES(?, ?) ON DUPLICATE KEY UPDATE measurement_types = ?");
        $stmt->bind_param("iss", $clothing_id, $measurement_types, $measurement_types);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateTSStandard($user_id, $ts_standard) {
        $stmt = $this->conn->prepare("INSERT INTO user_ts_standard (user_id, ts_standard) VALUES(?, ?) ON DUPLICATE KEY UPDATE ts_standard = ?");
        $stmt->bind_param("iss", $user_id, $ts_standard, $ts_standard);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function getAddressByAddressId($address_id) {
        $stmt = $this->conn->prepare("SELECT address_id, address_name, address_person_name, address_line1, address_line2, address_city, s.state_name, address_pincode, c.country_name, address_mobile FROM address, address_state AS s, address_country AS c WHERE address_state_id=s.state_id AND s.country_id=c.country_id AND address_id = ?");
        $stmt->bind_param("i", $address_id);
        if ($stmt->execute()) {
            $stmt->bind_result($address_id, $address_name, $address_person_name, $address_line1, $address_line2, $address_city, $state_name, $address_pincode, $country_name, $address_mobile);
            $stmt->fetch();
            $address = array();
            $address["address_id"] = $address_id;
            $address["address_name"] = $address_name;
            $address["address_person_name"] = $address_person_name;
            $address["address_line1"] = $address_line1;
            $address["address_line2"] = $address_line2;
            $address["address_city"] = $address_city;
            $address["state_name"] = $state_name;
            $address["address_pincode"] = $address_pincode;
            $address["country_name"] = $country_name;
            $address["address_mobile"] = $address_mobile;
            $stmt->close();
            return $address;
        } else {
            return NULL;
        }
    }

    public function getStatusByOrderId($order_id) {
        $stmt = $this->conn->prepare("SELECT s.status_id, s.status_text_id, st.status_text, s.status_create_date FROM status AS s, status_text AS st WHERE s.status_text_id=st.status_text_id AND s.order_id = ? ORDER BY s.status_id DESC");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getAddressesByUserId($user_id) {
        $stmt = $this->conn->prepare("SELECT address_id, address_name, address_person_name, address_line1, address_line2, address_city, s.state_name, address_pincode, c.country_name, address_mobile FROM address, address_state AS s, address_country AS c WHERE address_state_id=s.state_id AND s.country_id=c.country_id AND user_id = ? AND address.voided=0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function getUserProfileByUserId($user_id) {
        $stmt = $this->conn->prepare("SELECT name, mobile, email, user_create_date FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $results = $stmt->get_result();
        $stmt->close();
        return $results;
    }

    public function updateClothing($clothing_id, $clothing_name, $price) {
        $stmt = $this->conn->prepare("UPDATE clothing SET clothing_name = ?, price = ?  WHERE clothing_id = ?");
        $stmt->bind_param("sii", $clothing_name, $price, $clothing_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateFabric($fabric_id, $fabric_name, $fabric_price) {
        $stmt = $this->conn->prepare("UPDATE fabric SET fabric_name = ?, fabric_price = ? WHERE fabric_id = ?");
        $stmt->bind_param("sii", $fabric_name, $fabric_price, $fabric_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateDesign($design_id, $design_name) {
        $stmt = $this->conn->prepare("UPDATE design SET design_name = ? WHERE design_id = ?");
        $stmt->bind_param("si", $design_name, $design_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateDesignGroup($design_group_id, $design_group_name) {
        $stmt = $this->conn->prepare("UPDATE design_group SET design_group_name = ? WHERE design_group_id = ?");
        $stmt->bind_param("si", $design_group_name, $design_group_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateAlterationType($alteration_type_id, $alteration_type_title, $alteration_type_price) {
        $stmt = $this->conn->prepare("UPDATE alteration_type SET alteration_type_title = ?, alteration_type_price = ?  WHERE alteration_type_id = ?");
        $stmt->bind_param("sii", $alteration_type_title, $alteration_type_price, $alteration_type_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateAddon($addon_id, $addon_name, $addon_price) {
        $stmt = $this->conn->prepare("UPDATE addon SET addon_name = ?, addon_price = ?  WHERE addon_id = ?");
        $stmt->bind_param("sii", $addon_name, $addon_price, $addon_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateVendor($vendor_id, $vendor_name, $vendor_url) {
        $stmt = $this->conn->prepare("UPDATE vendor SET vendor_name = ?, vendor_url = ? WHERE vendor_id = ?");
        $stmt->bind_param("ssi", $vendor_name, $vendor_url, $vendor_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateState($state_id, $state_name) {
        $stmt = $this->conn->prepare("UPDATE address_state SET state_name = ? WHERE state_id = ?");
        $stmt->bind_param("si", $state_name, $state_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateCountry($country_id, $country_name) {
        $stmt = $this->conn->prepare("UPDATE address_country SET country_name = ? WHERE country_id = ?");
        $stmt->bind_param("si", $country_name, $country_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateStatusType($status_text_id, $status_text) {
        $stmt = $this->conn->prepare("UPDATE status_text SET status_text = ? WHERE status_text_id = ?");
        $stmt->bind_param("si", $status_text, $status_text_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function addOrderStatus($order_id, $status_text_id) {
        $stmt = $this->conn->prepare("INSERT INTO status (order_id, status_text_id) VALUES(?, ?)");
        $stmt->bind_param("ii", $order_id, $status_text_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updateOrderStatusColumn($order_id, $status_text_id) {
        $stmt = $this->conn->prepare("UPDATE order_details SET status = ? WHERE order_id = ?");
        $stmt->bind_param("ii", $status_text_id, $order_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function updatePromo($promo_id, $promo_discount, $promo_minimum_amount) {
        $stmt = $this->conn->prepare("UPDATE promo SET promo_discount = ?, promo_minimum_amount = ? WHERE promo_id = ?");
        $stmt->bind_param("iii", $promo_discount, $promo_minimum_amount, $promo_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function activatePromo($promo_id) {
        $stmt = $this->conn->prepare("UPDATE promo SET active='1' WHERE promo_id = ?");
        $stmt->bind_param("i", $promo_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function deactivatePromo($promo_id) {
        $stmt = $this->conn->prepare("UPDATE promo SET active='0' WHERE promo_id = ?");
        $stmt->bind_param("i", $promo_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidClothing($clothing_id) {
        $stmt = $this->conn->prepare("UPDATE clothing SET voided='1' WHERE clothing_id = ?");
        $stmt->bind_param("i", $clothing_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidFabric($fabric_id) {
        $stmt = $this->conn->prepare("UPDATE fabric SET voided='1' WHERE fabric_id = ?");
        $stmt->bind_param("i", $fabric_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidDesign($design_id) {
        $stmt = $this->conn->prepare("UPDATE design SET voided='1' WHERE design_id = ?");
        $stmt->bind_param("i", $design_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidDesignGroup($design_group_id) {
        $stmt = $this->conn->prepare("UPDATE design_group SET voided='1' WHERE design_group_id = ?");
        $stmt->bind_param("i", $design_group_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidAlterationType($alteration_type_id) {
        $stmt = $this->conn->prepare("UPDATE alteration_type SET voided='1' WHERE alteration_type_id = ?");
        $stmt->bind_param("i", $alteration_type_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidAddon($addon_id) {
        $stmt = $this->conn->prepare("UPDATE addon SET voided='1' WHERE addon_id = ?");
        $stmt->bind_param("i", $addon_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidVendor($vendor_id) {
        $stmt = $this->conn->prepare("UPDATE vendor SET voided='1' WHERE vendor_id = ?");
        $stmt->bind_param("i", $vendor_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidState($state_id) {
        $stmt = $this->conn->prepare("UPDATE address_state SET voided='1' WHERE state_id = ?");
        $stmt->bind_param("i", $state_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidCountry($country_id) {
        $stmt = $this->conn->prepare("UPDATE address_country SET voided='1' WHERE country_id = ?");
        $stmt->bind_param("i", $country_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidStatusType($status_text_id) {
        $stmt = $this->conn->prepare("UPDATE status_text SET voided='1' WHERE status_text_id = ?");
        $stmt->bind_param("i", $status_text_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidBanner($banner_id) {
        $stmt = $this->conn->prepare("UPDATE banner SET voided='1' WHERE banner_id = ?");
        $stmt->bind_param("i", $banner_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidUserAddress($address_id) {
        $stmt = $this->conn->prepare("UPDATE address SET voided='1' WHERE address_id = ?");
        $stmt->bind_param("i", $address_id);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    public function voidSubscriber($subscriber_email) {
        $stmt = $this->conn->prepare("UPDATE subscriber SET voided='1' WHERE subscriber_email = ?");
        $stmt->bind_param("s", $subscriber_email);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            return $result;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // $api_key = $stmt->get_result()->fetch_assoc();
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT user_id FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT user_id from user WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
}

?>

