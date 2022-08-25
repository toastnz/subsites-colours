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

class PageExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
         
       
    }
}

class PageControllerExtension extends Extension
{

    protected function init()
    {
        parent::init();
       
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
        
        Requirements::customCSS(file_get_contents('themes/marmalade/dist/styles/critical.css'));
    }

}