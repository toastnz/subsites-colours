<?php

namespace Toast\SubsitesTheme\Helpers;

use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;
use Toast\Tasks\GenerateSubsitesThemeColourTask;
use SilverStripe\Control\Controller;

class Helper 
{
    static function isMainSite()
    {
        return SubsiteState::singleton()->getSubsiteId() == 0;
    }

    static function isSubsite()
    {
        return !self::isMainSite();
    }

    static function currentSubsite()
    {
        if ($subsiteId = SubsiteState::singleton()->getSubsiteId()) {
            return Subsite::get()->byID($subsiteId);
        }
        return false;
    }

    static function getSubsiteThemeColoursArray()
    {
        $array=[];
        if(self::isSubsite()){
            $array['none'] = 'none';
            $array['white'] = '#fff';
            $array['black'] = '#000';
            $subsite = Subsite::currentSubsite();
            if ($colours = $subsite->ThemeColours()->map('Title','Colour')){
                foreach($colours as $key => $colour){
                    // convert string to lowercase and replace whitespaces with hyphen
                    if ($colour){
                        $classTitle = strtolower(str_replace(" ","-",$key));
                        $array[$classTitle] = '#' . $colour;
                    }
                }
                    
            }
        }
        return $array;
    }
    static function generateCSSFiles($themeCssFilePath)
    {
        if(!$themeCssFilePath){
            return ;
        }
   
        if (!file_exists($themeCssFilePath)){
            $regenerateTask = new GenerateSubsitesThemeColourTask;
            $regenerateTask->run(Controller::curr()->getRequest());
        }
    }
}