<?php


class Helper {

    public $canvasId = null;
    public $canvasURL = null;
    public $canvasToken = null;
    public $sheetID = null;
    public $enrollmentTerm = null;

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = $this->expandHomeDirectory('credentials.json');
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path)
{
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}


function runReport($reportName) {
    $curl = curl_init();

    // could not get this working with the canvas php helper
    // 
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://" . $this->canvasURL . "/api/v1/accounts/" . $this->canvasId . "/reports/" . $reportName,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"parameters[enrollment_term_id]\"\r\n\r\n$this->enrollmentTerm\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"parameters[courses]\"\r\n\r\n1\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $this->canvasToken,
        "Cache-Control: no-cache",
        "Content-Type: multipart/form-data",
        "Postman-Token: 6b1bfad3-d36a-4c6c-add8-756b419ae8df",
        "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
    ),
  ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);


    if ($err) {
      echo "cURL Error #:" . $err;
  } else {
      $response = json_decode($response);
  }
  return $response;

}

function getReport($reportName, $reportId) {
    $token = "Authorization: Bearer " . $this->canvasToken;
      // Update to reflect the address to your institute
    $cURL = new Curl($token, $this->canvasURL);


    $targetURL = null;
    $i = 0;

    while($i< 100) {

        $status = $cURL->get("/accounts/" . $this->canvasId . "/reports/" . $reportName . "/" . $reportId);
        if($status[0]->status == "complete") {
            $targetURL = $status[0]->attachment->url;
            break;
        }  
        if($status[0]->status == "error") {
            break;
        }

        sleep(1);
        $i++;
    }


    if(!$targetURL) {
        echo "Error generating report\n";
        die;
    }


    exec("curl -o $reportName.csv -L -J '" . $targetURL . "' 2> /dev/null") ;

}



function uploadReport($report, $sheet) {

    $fp = fopen($report . ".csv", "r");


// Get the API client and construct the service object.
    $client = $this->getClient();

    $service = new Google_Service_Sheets($client);

// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit
    


    $range = $sheet;

    $clearRange = new Google_Service_Sheets_ClearValuesRequest();
    $service->spreadsheets_values->clear($this->sheetID, $range, $clearRange);

    $conf = ["valueInputOption" => "RAW"];



    $valueRange= new Google_Service_Sheets_ValueRange();

    $fileArray = array();
    while($line = fgetcsv($fp)) {
        $fileArray[] = $line;
    }  



    $valueRange->setValues($fileArray); 
    $service->spreadsheets_values->update($this->sheetID, $range, $valueRange, $conf);


}
}