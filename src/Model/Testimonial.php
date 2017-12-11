<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Testimonials\Model
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-testimonials
 */

namespace SilverWare\Testimonials\Model;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverWare\Extensions\Model\MetaDataExtension;
use SilverWare\Extensions\Lists\ListItemExtension;
use SilverWare\Security\CMSMainPermissions;
use SilverWare\Testimonials\Pages\TestimonialPage;
use SilverWare\View\Renderable;
use SilverWare\View\ViewClasses;

/**
 * An extension of the data object class for a testimonial.
 *
 * @package SilverWare\Testimonials\Model
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-testimonials
 */
class Testimonial extends DataObject
{
    use Renderable;
    use ViewClasses;
    use CMSMainPermissions;
    
    /**
     * Define title mode constants.
     */
    const TITLE_MODE_AUTHOR     = 'author';
    const TITLE_MODE_AUTHOR_POS = 'author-position';
    const TITLE_MODE_AUTHOR_ORG = 'author-organisation';
    
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Testimonial';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Testimonials';
    
    /**
     * Defines the default sort field and order for this object.
     *
     * @var string
     * @config
     */
    private static $default_sort = 'Sort';
    
    /**
     * Defines the table name to use for this object.
     *
     * @var string
     * @config
     */
    private static $table_name = 'SilverWare_Testimonial';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'Sort' => 'Int',
        'Date' => 'Date',
        'Author' => 'Varchar(128)',
        'Content' => 'HTMLText',
        'Position' => 'Varchar(128)',
        'Organisation' => 'Varchar(128)',
        'TitleMode' => 'Varchar(32)',
        'ShowPosition' => 'Boolean',
        'ShowOrganisation' => 'Boolean',
        'ShowDate' => 'Boolean',
        'Disabled' => 'Boolean'
    ];
    
    /**
     * Defines the has-one associations for this object.
     *
     * @var array
     * @config
     */
    private static $has_one = [
        'Parent' => TestimonialPage::class
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'TitleMode' => 'author-position',
        'ShowPosition' => 1,
        'ShowOrganisation' => 0,
        'ShowDate' => 0,
        'Disabled' => 0
    ];
    
    /**
     * Maps field and method names to the class names of casting objects.
     *
     * @var array
     * @config
     */
    private static $casting = [
        'ContentOrSummary' => 'HTMLFragment'
    ];
    
    /**
     * Defines the summary fields of this object.
     *
     * @var array
     * @config
     */
    private static $summary_fields = [
        'StripThumbnail',
        'Date.Nice',
        'Author',
        'Position',
        'Organisation',
        'Disabled.Nice'
    ];
    
    /**
     * Defines the extension classes to apply to this object.
     *
     * @var array
     * @config
     */
    private static $extensions = [
        MetaDataExtension::class,
        ListItemExtension::class
    ];
    
    /**
     * Defines the default date format to use for the template.
     *
     * @var string
     * @config
     */
    private static $default_date_format = 'd MMMM Y';
    
    /**
     * Defines the meta field configuration for this object.
     *
     * @var array
     * @config
     */
    private static $meta_fields = [
        'Image' => [
            'tab' => 'Root.Main',
            'params' => ['mode' => 'simple']
        ],
        'Summary' => [
            'tab' => 'Root.Content',
            'before' => 'Content'
        ]
    ];
    
    /**
     * Defines the asset folder for uploading images.
     *
     * @var string
     * @config
     */
    private static $meta_image_folder = 'Testimonials';
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Create Field Tab Set:
        
        $fields = FieldList::create(TabSet::create('Root'));
        
        // Create Main Fields:
        
        $fields->addFieldsToTab(
            'Root.Main',
            [
                $date = DateField::create(
                    'Date',
                    $this->fieldLabel('Date')
                ),
                TextField::create(
                    'Author',
                    $this->fieldLabel('Author')
                ),
                TextField::create(
                    'Position',
                    $this->fieldLabel('Position')
                ),
                TextField::create(
                    'Organisation',
                    $this->fieldLabel('Organisation')
                )
            ]
        );
        
        // Create Content Tab:
        
        $fields->findOrMakeTab('Root.Content', $this->fieldLabel('Content'));
        
        // Create Content Field:
        
        $fields->addFieldToTab(
            'Root.Content',
            HTMLEditorField::create(
                'Content',
                $this->fieldLabel('Content')
            )->setRows(30)
        );
        
        // Create Options Tab:
        
        $fields->findOrMakeTab('Root.Options', $this->fieldLabel('Options'));
        
        // Create Options Fields:
        
        $fields->addFieldsToTab(
            'Root.Options',
            [
                DropdownField::create(
                    'TitleMode',
                    $this->fieldLabel('TitleMode'),
                    $this->getTitleModeOptions()
                ),
                CheckboxField::create(
                    'ShowDate',
                    $this->fieldLabel('ShowDate')
                ),
                CheckboxField::create(
                    'ShowPosition',
                    $this->fieldLabel('ShowPosition')
                ),
                CheckboxField::create(
                    'ShowOrganisation',
                    $this->fieldLabel('ShowOrganisation')
                ),
                CheckboxField::create(
                    'Disabled',
                    $this->fieldLabel('Disabled')
                )
            ]
        );
        
        // Extend Field Objects:
        
        $this->extend('updateCMSFields', $fields);
        
        // Answer Field Objects:
        
        return $fields;
    }
    
    /**
     * Answers a validator for the CMS interface.
     *
     * @return RequiredFields
     */
    public function getCMSValidator()
    {
        return RequiredFields::create([
            'Author'
        ]);
    }
    
    /**
     * Answers the labels for the fields of the receiver.
     *
     * @param boolean $includerelations Include labels for relations.
     *
     * @return array
     */
    public function fieldLabels($includerelations = true)
    {
        // Obtain Field Labels (from parent):
        
        $labels = parent::fieldLabels($includerelations);
        
        // Define Field Labels:
        
        $labels['Author'] = _t(__CLASS__ . '.AUTHOR', 'Author');
        $labels['Content'] = _t(__CLASS__ . '.CONTENT', 'Content');
        $labels['Options'] = _t(__CLASS__ . '.OPTIONS', 'Options');
        $labels['ShowDate'] = _t(__CLASS__ . '.SHOWDATE', 'Show date');
        $labels['Position'] = _t(__CLASS__ . '.POSITION', 'Position');
        $labels['TitleMode'] = _t(__CLASS__ . '.TITLEMODE', 'Title mode');
        $labels['Organisation'] = _t(__CLASS__ . '.ORGANISATION', 'Organisation');
        $labels['ShowPosition'] = _t(__CLASS__ . '.SHOWPOSITION', 'Show position');
        $labels['ShowOrganisation'] = _t(__CLASS__ . '.SHOWORGANISATION', 'Show organisation');
        $labels['StripThumbnail'] = _t(__CLASS__ . '.IMAGE', 'Image');
        $labels['Disabled'] = $labels['Disabled.Nice'] = _t(__CLASS__ . '.DISABLED', 'Disabled');
        $labels['Date'] = $labels['Date.Nice'] = _t(__CLASS__ . '.DATE', 'Date');
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Event method called before the extended object is written to the database.
     *
     * @return void
     */
    public function onBeforeWrite()
    {
        // Call Parent Event:
        
        parent::onBeforeWrite();
        
        // Publish Image:
        
        if ($this->hasMetaImage()) {
            $this->ImageMeta()->publishSingle();
        }
    }
    
    /**
     * Populates the default values for the fields of the receiver.
     *
     * @return void
     */
    public function populateDefaults()
    {
        // Populate Defaults (from parent):
        
        parent::populateDefaults();
        
        // Populate Defaults:
        
        $this->Date = date('Y-m-d');
    }
    
    /**
     * Answers the meta date for the testimonial.
     *
     * @return DBDate
     */
    public function getMetaDate()
    {
        return $this->dbObject('Date');
    }
    
    /**
     * Answers the meta link for the testimonial.
     *
     * @return string
     */
    public function getMetaLink()
    {
        return $this->getParent()->Link();
    }
    
    /**
     * Answers the meta image caption for the testimonial.
     *
     * @return string
     */
    public function getMetaImageCaption()
    {
        return $this->MetaSummary;
    }
    
    /**
     * Answers the title of the receiver for the CMS interface.
     *
     * @return string
     */
    public function getTitle()
    {
        return sprintf(_t(__CLASS__ . '.TESTIMONIALBY', 'Testimonial by %s'), $this->Author);
    }
    
    /**
     * Answers the meta title for the receiver.
     *
     * @return string
     */
    public function getMetaTitle()
    {
        switch ($this->TitleMode) {
            case self::TITLE_MODE_AUTHOR:
                return $this->Author;
            case self::TITLE_MODE_AUTHOR_ORG:
                return $this->getAuthorAndOrganisation();
            default:
                return $this->getAuthorAndPosition();
        }
    }
    
    /**
     * Answers a string with the author name and position (if available).
     *
     * @return string
     */
    public function getAuthorAndPosition()
    {
        if ($this->Position) {
            return sprintf('%s, %s', $this->Author, $this->Position);
        }
        
        return $this->Author;
    }
    
    /**
     * Answers a string with the author name and organisation (if available).
     *
     * @return string
     */
    public function getAuthorAndOrganisation()
    {
        if ($this->Organisation) {
            return sprintf('%s, %s', $this->Author, $this->Organisation);
        }
        
        return $this->Author;
    }
    
    /**
     * Answers the parent page of the testimonial.
     *
     * @return TestimonialPage
     */
    public function getParent()
    {
        return $this->Parent();
    }
    
    /**
     * Answers the date format for the receiver.
     *
     * @return string
     */
    public function getDateFormat()
    {
        if ($format = $this->getParent()->DateFormat) {
            return $format;
        }
        
        return $this->config()->default_date_format;
    }
    
    /**
     * Answers the date of the receiver as a formatted string.
     *
     * @param string $format
     *
     * @return string
     */
    public function getDateFormatted($format = null)
    {
        return $this->dbObject('Date')->Format($format ? $format : $this->getDateFormat());
    }
    
    /**
     * Answers true if the date is to be shown in the template.
     *
     * @return boolean
     */
    public function getDateShown()
    {
        return ($this->Date && $this->ShowDate);
    }
    
    /**
     * Answers true if the position is to be shown in the template.
     *
     * @return boolean
     */
    public function getPositionShown()
    {
        return ($this->Position && $this->ShowPosition);
    }
    
    /**
     * Answers true if the organisation is to be shown in the template.
     *
     * @return boolean
     */
    public function getOrganisationShown()
    {
        return ($this->Organisation && $this->ShowOrganisation);
    }
    
    /**
     * Answers a thumbnail of the image for a grid field.
     *
     * @return AssetContainer|DBHTMLText
     */
    public function getStripThumbnail()
    {
        if ($this->hasMetaImage()) {
            return $this->ImageMeta()->StripThumbnail();
        }
    }
    
    /**
     * Answers an array of image class names for the template.
     *
     * @return array
     */
    public function getImageClassNames()
    {
        $classes = $this->styles('image.fluid');
        
        if ($cornerStyle = $this->getParent()->ImageCornerStyleClass) {
            $classes[] = $cornerStyle;
        }
        
        return $classes;
    }
    
    /**
     * Answers the resize width for the meta image.
     *
     * @param integer $width
     *
     * @return integer
     */
    public function getImageMetaResizeWidth()
    {
        return $this->getParent()->ImageResizeWidth;
    }
    
    /**
     * Answers the resize height for the meta image.
     *
     * @param integer $width
     *
     * @return integer
     */
    public function getImageMetaResizeHeight()
    {
        return $this->getParent()->ImageResizeHeight;
    }
    
    /**
     * Answers the resize method for the meta image.
     *
     * @param integer $width
     *
     * @return integer
     */
    public function getImageMetaResizeMethod()
    {
        return $this->getParent()->ImageResizeMethod;
    }
    
    /**
     * Answers either the content of the testimonial or the summary (if no content is defined).
     *
     * @return DBHTMLText|string
     */
    public function getContentOrSummary()
    {
        return $this->Content ?: $this->MetaSummary;
    }
    
    /**
     * Answers an array of options for the title mode field.
     *
     * @return array
     */
    public function getTitleModeOptions()
    {
        return [
            self::TITLE_MODE_AUTHOR     => _t(__CLASS__ . '.AUTHOR', 'Author'),
            self::TITLE_MODE_AUTHOR_POS => _t(__CLASS__ . '.AUTHORANDPOSITION', 'Author and Position'),
            self::TITLE_MODE_AUTHOR_ORG => _t(__CLASS__ . '.AUTHORANDORGANISATION', 'Author and Organisation')
        ];
    }
}
