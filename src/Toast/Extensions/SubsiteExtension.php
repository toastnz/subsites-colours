<?php

namespace Toast\SubsitesTheme\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use Toast\Models\SubsiteThemeColour;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DB;

class SubsiteExtension extends DataExtension
{
    private static $many_many = [
        'ThemeColours' => SubsiteThemeColour::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('ThemeColours');
        $coloursConfig = GridFieldConfig_RelationEditor::create(50);
        $coloursConfig->addComponent(GridFieldOrderableRows::create('SortOrder'));
        $coloursConfig->removeComponentsByType(GridFieldDeleteAction::class);

        $coloursField = GridField::create(
            'ThemeColours',
            'Theme Colours',
            $this->owner->ThemeColours(),
            $coloursConfig
        );

        if($this->owner->ID){
            $fields->addFieldsToTab('Root.ThemeColours', [
                HeaderField::create('ColourFields', 'Theme Colours Configuration'),
                LiteralField::create('ColourFieldsWarning', '<div class="message warning"><strong>Note:</strong> Only <strong>Default Admin</strong> can view these settings</div>'),
                LiteralField::create('ColourFieldsLink', '<div class="message notice">Please run this <a href="'.Director::absoluteBaseURL().'dev/tasks/generate_subsite_theme_css_file" target="_blank">task</a> to regenerate files after creating new colours.</div>'),
                $coloursField,
                
            ]);
        }
    }

  
    public function onBeforeWrite()
    {
        $this->generateDefaultColours();
        parent::onBeforeWrite();
    }

    public function generateDefaultColours()
    {
        foreach ($this->getDefaultColourNames() as $name) {
            $existingRecord = $this->owner->ThemeColours()
                ->filter([
                    'Title' => $name
                ])
                ->first();

            $restricted = [
                'Primary',
                'Secondary',
                'Tertiary',
            ];

            if (!$existingRecord) {
                // If the lowercase title is not in the restricted array, then create a new record.
                if (!in_array(strtolower($name), $restricted)) {
                    $colour = new SubsiteThemeColour();
                    $colour->Title = $name;
                    $colour->write();
                    $this->owner->ThemeColours()->add($colour->ID);
                }
            }
        }
    }

    protected function getDefaultColourNames()
    {
        return SubsiteThemeColour::config()->get('default_colours') ?: [];
    }
}