<?php
/*
    Blacklist Mediawiki Extension
    By Jeremy Pyne  jeremy dot pyne at gmail dot com
 
    This extension adds support for a $wgBlacklist array, layed out like $wgGroupPermissions, to support overrides.
        For example I can set $wgBlacklist['*']['read']  to diable specific special pages or
        make some pages of the site only visible for special groups.
    This blacklisting is done from lowest to highest powered groups and is implisit.  IE if you deny Main Page to User, it also denies it for all parent's of user.
        To override a blacklist at a higher level ou have to add an entry to $$wgWhitelist['sysop']['read'] to re-enable the pages if you are a sysop.
 
    Options: 
        $wgBlacklistOps["useRegex"] = true;
                This setting dictates whether to tread the page lists as regular expressions or not.  Though turning regular expressions off is much faster, you can not 
                mark page groups, partial page titles, or variations of title formating.
 
    Example: To block some special pages for normal users, but not sysops do this.
        $wgWhitelist['sysop']['read']  = $wgBlacklist['*']['read'] = array("Special:Export", "Special:Listusers", "Special:Ipblocklist", "Special:Log", "Special:Allmessages");
    Or wth a RegEx
        $wgBlacklistOps["useRegex"] = true;
        $wgWhitelist['sysop']['read'] = $wgBlacklist['*']['read'] = array("^Special:(Export|Listusers|Ipblocklist|Log|Allmessages)$");
 
     Note: This is not flawless method as page inclusions and such can get around this.
*/
 
if (!defined('MEDIAWIKI')) die();

$wgExtensionCredits['other'][] = array(
    'name' => 'blacklist',
    'description' => 'adds $wgBlacklist array to provide blacklist overrides',
    'url' => 'https://www.mediawiki.org/wiki/Extension:Blacklist',
    'author' => 'Jeremy Pyne',
    'version' => '1.0'
);
 
$wgHooks['userCan'][] = 'checkBlacklist';
 
/**
 * Is this page blacklisted
 * @param &$title the concerned page
 * @param &$wgUser the current mediawiki user
 * @param $action the action performed
 * @param &$result (out) true or false, or null if we don't care about the parameters
 */
function checkBlacklist(&$title, &$wgUser, $action, &$result) {
        global $wgBlacklist;
        global $wgWhitelist;
        global $wgBlacklistOps;
        $hideMe = false;
 
        $groupPower = array(
                0 => "*",
                1 => "user",
                2 => "autoconfirmed",
                3 => "emailconfirmed",
                4 => "bot",
                5 => "sysop",
                6 => "bureaucrat");
        $myGroups = array_intersect($groupPower, $wgUser->getEffectiveGroups());
 
        foreach($myGroups as $myGroup) {
                if(array_key_exists($myGroup, $wgBlacklist) && array_key_exists($action, $wgBlacklist[$myGroup]) &&  is_array($wgBlacklist[$myGroup][$action]))
                {
                        if($wgBlacklistOps["useRegex"]) {
                                foreach($wgBlacklist[$myGroup][$action] as $myBlacklist)
                                        if(preg_match("/$myBlacklist/", $title->getPrefixedText()))
                                        {
                                                $hideMe = true;
                                                break;
                                        }
                        } else {
                                $myBlacklist = array_flip($wgBlacklist[$myGroup][$action]);
                                if(array_key_exists($title->getPrefixedText(), $myBlacklist))
                                        $hideMe = true;
                        }
                }
 
                if(array_key_exists($myGroup, $wgWhitelist) && array_key_exists($action, $wgWhitelist[$myGroup]) &&  is_array($wgWhitelist[$myGroup][$action]))
                {
                        if($wgBlacklistOps["useRegex"]) {
                                foreach($wgWhitelist[$myGroup][$action] as $myWhitelist)
                                        if(preg_match("/$myWhitelist/", $title->getPrefixedText()))
                                        {
                                                $hideMe = false;
                                                break;
                                        }
                        } else {
                                $myWhitelist = array_flip($wgWhitelist[$myGroup][$action]);
                                if(array_key_exists($title->getPrefixedText(), $myWhitelist))
                                        $hideMe = false;
                        }
                }
        }
 
        if($hideMe)
                $result = false;
 
        return !$hideMe;
}