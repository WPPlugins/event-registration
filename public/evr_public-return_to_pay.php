<?php
function evr_retun_to_pay(){
		global $wpdb, $company_options;
        //$company_options = get_option('evr_company_settings');
        if ($company_options['pay_now']!=""){$pay_now = $company_options['pay_now'];} else {$pay_now = "PAY NOW";}
        $id="";
        $passed_id="";
        $passed_id = $_GET['id'];
        if (is_numeric($passed_id)){$id = $passed_id;}
        else {
            $passed_id = "";
            _e('Failure - please retry!','evr_language'); 
            exit;}
        if ($passed_id ==""){_e('Please check your email for payment information.','evr_language');}
        else{
        $attendee = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". get_option('evr_attendee') ." WHERE id = %d",$passed_id));
			    $attendee_id = $attendee->id;
				$lname = $attendee->lname;
				$fname = $attendee->fname;
				$address = $attendee->address;
				$city = $attendee->city;
				$state = $attendee->state;
				$zip = $attendee->zip;
				$email = $attendee->email;
				$phone = $attendee->phone;
				$date = $attendee->date;
				$payment = $attendee->payment;
				$event_id = $attendee->event_id;
                $quantity = $attendee->quantity;
 			    $reg_type = $attendee->reg_type;
                $ticket_order = unserialize($attendee->tickets);
                $coupon = $attendee->coupon;
				$attendee_name = $fname." ".$lname;
		$event = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". get_option('evr_event') ." WHERE id = %d",$event_id));	
				$event_name = stripslashes($event->event_name);
				$event_desc = stripslashes($event->event_desc);
				$event_description = stripslashes($event->event_desc);
				$event_identifier = $event->event_identifier;
echo "<br><br><strong>".__('Payment Page for','evr_language')." " .$fname." ".$lname." ".__('for event','evr_language')." ".$event_name."</strong><br><br>";
// Print the Order Verification to the screen.
        ?>				  
                        <p align="left"><strong><?php _e('Registration Detail Summary:','evr_language');?></strong></p>
                            <table width="95%" border="0">
                              <tr>
                                <td><strong><?php _e('Event Name/Cost:','evr_language');?></strong></td>
                                <td><?php echo $event_name;?> - <?php echo $ticket_order[0]['ItemCurrency'];?><?php echo $payment;?></td>
                              </tr>
                              <tr>
                                <td><strong><?php _e('Attendee Name:','evr_language');?></strong></td>
                                <td><?php echo $attendee_name?></td>
                              </tr>
                              <tr>
                                <td><strong><?php _e('Email Address:','evr_language');?></strong></td>
                                <td><?php echo $email?></td>
                              </tr>
                               <tr>
                                <td><strong><?php _e('Number of Attendees:','evr_language');?></strong></td>
                                <td><?php echo $quantity?></td>
                              </tr>
                               <tr>
                                <td><strong><?php _e('Order Details:','evr_language');?></strong></td>
                                <td><?php $row_count = count($ticket_order);
            for ($row = 0; $row < $row_count; $row++) {
            if ($ticket_order[$row]['ItemQty'] >= "1"){ echo $ticket_order[$row]['ItemQty']." ".$ticket_order[$row]['ItemCat']."-".$ticket_order[$row]['ItemName']." ".$ticket_order[$row]['ItemCurrency'] . " " . $ticket_order[$row]['ItemCost']."<br \>";}
            } ?></td>
                              </tr>
                            </table><br />
        <?php
//End Verification 
                                $made_payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . get_option('evr_payment') . " WHERE payer_id=%d",$attendee->id));
                                $rows = $wpdb->get_results( "SELECT * FROM ". get_option('evr_payment') ." WHERE payer_id= $attendee->id" );
                                if ($rows){
                                    $payment_made="0";
                                echo '<p align="left"><strong>';
                                _e('Payments Received:','evr_language');
                                echo "</strong></p>";
                                	foreach ($rows as $payment){
                                		echo  __('Payment','evr_language')." ".$payment->mc_currency." ".$payment->mc_gross." ".$payment->txn_type." ".$payment->txn_id." (".$payment->payment_date.")"."<br />";
                                        $payment_made = $payment_made + $payment->mc_gross;
                                	}
                                echo '<font color="red">';
                                        echo "<br/>";
                                        _e('Total Outstanding Payment Due*:','evr_language');
                                        $total_due = $attendee->payment - $payment_made;
                                        echo $ticket_order[0]['ItemCurrency']." ".$total_due;
                                        echo '</font><br/><br/>';
                                }
                                else {
                                    echo '<font color="red">';
                                _e('No Payments Received!','evr_language');
                                echo "<br/>";
                                _e('Total Payment Due*:','evr_language');
                                $total_due = $payment;
                                echo $ticket_order[0]['ItemCurrency']." ".$total_due;
                                echo '</font><br/><br/>';}
