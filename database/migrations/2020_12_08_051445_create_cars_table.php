<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->text('name')->nullable();
            $table->text('index')->nullable();
            $table->text('Reserve')->nullable();
            $table->text('owner_id')->nullable();
            $table->text('owner_name')->nullable();
            $table->text('owner_email')->nullable();
            $table->text('GCLID')->nullable();
            $table->text('Item_6')->nullable();
            $table->text('Item_5')->nullable();
            $table->text('Item_4')->nullable();
            $table->text('Item_3')->nullable();
            $table->text('Item_2')->nullable();
            $table->text('Item_1')->nullable();
            $table->text('What_Kind_of_Mechanical_Issues_Are_There')->nullable();
            $table->text('Phone_Text_Or_Email')->nullable();
            $table->text('Dispatch_order_to_Email')->nullable();
            $table->text('Paid_Total')->nullable();
            $table->text('Sold_For')->nullable();
            $table->text('Title_Type')->nullable();
            $table->text('Title_Number')->nullable();
            $table->text('State')->nullable();
            $table->text('process_flow')->nullable();
            $table->text('Ad_Network')->nullable();
            $table->text('Stage')->nullable();
            $table->text('Street')->nullable();
            $table->text('Did_customer_sign_anywhere_on_open_Title')->nullable();
            $table->text('Notification_Opt_Out')->nullable();
            $table->text('Buyers_Quote')->nullable();
            $table->text('Customer_Reimbursement')->nullable();
            $table->text('approval')->nullable();
            $table->text('Buyer_Portal_Email')->nullable();
            $table->text('Increased_Offer')->nullable();
            $table->text('Team_Pics')->nullable();
            $table->text('Cost_per_Click')->nullable();
            $table->dateTime('Created_Time')->nullable();
            $table->text('Make')->nullable();
            $table->text('Support_Saved')->nullable();
            $table->text('Dispatch_Notes')->nullable();
            $table->text('Vehicle_Series')->nullable();
            $table->text('Is_There_Any_Body_Damage_Broken_Glass_2')->nullable();
            $table->text('Ad_Click_Date')->nullable();
            $table->text('SMS_Body')->nullable();
            $table->text('Created_By_id')->nullable();
            $table->text('Created_By_name')->nullable();
            $table->text('Created_By_email')->nullable();
            $table->text('Reason_Code')->nullable();
            $table->text('Description')->nullable();
            $table->text('Expenses_6')->nullable();
            $table->text('Ad')->nullable();
            $table->text('Reference_Number')->nullable();
            $table->text('Does_the_Vehicle_Run_and_Drive')->nullable();
            $table->text('Search_Partner_Network')->nullable();
            $table->text('review_process')->nullable();
            $table->text('Owner_Retain')->nullable();
            $table->text('Body_Fire_Flood_Damage')->nullable();
            $table->text('Lead_Status')->nullable();
            $table->text('Buyer_Auction_Tow')->nullable();
            $table->text('Fire_or_Flood_Damage')->nullable();
            $table->text('Lead_Conversion_Time')->nullable();
            $table->text('What_kind_of_paperwork_do_they_have')->nullable();
            $table->text('Review_Confirmed')->nullable();
            $table->text('Overall_Sales_Duration')->nullable();
            $table->text('Unpaid_Stage')->nullable();
            $table->text('Email_Opt_Out')->nullable();
            $table->text('Reviewed')->nullable();
            $table->text('Keyword')->nullable();
            $table->text('Are_There_Any_Mechanical_Issues')->nullable();
            $table->text('In_their_name')->nullable();
            $table->text('Any_Liens_on_Vehicle')->nullable();
            $table->text('Last_Text_Received')->nullable();
            $table->text('Dealer_Car')->nullable();
            $table->text('orchestration')->nullable();
            $table->text('Temp2')->nullable();
            $table->text('Where_Is_The_Broken_Glass')->nullable();
            $table->text('Commission_Paid')->nullable();
            $table->dateTime('Zoho_Invoice_Date')->nullable();
            $table->text('Year')->nullable(); // getvalue
            $table->text('Layout')->nullable();
            $table->text('Ad_Campaign_Name')->nullable();
            $table->text('Model')->nullable();
            $table->text('Lead_Source')->nullable();
            $table->text('Need_Update_Stage')->nullable();
            $table->text('Tow_Company_id')->nullable();
            $table->text('Tow_Company_name')->nullable();
            $table->text('Tag')->nullable();
            $table->text('Reason_for_Conversion_Failure')->nullable();
            $table->text('Alt_Phone')->nullable();
            $table->text('Tow_Company_Cancellation')->nullable();
            $table->text('Do_they_have_a_Title')->nullable();
            $table->text('Check_Number')->nullable();
            $table->text('Email')->nullable();
            $table->text('currency_symbol')->nullable();
            $table->text('Test_Line')->nullable();
            $table->text('followers')->nullable();
            $table->text('Difference')->nullable();
            $table->text('FormulaTime')->nullable();
            $table->text('Abandoned')->nullable();
            $table->dateTime('Last_Activity_Time')->nullable();
            $table->text('Referral_Number')->nullable();
            $table->dateTime('Scheduled_Time')->nullable();
            $table->text('Scheduled_Notes')->nullable();
            $table->text('Deal_Name')->nullable();
            $table->text('Last_Text_Sent')->nullable();
            $table->text('Profit')->nullable();
            $table->text('Quoted_By')->nullable();
            $table->text('Zip_Code')->nullable();
            $table->text('Any_Missing_Body_Panels_Interior_or_Engine_Parts')->nullable();
            $table->dateTime('Dispatch_Date_Time')->nullable();
            $table->text('approved')->nullable();
            $table->text('Total_Junk_and_Auction_Profit')->nullable();
            $table->dateTime('Conversion_Exported_On')->nullable();
            $table->text('What_s_your_relation')->nullable();
            $table->text('Missing_Parts')->nullable();
            $table->text('JCB_Funded')->nullable();
            $table->text('CUSTOMERS_QUOTE')->nullable();
            $table->text('Buyers_New_Offer')->nullable();
            $table->text('Click_Type')->nullable();
            $table->text('Color')->nullable();
            $table->text('followed')->nullable();
            $table->text('editable')->nullable();
            $table->text('City')->nullable();
            $table->text('AdGroup_Name')->nullable();
            $table->text('Additional_Fees')->nullable();
            $table->text('Are_there_any_missing_Body_Panels_or_Interior')->nullable();
            $table->text('Miles')->nullable();
            $table->text('Auto_Dispatch_Email_2')->nullable();
            $table->text('Is_there_any_Body_Damage_Broken_Glass')->nullable();
            $table->text('Auto_Dispatch_Email_3')->nullable();
            $table->text('Auto_Dispatch_Email_4')->nullable();
            $table->text('Number_2')->nullable();
            $table->text('Expense_1')->nullable();
            $table->text('Expense_2')->nullable();
            $table->text('Expense_3')->nullable();
            $table->text('Expense_4')->nullable();
            $table->text('Expense_5')->nullable();
            $table->text('Text_Capable')->nullable();
            $table->text('Any_Other_Issues_Besides_Transmission')->nullable();
            $table->text('What_s_Wrong_With_Vehicle')->nullable();
            $table->text('Airbags_Deployed')->nullable();
            $table->text('Drivetrain_Options')->nullable();
            $table->text('Paid')->nullable();
            $table->date('Sold_Date')->nullable();
            $table->text('Drawing_Pool')->nullable();
            $table->text('WD')->nullable();
            $table->text('Paperwork_Complete')->nullable();
            $table->date('Closing_Date')->nullable();
            $table->text('Missing_Tires')->nullable();
            $table->text('Conversion_Export_Status')->nullable();
            $table->text('Cost_per_Conversion')->nullable();
            $table->text('Modified_By_id')->nullable();
            $table->text('Modified_By_name')->nullable();
            $table->text('Modified_By_email')->nullable();
            $table->text('review')->nullable();
            $table->text('Which_tires_are_missing')->nullable();
            $table->text('Phone')->nullable();
            $table->text('Cancelled_Reason')->nullable();
            $table->text('Has_Title')->nullable();
            $table->text('Follow_Up_1')->nullable();
            $table->text('Follow_Up_2')->nullable();
            $table->text('Follow_Up_3')->nullable();
            $table->text('Follow_Up_4')->nullable();
            $table->text('Follow_Up_5')->nullable();
            $table->text('Follow_Up_6')->nullable();
            $table->text('Ready_for_Sending')->nullable();
            $table->text('Referral_Paid')->nullable();
            $table->text('Is_There_Any_Broken_Glass_Windows_etc')->nullable();
            $table->dateTime('Modified_Time')->nullable();
            $table->text('Device_Type')->nullable();
            $table->text('Sold_To')->nullable();
            $table->text('Extra_Expenses')->nullable();
            $table->text('Sales_Cycle_Duration')->nullable();
            $table->text('in_merge')->nullable();
            $table->text('Model1')->nullable();
            $table->text('VIN')->nullable();
            $table->text('Keys_Present')->nullable();
            $table->text('MSNG')->nullable();
            $table->text('Which_ones_are_flat')->nullable();
            $table->text('Follow_Up_Saved')->nullable();
            $table->text('approval_state')->nullable();
            $table->text('Are_all_the_tires_mounted')->nullable();
            $table->text('Are_All_the_Tires_Inflated')->nullable();
            $table->text('Location')->nullable();
            $table->text('Zoho_Books_Invoice_Number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cars');
    }
}
