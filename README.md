# product-model-php-plugin
This repository hosts code for the php plugin component to https://github.com/osrgroup/product-model-toolkit

## PHP scanner
Creates a file called phpScanner.json that contains information about licenses by using composer.

Before running: replace the uppercase placeholders in the `config.json` with the data that should be displayed in the result JSON file.

Run: `phpScanner.php --sourcedir=<path/to/scanned/folder> --outputdir=<path/to/output/folder>`

## Docker container
This component comes with a docker container that allows you to scan arbitrary php applications or libraries

### Download and installation
* Clone this repository
* Switch to the `containers` folder.
* Build the docker image with `docker build -t php-scanner .`
* *(Optional)* Create mountable folders for source and output with `mkdir source` and `mkdir output`. *Note:* you can place these folders anywhere in your filesystem.
* *(Optional)* Clone the repository to be scanned into the `source` folder

### Run the scanner
To run the scanner, use this command from the repository root folder: 

`sudo docker run -e USE_DEFAULT_REPO=0 -v $PWD/source:/source -v $PWD/output:/output php-scanner`

The source folder needs to be mounted as `/source`. The folder where the `phpScanner.json` output file should go needs to be mounted as `/output`. 

For testing reasons, you can switch on a default repo with `-e USE_DEFAULT_REPO=1`. Then, a standard repository is cloned and analyzed.

After a successful test run, you should find a file `phpScanner.json` in your `output` folder.
