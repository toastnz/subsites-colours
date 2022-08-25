<?php

namespace Toast\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use Toast\Forms\IconOptionsetField;
use TractorCow\Colorpicker\Forms\ColorField;
use SilverStripe\Control\Controller;
use Toast\Tasks\GenerateSubsitesThemeColourTask;
use SilverStripe\ORM\DB;
use Toast\Helpers\Helper;
use SilverStripe\Subsites\Model\Subsite;
use SilverStripe\Core\Config\Config;

class SubsiteThemeColour extends DataObject
{
    private static $table_name = 'SubsiteThemeColour';

    private static $db = [
        'SortOrder' => 'Int',
        'Title'     => 'Varchar(255)',
        'Colour'    => 'Color',
    ];

    private static $belongs_many_many = [
        'Subsite'    => Subsite::class,
    ];

    private static $summary_fields = [
        'Title'  => 'Title',
        'Colour.ColorCMS' => 'Color',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['SortOrder','Subsite']);

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', 'Title'),
            ColorField::create('Colour', 'Colour'),
        ]);

        $restricted = $this->getDefaultColourNames();

        // If the title as lower case matches one of the restricted colours, then don't allow the user to change it.
        if (in_array(strtolower($this->Title), $restricted)) {
            $fields->fieldByName('Root.Main.Title')->setReadonly(true);
        }

        return $fields;
    }

    public function getHexColourCode()
    {
        return '#' . $this->Colour;
    }

    public function canDelete($member = null)
    {
        if ($this->Title == 'Primary' || $this->Title == 'Secondary' || $this->Title == 'Tertiary')
        {
            return false;
        }
        return true;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();   
        $regenerateTask = new GenerateSubsitesThemeColourTask;
        $regenerateTask->run(Controller::curr()->getRequest());
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $subsites = Subsite::get();
        foreach ($subsites as $subsite){
            foreach ($this->getDefaultColourNames() as $name) {
            
                $existingRecord = $subsite->ThemeColours()
                    ->filter(['Title' => $name,
                        'SubsiteID' => $subsite->ID
                        ])
                    ->first();
    
                if (!$existingRecord) {
                    $colour = new SubsiteThemeColour();
                    $colour->Title = $name;
                    $colour->write();
                    $subsite->ThemeColours()->add($colour->ID);
                    DB::alteration_message("ThemeColour '$name' created for '$subsite->Title'", 'created');
                }
            }  
        }
        
    }

    protected function getDefaultColourNames()
    {
        $config = Config::inst()->get(Subsite::class, 'default_colours');
        return $config ?: [];
    }

    protected function getClassTitle()
    {
        return  strtolower(str_replace(" ","-",$this->Title));
    }

}