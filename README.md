# Steam-Zero
Steam achivements statistics script.

## Usage
1. Set up a web server with this PHP-page. Steam API doesn't work from a browser directly anymore because CORS restrictions.
1. Register for API-key at https://steamcommunity.com/dev/apikey
  * Docs for Steam API are at https://steamcommunity.com/dev
  * Script works with public accounts only.
1. Go to your own url, enter an username and enjoy!

## History
### v0.10
* Re-did the whole thing with a PHP-piggyback to Steam API to navigate around CORS.
### v0.09
* Added information about recent games.
* Redesigned statistics.
* Display of digits and dates now corresponds to locale.
### v0.08
* Round persision fix.
* Achievements table sorting.
### v0.07
Base functions:
* Gaining player achievement statistics.
* Saving statistics to the LocalStorage for quick loading.
* Statistics update button (statistics on the game is not updated automatically).
* Display the process of collecting statistics in percent. Duration collection depends on the number of games per account.

## P. S.
Forgive for the imperfect code and my bad English.
