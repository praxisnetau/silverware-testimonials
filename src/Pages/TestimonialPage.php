<?php

/**
 * This file is part of SilverWare.
 *
 * PHP version >=5.6.0
 *
 * For full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 *
 * @package SilverWare\Testimonials\Pages
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-testimonials
 */

namespace SilverWare\Testimonials\Pages;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\TextField;
use SilverWare\Forms\DimensionsField;
use SilverWare\Forms\FieldSection;
use SilverWare\Lists\ListSource;
use SilverWare\Tools\ImageTools;
use SilverWare\Testimonials\Model\Testimonial;
use Page;

/**
 * An extension of the page class for a testimonial page.
 *
 * @package SilverWare\Testimonials\Pages
 * @author Colin Tucker <colin@praxis.net.au>
 * @copyright 2017 Praxis Interactive
 * @license https://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @link https://github.com/praxisnetau/silverware-testimonials
 */
class TestimonialPage extends Page implements ListSource
{
    /**
     * Define corner constants.
     */
    const CORNER_ROUNDED  = 'rounded';
    const CORNER_CIRCULAR = 'circular';
    
    /**
     * Define sort constants.
     */
    const SORT_RANDOM = 'random';
    const SORT_RECENT = 'recent';
    
    /**
     * Human-readable singular name.
     *
     * @var string
     * @config
     */
    private static $singular_name = 'Testimonial Page';
    
    /**
     * Human-readable plural name.
     *
     * @var string
     * @config
     */
    private static $plural_name = 'Testimonials Page';
    
    /**
     * Description of this object.
     *
     * @var string
     * @config
     */
    private static $description = 'Holds a series of testimonials';
    
    /**
     * Icon file for this object.
     *
     * @var string
     * @config
     */
    private static $icon = 'silverware/testimonials: admin/client/dist/images/icons/TestimonialPage.png';
    
    /**
     * Maps field names to field types for this object.
     *
     * @var array
     * @config
     */
    private static $db = [
        'ImageResize' => 'Dimensions',
        'ImageResizeMethod' => 'Varchar(32)',
        'ImageCornerStyle' => 'Varchar(32)',
        'DateFormat' => 'Varchar(32)',
        'SortBy' => 'Varchar(16)'
    ];
    
    /**
     * Defines the has-many associations for this object.
     *
     * @var array
     * @config
     */
    private static $has_many = [
        'Testimonials' => Testimonial::class
    ];
    
    /**
     * Defines the default values for the fields of this object.
     *
     * @var array
     * @config
     */
    private static $defaults = [
        'SortBy' => 'random',
        'ImageResizeWidth' => 80,
        'ImageResizeHeight' => 80,
        'ImageResizeMethod' => 'fill-priority',
        'ImageCornerStyle' => 'circular'
    ];
    
