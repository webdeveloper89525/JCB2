<?php

namespace App\Helpers;

use com\zoho\api\authenticator\OAuthToken;
use com\zoho\api\authenticator\TokenType;
use com\zoho\api\authenticator\store\DBStore;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\crm\api\Initializer;
use com\zoho\crm\api\UserSignature;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\crm\api\logger\Logger;
use com\zoho\crm\api\logger\Levels;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\ParameterMap;
use com\zoho\crm\api\record\GetRecordsHeader;
use com\zoho\crm\api\record\GetRecordsParam;
use com\zoho\crm\api\record\ResponseWrapper;
use com\zoho\crm\api\record\Deals;
use com\zoho\crm\api\record\Leads;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\SearchRecordsParam;
use com\zoho\crm\api\Param;
use com\zoho\crm\api\query\QueryOperations;
use com\zoho\crm\api\query\BodyWrapper;
use com\zoho\crm\api\record\BodyWrapper as RecordBodyWrapper;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\users\UsersOperations;
use com\zoho\crm\api\users\GetUsersParam;
use com\zoho\crm\api\users\GetUsersHeader;
use DB;
use App\Models\Car;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Facades\Http;

use com\zoho\crm\api\modules\ModulesOperations;
use com\zoho\crm\api\modules\GetModulesHeader;
use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\bulkread\BulkReadOperations;
use com\zoho\crm\api\bulkread\RequestWrapper;
use com\zoho\crm\api\bulkread\CallBack;
use com\zoho\crm\api\bulkread\Query;
use com\zoho\crm\api\bulkread\Criteria;
use com\zoho\crm\api\customviews\CustomViewsOperations;
use com\zoho\crm\api\query\APIException;

class ZohoSerivce {
    private $response = null;
    private $ZOHO_CURRENT_USER_EMAIL="developer@junkcarboys.com";

    public function __construct() {
        $refreshToken = '1000.93cee18e07c00a302901225649b1ca85.7219badd7e1bb31f9746779bbec3ae03';
        $user = new UserSignature($this->ZOHO_CURRENT_USER_EMAIL);
        $environment = USDataCenter::PRODUCTION();
        $token = new OAuthToken(env('ZOHO_CRM_CLIENT_ID'), env('ZOHO_CRM_CLIENT_SECRET'), $refreshToken, TokenType::REFRESH, env('ZOHO_REDIRECT_URI'));
        $tokenstore = new FileStore(storage_path('zoho/token.key'));
        $autoRefreshFields = false;
        $pickListValidation = false;
        $sdkConfig = (new SDKConfigBuilder())->setAutoRefreshFields($autoRefreshFields)->setPickListValidation($pickListValidation)->build();
        $resourcePath = base_path();

        $this->response = Initializer::initialize($user, $environment, $token, $tokenstore, $sdkConfig, $resourcePath, null, null);

    }

    public function getRecords($module = 'Deals', $page, $length) {
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();
        $paramInstance->add(GetRecordsParam::page(), $page);
        $paramInstance->add(GetRecordsParam::perPage(), $length);
        $paramInstance->add(GetRecordsParam::sortBy(), 'Created_Time');
        $headerInstance = new HeaderMap();
        $moduleAPIName = $module;
        $response = $recordOperations->getRecords($moduleAPIName, $paramInstance, $headerInstance);
        $responseHandler = $response->getObject();
        $records = $responseHandler->getData();
        return $records;
    }

    public function getAccount($email) {
        $moduleAPIName = "Accounts";
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();
        $paramInstance->add(SearchRecordsParam::criteria(), "(Buyer_Portal_Email:equals:".$email.")");
        $response = $recordOperations->searchRecords($moduleAPIName,$paramInstance);
        $responseHandler = $response->getObject();
        if (!$responseHandler) return null;
        $records = $responseHandler->getData();
        return $records[0];
    }

    public function getDealInfo($car_id) {
        $moduleAPIName = "Deals";
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();
        $paramInstance->add(SearchRecordsParam::criteria(), "(id:equals:".$car_id.")");

        $response = $recordOperations->searchRecords($moduleAPIName,$paramInstance);
        $responseHandler = $response->getObject();
        if (!$responseHandler) return null;
        $records = $responseHandler->getData();
        return $records[0];
    }

