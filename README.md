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
+ [TDB 335.62](https://github.com/TrinityCore/TrinityCore/releases/tag/TDB335.62)
+ Tools require cmake: Please refer to the individual repositories for detailed information
 + [MPQExtractor](https://github.com/Sarjuuk/MPQExtractor)
 + [BLPConverter](https://github.com/Sarjuuk/BLPConverter)
 + [FFmpeg](https://ffmpeg.org/download.html)
 + WIN users may find it easier to use these alternatives
   + [MPQEditor](http://www.zezula.net/en/mpq/download.html) / [BLPConverter](https://github.com/PatrickCyr/BLPConverter) / [SoX](https://sourceforge.net/projects/sox/files/sox/)

audio processing may require [lame](https://sourceforge.net/projects/lame/files/lame/3.99/) or [vorbis-tools](https://www.xiph.org/downloads/) (which may require libvorbis (which may require libogg))


#### Highly Recommended
+ setting the following configuration values on your TrintyCore server will greatly increase the accuracy of spawn points
> Calculate.Creature.Zone.Area.Data = 1  
> Calculate.Gameoject.Zone.Area.Data = 1


## Install

1. Acquire this repository `git clone git@github.com:Sarjuuk/aowow.git aowow`
2. Acquire the required tool MPQExtractor: `git clone git@github.com:Sarjuuk/MPQExtractor.git MPQExtractor`
3. Prepare the DB, check that the account you are going to use has **full** access on the database AoWoW is going to occupy and ideally only **read** access on the world database you are going to reference.
  import `setup/db_structure.sql` into the AoWoW-DB `mysql -p {your-db-here} < setup/db_structure.sql`
4. see to it, that the web server is able to write the following directories: `cache/`, `static/` and `config/`
5. compile the MPQExtractor
  extract the following directories from the client archives into `setup/mpqdata/`, while maintaining patch order (suffix: 1 -> 9 -> A -> Z)
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
   > \<localeCode>/Sounds/  
   
   .. optionaly (for other uses):
   > \<localeCode>/Interface/GLUES/LOADINGSCREENS/  
6. reencode the audio files. WAV-files need to be reencoded as `ogg/vorbis` and some MP3s may identify themselves as `application/octet-stream` instead of `audio/mpeg`.
   example for ffmpeg
   ```
   cd path/to/mpqdata/<localeCode>  
   find -name "*.wav" -exec ffmpeg -hide_banner -y -i {} -acodec libvorbis {}.ogg \;          # file.wav -> file.wav.ogg  
   find -name "*.mp3" -exec ffmpeg -hide_banner -y -i {} -f mp3 -acodec libmp3lame {}.mp3 \;  # file.mp3 -> file.mp3.mp3  
   ```

7. run the initial setup from the CLI `php aowow --firstrun`. It should guide you through with minimal input required from your end.
  This will take some time though, especially compiling the zone-images. Use it to familiarize yourself with the other functions this setup has. Yes, I'm dead serious: *Go read the code!* It will help you understand how to configure AoWoW and keep it in sync with your world database.


## Troubleshooting

Q: The Page appears white, without any styles.
A: The static content is not being displayed. You are either using SSL and AoWoW is unable to detect it or STATIC_HOST is not defined poperly. Either way this can be fixed via config `php aowow --siteconfig`

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