    /**
     * Answers a list of field objects for the CMS interface.
     *
     * @return FieldList
     */
    public function getCMSFields()
    {
        // Obtain Field Objects (from parent):
        
        $fields = parent::getCMSFields();
        
        // Define Placeholder:
        
        $placeholder = _t(__CLASS__ . '.DROPDOWNDEFAULT', '(default)');
        
        // Create Testimonials Tab:
        
        $fields->findOrMakeTab('Root.Testimonials', $this->fieldLabel('Testimonials'));
        
        // Create Testimonials Grid Field:
        
        $fields->addFieldToTab(
            'Root.Testimonials',
            GridField::create(
                'Testimonials',
                $this->fieldLabel('Testimonials'),
                $this->Testimonials(),
                GridFieldConfig_RecordEditor::create()
            )
        );
        
        // Create Style Tab:
        
        $fields->findOrMakeTab('Root.Style', $this->fieldLabel('Style'));
        
        // Create Style Fields:
        
        $fields->addFieldsToTab(
            'Root.Style',
            [
                FieldSection::create(
                    'TestimonialsStyle',
                    $this->fieldLabel('Testimonials'),
                    [
                        DimensionsField::create(
                            'ImageResize',
                            $this->fieldLabel('ImageResize')
                        ),
                        DropdownField::create(
                            'ImageResizeMethod',
                            $this->owner->fieldLabel('ImageResizeMethod'),
                            ImageTools::singleton()->getResizeMethods()
                        ),
                        DropdownField::create(
                            'ImageCornerStyle',
                            $this->owner->fieldLabel('ImageCornerStyle'),
                            $this->getImageCornerStyleOptions()
                        )->setEmptyString(' ')->setAttribute('data-placeholder', $placeholder)
                    ]
                )
            ]
        );
        
        // Create Options Tab:
        
        $fields->findOrMakeTab('Root.Options', $this->fieldLabel('Options'));
        
        // Create Options Fields:
        
        $fields->addFieldsToTab(
            'Root.Options',
            [
                FieldSection::create(
                    'TestimonialsOptions',
                    $this->fieldLabel('Testimonials'),
                    [
                        DropdownField::create(
                            'SortBy',
                            $this->fieldLabel('SortBy'),
                            $this->getSortByOptions()
                        ),
                        TextField::create(
                            'DateFormat',
                            $this->fieldLabel('DateFormat')
                        )
                    ]
                )
            ]
        );
        
        // Answer Field Objects:
        
        return $fields;
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
        
        $labels['Style'] = _t(__CLASS__ . '.STYLE', 'Style');
        $labels['SortBy'] = _t(__CLASS__ . '.SORTBY', 'Sort by');
        $labels['Options'] = _t(__CLASS__ . '.OPTIONS', 'Options');
        $labels['DateFormat'] = _t(__CLASS__ . '.DATEFORMAT', 'Date format');
        $labels['ImageResize'] = _t(__CLASS__ . '.IMAGERESIZE', 'Image resize');
        $labels['Testimonials'] = _t(__CLASS__ . '.TESTIMONIALS', 'Testimonials');
        $labels['ImageResizeMethod'] = _t(__CLASS__ . '.IMAGERESIZEMETHOD', 'Image resize method');
        $labels['ImageCornerStyle'] = _t(__CLASS__ . '.IMAGECORNERSTYLE', 'Image corner style');
        
        // Answer Field Labels:
        
        return $labels;
    }
    
    /**
     * Answers a list of the enabled testimonials within the receiver.
     *
     * @return DataList
     */
    public function getEnabledTestimonials()
    {
        return $this->Testimonials()->filter('Disabled', 0)->sort($this->getSortOrder());
    }
    
    /**
     * Answers a list of testimonials within the receiver.
     *
     * @return SS_List
     */
    public function getListItems()
    {
        return $this->getEnabledTestimonials();
    }
    
    /**
     * Answers the sort order for the enabled testimonials.
     *
     * @return string
     */
    public function getSortOrder()
    {
        switch ($this->SortBy) {
            
            case self::SORT_RECENT:
                return 'Date DESC';
            
            case self::SORT_RANDOM:
                return 'RAND()';
            
        }
    }
    
    /**
     * Answers an array of options for the sort by field.
     *
     * @return array
     */
    public function getSortByOptions()
    {
        return [
            self::SORT_RANDOM => _t(__CLASS__ . '.RANDOM', 'Random'),
            self::SORT_RECENT => _t(__CLASS__ . '.RECENT', 'Recent')
        ];
    }
    
    /**
     * Answers an array of options for the image corner style field.
     *
     * @return array
     */
    public function getImageCornerStyleOptions()
    {
        return [
            self::CORNER_ROUNDED  => _t(__CLASS__ . '.ROUNDED', 'Rounded'),
            self::CORNER_CIRCULAR => _t(__CLASS__ . '.CIRCULAR', 'Circular'),
        ];
    }
    
    /**
     * Answers the corner style class for testimonial images.
     *
     * @return string
     */
    public function getImageCornerStyleClass()
    {
        switch ($this->ImageCornerStyle) {
            case self::CORNER_ROUNDED:
                return $this->style('rounded');
            case self::CORNER_CIRCULAR:
                return $this->style('rounded.circle');
        }
    }
    
    /**
     * Answers a message string to be shown when no data is available.
     *
     * @return string
     */
    public function getNoDataMessage()
    {
        return _t(__CLASS__ . '.NODATAAVAILABLE', 'No data available.');
    }
}
