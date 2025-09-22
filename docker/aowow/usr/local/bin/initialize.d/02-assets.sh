#!/usr/bin/env sh
set -aeu

download_prepared_assets() {
  echo "Downloading prepared game data assets ..."

  mkdir -p '/var/www/html/setup/mpqdata/interface/framexml' \
           '/var/www/html/setup/mpqdata/enUS/interface/framexml' \
           '/var/www/html/setup/mpqdata/enUS/DBFilesClient'

  curl -sL "$MPQ_DATA_INTERFACE_URL" -o '/var/www/html/setup/mpqdata/interface/framexml/globalstrings.lua'

  # ... there is no need to save full archive and extract all contents, try to extract only required data on fly:
  curl -sL "$EXTRACTED_DATA_ARCHIVE_URL" | bsdtar -xzf - --strip-components=1 -C '/var/www/html/setup/mpqdata/enUS/DBFilesClient' 'dbc/*'
}

if [ "$(ls -A '/var/www/html/setup/mpqdata' | wc -l)" -eq 0 ]; then
  echo "Directory 'setup/mpqdata' is empty"

  # Actually, assets should be extracted from client game data, this method does not perform extraction,
  # but downloads already extracted data.

  download_prepared_assets;
else
  echo "Directory 'setup/mpqdata' is not empty. Skip data download"
fi
