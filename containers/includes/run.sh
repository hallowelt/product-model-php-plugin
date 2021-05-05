#!/bin/bash
export NODE_TLS_REJECT_UNAUTHORIZED=0
cd /opt/workspace
rm -rf *

if [ $USE_DEFAULT_REPO -eq 1 ]; then
	git clone https://github.com/wikimedia/mediawiki-extensions-BlueSpiceFoundation.git /opt/workspace/target
fi
cd /opt/workspace/target
composer update --ignore-platform-reqs

cd /opt/workspace
git clone https://github.com/hallowelt/product-model-php-plugin.git /opt/workspace/scanner
cd /opt/workspace/scanner
composer update --ignore-platform-reqs

cp config.template.json config.json
php phpScanner.php /opt/workspace/target
