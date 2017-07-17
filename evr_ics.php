<?php
/**
 * Calendar File for Event Registration Plugin for Wordpress
 * Copyright 2011  David F. Fleming
 * 
 **/
$curdate = date("Ymd");
$curtime = date("His");

 session_start();
 $event = $_SESSION['event_array'];

    $event_id       = $event->id;
	$event_name     = html_entity_decode(stripslashes($event->event_name),ENT_NOQUOTES, 'UTF-8');
	$event_identifier = stripslashes($event->event_identifier);
	$event_location = html_entity_decode(stripslashes($event->event_location),ENT_NOQUOTES, 'UTF-8');
    $event_desc     = html_entity_decode(stripslashes($event->event_desc),ENT_NOQUOTES, 'UTF-8');
    $event_address  = $event->event_address;
    $event_city     = $event->event_city;
    $event_state     = $event->event_state;
    $event_postal   = $event->event_postal;
    $reg_limit      = $event->reg_limit;
	$start_time     = $event->start_time;
	$end_time       = $event->end_time;
	$conf_mail      = $event->conf_mail;
	$start_date     = $event->start_date;
	$end_date       = $event->end_date;

header("Content-Type: text/Caledar");
header("Content-Disposition: inline; filename=".rawurlencode($event_name).".ics");
echo "BEGIN:VCALENDAR\n";
echo "BEGIN:VEVENT\n";
echo "CLASS:PUBLIC\n";
echo "CREATED:".$curdate."T".$curtime."\n";
echo "DESCRIPTION:".$event_desc."\n";
echo "DTEND:".date("Ymd",strtotime($end_date))."T".date("His",strtotime($end_time))."\n";
echo "DTSTAMP:".$curdate."T".$curtime."\n";;
echo "DTSTART:".date("Ymd",strtotime($start_date))."T".date("His",strtotime($start_time))."\n";
echo "LAST-MODIFIED:20091109T101015Z\n";
echo "LOCATION:".$event_location.", ".$event_address.", ".$event_city.", ".$event_state.", ".$event_postal."\n";;
echo "SUMMARY;LANGUAGE=en-us:".$event_name."\n";
echo "BEGIN:VALARM\n";
echo "TRIGGER:-PT1440M\n";
echo "ACTION:DISPLAY\n";
echo "DESCRIPTION:Reminder\n";
echo "END:VALARM\n";
echo "END:VEVENT\n";
echo "END:VCALENDAR\n";
?>