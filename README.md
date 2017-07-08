# devsLife-Bot

Simple push-only (enabled via cron) notication Discord bot that notifies whenever a new YouTube video is uploaded or live stream is started.


## Setup and Customization

### Prerequisites

 * PHP 5.6 or above
 * [Composer](https://getcomposer.org/)
 * MariaDB/MySQL (for production only)
 * Enable [YouTube Data API](https://console.developers.google.com/start/api?id=youtube)
 * Create [Discord](https://discordapp.com/developers/applications/) application with bot user access and [grant](https://discordapp.com/developers/docs/topics/oauth2#adding-bots-to-guilds) it access to your Discord server.
 * Enable developer mode on your Discord account. *User Settings* -> *Appearance* -> *Enable Developer Mode*

### Development Mode

For local development, test the bot using the following:

`php devsLife-Bot.php --dev`


#### References:

 * [https://developers.google.com/api-client-library/php/start/get_started](https://developers.google.com/api-client-library/php/start/get_started)
 * [https://developers.google.com/youtube/v3/docs/channels](https://developers.google.com/youtube/v3/docs/channels)
 * [https://github.com/restcord/restcord](https://github.com/restcord/restcord)

## Example
![devsLife-Bot Image](http://i.imgur.com/mLMaoE2.png)