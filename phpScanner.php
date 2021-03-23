<?php

require_once (__DIR__ . "/vendor/composer/spdx-licenses/src/SpdxLicenses.php");
use Composer\Spdx\SpdxLicenses;

$strJsonFileContents = file_get_contents("config.json");
$arrayJsonFileContents = json_decode($strJsonFileContents, true);
$id = $arrayJsonFileContents["id"];
$version = $arrayJsonFileContents["version"];
$vcs = $arrayJsonFileContents["vcs"];
$description = $arrayJsonFileContents["description"];
$comment = $arrayJsonFileContents["comment"];
$homepageUrl = $arrayJsonFileContents["homepageUrl"];
$externalRef = $arrayJsonFileContents["externalRef"];
$usageType = $arrayJsonFileContents["usageType"];

$spdxLicense = new SpdxLicenses();

$scannerArray = getScannerArray($id, $version, $vcs, $description, $comment, $homepageUrl, $externalRef, $usageType, $spdxLicense);
$scannerJson = json_encode($scannerArray, true);

$phpScannerFile = fopen("phpScanner.json", "w");
fwrite($phpScannerFile, $scannerJson);
fclose($phpScannerFile);

function getScannerArray($id, $version, $vcs, $description, $comment, $homepageUrl, $externalRef, $usageType, $spdxLicense){
    $arrayObj = [
        "id" => $id,
        "version" => $version,
        "vcs" => $vcs,
        "description" => $description,
        "comment" => $comment,
        "hompageUrl" => $homepageUrl,
        "externalRef" => $externalRef,
        "components" => getComponents($spdxLicense),
        "usageTypes" => $usageType,
        "clearingState" => "", 
        "depGraph" => "", 
        "infrastructure" => "",
    ];
    return $arrayObj;
}

function getComponents($spdxLicense){
    $processedComponentsArray = array();
    $componentsJson =  shell_exec("composer licenses --format=json");
    $componentsArray = json_decode($componentsJson, true);
    $componentsArray = $componentsArray["dependencies"];

    //adds all the components that are within the initial component to a array
    foreach ($componentsArray as $component){
        $name = key($componentsArray);
        $version = $component["version"];
        $licenseData = array();
        $spdxId = "no license found";
        $declaredLicense = "";

        if(array_key_exists(0, $component["license"])){
            $spdxId = $component["license"][0];
            $licenseData = getLicense($component["license"][0], $spdxLicense);
            if(array_key_exists(0, $licenseData)){
                $declaredLicense = $licenseData[0];
            }else{
                $declaredLicense = "the spdx plugin could not find a matching license";
            }
        }

        $componentObj = createComponent($name, $version, $spdxId, $declaredLicense);
        array_push($processedComponentsArray, $componentObj);
        array_shift($componentsArray);
    }   
    return $processedComponentsArray;
}

function getLicense($license, $spdxLicense){
    $licenseDataArray = array();
    $licenseData = $spdxLicense->getLicenseByIdentifier($license);
    if($licenseData != null){
        $licenseDataArray = $licenseData;
    }
    return $licenseDataArray;
}

function createComponent($name, $version, $spdxId, $declaredLicense){
    $componentObj = [
        "id" => "",
        "name" => "",
        "package" => $name,
        "version" => $version,
        "license" => [
            "spdxId" => $spdxId,
            "declaredLicense:" => $declaredLicense,
            "concludedLicense:" => ""
        ]
    ];
    return $componentObj;
}