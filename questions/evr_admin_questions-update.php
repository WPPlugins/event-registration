<?php
function evr_post_update_question(){
    global $wpdb;
	(is_numeric($_REQUEST['event_id'])) ? $event_id = $_REQUEST['event_id'] : $event_id = "0";
    $event_name = $_REQUEST['event_name'];
    $question_text = $_REQUEST ['question'];
	$question_id = $_REQUEST ['question_id'];
	$question_type = $_REQUEST ['question_type'];
	$values = $_REQUEST ['values'];
	$required = @$_REQUEST ['required'] ? 'Y' : 'N';
	$remark = $_REQUEST['remark'];	
	if ($wpdb->query ( "UPDATE ".get_option('evr_question')." set `question_type` = '$question_type', `question` = '$question_text', " . " `response` = '$values', `required` = '$required', `remark` = '$remark' where id = $question_id " )){
	   ?>
	<div id="message" class="updated fade"><p><strong><?php _e('The question has been updated.','evr_language');?> </strong></p>
    <p><strong><?php _e(' . . .Now returning you to Question Managment . . ','evr_language');?><meta http-equiv="Refresh" content="1; url=admin.php?page=questions&action=new&event_id=<?php echo $event_id;?>&event_name=<?php echo $event_name;?>"></strong></p></div>
    <?php }else { ?>
	<div id="message" class="error"><p><strong><?php _e('There was an error in your submission, please try again. The question was not updated!','evr_language');?><?php print $wpdb->last_error; ?>.</strong></p>
    <p><strong><?php _e(' . . .Now returning you to Question Management . . ','evr_language');?><meta http-equiv="Refresh" content="3; url=admin.php?page=questions&action=new&event_id=<?php echo $event_id;?>&event_name=<?php echo $event_name;?>"></strong></p>
    </div>
<?php } 

	
?>
<META HTTP-EQUIV="refresh" content="0;URL=admin.php?page=questions&action=new&event_id=<?php echo $event_id . "&event_name=" . $event_name;?>">
<?php 
}
?>