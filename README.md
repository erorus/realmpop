# Realm Pop

[Realm Pop](https://realmpop.com) shows you population statistics on the players' characters in World of Warcraft.

## System Requirements

Realm Pop is currently hosted on a 1GB [Linode](https://www.linode.com/) VPS, using:
 - CentOS 6.7
 - Lighttpd 1.4.35
 - MySQL 5.5
 - Memcached 1.4.4
 - PHP 5.5

## How It Works

It's assumed that this repo lives in `/var/realmpop`. The MySQL table schema is in tables.sql. The `public` directory is the HTTP root.

Start with running `scripts/realms2houses.php` to populate your realms table. It tries to figure out which realms are connected by looking at the AH data.
**Note:** Realms2houses is probably broken since Blizzard included multiple realm names in the AH data. I haven't fixed this yet.

A cron job kicks off `scripts/fetchandparse.php` every minute. Only one copy runs at a time. It picks a realm to fetch its auction house data, then uses that to crawl characters and guilds.

Once every few days, cron starts `scripts/buildfiles.sh` which will run queries against mysql to assemble the json data and rebuild the html files for each realm.

*The web server (Lighttpd) does not need to run PHP.* All the PHP code runs from the CLI, and the web server just serves up static files.

## How am I expected to clone this?

You aren't, not really. I don't really expect Realm Pop to be forked/cloned, and this is more for reference for your own Battle.net projects (or just for curiosity).

## License

All source code for Realm Pop is licensed under the Apache License, Version 2.0 (the "License");
you may not use these files except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
