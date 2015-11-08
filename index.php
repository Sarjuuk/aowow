<?php

require 'includes/shared.php';

if (CLI)
    die("this script must not be run from CLI.\nto setup aowow use 'php aowow'\n");


// maybe add additional setup checks?
if (!DB::isConnectable(DB_AOWOW) || !DB::isConnectable(DB_WORLD))
    (new GenericPage($pageCall))->maintenance();


$altClass = '';
switch ($pageCall)
{
    /* called by user */
    case '':                                                // no parameter given -> MainPage
        $altClass = 'home';
    case 'home':
    case 'admin':
    case 'account':                                         // account management [nyi]
    case 'achievement':
    case 'achievements':
    // case 'arena-team':
    // case 'arena-teams':
    case 'class':
    case 'classes':
    case 'currency':
    case 'currencies':
    case 'compare':                                         // tool: item comparison
    case 'emote':
    case 'emotes':
    case 'enchantment':
    case 'enchantments':
    case 'event':
    case 'events':
    case 'faction':
    case 'factions':
    // case 'guild':
    // case 'guilds':
    case 'item':
    case 'items':
    case 'itemset':
    case 'itemsets':
    case 'maps':                                            // tool: map listing
    case 'npc':
    case 'npcs':
    case 'object':
    case 'objects':
    case 'pet':
    case 'pets':
    case 'petcalc':                                         // tool: pet talent calculator
        if ($pageCall == 'petcalc')
            $altClass = 'talent';
    case 'profile':                                         // character profiler [nyi]
    case 'profiles':                                        // character profile listing [nyi]
    case 'profiler':                                        // character profiler main page
    case 'quest':
    case 'quests':
    case 'race':
    case 'races':
    case 'screenshot':                                      // prepare uploaded screenshots
    case 'search':                                          // tool: searches
    case 'skill':
    case 'skills':
    // case 'sound':                                        // db: sounds for zone, creature, spell, ...
    // case 'sounds':
    case 'spell':
    case 'spells':
    case 'talent':                                          // tool: talent calculator
    case 'title':
    case 'titles':
    case 'user':
    case 'video':
    case 'zone':
    case 'zones':
    /* called by script */
    case 'data':                                            // tool: dataset-loader
    case 'cookie':                                          // lossless cookies and user settings
    case 'contactus':
    case 'comment':
    // case 'filter':                                       // just a note: this would be accessed from filtrable pages as ?filter=typeStr (with POST-data) and forwards back to page with GET-data .. why? Hell if i know..
    case 'go-to-comment':                                   // find page the comment is on and forward
    case 'locale':                                          // subdomain-workaround, change the language
        $cleanName = str_replace(['-', '_'], '', ucFirst($altClass ?: $pageCall));
        try                                                 // can it be handled as ajax?
        {
            $class = 'Ajax'.$cleanName;
            $ajax  = new $class(explode('.', $pageParam));
            if ($ajax->handle($out))
            {
                Util::sendNoCacheHeader();

                if ($ajax->doRedirect)
                    header('Location: '.$out, true, 302);
                else
                {
                    header('Content-type: '.$ajax->getContentType());
                    die($out);
                }
            }
            else
                throw new Exception('not handled as ajax');
        }
        catch (Exception $e)                                // no, apparently not..
        {
            $class = $cleanName.'Page';
            (new $class($pageCall, $pageParam))->display();
        }

        break;
    /* other pages */
    case 'whats-new':
    case 'searchplugins':
    case 'searchbox':
    case 'tooltips':
    case 'help':
    case 'faq':
    case 'aboutus':
        (new MorePage($pageCall, $pageParam))->display();
        break;
    case 'latest-additions':
    case 'latest-articles':
    case 'latest-comments':
    case 'latest-screenshots':
    case 'latest-videos':
    case 'unrated-comments':
    case 'missing-screenshots':
    case 'most-comments':
    case 'random':
        (new UtilityPage($pageCall, $pageParam))->display();
        break;
    default:                                                // unk parameter given -> ErrorPage
        if (isset($_GET['power']))
            die('$WowheadPower.register(0, '.User::$localeId.', {})');
        else                                                // in conjunction with a proper rewriteRule in .htaccess...
            (new GenericPage($pageCall))->error();
        break;
}

?>
