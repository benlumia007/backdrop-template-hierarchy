<?php
/**
 * Backdrop Core ( functions-template.php )
 *
 * @package   Backdrop Core
 * @copyright Copyright (C) 2019-2021. Benjamin Lu
 * @author    Benjamin Lu ( https://getbenonit.com )
 * @license   https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Define namespace
 */
namespace Benlumia007\Backdrop\Template\Hierarchy;
use Benlumia007\Backdrop\Template\Hierarchy\Contracts\Hierarchy as templateHierarchy;
use function Benlumia007\Backdrop\Template\Helpers\path;
use function Benlumia007\Backdrop\Template\Helpers\filter_templates;
use WP_User;

/**
 * Overwrites the core WP template hierarchy.
 *
 * @since  1.0.0
 * @access public
 */
class Component implements templateHierarchy {
    /**
     * Array of template types in WordPress Core.
     * 
     * @since  1.0.0
     * @access protected
     * @var    array
     */
    protected $types = [
        '404',
        'archive',
        'attachment',
        'author',
        'category',
        'date',
        'embed',
        'fontpage',
        'home',
        'index',
        'page',
        'paged',
        'privacypolicy',
        'search',
        'single',
        'singular',
        'tag',
        'taxonomy',
    ];

    /**
     * Copy of the located template found when running through
     * the tamplate hierarchy.
     * 
     * @since  1.0.0
     * @access protected
     * @var    string
     */
    protected $located = '';

    /**
     * An array of the entire template hierarchy for the current page view.
     * This hierarchy does not have the `.php` file name extension.
     */
    protected $hierarchy = [];

    /**
     * Setup the template hierarchy filters.
     * 
     * @since  1.0.0
     * @access public
     * @return void
     */
    public function boot() {

        // System to capture template hierarchy.
        foreach( $this->types as $type ) {
			// Capture the template hierarchy for each type.
			add_filter( "{$type}_template_hierarchy", [ $this, 'templateHierarchy' ], PHP_INT_MAX );

			// Capture the located template.
			add_filter( "{$type}_template", [ $this, 'template' ], PHP_INT_MAX );
        }

		// Re-add the located template.
		add_filter( 'template_include', [ $this, 'templateInclude' ], PHP_INT_MAX );
    }

	/**
	 * Returns the full template hierarchy for the current page load.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return array
	 */
	public function hierarchy() {
		return $this->hierarchy;
	}

    /**
     * Filters a queried template hierarchy for each type of template
     * and looks templates within `resources/views'.
     * 
     * @since  1.0.0
     * @access public
     * @return array
     */
    public function templateHierarchy( $templates ) {
        /**
         * Merge the current template's hierarchy with the overall hierarchy array.
         */
        $this->hierarchy = array_merge(
            $this->hierarchy,
            array_map( function( $template ) {
                return substr(
                    $template,
                    0,
                    strlen( $template ) - strlen( strrchr( $template, '.' ) )
                );
            }, $templates )
        );

        $this->hierarchy = array_unique( $this->hierarchy );

        return filter_templates( $templates );
    }

    /**
     * Filters the template for each type of template in the hierarchy.
     * If `$templates` exists, it means we've located a template, so
     * we are going to store that template for later use and return
     * an empty string so that the template hierarchy continues processing.
     * This way, we can capture the entire hierarchy.
     * 
     * @since  1.0.0
     * @access public
     * @param  string $template
     */
    public function template( $template ) {
        if ( ! $this->located && $template ) {
            $this->located = $template;
        }

        return '';
    }
    
    /**
     * Filters on  `template_include` to make sure we fall
     * back to our template from earlier.
     * 
     * @since  1.0.0
     * @access public
     * @param  string $template
     * @return string
     */
    public function templateInclude( $template ) {
		// If the template is not a string at this point, it either
		// doesn't exist or a plugin is telling us it's doing
		// something custom.
		if ( ! is_string( $template ) ) {

			return $template;
		}

		// If there's a template, return it. Otherwise, return our
		// located template from earlier.
		return $template ?: $this->located;
    }
}