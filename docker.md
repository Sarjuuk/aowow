# Run in Docker

Running application in container may be either for production or development environment.

## Setup

Running application requires manual (at this moment) one-time setup.

### Prepare client data

Running application for the first time requires installation:

1. Download game client: [01-retrieve-wotlk-enus-client.sh](https://gist.github.com/unsektor/9777d180e5fbc1e5a3e1cde8e2e86abe#file-01-retrieve-wotlk-enus-client-sh)
2. Extract MPQ data from game client: [02-extract-mpq-data-for-aowow.sh](https://gist.github.com/unsektor/9777d180e5fbc1e5a3e1cde8e2e86abe#file-02-extract-mpq-data-for-aowow-sh)

### Setup application

1. Create environment settings files:
   ```sh
   cp .env.aowow.dist .env.aowow
   cp .env.mysql.dist .env.mysql
   ```
   Typically, `.env.mysql` is not required for production, because database server is hosted somewhere outside container.

   > **Note:** You don't need to change `config/config.php` file, it will be overwritten with a special file, which
   > takes required credentials from the environment. See [docker/aowow/src/config/config.php](./docker/aowow/src/config/config.php)
   > for details.

   > **Note:** If you don't need database server for development environment (for example database is placed on a host), 
   > then make copy of configuration:
   > 
   > ```sh
   > cp compose.dev.yaml compose.override.yaml
   > ```
   > 
   > and then adjust `compose.override.yaml` for your needs (this file is excluded from tracking by VCS).
   > Then use `compose.override.yaml` instead of `compose.dev.yaml` in further examples: 
   >
   > ```sh
   > docker compose -f compose.yaml -f compose.override.yaml
   > # instead of: 
   > # docker compose -f compose.yaml -f compose.dev.yaml 
   > ```
   > 
   > See [Change compose configuration](#change-compose-configuration) for details
2. Edit `.env.*` files according to your environment
3. Launch application setup
   ```sh
   ## ... adjust path to directory with extracted data directory 
   env EXTRACTED_MPQ_DATA_PATH=~/wotlk-enus-extracted-mpqdata \
     docker compose -f compose.yaml -f compose.dev.yaml up
   ```

   What does it do:

   - Database container (see `./mysql/docker-entrypoint-initdb.d/initialize-game-server-databases.sh`):
     - Downloads TrinityCore databases dump and patch files only (uses sparse checkout to retrieve only required files)
     - Creates game server databases (`world`, `characters`, `auth`)
     - Applies game server database patches (required by `AoWoW`)
   - Web-application container (see `./aowow/usr/local/bin/initialize.sh`):
     - Awaits database container initialization done
     - Creates application database from dumps
     - Launches application setup script (enriches database from extracted MPQ files)
     - Finalizes application setup and starts application web-server

   > **Note:** It may take long time (up to 30 minutes and more). Please, be patient.  
   > If it's required to skip initialization at first launch, then set `SETUP_SKIP` option value to `1`.

Then web-application should be accessible at <http://172.28.0.10>.

## Running

After [application setup](#setup-application) is done:

**Run application:**

- for production:
  ```sh
  docker compose -f compose.yaml up
  ```
- for development:
  ```sh
  docker compose -f compose.yaml -f compose.dev.yaml up
  ```

> **Note:** First launch may fail for some reason (e.g. fail to download data, slow network or healthcheck failure),
> it's recommended to rebuild containers for initialization scripts invocation, see [Maintenance](#maintenance) for 
> details.

### Mail server

After application is launched, development SMTP server should be available with web user interface. 
It's used to intercept all emails without it actual sending, useful for debugging user verification logic. 

Web interface should be accessible at <http://172.28.0.12:8025>.

## Maintenance

Rebuild only aowow service (don't wipe database data):

```sh
docker container rm aowow-aowow-1
docker volume rm aowow_setup_aowow
# docker compose -f compose.yaml -f compose.dev.yaml up --build
docker compose -f compose.yaml -f compose.dev.yaml --env-file .env.aowow up --build
```

Full rebuild (wipe all data):

```sh
docker container rm aowow-mysql-1 aowow-aowow-1 
docker volume rm aowow_data_mysql
# docker volume rm aowow_setup_mysql  # ... contains TrinityCore SQL dumps, typically it's never required to be removed 
docker compose -f compose.yaml -f compose.dev.yaml up --build
# PHP_IDE_CONFIG='serverName=aowow' XDEBUG_MODE=on php ./aowow --sql
```

Path to extracted MPQ data. Required for aowow setup. When value is empty, setup script will try to retrieve
prepared data from the internet, see `SETUP_EXTRACTED_DATA_ARCHIVE_URL` variable.

### Add verbosity 

```sh
cat << SQL | mysql "$DB_AOWOW_DB"
UPDATE ${DB_AOWOW_PREFIX}config SET value='3' WHERE \`key\` = 'debug';
SQL
```

### Change SMTP server settings for sendmail

1. Create copy of base configuration
   ```sh
   cp docker/aowow/etc/msmtprc docker/aowow/etc/msmtprc.override
   ```
   This file is already excluded from tracking by VCS.
2. Change mount of configuration in `compose.override.yaml` file
   (see [Change compose configuration](#change-compose-configuration) for details)

   ```yaml
   - ./docker/aowow/etc/msmtprc:/etc/msmtprc:ro
   ```

   to this:

   ```yaml
   - ./docker/aowow/etc/msmtprc.override:/etc/msmtprc:ro 
   ```

## Change compose configuration

It's common practice to override base setup with appropriate `compose.override.yaml` file.
This file is excluded from VCS by default. If it's required to change
settings according to some needs, copy related file and use it, for example:

1. Copy file:
   ```sh
   cp compose.dev.yaml compose.override.yaml
   ```
   This file is already excluded from tracking by VCS.
2. Modify new file according to your needs
3. Use it, for example:
   ```sh
   docker compose -f ./compose.yaml -f ./compose.override.yaml
   ```
