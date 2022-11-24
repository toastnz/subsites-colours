<?php

namespace Toast\SubsitesTheme\Extensions;

use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;
use Toast\SubsitesTheme\Helpers\Helper;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;
use Heyday\ColorPalette\Fields\ColorPaletteField;

class PageExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {

        if(class_exists(Subsite::class)){
            $config = Config::inst()->get(Subsite::class, 'has_subsites_colours');
            if ( $config ){
                if($subsite = Subsite::currentSubsite()){
                    $array = [];
                    if ($colours = $subsite->ThemeColours()){
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
                    $fields->addFieldToTab('Root.Main',  ColorPaletteField::create('PageBGColour', 'Page Background Colour', $array)
                    ->addExtraClass('stacked'));
                }
                else{
                    if ($themeColours = Helper::getThemeColoursArray(false)){
                        $fields->addFieldToTab('Root.Main',  ColorPaletteField::create('PageBGColour', 'Page Background Colour', $themeColours)
                        ->addExtraClass('stacked'));
                    }
                }
            }
        }
    }
}

class PageControllerExtension extends Extension
{

    public function onBeforeInit()
    {

        $themeCssFilePath = Director::baseFolder() . '/app/client/styles/mainsite-frontend.css';
       
        // require current active site css if subsite exists
        if (class_exists(Subsite::class)){
            if(Helper::isSubsite()){
                $subsite = Subsite::currentSubsite();
                $subsiteCSSFileName = 'subsite' . $subsite->ID . '-frontend.css';
                $themeCssFilePath = Director::baseFolder() . '/app/client/styles/'.$subsiteCSSFileName;
            
            }
        }
   
        if (!file_exists($themeCssFilePath)){
            $result = Helper::generateCSSFiles($themeCssFilePath);
        }
        if (file_exists($themeCssFilePath)){
            Requirements::customCSS(file_get_contents($themeCssFilePath));
        }
        
    }

}