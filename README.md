![logo](static/images/logos/home.png)


## Build Status
![fuck it ship it](http://forthebadge.com/images/badges/fuck-it-ship-it.svg)


## Introduction

AoWoW is a Database tool for World of Warcraft v3.3.5 (build 12340)
It is based upon the other famous Database tool for WoW, featuring the red smiling rocket.
While the first releases can be found as early as 2008, today it is impossible to say who created this project.
This is a complete rewrite of the serverside php code and update to the clientside javascripts from 2008 to something 2013ish.

I myself take no credit for the clientside scripting, design and layout that these php-scripts cater to.
Also, this project is not meant to be used for commercial puposes of any kind!


## Requirements

+ Webserver running PHP ≥ 5.5.0 including extensions:
  + SimpleXML
  + GD
  + Mysqli
  + mbString
+ MySQL ≥ 5.5.30
+ [TDB 335.63](https://github.com/TrinityCore/TrinityCore/releases/tag/TDB335.63) - including world updates up to 04.05.2017
+ Tools require cmake: Please refer to the individual repositories for detailed information
  + [MPQExtractor](https://github.com/Sarjuuk/MPQExtractor) / [FFmpeg](https://ffmpeg.org/download.html) / [BLPConverter](https://github.com/Sarjuuk/BLPConverter) (optional)
  + WIN users may find it easier to use these alternatives
     + [MPQEditor](http://www.zezula.net/en/mpq/download.html) / [FFmpeg](http://ffmpeg.zeranoe.com/builds/) / [BLPConverter](https://github.com/PatrickCyr/BLPConverter) (optional)

audio processing may require [lame](https://sourceforge.net/projects/lame/files/lame/3.99/) or [vorbis-tools](https://www.xiph.org/downloads/) (which may require libvorbis (which may require libogg))


#### Highly Recommended
+ setting the following configuration values on your TrintyCore server will greatly increase the accuracy of spawn points
  > Calculate.Creature.Zone.Area.Data = 1  
  > Calculate.Gameoject.Zone.Area.Data = 1


## Install

#### 1. Acquire the required repositories
`git clone git@github.com:Sarjuuk/aowow.git aowow`  
`git clone git@github.com:Sarjuuk/MPQExtractor.git MPQExtractor`  

#### 2. Prepare the database  
Ensure that the account you are going to use has **full** access on the database AoWoW is going to occupy and ideally only **read** access on the world database you are going to reference.  
Import `setup/db_structure.sql` into the AoWoW database `mysql -p {your-db-here} < setup/db_structure.sql`  

#### 3. Server created files
See to it, that the web server is able to write the following directories and their children. If they are missing, the setup will create them with appropriate permissions
 * `cache/`
 * `config/`
 * `static/download/`
 * `static/widgets/`
 * `static/js/`
 * `static/uploads/`
 * `static/images/wow/`
 * `datasets/`  
 
#### 4. Extract the client archives (MPQs)
Extract the following directories from the client archives into `setup/mpqdata/`, while maintaining patch order (base mpq -> patch-mpq: 1 -> 9 -> A -> Z). The required paths are scattered across the archives. Overwrite older files if asked to.  
   .. for every locale you are going to use:
   > \<localeCode>/DBFilesClient/  
   > \<localeCode>/Interface/WorldMap/  
   > \<localeCode>/Interface/FrameXML/GlobalStrings.lua  
   
   .. once is enough (still apply the localeCode though):
   > \<localeCode>/Interface/TalentFrame/  
   > \<localeCode>/Interface/Glues/Credits/  
   > \<localeCode>/Interface/Icons/  
   > \<localeCode>/Interface/Spellbook/  
   > \<localeCode>/Interface/PaperDoll/  
   > \<localeCode>/Interface/GLUES/CHARACTERCREATE/  
   > \<localeCode>/Interface/Pictures  
   > \<localeCode>/Interface/PvPRankBadges  
   > \<localeCode>/Interface/FlavorImages  
   > \<localeCode>/Interface/Calendar/Holidays/  
   > \<localeCode>/Sound/  
   
   .. optionaly (not used in AoWoW):
   > \<localeCode>/Interface/GLUES/LOADINGSCREENS/  

#### 5. Reencode the audio files
WAV-files need to be reencoded as `ogg/vorbis` and some MP3s may identify themselves as `application/octet-stream` instead of `audio/mpeg`.  
 * [example for WIN](https://gist.github.com/Sarjuuk/d77b203f7b71d191509afddabad5fc9f)  
 * [example for \*nix](https://gist.github.com/Sarjuuk/1f05ef2affe49a7e7ca0fad7b01c081d)

#### 6. Run the initial setup from the CLI
`php aowow --firstrun`.  
This should guide you through with minimal input required from your end, but will take some time though, especially compiling the zone-images. Use it to familiarize yourself with the other functions this setup has. Yes, I'm dead serious: *Go read the code!* It will help you understand how to configure AoWoW and keep it in sync with your world database.  
When you've created your admin account you are done.


## Troubleshooting

Q: The Page appears white, without any styles.  
A: The static content is not being displayed. You are either using SSL and AoWoW is unable to detect it or STATIC_HOST is not defined poperly. Either way this can be fixed via config `php aowow --siteconfig`

Q: Fatal error: Can't inherit abstract function \<functionName> (previously declared abstract in \<className>) in \<path>  
A: You are using cache optimization modules for php, that are in confict with each other. (Zend OPcache, XCache, ..) Disable all but one.

Q: Some generated images appear distorted or have alpha-channel issues.  
A: Image compression is beyond my understanding, so i am unable to fix these issues within the blpReader.
 BUT you can convert the affected blp file into a png file in the same directory, using the provided BLPConverter.
 AoWoW will priorize png files over blp files.

Q: How can i get the modelviewer to work?  
A: You can't anymore. Wowhead switched from Flash to WebGL (as they should) and moved or deleted the old files in the process.


## Thanks

@mix: for providing the php-script to parse .blp and .dbc into usable images and tables
@LordJZ: the wrapper-class for DBSimple; the basic idea for the user-class
@kliver: basic implementation of screenshot uploads


## Special Thanks
Said website with the red smiling rocket, for providing this beautifull website!
Please do not reagard this project as blatant rip-off, rather as "We do really liked your presentation, but since time and content progresses, you are sadly no longer supplying the data we need".

![uses badges](http://forthebadge.com/images/badges/uses-badges.svg)