    public function getAllModules() {

        $moduleOperations = new ModulesOperations();
        $headerInstance = new HeaderMap();
        $datetime = date_create("2020-07-15T17:58:47+05:30")->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $headerInstance->add(GetModulesHeader::IfModifiedSince(), $datetime);
        //Call getModules method that takes headerInstance as parameters
        $response = $moduleOperations->getModules($headerInstance);

        return $response;
    }

    public function bid($car_id, $price) {
        $moduleAPIName = "Deals";
        $recordId =$car_id;
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        $record1 = new Record();
        $record1->setId($recordId);
        $record1->addKeyValue('Buyers_Quote', $price);
        $records[] = $record1;
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }



    public function updateDealInfo($car_id, $price, $user_id, $user_name) {
        $moduleAPIName = "Deals";
        $recordId =$car_id;
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        $record1 = new Record();
        $record1->setId($recordId);
        $record1->addKeyValue('Buyers_Quote', floatval($price));
        $Tow_company = new Record();
        $Tow_company->addKeyValue('id', $user_id);
        $Tow_company->addKeyValue('name', $user_name);
        $record1->addKeyValue('Tow_Company', $Tow_company);
        $records[] = $record1;
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }

    public function pay4Car($car_arr) {
        $moduleAPIName = "Deals";
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        foreach($car_arr as $key =>$car) {
            $recordId =$car["id"];
            $record1 = new Record();
            $record1->setId($recordId);
            $record1->addKeyValue('Stage', new Choice("Paid"));
            $record1->addKeyValue('Paid', true);
            $records[] = $record1;
        }
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }

    public function pickupMass($car_arr, $now) {
        $moduleAPIName = "Deals";
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        foreach($car_arr as $key =>$car) {
            $recordId =$car["id"];
            $record1 = new Record();
            $record1->setId($recordId);
            $record1->addKeyValue('Stage', new Choice("Picked Up"));
            $record1->addKeyValue('Scheduled_Time', $now);
            $records[] = $record1;
        }
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }

    public function cancel($car_id) {
        $moduleAPIName = "Deals";
        $recordId =$car_id;
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        $record1 = new Record();
        $record1->setId($recordId);
        $record1->addKeyValue('Stage', new Choice("Cancelled"));
        $records[] = $record1;
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }

