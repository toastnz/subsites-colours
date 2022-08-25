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
            $subsite = Subsite::currentSubsite();
            $editorCSSFileName = 'subsite' . $subsite->ID . '-editor.css';
            $subsiteCSSFileName = 'subsite' . $subsite->ID . '-frontend.css';
            $themeCssFilePath = '/app/client/styles/'.$subsiteCSSFileName;
            $editorCssFilePath = '/app/client/styles/'.$editorCSSFileName;
            if (!file_exists(Director::baseFolder() .$themeCssFilePath)){
                $result = Helper::generateCSSFiles($themeCssFilePath);
            }
            if (file_exists(Director::baseFolder() .$themeCssFilePath) && file_exists(Director::baseFolder() .$editorCssFilePath)) {
                $colours = $subsite->ThemeColours();
                $config->setContentCSS([
                    // $themeCssFilePath,
                    $editorCssFilePath
                ]);
                Requirements::customCSS(file_get_contents(Director::baseFolder() .$themeCssFilePath));
            }
            $themeFormats = $this->getFormatsForTinyMCE($colours);
            $formats = [
                        [
                            'title' => 'Colours',
                            'items' => $themeFormats,
                        ]
                    ];
            $config->setOptions([
                'importcss_append' => true,
                'style_formats' => $formats,
            ]);
            
        }
    }

    public function getFormatsForTinyMCE($colours = null)
    {
        $formats = [];
        if (!$colours)
        {
            return ;
        }

        // get current subsite colours
        foreach ($colours as $colour) {
            $formats[] = [
                'title'          => $colour->Title,
                'inline'         => 'span',
                'classes'        => 'colour--' . strtolower(str_replace(" ","-",$colour->Title)),
                'wrapper'        => true,
                'merge_siblings' => true
            ];
        }

        return $formats;
    }
}