<?php

require __DIR__ . '/vendor/autoload.php';
use Composer\Spdx\SpdxLicenses;

class PhpScanner {
	function execute() {
		$options = getopt( "", ["sourcedir:", "outputdir::"] );
		$composerPath = $options['sourcedir'];
		$outputDir = $options['outputdir'] ? $options['outputdir'] : ".";

		$strJsonFileContents = file_get_contents( "config.json" );
		$arrayJsonFileContents = json_decode( $strJsonFileContents, true );

		$spdxLicense = new SpdxLicenses();

		$scannerArray = $this->getScannerArray( $arrayJsonFileContents, $spdxLicense, $composerPath );
		$scannerJson = json_encode( $scannerArray, JSON_PRETTY_PRINT );


		$phpScannerFile = fopen( $outputDir . "/phpScanner.json", "w" );
		fwrite( $phpScannerFile, $scannerJson );
		fclose( $phpScannerFile );
		return $scannerJson;
	}

	function getScannerArray( $config, $spdxLicense, $composerPath ) {
		$arrayObj = [
			"version" => $config['version'],
			"description" => $config['description'],
			"hompageUrl" => $config['homepageUrl'],
			"license" => $config['license'],
			"components" => $this->getComponents( $spdxLicense, $composerPath ),
			// There is more data available from composer. Leaving this here for future
			// reference
			//
			//"id" => $config['id'],
			//"vcs" => $config['vcs'],
			//"comment" => $config['comment'],
			//"externalRef" => $config['externalRef'],
			//"usageTypes" => $config['usageType'],
			//"clearingState" => "",
			//"depGraph" => "",
			//"infrastructure" => "",
		];
		return $arrayObj;
	}

	function getComponents( $spdxLicense, $composerPath ) {
		$processedComponentsArray = array();
		$componentsJson = shell_exec( "composer licenses --format=json --working-dir=" . $composerPath );

		$componentsArray = json_decode( $componentsJson, true );
		$componentsArray = $componentsArray["dependencies"];

		//adds all the components that are within the initial component to a array
		foreach ( $componentsArray as $component ){
			$name = key( $componentsArray );
			$version = $component["version"];
			$licenseData = array();
			$spdxId = "no license found";
			$declaredLicense = "";

			if( array_key_exists( 0, $component["license"] ) ) {
				$spdxId = $component["license"][0];
				$licenseData = $this->getLicense( $component["license"][0], $spdxLicense );
				if( array_key_exists( 0, $licenseData ) ){
					$declaredLicense = $licenseData[0];
				} else {
					$declaredLicense = "the spdx plugin could not find a matching license";
				}
			}

			$dependencies = $this->getDependencyTree( $name, $composerPath );

			$componentObj = $this->createComponent( $name, $version, $spdxId, $declaredLicense, $dependencies );
			array_push( $processedComponentsArray, $componentObj );
			array_shift( $componentsArray );
		}
		return $processedComponentsArray;
	}

	function getLicense( $license, $spdxLicense ) {
		$licenseDataArray = array();
		$licenseData = $spdxLicense->getLicenseByIdentifier( $license );
		if ( $licenseData != null ) {
			$licenseDataArray = $licenseData;
		}
		return $licenseDataArray;
	}

	function getDependencyTree( $name, $composerPath ) {
		$dependencyJson = shell_exec( "composer show --tree --format=json --working-dir=" . $composerPath . " " . $name );
		$dependencyArray = json_decode( $dependencyJson, false );

		$deps = [];

		if ( empty( $dependencyArray->installed[0]->requires ) ) {
			return $deps;
		}

		foreach ( $dependencyArray->installed[0]->requires as $dependency ) {
			//echo $dependency->name;
			$deps[] = $dependency->name;
		}

		return $deps;
	}

	function createComponent( $name, $version, $spdxId, $declaredLicense, $dependencies ) {
		$componentObj = [
			//"id" => "",
			"name" => "",
			"package" => $name,
			"version" => $version,
			"license" => [
				"spdxId" => $spdxId,
				"declaredLicense:" => $declaredLicense,
				"concludedLicense:" => ""
			],
			"requires" => $dependencies
		];
		return $componentObj;
	}
}

$scanner = new PhpScanner();
echo $scanner->execute();