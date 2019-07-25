<?php
require_once("vendor/autoload.php");
require_once("class.curl.php");

require_once("helper.php");


$helper = new Helper();
$helper->canvasURL = "umn.instructure.com";
$helper->canvasId = "XXX"; // the ID of the user in canvas
$helper->enrollmentTerm = 00; // the current enrollment term.  Can be harvested from the provisioning report.  A two digit number.
$helper->canvasToken = "xxxx"; // the token for your user - created in the developer menu
$helper->sheetID = "xxx"; // ID of the google sheet - taken from the URL - something like 1YfVBjAO33Dc9zFL5heiCnMvZk-f1u_tU6NLdH4JVWzk


$provisioningSheetName = "F19 Raw Provisioning Course Data";
$unusedSheetName = "F19 Raw Unused Course Data";

$provisioningReportName = "provisioning_csv";
$unusedCoursesName = "unused_courses_csv";


$response = $helper->runReport($provisioningReportName);

$reportId = $response->id;

echo "Provisioning Report ID:" . $reportId . "\n";
$responseURL = $helper->getReport($provisioningReportName, $reportId);
$helper->uploadReport($provisioningReportName, $provisioningSheetName);

$response = $helper->runReport($unused_courses_csv);

$reportId = $response->id;

echo "Unused Report ID:" . $reportId . "\n";
$responseURL = $helper->getReport($unused_courses_csv, $reportId);
$helper->uploadReport($unused_courses_csv, $unusedSheetName);