_e('*Payments could take several days to post to this page. Please check back in several days if you made a payment and your payment is not showing at this time.','evr_language');
echo "<br><br>";
//Set payment value for return payments
$payment=$total_due;
//Paypal 
    if ($company_options['payment_vendor']=="PAYPAL"){
    $p = new paypal_class;// initiate an instance of the class
    if ($company_options['use_sandbox'] == "Y") {
		$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // testing paypal url
		echo "<h3 style=\"color:#ff0000;\" title=\"Payments will not be processed\">Sandbox Mode Is Active</h3>";
	}else {
		$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr'; // paypal url
	}
    	if ($payment != "0.00" || $payment != "" || $payment != " "){
				  $p->add_field('business', $company_options['paypal_id']);
				  $p->add_field('return', evr_permalink($company_options['return_url']));
				  $p->add_field('cancel_return', evr_permalink($company_options['cancel_return']));
				  $p->add_field('notify_url', evr_permalink($company_options['evr_page_id']).'id='.$attendee_id.'&event_id='.$event_id.'&action=paypal_txn');
				  $p->add_field('item_name', $event_name . ' | Reg. ID: '.$attendee_id. ' | Name: '. $attendee_name .' | Total Registrants: '.$quantity);
				  $p->add_field('amount', $payment);
				  $p->add_field('currency_code', $company_options['default_currency']);
				  //Post variables
				  $p->add_field('first_name', $fname);
				  $p->add_field('last_name', $lname);
				  $p->add_field('email', $email);
				  $p->add_field('address1', $address);
				  $p->add_field('city', $city);
				  $p->add_field('state', $state);
				  $p->add_field('zip', $zip);				 
                  $p->submit_paypal_post($pay_now); // submit the fields to paypal
				  if ($company_options['use_sandbox'] == "Y") {
					  $p->dump_fields(); // for debugging, output a table of all the fields
				  }   
			}
 }
 //End Paypal Section
