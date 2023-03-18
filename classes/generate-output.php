<?php
/**
 * Generates HTML output from a WordPress plugin readme file.
 *
 * @package Pandammonium-Readme-Parser
 * @author dartiss, pandammonium
 * @since 1.0
 * @since 2.0.0 Converts the file to a class.
 *
 * @todo Absorb as many functions as is meaningful from includes/
 * functions.php.
 * @todo Sort out the calls to prp_log().
 * @todo Improve the documentation wrt PHPDoc.
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();

if ( !class_exists( 'Generate_Output' ) ) {
  /**
   * Provides a shortcode to convert a WordPress plugin readme to
   * HTML.
   *
   * Using the parameters and content of a shortcode, the class
   * fetches the readme file of a specified plugin from the
   * WordPress servers, parses its contents and converts it, using
   * a third-party library (Michelf, included), to HTML.
   *
   * WordPress plugin readme files have specific sections, which
   * this class looks out for, and selectively displays or
   * witholds, depending on the options the user provides via the
   * shortcode parameters.
   *
   * @author dartiss, pandammonium
   * @since 1.0
   * @since 2.0.0 Converted the collection of functions to a class.
   */
  class Generate_Output {

    private string|array|null $parameters;
    private $content;

    private array $file_array;
    private array $file_data;

    private $plugin_url;
    private $plugin_name;
    private $plugin_title;

    private $cache;
    private $cache_key;

    private $exclude;
    private $include;
    private $hide;
    private $links;
    private $ignore;
    private $mirror;
    private $nofollow;
    private $version;
    private $target;
    private $download;
    private $metadata;

    private $show_links;
    private $show_head;
    private $show_meta;

    private $head_explicitly_excluded;
    private $head_explicitly_included;
    private $meta_explicitly_excluded;
    private $meta_explicitly_included;

    private $section;
    private $prev_section;
    private $last_line_blank;
    private $div_written;
    private $add_to_output;
    private $code;
    private $file_combined;

    private $my_html;

    private $name;
    private $data;

    private const WP_REPO_URL = '';
    private const WP_PLUGIN_DIR_URL = 'https://plugins.svn.wordpress.org/';
    private const WP_DOWNLOAD_DIR_URL = 'https://downloads.wordpress.org/plugin/';
    private const WP_PLUGIN_PAGE_URL = 'https://wordpress.org/extend/plugins/';
    private const WP_PLUGIN_SUPPORT_URL = 'https://wordpress.org/support/plugin/';

    private const LINE_END = "\r\n";

    private const STR_LEN = 30;

    private const QUOTES = array(
     '“' => '',
     '”' => '',
     '‘' => '',
     '’' => '',
     '&#8220;' => '',
     '&#8221;' => ''
    );

    // private static $c = 0;
    // private const COLOURS_DEBUG = array (
    //   0 => 'red',
    //   1 => 'orange',
    //   2 => 'yellow',
    //   3 => 'green',
    //   4 => 'blue',
    // );

    /**
     * Construct an instance of Generate_Output.
     *
     * @since 2.0.0
     */
    public function __construct() {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $this->initialise();
    }

    /**
     * Output the readme
     *
     * Function to output the results of the readme
     *
     * @uses   display_links     Show the links section
     * @uses   $this->get_file      Fetch file
     * @uses   prp_get_readme      Fetch the readme
     * @uses   prp_get_section_name  Get the name of the current section
     * @uses   this->get_list      Extract a list
     * @uses   $this->is_it_excluded    Check if the current section is excluded
     * @uses   format_list      Strip a user or tag list and add links
     * @uses   // prp_log             Output debug info to the WP error log
     *
     * @param string $content readme filename
     * @param string $paras Parameters
     * @return string Output.
     */
    public function readme_parser( string|array|null $paras = null, string $content = '' ): string {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( '---------------- ' . __FUNCTION__ . ' ----------------' );
      // prp_log( '---------------- ' . self::COLOURS_DEBUG[ self::$c++ ] );

      $this->initialise();
      try {
        $this->normalise_parameters( $paras );
      } catch ( PRP_Exception $e ) {
        throw $e;
      }
      $attributes = shortcode_atts( array( 'exclude' => '', 'hide' => '', 'include' => '', 'target' => '_blank', 'nofollow' => '', 'ignore' => '', 'cache' => '5', 'version' => '', 'mirror' => '', 'links' => 'bottom', 'name' => '' ), $this->parameters );
      extract( $attributes );

      // prp_log( 'all ' . __FUNCTION__ . ' shortcode attributes (arg1 + defaults)', $attributes );
      // prp_log( 'content (arg2)', $content );

      if ( true === $this->toggle_global_shortcodes() ) {

        try {

          $this->validate_sections( $exclude, $include );
          $this->determine_show_head();
          $this->determine_show_links( $links );
          // prp_log( 'exclude', $this->exclude );
          // prp_log( 'cache', $cache );
          // prp_log( 'content', $content );
          // prp_log( 'hide', $hide );
          // prp_log( 'ignore', $ignore );
          // prp_log( 'links', $this->links );
          // prp_log( 'mirror', $mirror );
          // prp_log( 'nofollow', $nofollow );
          // prp_log( 'target', $target );
          // prp_log( 'version', $version );
          // prp_log( 'include', $this->include );
          $cache_key = 'prp_' . md5( $this->exclude . $cache . $content . $hide . $ignore . $this->links . $mirror . $nofollow . $target . $version . $this->include );
          // prp_log( 'cache_key', $cache_key );
          $result = $this->get_cache( $cache_key, $cache );

          if ( false === $result ) {
            $this->plugin_url = $content;
            $this->hide = strtolower( $hide );
            $this->ignore = $this->get_list( $ignore, ',,', 'ignore' );
            $this->mirror = $this->get_list( $mirror, ',,', 'mirror' );
            $this->version = $version;
            $this->target = $target;
            $this->nofollow = 'yes' === strtolower( $nofollow ) ? ' rel="nofollow"' : '';

            try {
              $this->get_readme( $this->plugin_url, $this->version );
            } catch( PRP_Exception $e ) {
              $e->get_prp_nice_error();
            }

            if ( false === $this->file_data ) {
              $this->process_invalid_file();
            } else {
              $this->process_valid_file();
            }

            // Send the resultant code back, plus encapsulating DIV and version comments. Use double quotes to permit linebreaks ("\n")

            $this->content = "\n<!-- " . plugin_readme_parser_name . " v" . plugin_readme_parser_version . " -->\n<div class=\"np-notepad\">" . $this->my_html . "</div>\n<!-- End of " . plugin_readme_parser_name . " code -->\n";

            // Cache the results


            $this->set_cache( true );

            // prp_log( 'set cache', ( strlen( $result ) > self::STR_LEN ? substr( $result, 0, self::STR_LEN ) . '…' : $result ) );
            // prp_log( 'cache just set ' . $this->cache_key, $this->get_cache( $this->cache_key ) );

          } else {
            // prp_log( 'cached content type', gettype( $result ) );
              $this->content = $result;
          }

        } catch ( PRP_Exception $e ) {
          // throw $e;
          return $e->get_prp_nice_error();
        } finally {
          $this->toggle_global_shortcodes();
        }
      }

      // prp_log( __( '---------------- README PARSER -- end ---------', plugin_readme_parser_domain ) );

      // prp_log( 'content ' . ( strlen( $content ) > self::STR_LEN ? substr( $this->content, 0, self::STR_LEN ) . '…' : $this->content ) );
      // return '<p>Hello World</p>';
      return $this->content;
    }

    /**
     * readme information
     *
     * Function to output a piece of requested readme information
     *
     * @uses   prp_get_readme      Fetch the readme file
     *
     * @param string[] $para Parameters
     * @param string $content Post content
     * @return  string          Output
     */
    public function readme_info( array $paras = array(), string $content = '' ): string {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( '----------------- ' . __FUNCTION__ . ' -----------------' );
      // prp_log( '----------------- ' . self::COLOURS_DEBUG[ self::$c++ ] );

      $output = '';
      $this->initialise();
      try {
        $this->normalise_parameters( $paras );
      } catch ( PRP_Exception $e ) {
        throw $e;
      }
      $attributes = shortcode_atts( array( 'name' => '', 'target' => '_blank', 'nofollow' => '', 'data' => '', 'cache' => '5' ), $this->parameters );
      extract( $attributes );

      // prp_log( 'all ' . __FUNCTION__ . ' shortcode attributes (arg1 + defaults)', $attributes );
      // prp_log( 'content (arg2)', $content );

      if ( true === $this->toggle_global_shortcodes() ) {

        try {

          $this->data = strtolower( $data );
          $this->content = $content;

          $result = $this->get_cache( 'prp_info_' . md5( $name . $cache ), $cache );

          if ( false === $result ) {

            $this->name = $name;
            $this->target = $target;
            $this->nofollow = 'yes' === strtolower( $nofollow ) ? ' rel="nofollow"' : '';

            $this->parse_readme_info();
            // prp_log( 'file data', reset( $this->file_data ) );
            $this->set_cache();

          } else {

            // Cache retrieved, so get information from resulting array

            // prp_log( 'cached plugin name', $result[ 'name' ] );
            // prp_log( 'cached version', $result[ 'version' ] );

            $this->plugin_name = $result[ 'name' ];
            $this->version = $result[ 'version' ];

            // prp_log( 'this version', $this->version );
            // prp_log( 'this plugin name', $this->plugin_name );
          }

          if ( '' === $output ) {

            // prp_log( 'data', $data );

            // Need to have this try–catch block so that any remaining shortcodes are evaluated. Without it, the shortcodes are displayed as-is.
            try {
              $output = $this->parse_the_data_parameter();
            } catch ( PRP_Exception $e ) {
              $output = $e->get_prp_nice_error();
            }

          }

        } catch ( PRP_Exception $e ) {
          // throw $e;
          return $e->get_prp_nice_error();
        } finally {
          $this->toggle_global_shortcodes();
        }
      }
      // prp_log( __FUNCTION__ . ' output', $output );
      return do_shortcode( $output );
    }

    private function parse_readme_info(): void {

      // Get the readme file

      try {
        $this->get_readme( $this->name );
        $this->plugin_name = $this->file_data[ 'name' ];
        if ( false === $this->file_data ) {
          $this->process_invalid_file();
        } else {
          $this->get_plugin_name_and_version();
        }

      } catch ( PRP_Exception $e ) {
        throw new PRP_Exception( 'Failed to parse readme file', PRP_Exception::PRP_ERROR_BAD_FILE, $e);
      }

    }

    /**
     * Normalises the quotation marks to straight ones from curly
     * ones.
     * Fixes the erroneous array member created by having a space
     * in 'Upgrade Notice' and names of plugins.
     *
     * @param  $parameters  string|string[]|null  The text to normalise the quotation marks in.
     * @return        null|WP_Error  The text containing normalised quotation marks.
     */
    private function normalise_parameters( string|array|null $parameters = null ): null|WP_Error {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( __( 'Parameters (raw)', plugin_readme_parser_domain), $parameters );

      if ( null === $parameters ) {
        $this->parameters = $parameters;
        // prp_log( 'Parameters are null', $parameters );
      } else {
        switch( gettype( $parameters ) ) {
          case 'array':
            $normalised_parameters = array();
            foreach ( $parameters as $key => $value ) {
              // prp_log( $key . ': ' . $value );
              $normalised_parameters[$key] = str_replace(array_keys(self::QUOTES), array_values(self::QUOTES), $parameters[$key]);
              // prp_log( $key . ': ' . $normalised_parameters[$key] );
            }
            if ( isset( $normalised_parameters[0] ) ) {
              // If there's a value in [0], it means there's a space in the value, e.g. 'Upgrade Notice'. Need to join it up with the apprpriate key and remove the erroneous 0 key.
              if ( isset( $normalised_parameters[ 'exclude' ] ) ) {
                $normalised_parameters['exclude'] .= ' ' . $normalised_parameters[0];
              } else if ( isset( $normalised_parameters[ 'include' ] ) ) {
                $normalised_parameters['include'] .= ' ' . $normalised_parameters[0];
              } else if ( isset( $normalised_parameters[ 'name' ] ) ) {
                $normalised_parameters['name'] .= ' ' . $normalised_parameters[0];
              } else {
                throw new PRP_Exception( 'Parameter is invalid. Expected <samp><kbd>exclude</kbd></samp>, <samp><kbd>include</kbd></samp> or <samp><kbd>name</kbd></samp>; got <samp><kbd>' . print_r( $normalised_parameters[0], true ) . '</kbd></samp>', PRP_Exception::PRP_ERROR_BAD_INPUT);
              }
              unset( $normalised_parameters[0] );
            }
            $this->parameters = $normalised_parameters;
          break;
          case 'string':
            $normalised_parameters = str_replace(array_keys(self::QUOTES), array_values(self::QUOTES), $parameters);
            $this->parameters = $parameters;
          break;
          default:
            $this->parameters = null;
            throw new PRP_Exception( 'Parameter type is incorrect. Expected <samp><kbd>string|array|null</kbd></samp>; got <samp><kbd>' . gettype( $parameters ) . '</kbd></samp>: ' . print_r( $parameters, true ), PRP_Exception::PRP_ERROR_BAD_INPUT ) ;
        }
      }
      // prp_log( __( 'Parameter ' . gettype( $parameters ) . ' (normalised)', plugin_readme_parser_domain), print_r( $this->parameters, true ) );
      return null;
    }

    /**
     * Determines whether the links should be shown or not.
     */
    private function determine_show_links( string $links ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( 'show links (before)', $this->show_links );
      // prp_log( 'include', $this->include );
      // prp_log( 'exclude', $this->exclude );
      if ( '' !== $this->include ) {
        if ( $this->is_it_excluded( 'links', $this->include ) ) {
          $this->show_links = true;
        }
      } else {
        if ( !$this->is_it_excluded( 'links', $this->exclude ) ) {
          $this->show_links = true;
        }
      }
      $this->links = $this->show_links ? strtolower( $links ) : '';;
      // prp_log( 'show links (after)', $this->show_links );
    }

    /**
     */
    private function determine_show_head(): void {

      // $this->show_head = false;
      // $this->show_meta = false;

      $this->head_explicitly_excluded = $this->is_it_excluded( 'head', $this->exclude );
      $this->head_explicitly_included = $this->is_it_excluded( 'head', $this->include );
      $this->meta_explicitly_excluded = $this->is_it_excluded( 'meta', $this->exclude );
      $this->meta_explicitly_included = $this->is_it_excluded( 'meta', $this->include );

      // prp_log( __( 'head exp exc', plugin_readme_parser_domain ), ( $this->head_explicitly_excluded ? 'true' : 'false' ) );
      // prp_log( __( 'head exp inc', plugin_readme_parser_domain ), ( $this->head_explicitly_included ? 'true' : 'false' ) );
      // prp_log( __( 'meta exp exc', plugin_readme_parser_domain ), ( $this->meta_explicitly_excluded ? 'true' : 'false' ) );
      // prp_log( __( 'meta exp inc', plugin_readme_parser_domain ), ( $this->meta_explicitly_included ? 'true' : 'false' ) );

      if ( !$this->head_explicitly_excluded ) {
        if ( !$this->meta_explicitly_excluded ) {
          if ( $this->meta_explicitly_included ) {
            $new_include = str_replace( 'meta', 'head', $this->include );
            prp_log( __( "Cannot include the meta data part of the head without the summary part:\n  Parameters supplied:   include=\"" . $this->include . "\"\n  Parameters changed to: include=\"" . $new_include . "\"", plugin_readme_parser_domain ), "", false, true );
            // Add the head to the include parameter value:
            $this->include = $new_include;
            // Set show_head to be true instead of false:
            $this->show_head = true;
            $this->show_meta = true;
          } else {
            $this->show_head = true;
            $this->show_meta = true;
          }
        }
      }
      if ( !$this->head_explicitly_included ) {
        if ( $this->meta_explicitly_excluded ) {
          $this->show_head = true;
          $this->show_meta = false;
        }
      }
      // prp_log( __( 'show head', plugin_readme_parser_domain ), ( $this->show_head ? 'true' : 'false' ) );
      // prp_log( __( 'show meta', plugin_readme_parser_domain ), ( $this->show_meta ? 'true' : 'false' ) );
    }

    /**
     * Make sure the exclude and include parameters are not both
     * specified.
     *
     * @param string $exclude The sections to be excluded from
     * the display.
     * @param string $include The sections to be included in the
     * display.
     * @throws PRP_Exception If the exclude and include parameters
     * are both specified.
     * @return true if the exclude and include parameters
     * are not both specified.
     */
    private function validate_sections( string $exclude, string $include ): bool {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( '' !== $exclude ) &&
           ( '' !== $include ) ) {
        throw new PRP_Exception( 'Parameters \'include\' and \'exclude\' cannot both be specified in the same shortcode', PRP_Exception::PRP_ERROR_BAD_INPUT );
      } else {
        $this->exclude = strtolower( $exclude );
        $this->include = strtolower( $include );
        return true;
      }
    }

    /**
     * Read the file that is stored line by line in the provided
     * array.
     *
     * @param void $ This method takes no arguments.
     * @return void
     */
    private function read_file_array(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $count = count( $this->file_array );
      // prp_log( __( 'readme file has ' . $count . ' lines', plugin_readme_parser_domain ) );
      for ( $i = 0; $i < $count; $i++ ) {
        // prp_log_truncated_line( $this->file_array[ $i ], $i );

        $this->add_to_output = true;

        // Remove non-visible character from input - various characters can sneak into
        // text files and this can affect output

        $this->file_array[ $i ] = rtrim( ltrim( ltrim( $this->file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

        // If the line begins with equal signs, replace with the standard hash equivalent

        $this->standardise_headings_markup( $i );

        // If an asterisk is used for a list, but it doesn't have a space after it, add one!
        // This only works if no other asterisks appear in the line

        $this->standardise_lists( $i );

        // Track current section. If very top, make it "head" and save as plugin name

        $this->track_current_section();

        $this->read_section();

        // Get the download link for the most recent version

        $this->read_download_link( $i );

        // if ( 'head' === $this->section ) {
        //   // prp_log( 'Just before \'head\', $this->add_to_output===' . ( $this->add_to_output ? 'true' : 'false' ) );
        // }

        $this->read_head( $i );

        // if ( 'Description' === $this->section ) {
        //   // prp_log( 'ADD TO OUTPUT', $this->add_to_output );
        // }

        $this->read_screenshots();

        // Add current line to output, assuming not compressed and not a second blank line

        // prp_log( __( 'test', plugin_readme_parser_domain ), array(
        //   'line no.'        => $i,
        //   'line'            => $this->file_array[ $i ],
        //   'last line blank' => $this->last_line_blank,
        //   'add to output'   => $this->add_to_output
        // ) );

        $this->add_current_line_to_output( $i );

      }
      $this->file_combined .= '</div>' . self::LINE_END;
    }

    /**
     * Add the HTML required to display the links section to the
     * current content.
     *
     * @param void $ This method takes no arguments.
     * @return void
     */
    private function display_links_section(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( $this->show_links ) &&
           ( 'bottom' === $this->links ) ) {
        $this->display_links();
      }
    }

    /**
     * Write out the HTML to a string ready for display.
     *
     * @param void $ This method takes no arguments.
     * @return void
     */
    private function write_html(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $titles_found = 0;
      $count = count( $this->file_array );

      for ( $i = 0; $i < $count; $i++ ) {

        // If Content Reveal plugin is active

        $titles_found = $this->write_content_reveal_plugin( $i, $titles_found );

        $this->normalise_html_code_tags( $i );

        if ( '' !== $this->file_array[ $i ] ) {
          $this->my_html .= $this->file_array[ $i ] . self::LINE_END;
        }
      }

      // Modify <CODE> and <PRE> with class to suppress translation

      $this->my_html = str_replace( '<code>', '<code class="notranslate">', str_replace( '<pre>', '<pre class="notranslate">', $this->my_html ) );
    }

    /**
     * Set all the member data to their initial values.
     *
     * @param void $ This method takes no arguments.
     * @return void
     */
    private function initialise(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $this->parameters = null;
      $this->content = '';

      $this->file_array = array();
      $this->file_data = array();

      $this->plugin_url = '';
      $this->plugin_name = '';
      $this->plugin_title = '';

      $this->cache = '';
      $this->cache_key = '';
      $this->exclude = '';
      $this->include = '';
      $this->hide = '';
      $this->links = '';
      $this->ignore = '';
      $this->mirror = '';
      $this->nofollow = '';
      $this->version = '';
      $this->target = '';
      $this->download = '';
      $this->metadata = '';

      $this->show_links = false;
      $this->show_head = false;
      $this->show_meta = false;

      $this->head_explicitly_excluded = false;
      $this->head_explicitly_included = false;
      $this->meta_explicitly_excluded = false;
      $this->meta_explicitly_included = false;

      $this->section = '';
      $this->prev_section = '';'';
      $this->last_line_blank = true;
      $this->div_written = false;
      $this->code = false;
      $this->file_combined = '';

      $this->my_html = '';

      $this->name = '';
      $this->data = '';

      // prp_log( __('all the things have been initialised', plugin_readme_parser_domain), $this );
    }

    private function standardise_headings_markup( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( '=== ' === substr( $this->file_array [$i ], 0, 4 ) ) {
        $this->file_array[ $i ] = str_replace( '===', '#', $this->file_array[ $i ] );
        $this->section = $this->get_section_name( $this->file_array[ $i ], 1 );
        // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
      } else {
        if ( '== ' === substr( $this->file_array[ $i ], 0, 3 ) ) {
          $this->file_array[ $i ] = str_replace( '==', '##' , $this->file_array[ $i ] );
          $this->section = $this->get_section_name( $this->file_array[ $i ], 2 );
          // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
        } else {
          if ( '= ' === substr( $this->file_array[ $i ], 0, 2 ) ) {
            $this->file_array[ $i ] = str_replace( '=', '###', $this->file_array[ $i ] );
            // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
          }
        }
      }
    }

    private function standardise_lists( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( '*' === substr( $this->file_array[ $i ], 0, 1 ) ) &&
           ( ' ' !== substr( $this->file_array[ $i ], 0, 2 ) ) &&
           ( false === strpos( $this->file_array[ $i ], '*', 1 ) ) ) {
        $this->file_array[ $i ] = '* ' . substr( $this->file_array[ $i ], 1 );
      }
    }

    private function track_current_section(): void {

      if ( ( $this->section !== $this->prev_section ) &&
           ( '' === $this->prev_section ) ) {

        // If a plugin name was not specified attempt to use the name parameter. If that's not set, assume
        // it's the one in the readme file header

        // // prp_log( __( 'name (from args)', plugin_readme_parser_domain ), $this->name );

        if ( '' === $this->plugin_name ) {
          if ( '' === $this->name ) {
            $this->plugin_name = str_replace( ' ', '-', strtolower( $this->section ) );
          } else {
            $this->plugin_name = $this->name;
          }
        }

        $this->plugin_title = $this->section;
        $this->add_to_output = false;
        $this->section = 'head';
        // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );

      }
    }

    private function read_section(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( '' !== $this->include ) {
        $this->read_included_sections();
      } else {
        $this->read_excluded_sections();
      }
      $this->read_excluded_line();
      $this->read_links();
      $this->prev_section = $this->section;

      // prp_log( __( '(previous) section', plugin_readme_parser_domain ), $this->prev_section );
    }

    private function read_included_sections(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // Is this an included section?

      if ( $this->is_it_excluded( $this->section, $this->include ) ) {
        // prp_log( __( 'included', plugin_readme_parser_domain ), $this->section );

        if ( $this->section !== $this->prev_section ) {
          if ( $this->div_written ) {
            $this->file_combined .= '</div>' . self::LINE_END;
          }
          $this->file_combined .= self::LINE_END . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $this->section ) ) ) . '">' . self::LINE_END;
          $this->div_written = true;
        }
      } else {
        $this->add_to_output = false;
      }
    }

    private function read_excluded_sections(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // Is this an excluded section?

      if ( $this->is_it_excluded( $this->section, $this->exclude ) ) {
        $this->add_to_output = false;
        // prp_log( __( 'excluded', plugin_readme_parser_domain ), $this->section );
      } else {
        if ( $this->section !== $this->prev_section ) {
          if ( $this->div_written ) {
            $this->file_combined .= '</div>' . self::LINE_END;
          }
          $this->file_combined .= self::LINE_END . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $this->section ) ) ) . '">' . self::LINE_END;
          $this->div_written = true;
        }
      }
    }

    private function read_excluded_line(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // Is it an excluded line?

      if ( $this->add_to_output ) {
        $exclude_loop = 1;
        while ( $exclude_loop <= $this->ignore[ 0 ] ) {
          if ( false !== strpos( $this->file_array[ $i ], $this->ignore[ $exclude_loop ], 0 ) ) {
            $this->add_to_output = false;
          }
        $exclude_loop++;
        }
      }
    }

    private function read_links(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( $this->links === strtolower( $this->section ) ) &&
           ( $this->section !== $this->prev_section ) ) {
        if ( $this->show_links ) {
          $this->file_array[ $i ] = $this->display_links( $this->download, $this->target, $this->nofollow, $this->version, $this->mirror, $this->plugin_name ) . $this->file_array[ $i ];
        }
      }
    }

    private function read_download_link( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( 'Stable tag:' === substr( $this->file_array[ $i ], 0, 11 ) ) {

        $this->version = substr( $this->file_array[ $i ], 12 );
        // prp_log( __( 'version', plugin_readme_parser_domain ), $this->version );
        $this->download = 'https://downloads.wordpress.org/plugin/' . $this->plugin_name . '.' . $this->version . '.zip';
        // // prp_log( __( 'download link', plugin_readme_parser_domain ), $this->download );

      }
    }

    private function read_head( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( $this->add_to_output ) {

        // prp_log( __( 'SECTION', plugin_readme_parser_domain ), $this->section );

        if ( 'head' === $this->section ) {
          $this->metadata = array(
            'exclude' => $this->exclude,
            'nofollow' => $this->nofollow,
            'version' => $this->version,
            'download' => isset( $this->download ) ? $this->download : '',
            'target' => $this->target,
          );
          $this->add_to_output = $this->add_head_meta_data_to_output( $i );
        }
      }
    }

    private function read_screenshots(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( 'Screenshots' === $this->section ) {
        // Do not display screenshots: any attempt to access the screenshots on WordPress' SVN servers is met with an HTTP 403 (forbidden) error.
        $this->add_to_output = false;
        // prp_log( __( 'Can\'t output screenshots.', plugin_readme_parser_domain ), '', true, false );
      }
    }

    private function add_current_line_to_output( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( '' !== $this->file_array[ $i ] or !$this->last_line_blank ) &&
         $this->add_to_output ) {
        $this->file_combined .= $this->file_array[ $i ] . self::LINE_END;
        // prp_log_truncated_line( 'Adding l.' . $i . ' ' . $this->file_array[ $i ] );

        if ( '' === $this->file_array[ $i ] ) {
          $this->last_line_blank = true;

        } else {
          $this->last_line_blank = false;
        }

      // } else {
        // prp_log_truncated_line( 'Not adding l.' . $i . ' ' . $this->file_array[ $i ] );

        }
    }

    private function write_html_title( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $this->title = substr( $this->file_array[ $i ], 4, strpos( $this->file_array[ $i ], '</h2>' ) - 4 );
      if ( $this->is_it_excluded( $this->title, $this->hide ) ) {
        $state = 'hide';
      } else {
        $state = 'show';
      }
    }

    private function normalise_html_code_tags( int $i ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // If first line of code multi-line, replace CODE with PRE tag

      if ( ( strpos( $this->file_array[ $i ], '<code>', 0 ) ) && ( !strpos( $this->file_array[ $i ], '</code>', 0 ) ) ) {
        $this->file_array[ $i ] = str_replace( '<code>', '<pre>', $this->file_array[ $i ] );
      }

      // If final line to code multi-line, replace /CODE with /PRE tag

      if ( ( strpos( $this->file_array[ $i ], '</code>', 0 ) ) && ( !strpos( $this->file_array[ $i ], '<code>', 0 ) ) ) {
        $this->file_array[ $i ] = str_replace( '</code>', '</pre>', $this->file_array[ $i ] );
      }

      // If all code is one line, replace CODE with PRE tags

      if ( ( strpos( $this->file_array[ $i ], '<code>', 0 ) ) && ( strpos( $this->file_array[ $i ], '</code>', 0 ) ) ) {
        if ( '' === ltrim( strip_tags( substr( $this->file_array[ $i ], 0, strpos( $this->file_array[ $i ], '<code>', 0 ) ) ) ) ) {
          $this->file_array[ $i ] = str_replace( 'code>', 'pre>', $this->file_array[ $i ] );
        }
      }
    }

    private function write_content_reveal_plugin( int $i, string $titles_found ): int {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      if ( is_plugin_active( 'simple-content-reveal/simple-content-reveal.php' ) ) {

        // If line is a sub-heading add the first part of the code

        if ( '<h2>' === substr( $this->file_array[ $i ], 0, 4 ) ) {

          // Extract title and check if it should be hidden or shown by default

          $this->write_html_title( $i );

          // Call Content Reveal with heading details and replace current line

          $titles_found = $this->write_content_reveal_heading( $i, $titles_found );
        }

        // If a DIV is found and previous section is not hidden add the end part of code

        $this->write_content_reveal_end( $i, $titles_found );
      }
      return $titles_found;
    }

    private function write_content_reveal_heading( int $i, string $titles_found ): int {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $this->file_array[ $i ] = acr_start( '<h2>%image% ' . $this->title . '</h2>', $this->title, $this->state, $scr_url, $scr_ext );
      return ++$titles_found;
    }

    private function write_content_reveal_end( int $i, string $titles_found ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( '</div>' === $this->file_array[ $i ] ) && ( 0 < $titles_found ) ) {
        $this->file_array[ $i ] = acr_end() . self::LINE_END . $this->file_array[ $i ];
      }
    }

    private function process_valid_file(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( __( 'file_data', plugin_readme_parser_domain ), $this->file_data );

      if ( isset( $this->file_data[ 'name' ] ) ) {
        $this->plugin_name = $this->file_data[ 'name' ];
      } else {
        $this->plugin_name = '';
      }
      // prp_log( 'plugin name', $this->plugin_name );

      // Split file into array based on CRLF

      $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $this->file_data[ 'file' ] );
      // prp_log( __( 'file_array', plugin_readme_parser_domain ), $this->file_array, false, true );

      // Count the number of lines and read through the array

      $this->read_file_array();

      // Display links section

      $this->display_links_section();

      // Call Markdown code to convert

      $this->my_html = \Michelf\MarkdownExtra::defaultTransform( $this->file_combined );

      // Split HTML again

      $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $this->my_html );
      $this->my_html = '';

      // Count lines of code and process one at a time

      $this->write_html();
    }

    private function process_invalid_file(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( false === $this->file_data ) {
        throw new PRP_Exception( 'The readme file ' . ( empty( $this->name ) ? '' : ' for \'' . $this->name . '\'' ) . ' is invalid', PRP_Exception::PRP_ERROR_BAD_FILE );

      } else if ( ( 0 < strlen( $this->file_data[ 'file' ] ) ) &&
                  ( 0 === substr_count( $this->file_data[ 'file' ], "\n" ) ) ) {
        throw new PRP_Exception( 'The readme file ' . ( empty( $this->name ) ? '' : ' for \'' . $this->name . '\'' ) . ' is invalid: there are no newlines', PRP_Exception::PRP_ERROR_BAD_FILE );

      } else {
        throw new PRP_Exception( 'The readme file ' . ( empty( $this->name ) ? '' : ' for \'' . $this->name . '\'' ) . ' is either missing or invalid', PRP_Exception::PRP_ERROR_BAD_FILE );

      }
    }

    private function set_cache( bool $save_this_content = false ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( 'attempting to set cache ' . $this->cache_key . ' to the ' . ( $save_this_content ? 'readme file' : 'plugin name and version' ) );

      $result = false;

      $cached_info = array();
      try {
        if ( is_numeric( $this->cache ) ) {
          if ( false === $save_this_content ) {
            $cached_info = array(
              'name'    => $this->plugin_name,
              'version' => $this->version,
            );
          } else {
            $cached_info = $this->content;
          }
          $transient = get_transient( $this->cache_key );
          if ( false === $transient ) {
            // prp_log( 'attempting to create new cache ' . $this->cache_key );
            $result = set_transient( $this->cache_key, $cached_info, 60 * $this->cache );
            // prp_log( 'new cache ' . $this->cache_key . ' created', $result ? true : false );
          } else {
            // Don't fail if the cache already exists
            // prp_log( 'cache ' . $this->cache_key . ' already exists' );
            $result = true;
          }
        } else {
          if ( 'no' !== strtolower( $this->cache ) ) {
            throw new PRP_Exception( 'Cache expiration is invalid. Expected integer; got ' . gettype( $this->cache ) . ' ' . $this->cache, PRP_Exception::PRP_ERROR_BAD_CACHE );
          // } else {
          //   prp_log( 'cache not in use' );
          }
        }
      } catch( PRP_Exception $e ) {
        $e->get_prp_nice_error();
      }
      // prp_log( 'cache ' . $this->cache_key . ' set', $result );
      if ( false === $result ) {
        $deleted = delete_transient( $this->cache_key );
        $deleted_msg = $deleted ? 'Cache has been deleted' : 'Cache was not deleted' . ( get_transient( $this->cache_key ) ? ', so it is still lurking' : ' because it doesn\'t exist' );
        throw new PRP_Exception( 'Failed to set cache ' . $this->cache_key . '. ' . $deleted_msg, PRP_Exception::PRP_ERROR_BAD_CACHE );
      }
    }

    private function get_cache( string $cache_key, string $cache ): bool|array|string {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // $result = false;
      $this->cache = $cache;
      $this->cache_key = $cache_key;
      // prp_log( 'looking for cache', $this->cache_key );

      if ( is_numeric( $this->cache ) ) {
        // prp_log( 'expiry time for cache ' . $this->cache_key . ' (minutes)', $this->cache );
        $result = get_transient( $this->cache_key );
        // prp_log( 'found cache ' . $this->cache_key, ( $result ? 'yes' : 'no' ) );
        // prp_log( 'cache ' . $this->cache_key . ' contains', $result );
        return $result;

      } else {
        // prp_log( 'expiry time for cache ' . $this->cache_key . ' is invalid', $this->cache );
        throw new PRP_Exception( 'Cache expiry time is invalid: ' . $this->cache, PRP_Exception::PRP_ERROR_BAD_CACHE );
      }
    }

    /**
     * Get the readme file
     *
     * Function to work out the filename of the readme and get it
     *
     * @since 1.2
     *
     * @param string $plugin_url readme name or URL
     * @return       void
     */
    private function get_readme( string $plugin_url, string $version = '' ): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( 'plugin url', $plugin_url );

      // Work out URL and fetch the contents

      // $plugin_url = 'example-plugin'; // for testing purposes

      if ( strpos( $plugin_url, '://' ) === false ) {
        $this->file_data[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
        $this->plugin_url = self::WP_PLUGIN_DIR_URL . $this->file_data[ 'name' ] . '/';
        // prp_log( __( '  url:        \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );

        if ( is_numeric( $version ) ) {
          $this->plugin_url .= 'tags/' . $version;
          // prp_log( __( '  tag url:    \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );

        } else {
          $this->plugin_url .= 'trunk';
          // prp_log( __( '  trunk url:  \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
        }

        $this->plugin_url .= '/readme.txt';
        // prp_log( __( '  readme.txt: \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
      }

      try {
        $result = $this->get_file();

        // Ensure the file is valid
        /** @todo When '$this->get_file' is updated, update this error-checking to match. */
        if ( ( $result[ 'rc' ] === 0 ) &&
             ( $result[ 'file' ] !== '' ) &&
             ( substr( $result[ 'file' ], 0, 9 ) !== '<!DOCTYPE' ) &&
             ( substr_count( $result[ 'file' ], "\n" ) !== 0 ) ) {

          // Return values

          $this->file_data[ 'file' ] = $result[ 'file' ];

          // return $array;

        } else {

          $this->file_data = false;
          throw new PRP_Exception( 'The readme file ' . ( empty( $this->name ) ? '' : ' for \'' . $this->name . '\'' ) . ' is invalid', PRP_Exception::PRP_ERROR_BAD_FILE );

          // prp_log( __( '  readme file is invalid', plugin_readme_parser_domain ) );
        }

      } catch ( PRP_Exception $e ) {
        throw $e;
      }
    }

    private function get_plugin_name_and_version(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // Split file into array based on CRLF

      $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $this->file_data[ 'file' ] );

      // Loop through the array

      $count = count( $this->file_array );
      for ( $i = 0; $i < $count; $i++ ) {

        // Remove non-visible character from input - various characters can sneak into
        // text files and this can affect output

        $this->file_array[ $i ] = rtrim( ltrim( ltrim( $this->file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

        // If first record extract plugin name

        if ( ( '' === $this->plugin_name ) &&
             ( 0 === $i ) ) {

          $pos = strpos( $this->file_array [ 0 ], ' ===' );
          if ( false !== $pos ) {
            $this->plugin_name = substr( $this->file_array[ 0 ], 4, $pos - 4 );
            $this->plugin_name = str_replace( ' ', '-', strtolower( $this->plugin_name ) );
          }
        }

        // Extract version number

        if ( 'Stable tag:' === substr( $this->file_array[ $i ], 0, 11 ) ) {
          $this->version = substr( $this->file_array[ $i ], 12 );
        }
      }
    }

    private function parse_the_data_parameter(): string {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $output = '';

      // prp_log( 'data', $this->data );

      $msg = '';
      $code = PRP_Exception::PRP_ERROR_NONE;

      if ( 'download' === $this->data ) {
        $plugin_name_found = '' !== $this->plugin_name;
        $version_found = '' !== $this->version;
        if ( $plugin_name_found &&
             $version_found ) {
          $output = '<a href="' . self::WP_DOWNLOAD_DIR_URL . $this->plugin_name . '.' . $this->version . '.zip" target="' . $this->target . '"' . $this->nofollow . '>' . $this->content . '</a>';
        } else if ( $plugin_name_found &&
                    !$version_found ) {
          $msg = 'The plugin version could not be found in the readme file. Without it, the link for the download file cannot be determined';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;

        } else if ( !$plugin_name_found &&
                    $version_found ) {
          $msg = 'The plugin name could not be found in the readme file. Without it, the link for the download file cannot be determined';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;

        } else {
          $msg = 'The plugin name and version number could not be found in the readme file. Without them, the link for the download file cannot be determined';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;
        }

      } else if ( 'version' === $this->data ) {
        if ( '' !== $this->version ) {
          $output = $this->version;
        } else {
          $msg = 'The plugin version number could not be found in the readme file';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;
        }

      } else if ( 'forum' === $this->data ) {
        if ( '' !== $this->plugin_name ) {
          $output = '<a href="https://wordpress.org/support/plugin/' . $this->plugin_name . '" target="' . $this->target . '"' . $this->nofollow . '>' . $this->content . '</a>';
        } else {
          $msg = 'The plugin name was not given in the shortcode parameters. It\'s needed to obtain the link for the support forum';
          $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
        }

      } else if ( 'wordpress' === $this->data ) {
        if ( '' !== $this->plugin_name ) {
          $output = '<a href="https://wordpress.org/extend/plugins/' . $this->plugin_name . '/" target="' . $this->target . '"' . $this->nofollow . '>' .$this->content . '</a>';
        } else {
          $msg = 'The plugin name was not given in the shortcode parameters. It\'s needed to determine the link to the plugin in the WordPress plugin directory';
          $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
        }

      } else {
        $msg = 'The <samp><kbd>data</kbd></samp> parameter in the shortcode is invalid' . ( '' === $this->data ? '' : ': <samp><kbd>data="' . $this->data . '"</kbd></samp>' );
        $code = PRP_Exception::PRP_ERROR_BAD_INPUT;

      }
      if ( '' === $output ) {
        $msg = 'The <kbd><samp>data</kbd></samp> parameter in the shortcode is ' . ( '' === $this->data ? 'missing' : 'invalid: <samp><kbd><kbd>data="' . $this->data . '"</kbd></samp>' );
        $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
      }

      if ( '' !== $msg &&
           PRP_Exception::PRP_ERROR_NONE !== $code ) {
        throw new PRP_Exception( $msg, $code );
      }
      return $output;
    }

    /**
     * Toggles the global shortcodes on and off.
     *
     * @param void $ This method has no parameters.
     * @throws PRP_Exception on failure.
     * @return true|string True on success; an error message on
     * fail.
     */
    function toggle_global_shortcodes(): bool|string {

      prp_log( 'method', __FUNCTION__ );
      prp_log( 'arguments', func_get_args() );

      // try {
        $result = prp_toggle_global_shortcodes( $this->content );
        // if ( is_wp_error( $result ) ) {
        if ( $this->content !== $result ) {
          // prp_log( 'result', $result );
          throw new PRP_Exception( $result );
        }
        return true;
      // } catch ( PRP_Exception $e ) {
      //   return $e->get_prp_nice_error();
      // }
    }

    /**
     * @deprecated 2.0.0 This method is obsolete and will be
     * removed in a future version. There is no replacement because
     * the plugin does not have the required access to the
     * WordPress server.
     *
     * @author dartiss
     * @since 2.0.0 Deprecated.
     *
     * @param $ This method has no srguments.
     * @throws PRP_Exception on being invoked.
     * @return void
     */
    function readme_banner(): void {

      // prp_log( 'method', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      throw new PRP_Exception( 'The <samp><kbd>readme_banner</kbd></samp> shortcode is obsolete. Please use either the <samp><kbd>readme</kbd></samp> or <samp><kbd>readme_info</kbd></samp> shortcodes', PRP_Exception::PRP_ERROR_BAD_INPUT );
    }

    /**
     * Gets the section name from the current line in the readme file.
     *
     * @author dartiss
     * @since 1.0
     *
     * @param string $readme_line The current line in the readme file.
     * @param int $start_pos The position in the line to start
     * looking from.
     * @return string The section name given in the current line in
     * the readme file.
     */
    function get_section_name( string $readme_line, int $start_pos ): string {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $hash_pos = strpos( $readme_line, '#', $start_pos + 1 );

      if ( $hash_pos ) {
        $section = substr( $readme_line, $start_pos + 1, $hash_pos - $start_pos - 2 );
      } else {
        $section = substr( $readme_line, $start_pos + 1 );
      }

      // prp_log( __( '  Get section name:', plugin_readme_parser_domain ) );
      // prp_log( __( '  section name: \'' . $section . '\'', plugin_readme_parser_domain ) );

      return $section;
    }

    /**
     * Displays predefined links relevant to the plugin.
     *
     * The links displayed are:
     * * the ZIP archive of the latest version of the plugin for
     * download
     * * the official WordPress page for the plugin
     * * the WordPress support forum for the plugin
     *
     * Display of the links is controlled by the 'exclude'/'include'
     * parameters of the 'readme' shortcode.
     *
     * @author dartiss, pandammonium
     * @since 1.2
     * @since 2.0.0 Uses constants for the URLs. Updates the link
     * text, adding $plugin_nice_name to avoid using the vague 'this
     * plugin' in display text.
     *
     * @param void $ This method has no arguments.
     * @return void
     *
     * @todo Consider replacing the long list of arguments with an
     * array. Alternatively, move to Generate_Output.
     * @todo Consider making the link text customisable. How?
     * @todo Consider throwing an exception if the version cannot be
     * found.
     */
    function display_links(): void {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( __( '  Display links:', plugin_readme_parser_domain ) );
      // prp_log( __( '  download link: \'' . $download . '\'', plugin_readme_parser_domain ) );
      // prp_log( __( '  target:        \'' . $target . '\'', plugin_readme_parser_domain ) );
      // prp_log( __( '  nofollow:      \'' . $nofollow . '\'', plugin_readme_parser_domain ) );
      // prp_log( __( '  version:       \'' . $version . '\'', plugin_readme_parser_domain ) );

      // Don't overwrite the combined file:
      $this->file_combined .= '<div markdown="1" class="np-links">' . self::LINE_END . '## Links ##' . self::LINE_END . self::LINE_END;

      if ( $this->version !== '' ) {
        $this->file_combined .= '<a class="np-download-link" href="' . $this->download . '" target="' . $this->target . '"' . $this->nofollow . '>Download the latest version of ' . $this->plugin_title . '</a> (v' . $this->version . ')<br /><br />' . self::LINE_END;

        // prp_log( __( '  version found; outputting download link', plugin_readme_parser_domain ) );

        // If any mirrors exist, add them to the output:
        if ( $this->mirror[ 0 ] > 0 ) {
          for ( $m = 1; $m <= $this->mirror[ 0 ]; $m++ ) {
            $this->file_combined .= '<a class="np-download-link" href="' . $this->mirror[ $m ] . '" target="' . $this->target . '"' . $this->nofollow . '>Download ' . $this->plugin_title . ' from mirror ' . $m . '</a><br />' . self::LINE_END;
            // prp_log( __( '  mirror[' . $m . ']: ', plugin_readme_parser_domain ) . $this->mirror[ $m ] );
          }
          $this->file_combined .= '<br />';
        } else {
          // prp_log( __( '  mirror:        \'none\'', plugin_readme_parser_domain ) );
        }

      } else {
        // prp_log( __( '  no version, therefore no download link', plugin_readme_parser_domain ) );
        $this->file_combined .= '<span class="np-download-link"><span class="error">' . plugin_readme_parser_name . '</span>: No download link is available as the version number could not be found</span><br /><br />' . self::LINE_END;
      }

      $this->file_combined .= '<a href="' . self::WP_PLUGIN_PAGE_URL . $this->plugin_name . '/" target="' . $this->target . '"' . $this->nofollow . '>Visit the official WordPress plugin page for ' . $plugin_title . '</a><br />' . self::LINE_END;
      $this->file_combined .= '<a href="' . self::WP_PLUGIN_SUPPORT_URL . $this->plugin_name . '" target="' . $this->target . '"' . $this->nofollow . '>Need help? Visit the WordPress support forum for ' . $this->plugin_title . '</a><br />' . self::LINE_END . '</div>' . self::LINE_END;

    }

    /**
     * Determine which parts of the head meta data, if any, should
     * be added to the output.
     *
     * The head comprises the plugin title/name, the meta data and
     * a summary/description of the plugin. There may be one or
     * more blank lines. This function deals with the meta data
     * only.
     *
     * The meta data is the labelled data, such as tags, licence
     * and contributors. It is added to the output if one of the
     * following is true:
     * * $show_head === $show_meta === true
     * * $show_head === false and $show_meta === true.
     *
     * The summary is added to the output if one of the following
     * is true:
     * * $show_head === $show_meta === true
     * * $show_head === true and $show_meta === false.
     *
     * @author pandammonium
     * @since 2.0.0 Abstracted from Generate_Output.
     *
     * @param int $i The number of the
     * @return bool True if this line should be added to the
     * output, otherwise false.
     */
    function add_head_meta_data_to_output( $i ): bool {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $add_to_output = true;

      if ( $this->show_head || $this->show_meta ) {

        // prp_log_truncated_line( 'checking ' . ( '' === $this->file_array[ $i ] ? '\'\'' : $this->file_array[ $i ] ) );

        if ( $this->line_is_head_meta_data( $i ) ) {

          // Process meta data from top

          if ( !$this->show_meta ) {
            // prp_log( __( 'exclude all meta', plugin_readme_parser_domain ) );
            $add_to_output = false;

          } else if ( ( 'Requires at least:' === substr( $this->file_array[ $i ], 0, 18 ) ) &&
               ( $this->is_it_excluded( 'requires', $this->metadata[ 'exclude' ] ) ) ) {
            // prp_log( __( 'exclude WP req', plugin_readme_parser_domain ) );
            $add_to_output = false;

          } else if ( ( 'Requires PHP:' === substr( $this->file_array[ $i ], 0, 18 ) ) &&
               ( $this->is_it_excluded( 'requires php', $this->metadata[ 'exclude' ] ) ) ) {
            // prp_log( __( 'exclude PHP req', plugin_readme_parser_domain ) );
            $add_to_output = false;

          } else if ( ( 'Tested up to:' === substr( $this->file_array[ $i ], 0, 13 ) ) &&
               ( $this->is_it_excluded( 'tested', $this->metadata[ 'exclude' ] ) ) ) {
            // prp_log( __( 'exclude test', plugin_readme_parser_domain ) );
            $add_to_output = false;

          } else if ( ( 'License:' === substr( $this->file_array[ $i ], 0, 8 ) ) &&
               ( $this->is_it_excluded( 'license', $this->metadata[ 'exclude' ] ) ) ) {
            // prp_log( __( 'exclude licence', plugin_readme_parser_domain ) );
            $add_to_output = false;

          } else if ( 'Contributors:' === substr( $this->file_array[ $i ], 0, 13 ) ) {
            if ( $this->is_it_excluded( 'contributors', $this->metadata[ 'exclude' ] ) ) {
            // prp_log( __( 'exclude contrib', plugin_readme_parser_domain ) );
              $add_to_output = false;
            } else {
              // Show contributors and tags using links to WordPress pages
              $this->file_array[ $i ] = substr( $this->file_array[ $i ], 0, 14 ) . $this->format_list( substr( $this->file_array[ $i ], 14 ), 'c' );
            }

          } else if ( 'Tags:' === substr( $this->file_array[ $i ], 0, 5 ) ) {
            if ( $this->is_it_excluded( 'tags', $this->metadata[ 'exclude' ] ) ) {
            // prp_log( __( 'exclude tags', plugin_readme_parser_domain ) );
              $add_to_output = false;
            } else {
              $this->file_array[ $i ] = substr( $this->file_array[ $i ], 0, 6 ) . $this->format_list( substr( $this->file_array[ $i ], 6 ), 't' );
            }

          } else if ( 'Donate link:' === substr( $this->file_array[ $i ], 0, 12 ) ) {
            if ( $this->is_it_excluded( 'donate', $this->metadata[ 'exclude' ] ) ) {
            // prp_log( __( 'exclude donate', plugin_readme_parser_domain ) );
              $add_to_output = false;
            } else {
              // Convert the donation link to a hyperlink
              $text = substr( $this->file_array[ $i ], 13 );
              $this->file_array[ $i ] = substr( $this->file_array[ $i ], 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
            }

          } else if ( 'License URI:' === substr( $this->file_array[ $i ], 0, 12 ) ) {
            if ( $this->is_it_excluded( 'license uri', $this->metadata[ 'exclude' ] ) ) {
            // prp_log( __( 'exclude lic uri', plugin_readme_parser_domain ) );
              $add_to_output = false;
            } else {
              // Convert the licence URL to a hyperlink
              $text = substr( $this->file_array[ $i ], 13 );
              $this->file_array[ $i ] = substr( $this->file_array[ $i ], 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
            }

          } else if ( 'Stable tag:' === substr( $this->file_array[ $i ], 0, 11 ) ) {
            if ( $this->is_it_excluded( 'stable', $this->metadata[ 'exclude' ] ) ) {
            // prp_log( __( 'exclude stab tag', plugin_readme_parser_domain ) );
              $add_to_output = false;
            } else {
              // Link to the download given by the version
              $this->file_array[ $i ] = substr( $this->file_array[ $i ], 0, 12 ) . '<a href="' . $this->metadata[ 'download' ].'" style="max-width: 100%;">' . $this->metadata[ 'version' ] . '</a>';
            }
          }

          // If one of the header tags, add a BR tag to the end of the line.
          // As the output is meant to be XHTML, the BR tag needs to be closed. The proper way to do this is to have no space before the slash.

          $this->file_array[ $i ] .= '<br/>';
        } else {
          // prp_log( __( 'line is not meta data but is in head; add to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );
          return $this->show_head;
        }
      } else {
        $add_to_out = false;
      }

      // prp_log( __( 'add head meta data to output', plugin_readme_parser_domain ), ( $add_to_output ? 'true' : 'false' ) );
      return $add_to_output;
    }

    /**
     * Tests whether the current section is explicitly excluded from
     * the display.
     *
     * The section heading is compared to the value of the 'exclude'
     * parameter of the shortcode to see if it has been explicitly
     * excluded. Screenshots are always excluded because the plugin
     * is forbidden from accessing the image files on the WordPress
     * SVN server where they are stored.
     *
     * @author dartiss
     * @since 1.0
     *
     * @param string $tofind The section name.
     * @param string $exclude List of excluded sections.
     * @return bool True or false, depending on whether the section
     * was valid or invalid.
     */
    function is_it_excluded( string $tofind, string $exclude ): bool {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      $tofind = strtolower( $tofind );
      $exclude = strtolower( $exclude );
      $return = true;

      // prp_log( __( '  Is \'' . $tofind . '\' excluded?', plugin_readme_parser_domain ) );
      // prp_log( __( '  exclusion list: \'' . $exclude . '\'', plugin_readme_parser_domain ) );

      if ( 'screenshots' === $tofind ||
           'screenshot' === $tofind ) {
        $return = true;

      } else {
        if ( $tofind !== $exclude ) {
          // The presence of commas mean that they can be used as delimiters for searching in the parameter string, but they also mean that the search isn't as straightforward as it first appears.
          // Search in the middle:
          $pos = strpos( $exclude, ',' . $tofind . ',' );
          if ( $pos === false ) {
            // Search on the left:
            $pos = strpos( substr( $exclude, 0, strlen( $tofind ) + 1 ), $tofind . ',' );
            if ( $pos === false ) {
              // Search on the right:
              $pos = strpos( substr( $exclude, ( strlen( $tofind ) + 1 ) * -1, strlen( $tofind ) + 1 ), ',' . $tofind );
              if ( $pos === false ) {
                $return = false;
              }
            }
          }
        }
      }
      // if ( 'description' === $tofind ) {
      //   prp_log( __( '  \'' . $tofind . '\' is ' . ( $return ? 'explicitly excluded' : 'not explicitly excluded' ), plugin_readme_parser_domain ) );
      // }
      return $return;
    }


    /**
     * Formats lists for display.
     *
     * Each item in the list is cleaned up, and formatted as an
     * HTML link, ready for display.
     *
     * @author dartiss, pandammonium
     * @since 1.0
     * @since 2.0.0 Renamed from 'prp_strip_list' to
     * 'format_list'. Uses constants for the URLs. Renames return
     * variable for semantic clarity. Adds error handling.
     *
     * @param string $list A list of items, currently one of:
     * * plugin contributors
     * * plugin tags
     * @param string $type The type of list, currently one of:
     * * 't': tags
     * * 'c': contributors
     * @param string $target Link target.
     * @param string $nofollow Link nofollow.
     * @throws PRP_Exception if an unsupported list type is given.
     * @return string The list formatted as HTML.
     */
    function format_list( string $list, string $type ): string {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( 'list', $list );
      // prp_log( 'type', $type );
      // prp_log( 'target', $this->metadata[ 'target' ] );
      // prp_log( 'nofollow', $this->metadata[ 'nofollow' ] );

      $url = '';
      switch ( $type ) {
        case 'c':
          $url = WP_USER_DIR_URL;
        break;
        case 't':
          $url = WP_PLUGIN_TAGS_URL;
        break;
        default:
          throw new PRP_Exception( 'Invalid list type found: ' . $type, PRP_Exception::PRP_ERROR_BAD_DATA );
      }

      $startpos = 0;
      $number = 0;
      $endpos = strpos( $list, ',', 0 );
      $html = '';

      while ( $endpos !== false ) {
        ++$number;
        $item = trim( substr( $list, $startpos, $endpos - $startpos ) );
        // prp_log( 'item', $item );
        if ( $number > 1 ) {
          $html .= ', ';
        }
        $html .= '<a href="' . $url . $item . '" target="' . $this->metadata[ 'target' ] . '"' . $this->metadata[ 'nofollow' ] . '>' . $item . '</a>';
        $startpos = $endpos + 1;
        $endpos = strpos( $list, ',', $startpos );
      }

      $item = trim( substr( $list, $startpos ) );
      if ( $number > 0 ) {
        $html .= ', ';
      }
      $html .= '<a href="' . $url . $item . '" target="' . $this->metadata[ 'target' ] . '"' . $this->metadata[ 'nofollow' ] . '>' . $item . '</a>';

      return $html;
    }

    /**
     * Gets the given file from WordPress.
     *
     * Uses WordPress API to fetch a file from the WordPress server
     * and to and check the response code (rc):
     * * success: 0
     * * failure: -1
     *
     * @author dartiss, pandammonium
     * @since 1.6
     * @since 2.0.0 Enhances error handling.
     *
     * @param bool $header True if only the headers should be
     * fetched; false to fetch everything.
     * @return string[] The file contents and the server response.
     *
     * @todo Make error handling fully reliant on WP_Error and
     * exceptions rather than error codes with magic numbers.
     */
    function get_file( bool $header = false ): array {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( 'file url', $this->plugin_url );
      // prp_log( 'header', $header );

      $pos = strpos( strtolower( $this->plugin_url ), WP_PLUGIN_DIR_URL . '/' );
      if ( 0 === $pos ) {
        throw new PRP_Exception( 'The URL is missing the plugin name: <samp>' . substr( $this->plugin_url, $pos, strlen( $repo ) ) . '&lt;plugin-name&gt;/</samp>', PRP_Exception::PRP_ERROR_BAD_URL );
      }

      $file_return = array();
      $rc = 0;
      $error = '';
      if ( $header ) {
        $result = wp_remote_head( $this->plugin_url );
        if ( is_wp_error( $result ) ) {
          $error = 'Header: ' . $result -> get_error_message();
          $rc = -1;
          // throw new PRP_Exception( $error . '(' . $result->get_error_code . ')' );
        }
      } else {
        $result = wp_remote_get( $this->plugin_url );
        if ( is_wp_error( $result ) ) {
          $error = 'Body: ' . $result -> get_error_message();
          $rc = -1;
          // throw new PRP_Exception( $error . '(' . $result->get_error_code . ')' );
        } else {
          if ( isset( $result[ 'body' ] ) ) {
            $file_return[ 'file' ] = $result[ 'body' ];
          }
        }
      }

      $file_return[ 'error' ] = $error;
      $file_return[ 'rc' ] = $rc;
      if ( is_wp_error( $result ) ) {
        // prp_log( '  WP Error', $result );
        throw new PRP_Exception( $result->get_error_message(), $result->get_error_code() );
      } else {
        // prp_log( 'type of response', gettype( $result[ 'response' ] ) );
        // prp_log( 'response', $result[ 'response' ] );
        // prp_log( 'type of http response', gettype( $result[ 'http_response' ] ) );
        // prp_log( 'http response – null?', null === $result[ 'http_response' ] );
        if ( isset( $result[ 'response' ][ 'code' ] ) ) {
          $file_return[ 'response' ] = $result[ 'response' ][ 'code' ];
          // prp_log( 'file return', $file_return );
          // prp_log( 'response', $file_return[ 'response' ] );
          // prp_log( 'rc', $file_return[ 'rc' ] );
          // if ( isset( $file_return[ 'file' ] ) ) {
          //   prp_log( 'file', $file_return[ 'file' ] );
          // }
        }
        if ( isset( $result[ 'http_response' ] ) ) {
          $response = $result[ 'http_response' ]->get_response_object();
          // prp_log( 'type of response object', gettype( $response ) );
          try {
            $response->throw_for_status( false );
          } catch ( Exception $e) {
            throw new PRP_Exception( 'The URL <samp>' . $this->plugin_url . '</samp> of the readme file returned a <samp>' . $e->getMessage() . '</samp> error', PRP_Exception::PRP_ERROR_BAD_URL );
          }
        }

      }

      // prp_log( __( '  file out:    contents of readme file', plugin_readme_parser_domain ) );
      // prp_log( '  file out', $result );
      // prp_log( __( '  file return: contents of readme file', plugin_readme_parser_domain ) );
      // prp_log( '  file return', $file_return );

      // prp_log( 'file return (error)', $file_return[ 'error' ] );
      // prp_log( 'file return (rc)', $file_return[ 'rc' ] );
      // prp_log( 'file return (response)', $file_return[ 'response' ] );
      return $file_return;
    }

    /**
     * Extract parameters to an array
     *
     * Function to extract parameters from an input string and
     * add to an array
     *
     * @since 1.0
     *
     * @param string $input The input string that needs to be split.
     * @param string $separator The separator character used to split
     * the input string. If not specified, it defaults to a comma (,).
     * @param string $type Indicates the type of list; only used for debug purposes.
     * @return     string[]  Array of parameters.
     */
    function get_list( string $input, string $separator = '', string $type = '' ): array {   // Version 1.2

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      // prp_log( __( '  Get \'' . $type . '\' list:', plugin_readme_parser_domain ) );
      // prp_log( __( '  input:     \'' . $input . '\'', plugin_readme_parser_domain ) );
      // prp_log( __( '  separator: \'' . $separator . '\'', plugin_readme_parser_domain ) );

      if ( $separator === '' ) {
        $separator = ',';
      }
      $comma = strpos( strtolower( $input ), $separator );

      $item = 0;
      while ( $comma !== false ) {
        $item++;
        $content[ $item ] = substr( $input, 0, $comma );
        $input = substr( $input, $comma + strlen( $separator ) );
        $comma = strpos( $input, $separator );
      }

      if ( $input !== '' ) {
        $item++;
        $content[ $item ] = substr( $input, 0 );
      }

      $content[ 0 ] = $item;
      // prp_log( $content[0], '  content[0]:' );
      return $content;
    }

    /**
     * Checks that the the readme file line is head meta data.
     *
     * Tests to see whether the current line in the readme file is a line in
     * the head meta data (e.g. tags, licence, contributors) or not.
     *
     * @author pandammonium
     * @since 2.0.0 Abstracted from Generate_Output.
     *
     * @param int $i The current line number of the readme file being parsed.
     * @return bool True if the current line in the readme file is part of the
     * head meta data, otherwise false.
     */
    function line_is_head_meta_data( int $i ): bool {

      // prp_log( 'function', __FUNCTION__ );
      // prp_log( 'arguments', func_get_args() );

      if ( ( 'Contributors:' === substr( $this->file_array[ $i ], 0, 13 ) ) or
           ( 'Donate link:' === substr( $this->file_array[ $i ], 0, 12 ) ) or
           ( 'Tags:' === substr( $this->file_array[ $i ], 0, 5 ) ) or
           ( 'Requires at least:' === substr( $this->file_array[ $i ], 0, 18 ) ) or
           ( 'Requires PHP:' === substr( $this->file_array[ $i ], 0, 13 ) ) or
           ( 'Tested up to:' === substr( $this->file_array[ $i ], 0, 13 ) ) or
           ( 'Stable tag:' === substr( $this->file_array[ $i ], 0, 11 ) ) or
           ( 'License URI:' === substr( $this->file_array[ $i ], 0, 12 ) ) or
           ( 'License:' === substr( $this->file_array[ $i ], 0, 8 ) ) ) {
        return true;
      } else {
        return false;
      }
    }


  }


  $generator = new Generate_Output();

  if ( !function_exists( 'readme_parser' )) {
    /**
     * @api readme shortcode
     */
    function readme_parser( string|array|null $paras = null, string $content = '' ): string {

      prp_log( 'shortcode function', __FUNCTION__ );

      try {
        global $generator;
        // $generator = new Generate_Output();
        return $generator->readme_parser( $paras, $content );
      } catch ( PRP_Exception $e ) {
        return $e->get_prp_nice_error();
      } catch ( Exception $e ) {
        return plugin_readme_parser_name . ': something went wrong with the <samp><kbd>readme</kbd></samp> shortcode: ERROR ' . print_r( $e->getCode(), true ) . ' '  . print_r( $e->getMessage(), true );
      }
    }
    add_shortcode( 'readme', 'readme_parser' );
  }
  if ( !function_exists( 'readme_info' )) {
    /**
     * @api readme_info shortcode
     */
    function readme_info(array $paras = array(), string $content = '' ): string {

      // prp_log( 'shortcode function', __FUNCTION__ );

      try {
        global $generator;
        // $generator = new Generate_Output();
        return $generator->readme_info( $paras, $content);
      } catch ( PRP_Exception $e ) {
        return $e->get_prp_nice_error();
      } catch ( Exception $e ) {
        return plugin_readme_parser_name . ': something went wrong with the <samp><kbd>readme_info</kbd></samp> shortcode: ERROR ' . print_r( $e->getCode(), true ) . ' '  . print_r( $e->getMessage(), true );
      }
    }
    add_shortcode( 'readme_info', 'readme_info' );
  }
  if ( !function_exists( 'readme_banner' )) {
    /**
     * @api readme_banner shortcode
     * @deprecated 2.0.0 This shortcode is obsolete and should no
     * longer be used. There is no replacement because the plugin
     * does not have the required access to the WordPress server.
     * It will be removed from a future version of this plugin.
     */
    function readme_banner( string|array|null $paras = null, string $content = null ): string {

      prp_log( 'shortcode function', __FUNCTION__ );

      try {
        global $generator;
        $generator->readme_banner();
      } catch ( PRP_Exception $e ) {
        return $e->get_prp_nice_error();
      } catch ( Exception $e ) {
        return plugin_readme_parser_name . ': something went wrong with the obsolete <samp><kbd>readme_banner</kbd></samp> shortcode: ERROR ' . print_r( $e->getCode(), true ) . ' '  . print_r( $e->getMessage(), true );
      }
    }
    add_shortcode( 'readme_banner', 'readme_banner' );
  }

}
?>
