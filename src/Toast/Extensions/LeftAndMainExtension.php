<?php

namespace Toast\SubsitesTheme\Extensions;

use SilverStripe\Admin\CMSMenu;
use SilverStripe\Reports\ReportAdmin;
use SilverStripe\View\Requirements;
use SilverStripe\CampaignAdmin\CampaignAdmin;
use SilverStripe\VersionedAdmin\ArchiveAdmin;
use Symbiote\QueuedJobs\Controllers\QueuedJobsAdmin;
use Toast\SubsitesTheme\Helpers\Helper;
use SilverStripe\Control\Director;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;
use SilverStripe\Forms\HTMLEditor\TinyMCEConfig;
use SilverStripe\SiteConfig\SiteConfig;

class LeftAndMainExtension extends \SilverStripe\Admin\LeftAndMainExtension
{

    public function init()
    {

        $themeCssFilePath = '';

        // Set TinyMCE with generated subsitescss file
        $config = TinyMCEConfig::get('cms');
        $colours = null;

        if(Helper::isSubsite()){

            // TODO: Add an extension so that Subsite::currentSubsite() returns the theme even if we are on the main site
            $subsite = Subsite::currentSubsite();
            $subsiteName = ($subsite) ? $subsite->Title : 'marmalade';

            // Load in the site's cms styles
            Requirements::customCSS(file_get_contents('themes/'. $subsiteName .'/dist/styles/cms.css'));
            Requirements::javascript('themes/'. $subsiteName .'/dist/scripts/cms.js');

            $config = Config::inst()->get(Subsite::class, 'has_subsites_colours');
            if ( !$config ){
                // Set TinyMCE with generated css file
                $config = TinyMCEConfig::get('cms');
                
                $themeCssFilePath = '/app/client/styles/mainsite-frontend.css';
                $editorColoursCssFilePath = '/app/client/styles/mainsite-editor.css';
                $fontCssFile = '/app/client/styles/mainsite-font.css';
                $editorCssFilePage = '/themes/marmalade/dist/styles/editor.css';

                 // Generate css files if it doesn't exist
                 if (!file_exists(Director::baseFolder() .$themeCssFilePath)){
                    $result = Helper::generateCSSFiles($themeCssFilePath);
                }
                if (!file_exists(Director::baseFolder() .$fontCssFile)){
                    $result = Helper::generateFontCSSFiles();
                }

                if (file_exists(Director::baseFolder() .$editorCssFilePage) && 
                file_exists(Director::baseFolder() .$editorColoursCssFilePath) &&
                file_exists(Director::baseFolder() .$fontCssFile)){
                    $config->setContentCSS([
                        $editorCssFilePage,
                        $editorColoursCssFilePath,
                        $fontCssFile
                    ]);
                }
                
                if (file_exists(Director::baseFolder() .$themeCssFilePath)) {
                    Requirements::customCSS(file_get_contents(Director::baseFolder() .$themeCssFilePath));
                }

                // get colours from config
                $siteConfig = SiteConfig::current_site_config();
                if ($colours = $siteConfig->ThemeColours()){
                    $themeFormats = $this->getFormatsForTinyMCE($colours);
                    $formats = $themeFormats;
                    $config->setOptions([
                        'importcss_append' => true,
                        'style_formats' => $formats,
                    ]);
                }
            }
        
        }
    }
}