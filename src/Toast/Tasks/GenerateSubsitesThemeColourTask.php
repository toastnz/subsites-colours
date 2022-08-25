<?php

namespace Toast\Tasks;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Subsites\State\SubsiteState;
use SilverStripe\ORM\DataObject;
use Toast\Models\SubsiteThemeColour;
use SilverStripe\ORM\DB;
use Toast\Helpers\Helper;
use SilverStripe\Core\Config\Config;

class GenerateSubsitesThemeColourTask extends BuildTask
{

    private static $segment = 'generate_subsite_theme_css_file';

    protected $title = 'Regenerate Theme CSS file for subsites';

    protected $description = 'Regenerates Subsite Theme CSS file from configured colours in Subsite settings';

    public function run($request)
    {
            //check if there are subsites installed
            if (class_exists(Subsite::class)){
                    // loop through each subsite and generate a css file for each
                    //get all subsites
                    $subsites = Subsite::get();
                    $defaultColours = $this->getDefaultColourNames();
                    // loop through each subsite 
                    foreach ($subsites as $subsite) {
                        $subsiteID = $subsite->ID;
                       
                        $subsiteCSSFileName = 'subsite' . $subsiteID . '-frontend.css';
                        $editorCssFilePath = 'subsite' . $subsiteID . '-editor.css';
                       
                        $themeCssFilePath = Director::baseFolder() . '/app/client/styles/' . $subsiteCSSFileName;
                        $editorCssFilePath = Director::baseFolder() . '/app/client/styles/' . $editorCssFilePath;
                        // generate a css file for each
                        $this->generateFrontEndCSSFile($subsite->ThemeColours(), $themeCssFilePath, $editorCssFilePath);
                    }

                // }
            }
        // echo 'Subsite CSS files generated.' ;
    }

    public function generateFrontEndCSSFile($colours, $themeCssFilePath, $editorCssFilePath)
    {
        if(!$colours->exists()){
            return;
        }

        $defaultColours = $this->getDefaultColourNames();

         // remove file if exists
         if (file_exists($themeCssFilePath)){
            unlink($themeCssFilePath);
        }
     
        if (file_exists($editorCssFilePath)){
            unlink($editorCssFilePath);
        }
        
        // opening line for frontend css file
        $lines = ':root {';
        $editorLines = ':root {';

        // loop through root default colours and 
        foreach ($colours as $colour){
            //add css line if not empty
            if ($currentColour = $colour->Colour){
                
                $colourTitle = $colour->Title;
                // check if colour is in the default colours 
                if(in_array($colourTitle, array_values($defaultColours))){
                    // remove any whitespace
                    $title = strtolower(str_replace(" ","-",$colourTitle));
                    // generate dark and light from this colour
                    //dark 
                    $darkColour = $this->colourBrightness($colour->getHexColourCode(), -0.5);
                    //light
                    $lightColour = $this->colourBrightness($colour->getHexColourCode(), 0.5);

                    $lines .= '--' . $title. ': ' . $colour->getHexColourCode() . ';';
                    $lines .= '--' . $title . '-dark: ' . $darkColour . ';';
                    $lines .= '--' . $title . '-light: ' . $lightColour . ';';
                    $editorLines .= '--' . $title . ': ' . $colour->getHexColourCode(). ';';

                    // I dont believe we need this for the frontend file :) just --primary etc is fine, only need the subsite ID for the editor file
                }else{
                    $newtitle = strtolower(str_replace(" ","-",$colour->Title));
                    // var_dump($newtitle);
                    $editorLines .= '--' . $newtitle . ': ' . $colour->getHexColourCode(). ';';
                }
               
                    // ending line for frontend css file
                    
                    
            }
            
        }
        $lines .= '}';
        $editorLines .= '}';

        // non-root
        foreach ($colours as $colour){
            //add css line if not empty
            if ($currentColour = $colour->Colour){
                $colourTitle = $colour->Title;
                if(in_array($colourTitle, array_values($defaultColours))){
                    $title = strtolower(str_replace(" ","-",$colourTitle));
                    $lines .= '.colour--' . $title . '{'; 
                        $lines .= 'color: var(--' . $title . ');';
                        $lines .= '}';
                        $lines .= '.background-colour--' . $title . '{'; 
                        $lines .= 'background-color: var(--' . $title . ');';

                        $lines .= '}';

                        $editorLines .= 'body.mce-content-body  .colour--' . $title . '{'; 
                        $editorLines .= 'color: var(--' . $title . ');';
                        $editorLines .= '}';
                }else{
                    $title = strtolower(str_replace(" ","-",$colour->Title));
                    $editorLines .= 'body.mce-content-body  .colour--' . $title . '{'; 
                    $editorLines .= 'color: var(--' . $title . ');';
                    $editorLines .= '}';
                }
            }
        }
        // $editorLines .= '}';
        // write to file
        try{
            file_put_contents($themeCssFilePath, $lines);
            file_put_contents($editorCssFilePath, $editorLines);
        }   catch (\Exception $e){
            // do nothing
        }
        
            
    }

    protected function getDefaultColourNames()
    {
        $config = Config::inst()->get(SubsiteThemeColour::class, 'default_colours');
        return $config ?: [];
    }


    function colourBrightness($hex, $percent)
    {
        // Work out if hash given
        $hash = '';
        if (stristr($hex, '#')) {
            $hex = str_replace('#', '', $hex);
            $hash = '#';
        }
        /// HEX TO RGB
        $rgb = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        //// CALCULATE
        for ($i = 0; $i < 3; $i++) {
            // See if brighter or darker
            if ($percent > 0) {
                // Lighter
                $rgb[$i] = round($rgb[$i] * $percent) + round(255 * (1 - $percent));
            } else {
                // Darker
                $positivePercent = $percent - ($percent * 2);
                $rgb[$i] = round($rgb[$i] * (1 - $positivePercent)); // round($rgb[$i] * (1-$positivePercent));
            }
            // In case rounding up causes us to go to 256
            if ($rgb[$i] > 255) {
                $rgb[$i] = 255;
            }
        }
        //// RBG to Hex
        $hex = '';
        for ($i = 0; $i < 3; $i++) {
            // Convert the decimal digit to hex
            $hexDigit = dechex($rgb[$i]);
            // Add a leading zero if necessary
            if (strlen($hexDigit) == 1) {
                $hexDigit = "0" . $hexDigit;
            }
            // Append to the hex string
            $hex .= $hexDigit;
        }
        return $hash . $hex;
    }
}