    public function scheduleTime($car_id, $scheduleTime, $note, $pickup = false) {
        $moduleAPIName = "Deals";
        $recordId =$car_id;
        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();
        $record1 = new Record();
        $record1->setId($recordId);
        $record1->addKeyValue('Scheduled_Time', $scheduleTime);
        $record1->addKeyValue('Scheduled_Note', $note);
        if($pickup) $record1->addKeyValue('Stage', new Choice("Picked Up"));
        else $record1->addKeyValue('Stage', new Choice("Scheduled For Pick Up"));
        $records[] = $record1;
        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);
        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }


    public function updateInvoice($id, $subject, $user) {

        $moduleAPIName = "Invoices";

        $recordId =$id;

        $recordOperations = new RecordOperations();
        $body = new RecordBodyWrapper();
        $records = array();

        $record1 = new Record();
        $record1->setId($recordId);
        // $field = new Field("id, Buyers_Quote");
        $record1->addKeyValue('Subject', $subject);
        $account = new Record();
        $account->addKeyValue('id', $user->id);
        $account->addKeyValue('name', $user->name);
        $record1->addKeyValue('Account', $account);
        $record1->addKeyValue('Product_Name', "Car");
        $record1->addKeyValue('Quantity', 1);
        $record1->addKeyValue('Unit_Price', 100);
        $record1->addKeyValue('List_Price', 100);

        $records[] = $record1;

        $body->setData($records);
        $trigger = array("approval", "workflow", "blueprint");
        $body->setTrigger($trigger);

        $resp = $recordOperations->updateRecords($moduleAPIName, $body);
        return $resp;
    }

    public function refreshUserData() {
        $records = $this->getAllUsers();
        foreach ($records as $record) {
            $email = $record->getEmail();
            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = new User();
                $user->password = 'no_set';
            }

            $user->zoho_index = $record->getKeyValue('id');
            $name = $email;
            if ($record->getName()) {
                $name = $record->getName();
            } else if ($record->getFirstName() || $record->getLastName()) {
                $name = $record->getFirstName() . ' ' . $record->getLastName();
            }
            $user->name = $name;
            $user->email = $email;
            $user->zuid = $record->getZuid();
            $user->save();
        }
    }

    public function getAllUsers() {
        $usersOperations = new UsersOperations();
        $paramInstance = new ParameterMap();
        $paramInstance->add(GetUsersParam::type(), "ActiveUsers");
        $headerInstance = new HeaderMap();
        $response = $usersOperations->getUsers($paramInstance, $headerInstance);
        $responseHandler = $response->getObject();
        $records = $responseHandler->getUsers();
        return $records;
    }

    public function searchAccounts($owner_id, $page, $length) {
        $moduleAPIName = "Accounts";
        $recordOperations = new RecordOperations();
        $paramInstance = new ParameterMap();
        $paramInstance->add(SearchRecordsParam::criteria(), "(Owner.id:equals:".$owner_id.")");
        $paramInstance->add(SearchRecordsParam::page(), $page);
        $paramInstance->add(SearchRecordsParam::perPage(), $length);
        //Call searchRecords method
        $response = $recordOperations->searchRecords($moduleAPIName,$paramInstance);
        $responseHandler = $response->getObject();
        $records = $responseHandler->getData();
        return $records;
    }

    public function searchRecords1($query) {
        try {
            $page = 1;
            $limit = 200;

            $queryOperations = new QueryOperations();
            $bodyWrapper = new BodyWrapper();
            $records = [];
            while(true) {
                $selectQuery = $query . ' limit '. $page. ',' . $limit;

                $bodyWrapper->setSelectQuery($selectQuery);
                $response = $queryOperations->getRecords($bodyWrapper);
                $responseHandler = $response->getObject();
                $tmp_records = $responseHandler->getData();
                if (!is_array($tmp_records)) break;

                $records = array_merge($records, $tmp_records);

                if (count($tmp_records) < $limit) break;
                $page ++;


                echo '<br/>' . time().'-------' . $query.'-----------------' . count($records);die;
                if ($page == 15) break;
            }

        } catch (\Throwable $th) {
            if (!is_array($records)) $records = [];
        }

        return $records;
    }



    public function saveCarToMysql($record) {
        $car = new  Car();
        $car->index        =  $record->getKeyValue('id');
        $car->Reserve       =  $record->getKeyValue('Reserve');
        $car->owner_id      =  $record->getKeyValue('Owner')->getKeyValue('id');
        $car->owner_name    =  $record->getKeyValue('Owner')->getKeyValue('name');
        $car->owner_email   =  $record->getKeyValue('Owner')->getKeyValue('email');
        $car->GCLID         =  $record->getKeyValue('GCLID');
        $car->Item_6        =  $record->getKeyValue('Item_6');
        $car->Item_5        =  $record->getKeyValue('Item_5');
        $car->Item_4        =  $record->getKeyValue('Item_4');
        $car->Item_3        =  $record->getKeyValue('Item_3');
        $car->Item_2        =  $record->getKeyValue('Item_2');
        $car->Item_1        =  $record->getKeyValue('Item_1');
        $car->What_Kind_of_Mechanical_Issues_Are_There        =  json_encode($record->getKeyValue('What_Kind_of_Mechanical_Issues_Are_There'));
        $car->Phone_Text_Or_Email        =  $record->getKeyValue('Phone_Text_Or_Email') ? $record->getKeyValue('Phone_Text_Or_Email')->getValue() : null;
        $car->Dispatch_order_to_Email        =  $record->getKeyValue('Dispatch_order_to_Email');
        $car->Paid_Total        =  $record->getKeyValue('Paid_Total');
        $car->Sold_For        =  $record->getKeyValue('Sold_For');
        $car->Title_Type        =  $record->getKeyValue('Title_Type') ? $record->getKeyValue('Title_Type')->getValue() : null;
        $car->Title_Number        =  $record->getKeyValue('Title_Number');
        $car->State        =  $record->getKeyValue('$state');
        $car->process_flow        =  $record->getKeyValue('$process_flow');
        $car->Ad_Network        =  $record->getKeyValue('$Ad_Network');
        $car->Stage        =  $record->getKeyValue('Stage') ? $record->getKeyValue('Stage')->getValue('name') : null;
        $car->Street        =  $record->getKeyValue('Street');
        $car->Did_customer_sign_anywhere_on_open_Title        =  $record->getKeyValue('Did_customer_sign_anywhere_on_open_Title')?$record->getKeyValue('Did_customer_sign_anywhere_on_open_Title')->getValue():null;
        $car->Notification_Opt_Out        =  $record->getKeyValue('Notification_Opt_Out');
        $car->Buyers_Quote        =  $record->getKeyValue('Buyers_Quote');
        $car->Customer_Reimbursement        =  $record->getKeyValue('Customer_Reimbursement');
        $car->approval        =  json_encode($record->getKeyValue('$approval'));
        $car->Buyer_Portal_Email        =  $record->getKeyValue('Buyer_Portal_Email');
        $car->Increased_Offer        =  $record->getKeyValue('Increased_Offer');
        $car->Team_Pics        =  $record->getKeyValue('Team_Pics');
        $car->Cost_per_Click        =  $record->getKeyValue('Cost_per_Click');
        $car->Created_Time        =  $record->getKeyValue('Created_Time')->format('Y-m-d H:i:s');
        $car->Make        =  $record->getKeyValue('Make')?$record->getKeyValue('Make')->getValue():null;
        $car->Support_Saved        =  $record->getKeyValue('Support_Saved');
        $car->Dispatch_Notes        =  $record->getKeyValue('Dispatch_Notes');
        $car->Vehicle_Series        =  $record->getKeyValue('Vehicle_Series')?$record->getKeyValue('Vehicle_Series')->getValue():null;
        $car->Is_There_Any_Body_Damage_Broken_Glass_2        =  $record->getKeyValue('Is_There_Any_Body_Damage_Broken_Glass_2')?$record->getKeyValue('Is_There_Any_Body_Damage_Broken_Glass_2')->getValue():null;
        $car->Ad_Click_Date        =  $record->getKeyValue('Ad_Click_Date');
        $car->SMS_Body        =  $record->getKeyValue('SMS_Body');
        $car->Created_By_id        =  $record->getKeyValue('Created_By')->getKeyValue('id');
        $car->Created_By_name        =  $record->getKeyValue('Created_By')->getKeyValue('name');
        $car->Created_By_email        =  $record->getKeyValue('Created_By')->getKeyValue('email');
        $car->Reason_Code        =  $record->getKeyValue('Reason_Code') ? $record->getKeyValue('Reason_Code')->getValue() : null;
        $car->Description        =  $record->getKeyValue('Description');
        $car->Expenses_6        =  $record->getKeyValue('Expenses_6');
        $car->Ad        =  $record->getKeyValue('Ad');
        $car->Reference_Number        =  $record->getKeyValue('Reference_Number');
        $car->Does_the_Vehicle_Run_and_Drive        =  $record->getKeyValue('Does_the_Vehicle_Run_and_Drive')?$record->getKeyValue('Does_the_Vehicle_Run_and_Drive')->getValue(): null;
        $car->Search_Partner_Network        =  $record->getKeyValue('Search_Partner_Network') ? $record->getKeyValue('Search_Partner_Network')->getValue() : null;
        $car->review_process        =  json_encode($record->getKeyValue('$review_process'));
        $car->Owner_Retain        =  $record->getKeyValue('Owner_Retain');
        $car->Body_Fire_Flood_Damage        =  $record->getKeyValue('Body_Fire_Flood_Damage') ? $record->getKeyValue('Body_Fire_Flood_Damage')->getValue() : null;
        $car->Lead_Status        =  $record->getKeyValue('Lead_Status')?$record->getKeyValue('Lead_Status')->getValue():null;
        $car->Buyer_Auction_Tow        =  $record->getKeyValue('Buyer_Auction_Tow');
        $car->Fire_or_Flood_Damage        =  $record->getKeyValue('Fire_or_Flood_Damage')?$record->getKeyValue('Fire_or_Flood_Damage')->getValue():null;
        $car->Lead_Conversion_Time        =  $record->getKeyValue('Lead_Conversion_Time');
        $car->What_kind_of_paperwork_do_they_have        =  $record->getKeyValue('What_kind_of_paperwork_do_they_have')?$record->getKeyValue('What_kind_of_paperwork_do_they_have')->getValue():null;
        $car->Review_Confirmed        =  $record->getKeyValue('Review_Confirmed');
        $car->Overall_Sales_Duration        =  $record->getKeyValue('Overall_Sales_Duration');
        $car->Unpaid_Stage        =  $record->getKeyValue('Unpaid_Stage');
        $car->Email_Opt_Out        =  $record->getKeyValue('Email_Opt_Out');
        $car->Reviewed        =  $record->getKeyValue('Reviewed');
        $car->Keyword        =  $record->getKeyValue('Keyword');
        $car->Are_There_Any_Mechanical_Issues        =  $record->getKeyValue('Are_There_Any_Mechanical_Issues') ? $record->getKeyValue('Are_There_Any_Mechanical_Issues')->getValue() : null;
        $car->In_their_name        =  $record->getKeyValue('In_their_name')?$record->getKeyValue('In_their_name')->getValue():null;
        $car->Any_Liens_on_Vehicle        =  $record->getKeyValue('Any_Liens_on_Vehicle') ? $record->getKeyValue('Any_Liens_on_Vehicle')->getValue() : null;
        $car->Last_Text_Received        =  $record->getKeyValue('Last_Text_Received');
        $car->Dealer_Car        =  $record->getKeyValue('Dealer_Car');
        $car->orchestration        =  $record->getKeyValue('$orchestration');
        $car->Temp2        =  $record->getKeyValue('Temp2');
        $car->Where_Is_The_Broken_Glass        =  json_encode($record->getKeyValue('Where_Is_The_Broken_Glass'));
        $car->Commission_Paid        =  $record->getKeyValue('Commission_Paid');
        $car->Zoho_Invoice_Date        =  $record->getKeyValue('Zoho_Invoice_Date')?$record->getKeyValue('Zoho_Invoice_Date'):null;
        $car->Year        =  $record->getKeyValue('Year')?$record->getKeyValue('Year')->getValue():null;
        $car->Layout        =  $record->getKeyValue('Layout')->getName();
        $car->Ad_Campaign_Name        =  $record->getKeyValue('Ad_Campaign_Name');
        $car->Model        =  $record->getKeyValue('Model')?$record->getKeyValue('Model')->getValue():null;
        $car->Lead_Source        =  $record->getKeyValue('Lead_Source')?$record->getKeyValue('Lead_Source')->getValue():null;
        $car->Need_Update_Stage        =  $record->getKeyValue('Need_Update_Stage');
        $car->Tow_Company_id        =  $record->getKeyValue('Tow_Company')?$record->getKeyValue('Tow_Company')->getKeyValue('id'):null;
        $car->Tow_Company_name        =  $record->getKeyValue('Tow_Company')?$record->getKeyValue('Tow_Company')->getKeyValue('name'):null;
        $car->Tag        =  json_encode($record->getKeyValue('Tag'));
        $car->Reason_for_Conversion_Failure        =  $record->getKeyValue('Reason_for_Conversion_Failure') ? $record->getKeyValue('Reason_for_Conversion_Failure')->getValue() : null;
        $car->Alt_Phone        =  $record->getKeyValue('Alt_Phone');
        $car->Tow_Company_Cancellation        =  $record->getKeyValue('Tow_Company_Cancellation');
        $car->Do_they_have_a_Title        =  $record->getKeyValue('Do_they_have_a_Title')?$record->getKeyValue('Do_they_have_a_Title')->getValue():null;
        $car->Check_Number        =  $record->getKeyValue('Check_Number');
        $car->Email        =  $record->getKeyValue('Email');
        $car->currency_symbol        =  $record->getKeyValue('$currency_symbol');
        $car->Test_Line        =  $record->getKeyValue('Test_Line');
        $car->followers        =  $record->getKeyValue('$followers') ? json_encode($record->getKeyValue('$followers')) : null;
        $car->Difference        =  $record->getKeyValue('Difference');
        $car->FormulaTime        =  $record->getKeyValue('FormulaTime');
        $car->Abandoned        =  $record->getKeyValue('Abandoned');
        $car->Last_Activity_Time        =  $record->getKeyValue('Last_Activity_Time')?$record->getKeyValue('Last_Activity_Time')->format('Y-m-d H:i:s'):null;
        $car->Referral_Number        =  $record->getKeyValue('Referral_Number');
        $car->Scheduled_Time        =  $record->getKeyValue('Scheduled_Time')?$record->getKeyValue('Scheduled_Time')->format('Y-m-d H:i:s'):null;
        $car->Deal_Name        =  $record->getKeyValue('Deal_Name');
        $car->Last_Text_Sent        =  $record->getKeyValue('Last_Text_Sent');
        $car->Profit        =  $record->getKeyValue('Profit');
        $car->Quoted_By        =  $record->getKeyValue('Quoted_By')?$record->getKeyValue('Quoted_By')->getValue():null;
        $car->Zip_Code        =  $record->getKeyValue('Zip_Code');
        $car->Any_Missing_Body_Panels_Interior_or_Engine_Parts        =  $record->getKeyValue('Any_Missing_Body_Panels_Interior_or_Engine_Parts')?$record->getKeyValue('Any_Missing_Body_Panels_Interior_or_Engine_Parts')->getValue():null;
        $car->Dispatch_Date_Time        =  $record->getKeyValue('Dispatch_Date_Time')?$record->getKeyValue('Dispatch_Date_Time')->format('Y-m-d H:i:s'):null;
        $car->approved        =  $record->getKeyValue('$approved');
        $car->Total_Junk_and_Auction_Profit        =  $record->getKeyValue('Total_Junk_and_Auction_Profit');
        $car->Conversion_Exported_On        =  $record->getKeyValue('Conversion_Exported_On') ? $record->getKeyValue('Conversion_Exported_On')->format('Y-m-d H:i:s') : null;
        $car->What_s_your_relation        =  $record->getKeyValue('What_s_your_relation') ? $record->getKeyValue('What_s_your_relation')->getValue() : null;
        $car->Missing_Parts        =  json_encode($record->getKeyValue('Missing_Parts'));
        $car->JCB_Funded        =  $record->getKeyValue('JCB_Funded');
        $car->CUSTOMERS_QUOTE        =  $record->getKeyValue('CUSTOMERS_QUOTE');
        $car->Buyers_New_Offer        =  $record->getKeyValue('Buyers_New_Offer');
        $car->Click_Type        =  $record->getKeyValue('Click_Type') ? $record->getKeyValue('Click_Type')->getValue():null;
        $car->Color        =  $record->getKeyValue('Color')?$record->getKeyValue('Color')->getValue():null;
        $car->followed        =  $record->getKeyValue('$followed');
        $car->editable        =  $record->getKeyValue('$editable');
        $car->City        =  $record->getKeyValue('City');
        $car->Difference        =  $record->getKeyValue('Difference');
        $car->AdGroup_Name        =  $record->getKeyValue('AdGroup_Name');
        $car->Additional_Fees        =  $record->getKeyValue('Additional_Fees');
        $car->Are_there_any_missing_Body_Panels_or_Interior        =  json_encode($record->getKeyValue('Are_there_any_missing_Body_Panels_or_Interior'));
        $car->Miles        =  $record->getKeyValue('Miles')?$record->getKeyValue('Miles')->getValue():null;
        $car->Auto_Dispatch_Email_2        =  $record->getKeyValue('Auto_Dispatch_Email_2');
        $car->Is_there_any_Body_Damage_Broken_Glass        =  json_encode($record->getKeyValue('Is_there_any_Body_Damage_Broken_Glass'));
        $car->Auto_Dispatch_Email_3        =  $record->getKeyValue('Auto_Dispatch_Email_3');
        $car->Auto_Dispatch_Email_4        =  $record->getKeyValue('Auto_Dispatch_Email_4');
        $car->Number_2        =  $record->getKeyValue('Number_2');
        $car->State        =  $record->getKeyValue('State');
        $car->Expense_1        =  $record->getKeyValue('Expense_1');
        $car->Expense_2        =  $record->getKeyValue('Expense_2');
        $car->Expense_3        =  $record->getKeyValue('Expense_3');
        $car->Expense_4        =  $record->getKeyValue('Expense_4');
        $car->Expense_5        =  $record->getKeyValue('Expense_5');
        $car->Text_Capable        =  $record->getKeyValue('Text_Capable');
        $car->Any_Other_Issues_Besides_Transmission        =  $record->getKeyValue('Any_Other_Issues_Besides_Transmission')?$record->getKeyValue('Any_Other_Issues_Besides_Transmission')->getValue():null;
        $car->What_s_Wrong_With_Vehicle        =  json_encode($record->getKeyValue('What_s_Wrong_With_Vehicle'));
        $car->Airbags_Deployed        =  $record->getKeyValue('Airbags_Deployed')?$record->getKeyValue('Airbags_Deployed')->getValue(): null;
        $car->Drivetrain_Options        =  $record->getKeyValue('Drivetrain_Options') ? $record->getKeyValue('Drivetrain_Options')->getValue() : null;
        $car->Paid        =  $record->getKeyValue('Paid');
        $car->Sold_Date        =  $record->getKeyValue('Sold_Date');
        $car->Drawing_Pool        =  $record->getKeyValue('Drawing_Pool');
        $car->WD        =  $record->getKeyValue('WD');
        $car->Paperwork_Complete        =  $record->getKeyValue('Paperwork_Complete');
        $car->Closing_Date        =  $record->getKeyValue('Closing_Date');
        $car->Missing_Tires        =  $record->getKeyValue('Missing_Tires');
        $car->Conversion_Export_Status        =  $record->getKeyValue('Conversion_Export_Status') ? $record->getKeyValue('Conversion_Export_Status')->getValue() : null;
        $car->Cost_per_Conversion        =  $record->getKeyValue('Cost_per_Conversion');
        $car->Modified_By_id        =  $record->getKeyValue('Modified_By') ? $record->getKeyValue('Modified_By')->getKeyValue('id') : null;
        $car->Modified_By_name        =  $record->getKeyValue('Modified_By') ? $record->getKeyValue('Modified_By')->getKeyValue('name') : null;
        $car->Modified_By_email        =  $record->getKeyValue('Modified_By') ? $record->getKeyValue('Modified_By')->getKeyValue('email') : null;
        $car->review        =  $record->getKeyValue('$review');
        $car->Which_tires_are_missing        = json_encode($record->getKeyValue('Which_tires_are_missing'));
        $car->Phone        =  $record->getKeyValue('Phone');
        $car->Cancelled_Reason        =  $record->getKeyValue('Cancelled_Reason') ? $record->getKeyValue('Cancelled_Reason')->getValue() : null;
        $car->Has_Title        =  $record->getKeyValue('Has_Title') ? $record->getKeyValue('Has_Title')->getValue() : null;
        $car->Follow_Up_1        =  $record->getKeyValue('Follow_Up_1');
        $car->Follow_Up_2        =  $record->getKeyValue('Follow_Up_2');
        $car->Follow_Up_3        =  $record->getKeyValue('Follow_Up_3');
        $car->Follow_Up_4        =  $record->getKeyValue('Follow_Up_4');
        $car->Follow_Up_5        =  $record->getKeyValue('Follow_Up_5');
        $car->Follow_Up_6        =  $record->getKeyValue('Follow_Up_6');
        $car->Ready_for_Sending        =  $record->getKeyValue('Ready_for_Sending');
        $car->Referral_Paid        =  $record->getKeyValue('Referral_Paid');
        $car->Is_There_Any_Broken_Glass_Windows_etc        =  $record->getKeyValue('Is_There_Any_Broken_Glass_Windows_etc') ? $record->getKeyValue('Is_There_Any_Broken_Glass_Windows_etc')->getValue() : null;
        $car->Modified_Time        =  $record->getKeyValue('Modified_Time') ? $record->getKeyValue('Modified_Time')->format('Y-m-d H:i:s') : null;
        $car->Device_Type        =  $record->getKeyValue('Device_Type') ? $record->getKeyValue('Device_Type')->getValue() : null;
        $car->Sold_To        =  $record->getKeyValue('Sold_To');
        $car->Extra_Expenses        =  $record->getKeyValue('Extra_Expenses');
        $car->Sales_Cycle_Duration        =  $record->getKeyValue('Sales_Cycle_Duration');
        $car->in_merge        =  $record->getKeyValue('$in_merge');
        $car->Model1        =  $record->getKeyValue('Model1');
        $car->VIN        =  $record->getKeyValue('VIN');
        $car->Keys_Present        =  $record->getKeyValue('Keys_Present') ? $record->getKeyValue('Keys_Present')->getValue() : null;
        $car->MSNG        =  $record->getKeyValue('MSNG');
        $car->Which_ones_are_flat        = json_encode( $record->getKeyValue('Which_ones_are_flat'));
        $car->Follow_Up_Saved        =  $record->getKeyValue('Follow_Up_Saved');
        $car->approval_state        =  $record->getKeyValue('approval_state');
        $car->Are_all_the_tires_mounted        =  $record->getKeyValue('Are_all_the_tires_mounted') ? $record->getKeyValue('Are_all_the_tires_mounted')->getValue() : null;
        $car->Are_All_the_Tires_Inflated        =  $record->getKeyValue('Are_All_the_Tires_Inflated') ? $record->getKeyValue('Are_All_the_Tires_Inflated')->getValue(): null;
        $car->Location        =  $record->getKeyValue('Location') ? $record->getKeyValue('Location')->getValue() : null;
        $car->Zoho_Books_Invoice_Number        =  $record->getKeyValue('Zoho_Books_Invoice_Number');



        foreach ($car->toArray() as $key => $value) {
            if (is_object($value) || is_array($value)) {
                var_dump($value);
                echo '-----------------Invalid Key : '. $key.'<br/>';
                die;
            }
        }

        return $car;
    }

    public function refreshCarLocations() {
        $google_key = 'AIzaSyBi76kzF9HZr3hjSUvBA45aqIJTwe-zR9g';
        $records_per_page = 100;
        $page = 0;
        while (true) {
            echo '------ get zip code time '. time() . '<br/>';
            $zip_codes = DB::table('cars')
                    ->leftjoin('locations', 'cars.Zip_Code', 'locations.Zip_Code')
                    ->whereNotNull('cars.Zip_Code')
                    ->whereNull('locations.Zip_Code')
                    ->groupby('cars.Zip_Code')
                    ->skip($records_per_page * ($page++))
                    ->take($records_per_page)
                    // ->toSql();var_dump($zip_codes);die;
                    ->pluck('cars.Zip_Code')->toArray();

            echo '------ end query time '. time() . '<br/>';

            $valid_codes = [];
            $insert_query = [];
            foreach ($zip_codes as $code) {
                try {
                    $data = $this->getLocationByZipCode($code);
                    if ($data) {
                        $insert_query[] = $location_data;
                        Location::create($location_data);
                    } else {
                        Location::create([
                            'Zip_Code' => $code,
                            'lat' => 0,
                            'lng' => 0
                        ]);
                    }
                } catch (\Throwable $th) {
                    echo 'get code error --- ' . $code . '<br/>';
                    Location::create([
                        'Zip_Code' => $code,
                        'lat' => 0,
                        'lng' => 0
                    ]);
                }
            }
            echo '------ save locations '. count($zip_codes) . '<br/>';

            if (count($zip_codes ) == 0) break;
        }
    }

    public function refreshCarLocationFromDB() {
        $records_per_page = 100;
        $page = 0;
        while (true) {
            echo time(). ' - start loctions <br/>';
            $cars = Car::whereNull('lat')->whereNull('lng')
                    ->skip($records_per_page * ($page++))
                    ->take($records_per_page)
                    ->get();

            echo time(). ' - fetch cars <br/>';

            foreach ($cars as $car) {
                $lat = 0;
                $lng = 0;
                if ($car->Zip_Code) {
                    $location = Location::where('Zip_Code', $car->Zip_Code)->first();
                    if ($location) {
                        $lat = $location->lat;
                        $lng = $location->lng;
                    } else {
                        try {
                            $data = $this->getLocationByZipCode($car->Zip_Code);
                            Location::create($data);
                            echo time(). ' - fetch new code <br/>';
                            $lat = $data['lat'];
                            $lng = $data['lng'];
                        } catch (\Throwable $th) { }
                    }
                }

                $car->lat = $lat;
                $car->lng = $lng;
                $car->save();
                echo time(). ' - save cars <br/>';
            }
            echo 'save locations: '. count($cars) . '<br/>';

            if (count($cars) < $records_per_page) break;
        }
    }

    public function getLocationByZipCode($code) {
        $google_key = 'AIzaSyBi76kzF9HZr3hjSUvBA45aqIJTwe-zR9g';
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $code . '&key=' . $google_key;
        $res = json_decode( file_get_contents($url));
        if ($res && is_object($res) && $res->results && $res->results[0] ) {
            $result = $res->results[0];
            $location = $result->geometry->location;
            return [
                'Zip_Code' => $code,
                'lat' => $location->lat,
                'lng' => $location->lng
            ];
        }
        return null;
    }


}