//Authorize.Net Payment Section
if ($company_options['payment_vendor']=="AUTHORIZE"){
        //Authorize.Net Payment 
        // This sample code requires the mhash library for PHP versions older than
        // 5.1.2 - http://hmhash.sourceforge.net/
        // the parameters for the payment can be configured here
        // the API Login ID and Transaction Key must be replaced with valid values
        //$loginID		= $company_options['authorize_id'];
        //$transactionKey = $company_options['authorize_key'];
        $amount 		= $payment;
        $description 	= $event_name . ' | Reg. ID: '.$attendee_id. ' | Name: '. $attendee_name .' | Total Registrants: '.$quantity;
        $label 			= $pay_now; // The is the label on the 'submit' button
        // By default, this sample code is designed to post to our test server for
        // developer accounts: https://test.authorize.net/gateway/transact.dll
        // for real accounts (even in test mode), please make sure that you are
        // posting to: https://secure.authorize.net/gateway/transact.dll
        if ($company_options['use_authorize_sandbox'] == "Y"){ 
            $loginID		= $company_options['authorize_sandbox_id'];
            $transactionKey = $company_options['authorize_sandbox_key'];
            $url            = "https://test.authorize.net/gateway/transact.dll";
            }
        else {
            $loginID		= $company_options['authorize_id'];
            $transactionKey = $company_options['authorize_key'];
            $url			= "https://secure.authorize.net/gateway/transact.dll";
            }
        if ($company_options['use_authorize_testmode']=="Y"){
            $testMode = "true";
        }
        else {
            $testMode = "false";
            }
        // If an amount or description were posted to this page, the defaults are overidden
        if ($_REQUEST["amount"])
        	{ $amount = $_REQUEST["amount"]; }
        if ($_REQUEST["description"])
        	{ $description = $_REQUEST["description"]; }
        // an invoice is generated using the date and time
        $invoice	= date(YmdHis);
        // a sequence number is randomly generated
        $sequence	= rand(1, 1000);
        // a timestamp is generated
        $timeStamp	= time ();
        // The following lines generate the SIM fingerprint.  PHP versions 5.1.2 and
        // newer have the necessary hmac function built in.  For older versions, it
        // will try to use the mhash library.
        if( phpversion() >= '5.1.2' )
        {	$fingerprint = hash_hmac("md5", $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey); }
        else 
        { $fingerprint = bin2hex(mhash(MHASH_MD5, $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $amount . "^", $transactionKey)); }
        if ($company_options['pay_msg'] !=""){ echo stripslashes($company_options['pay_msg']); }
     else { _e("To pay online, please select the Payment button to be taken to our payment vendor's site.",'evr_language'); }
     echo '<br/>';
        // Print the Order Verification to the screen.
        echo '<p align="left"><strong>'.__('Order details:','evr_language').'</strong></p><table width="95%" border="0"><tr><td><strong>';
                _e(' Event Name/Cost:','evr_language');
                echo '</strong></td><td>'.$event_name.' - '.$item_display_cur.' '.$payment.'</td></tr><tr><td><strong>';
                _e('Attendee Name:','evr_language');
                echo '</strong></td><td>'.$attendee_name.'</td></tr><tr><td><strong>';
                _e('Email Address:','evr_language');
                echo '</strong></td><td>'.$email.'</td></tr><tr><td><strong>';
                _e('Number of Attendees:','evr_language');
                echo '</strong></td><td>'.$quantity.'</td></tr><tr><td><strong>';
                _e('Order Details:','evr_language');
                echo '</strong></td><td>';
                $row_count = count($ticket_order);
                    for ($row = 0; $row < $row_count; $row++) {
                        if ($ticket_order[$row]['ItemQty'] >= "1"){ 
                            echo $ticket_order[$row]['ItemQty']." ".$ticket_order[$row]['ItemCat']."-".$ticket_order[$row]['ItemName']." ".
                            $item_display_cur . " " . $ticket_order[$row]['ItemCost']."<br \>";
                            }
                        } 
                echo '</td></tr>';
                if ($company_options['use_sales_tax'] == "Y"){ 
                    echo '<tr><td></td><td>';
                    _e('Sales Tax  ','evr_language'); 
                    echo ':  '.$tax;
                    echo '</td></tr>';
                    } 
                echo '<tr><td><strong>'.__('Total Cost:','evr_language').'</strong></td>';
                echo '<td>'.$ticket_order[0]['ItemCurrency'].'<strong>&nbsp;'.number_format($payment,2).'</strong></td></tr></table><br />';
                $ipn_url  = evr_permalink($company_options['evr_page_id']).'id='.$attendee_id.'&event_id='.$event_id.'&action=authorize_txn';  
        // Create the HTML form containing necessary SIM post values
        echo "<FORM method='post' action='$url' >";
        // Additional fields can be added here as outlined in the SIM integration guide
        // at: http://developer.authorize.net
        echo "	<INPUT type='hidden' name='x_login' value='$loginID' />";
        if ($price == "0"){echo "Enter Amount $<INPUT type='text' name='x_amount' value='10.00' />";}
        else { echo "	<INPUT type='hidden' name='x_amount' value='$amount' />";}
        echo "	<INPUT type='hidden' name='x_description' value='$description' />";
        echo "	<INPUT type='hidden' name='x_cust_id' value='$attendee_id' />";
        echo "	<INPUT type='hidden' name='x_invoice_num' value='$invoice' />";
        echo "	<INPUT type='hidden' name='x_fp_sequence' value='$sequence' />";
        echo "	<INPUT type='hidden' name='x_fp_timestamp' value='$timeStamp' />";
        echo "	<INPUT type='hidden' name='x_fp_hash' value='$fingerprint' />";
        echo "	<INPUT type='hidden' name='x_test_request' value='$testMode' />";
        echo "	<INPUT type='hidden' name='x_show_form' value='PAYMENT_FORM' />";
        echo "	<INPUT type='hidden' name='x_email_customer' value='TRUE' />";
        echo "  <INPUT type='hidden' name='x_receipt_link_method' value='POST' />";
        echo "  <INPUT type='hidden' name='x_receipt_link_url' value='$ipn_url' />";
        echo "  <INPUT type='hidden' name='x_receipt_link_text' value='Click This Link to Complete Transaction & Return to Registration Site' />";
        echo "  <INPUT type='hidden' name='x_relay_response' value='FALSE' />";
        
        echo "  <INPUT type='hidden' name='x_relay_url' value='$ipn_url' />";
        echo "	<INPUT type='hidden' name='x_invoice_num' value='$event_id' />";
        //echo    "<input type='hidden' name='x_logo_URL' value='Add URL for logo to display atop Authorize.net page'>";
        
        echo "	<input type='submit' value='$label' />";
        echo "</FORM>";
// This is the end of the code generating the "submit payment" button.    -->
?>
<br /><div style="display:block;"><img src="<?php echo EVR_PLUGINFULLURL?>/img/logo_authorize.gif"  /></div>
<?php
if ($company_options['use_authorize_sandbox'] == "Y"){
    ?>
    <h3>Authorize.Net Sandbox Field Output:</h3>
<table width="98%" border="1" cellpadding="2" cellspacing="0">
  <tr>
    <td bgcolor="black"><b><font color="white">Field Name</font></b></td>
    <td bgcolor="black"><b><font color="white">Value</font></b></td>
  </tr>
  <tr>
    <td>ID Key</td>
    <td><?php echo $loginID;?></td>
  </tr>
  <tr>
    <td>Txn Key</td>
    <td><?php echo $transactionKey;?></td>
  </tr>
  <tr>
    <td>Submit URL</td>
    <td><?php echo $url;?></td>
  </tr>
  <tr>
    <td>Description</td>
    <td><?php echo $description;?></td>
  </tr>
  <tr>
    <td>Amount</td>
    <td><?php echo $amount;?></td>
  </tr>
  <tr>
    <td>Email</td>
    <td><?php echo $email;?></td>
  </tr>
  <tr>
    <td>IPN Url</td>
    <td><?php echo evr_permalink($company_options['evr_page_id']).'id='.$attendee_id.'&event_id='.$event_id.'&action=authorize_txn';?></td>
  </tr>
  <tr>
    <td>Invoice</td>
    <td><?php echo $invoice;?></td>
  </tr>
    <tr>
    <td>Timestamp</td>
    <td><?php echo $timeStamp;?></td>
  </tr>
    <tr>
    <td>Sequence</td>
    <td><?php echo $sequence;?></td>
  </tr>
    <tr>
    <td>Fingerprint</td>
    <td><?php echo $fingerprint;?></td>
  </tr>
</table>
    <?php
}
}
//End Authorize.Net Section 
//GooglePay Payment Section
    if ($company_options['payment_vendor']=="GOOGLE"){
        // Create the HTML Payment Button
    //Google Payment Button
    ?>
     <form action="https://checkout.google.com/api/checkout/v2/checkoutForm/Merchant/<?php echo $company_options['google_id'];?>" id="BB_BuyButtonForm" method="post" name="BB_BuyButtonForm" target="_top">
    <input name="item_name_1" type="hidden" value="<?php echo $event_name."-".$attendee_name;?>"/>
    <input name="item_description_1" type="hidden" value="<?php echo $event_name . ' | Reg. ID: '.$attendee_id. ' | Name: '. $attendee_name .' | Total Registrants: '.$quantity;?>"/>
    <input name="item_quantity_1" type="hidden" value="1"/>
    <input name="item_price_1" type="hidden" value="<?php echo $payment;?>"/>
        <input name="item_currency_1" type="hidden" value="<?php echo $company_options['default_currency'];?>"/>
    <input name="_charset_" type="hidden" value="utf-8"/>
    <input alt="" src="https://checkout.google.com/buttons/buy.gif?merchant_id=<?php echo $company_options['payment_vendor_id'];?>&amp;w=117&amp;h=48&amp;style=trans&amp;variant=text&amp;loc=en_US" type="image"/>
    </form>
    <?php
}
//End Google Pay Section
//Begin Monster Pay Section
if ($company_options['payment_vendor']=="MONSTER"){
//Display Payment Button
?>    
<form action="https://www.monsterpay.com/secure/index.cfm" method="POST" enctype="APPLICATION/X-WWW-FORM-URLENCODED" target="_BLANK">
<input type="hidden" name="ButtonAction" value="buynow">
<input type="hidden" name="MerchantIdentifier" value="<?php echo $company_options['monster_id'];?>">
<input type="hidden" name="LIDDesc" value="<?php echo $event_name . ' | Reg. ID: '.$attendee_id. ' | Name: '. $attendee_name .' | Total Registrants: '.$quantity;?>">
<input type="hidden" name="LIDSKU" value="<?php echo $event_name."-".$attendee_name;?>">
<input type="hidden" name="LIDPrice" value="<?php echo $payment;?>">
<input type="hidden" name="LIDQty" value="1">
<input type="hidden" name="CurrencyAlphaCode" value="<?php echo $company_options['default_currency'];?>">
<input type="hidden" name="ShippingRequired" value="0">
<input type="hidden" name="MerchRef" value="">
<input type="submit" value="<?php echo $$pay_now;?>" style="background-color: #DCDCDC; font-family: Arial; font-size: 11px; color: #000000; font-weight: bold; border: 1px groove #000000;">
</form> 
<?php   
}
//End Monster Pay Section
	}
}
?>