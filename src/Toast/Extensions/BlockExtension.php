<?php

namespace Toast\SubsitesTheme\Extensions;

use Toast\SubsitesTheme\Helpers\Helper;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Heyday\ColorPalette\Fields\ColorPaletteField;

class BlockExtension extends DataExtension
{

    public function updateCMSFields(FieldList $fields)
    {
        if ($array = Helper::getSubsiteThemeColoursArray()){
            $fields->addFieldToTab('Root.Main',ColorPaletteField::create('BGColour', 'Background Colour',$array)
            ->setDescription('Colours added from Site Settings'));
        }
    }
}
