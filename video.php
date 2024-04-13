<?php 
require_once 'vendor/autoload.php';
use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;

$key = "";
$secret = "";
$region = "";
$bucket = "";
$folder = "";
$role = "";
$queve = "";

//Creamos objeto MediaConvert del SDK
$client = new MediaConvertClient([
    'profile' => 'default',
    'version' => '2017-08-29',
    'credentials' => [
        'key' => $key,
        'secret' => $secret,
    ],
    'region' => $region
]);

//Obtener el endPoint de MediaConvert
try {
    $result = $client->describeEndpoints([]);
} catch (AwsException $e) {
    echo $e->getMessage();
    echo "\n";
}
$single_endpoint_url = $result['Endpoints'][0]['Url'];

//Configuramos un MediaConvertClient

$mediaConvertClient = new MediaConvertClient([
    'version' => '2017-08-29',
    'region' => $region,
    'profile' => 'default',
    'endpoint' => $single_endpoint_url
]);
//VIDEO
$video_name = "video02.mp4";
//JSON Tem,plate
$jobSetting  = json_decode(file_get_contents("job.json"), true);

$origin = sprintf('%s/original/%s',$bucket,$video_name);
$FileInput = sprintf('s3://%s',$origin);

$jobSetting["Inputs"][0]["FileInput"] = $FileInput;
$destination = sprintf('s3://%s/%s/%d/playlist',$bucket,$folder,time());
$jobSetting["OutputGroups"][0]["OutputGroupSettings"]["HlsGroupSettings"]["Destination"] = $destination;

try {
    $result = $mediaConvertClient->createJob([
        "Role" => $role,
        "Settings" => $jobSetting,
        "Queue" => $queve,
        "UserMetadata" => [
            "Customer" => "Amazon"
        ],
    ]);
    $result = $result->toArray();
    var_dump(array("job_id" => $result["Job"]["Id"]));
} catch (AwsException $e) {
    echo $e->getMessage();
}

 ?>