<?php
/**
 * Generate output
 *
 * Functions to generate required output
 *
 * @package Pandammonium-Readme-Parser
 * @since  1.0
 */

if ( !class_exists( 'Generate_Output' ) ) {
  /**
   * The plugin-readme parser and converter-to-HTML.
   */
  class Generate_Output {

    private string|array|null $parameters;
    private $content;

    private array $file_array;
    private $file_data;

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

    private const LINE_END = "\r\n";

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
      $this->parameters = null;
      $this->content = '';

      $this->file_array = array();
      $this->file_data = '';

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
      $this->prev_section = '';
      $this->last_line_blank = true;
      $this->div_written = false;
      $this->add_to_output = true;
      $this->code = false;
      $this->file_combined = '';

      $this->my_html = '';

      $this->name = '';
      $this->data = '';
    }

    /**
     * Output the readme
     *
     * Function to output the results of the readme
     *
     * @uses   prp_display_links     Show the links section
     * @uses   prp_get_file      Fetch file
     * @uses   prp_get_readme      Fetch the readme
     * @uses   prp_get_section_name  Get the name of the current section
     * @uses   prp_get_list      Extract a list
     * @uses   prp_is_it_excluded    Check if the current section is excluded
     * @uses   prp_strip_list      Strip a user or tag list and add links
     * @uses   prp_log             Output debug info to the WP error log
     *
     * @param  string    $content  readme filename
     * @param  string    $paras  Parameters
     * @return   string          Output
     */
    public function readme_parser( string|array|null $paras = null, string $content = '' ): string {


      // prp_log( __( '---------------- README PARSER ----------------', plugin_readme_parser_domain ) );
      // prp_log( __( '---------------- ' . self::COLOURS_DEBUG[ self::$c++ ], plugin_readme_parser_domain ) );

      $this->reset();

      prp_log( 'HERE', __FUNCTION__ );
      $this->content = $content;
      try {
        $result = prp_toggle_global_shortcodes( $this->content );
        if ( is_wp_error( $result ) ) {
          prp_log( 'result', $result );
          // throw new PRP_Exception( PRP_Exception::set_error_code_as_string( $result->get_error_message() ), $result->get_error_code() );
        }
        $this->normalise_parameters( $paras );

        extract( shortcode_atts( array( 'exclude' => '', 'hide' => '', 'include' => '', 'target' => '_blank', 'nofollow' => '', 'ignore' => '', 'cache' => '5', 'version' => '', 'mirror' => '', 'links' => 'bottom', 'name' => '' ), $this->parameters ) );

        // Ensure EXCLUDE and INCLUDE parameters aren't both included
        $this->exclude = strtolower( $exclude );
        $this->include = strtolower( $include );

        $this->validate_parameters();
        // $valid = $this->validate_parameters();
        // if ( '' !== $valid ) {
        //   return $valid;
        // }
      } catch ( PRP_Exception $e ) {
        throw $e;
      }

      // prp_log( 'cache', $cache );

      // Get cached output

      $this->cache = $cache;
      $result = $this->get_cache( 'prp_' . md5( $exclude . $hide . $include . $target . $nofollow . $ignore . $this->cache . $version . $mirror .$this->content ) );

      // prp_log( __( 'shortcode content', plugin_readme_parser_domain ),$this->content );
      // prp_log( __( 'shortcode parameters', plugin_readme_parser_domain ), $this->parameters );

      // prp_log( __( 'result', plugin_readme_parser_domain ), $result );

      if ( !$result ) {

        // prp_log( __( 'transient not cached', plugin_readme_parser_domain ) );

        // Set parameter values

        $this->plugin_url =$this->content;

        $this->hide = strtolower( $hide );
        $this->links = strtolower( $links );
        $this->ignore = prp_get_list( $ignore, ',,', 'ignore' );
        $this->mirror = prp_get_list( $mirror, ',,', 'mirror' );
        $this->version = $version;
        $this->target = $target;

        // prp_log( __( 'Sections to be included', plugin_readme_parser_domain), $include );
        // prp_log( __( 'Sections to be excluded', plugin_readme_parser_domain), $exclude );

        if ( 'yes' === strtolower( $nofollow ) ) {
          $this->nofollow = ' rel="nofollow"';
        }

        // Work out in advance whether links should be shown

        $this->should_links_be_shown();

        // Work out in advance whether the head should be shown

        $this->should_head_be_shown();

        // Work out filename and fetch the contents

        try{
          $this->file_data = $this->get_readme( $this->plugin_url, $this->version );
        } catch ( PRP_Exception $e ) {
          $e->get_prp_nice_error();
          $this->file_data = false;
        }
        // Ensure the file is valid

        if ( false !== $this->file_data ) {
          $this->process_valid_file();

        } else {
          $this->process_invalid_file();
        }

        // Send the resultant code back, plus encapsulating DIV and version comments. Use double quotes to permit linebreaks (\n)

        $this->content = "\n<!-- " . plugin_readme_parser_name . " v" . plugin_readme_parser_version . " -->\n<div class=\"np-notepad\">" . $this->my_html . "</div>\n<!-- End of " . plugin_readme_parser_name . " code -->\n";

        // Cache the results

        $this->set_cache( true );

      } else {

        // prp_log( __( 'transient already cached', plugin_readme_parser_domain ) );

        $this->content = $result;
      }

      try {
        $result = prp_toggle_global_shortcodes( $this->content );
        if ( is_wp_error( $result ) ) {
          prp_log( 'result', $result );
          // throw new PRP_Exception( PRP_Exception::set_error_code_as_string( $result->get_error_message() ), $result->get_error_code() );
        }
      } catch ( PRP_Exception $e ) {
        throw $e;
      }


      // prp_log( __( '---------------- README PARSER -- end ---------', plugin_readme_parser_domain ) );

      return $this->content;
    }

    /**
     * readme information
     *
     * Function to output a piece of requested readme information
     *
     * @uses   prp_get_readme      Fetch the readme file
     *
     * @param  string    $para     Parameters
     * @param  string    $content  Post content
     * @param  string          Output
     */
    public function readme_info( array $paras = array(), string $content = '' ): string {

      // prp_log( __( '----------------- README INFO -----------------', plugin_readme_parser_domain ) );

      // prp_log( 'readme_info arg1: parameters', $paras );
      // prp_log( 'readme_info arg2: content', $content );

      $this->reset();

      prp_log( 'HERE', __FUNCTION__ );
      $this->content = $content;
      try {
        $result = prp_toggle_global_shortcodes( $this->content );
        if ( is_wp_error( $result ) ) {
          prp_log( 'result', $result );
          // throw new PRP_Exception( PRP_Exception::set_error_code_as_string( $result->get_error_message() ), $result->get_error_code() );
        }
        $this->normalise_parameters( $paras );
        extract( shortcode_atts( array( 'name' => '', 'target' => '_blank', 'nofollow' => '', 'data' => '', 'cache' => '5' ), $this->parameters ) );
      } catch ( PRP_Exception $e ) {
        throw $e;
      }


      // prp_log( 'user attributes', $attributes );

      $output = '';
      $this->data = strtolower( $data );

      // Get the cache

      $this->cache = $cache;
      $result = $this->get_cache( 'prp_info_' . md5( $name . $this->cache ) );

      // prp_log( 'cache is', $this->cache );
      // prp_log( 'result is of type', gettype( $result ) );
      // prp_log( 'result is', $result );


      if ( !$result ) {

        $this->name = $name;
        $this->target = $target;
        $this->nofollow = 'yes' === strtolower( $nofollow ) ? ' rel="nofollow"' : '';

        try {
          $this->parse_readme_info();
        } catch ( PRP_Exception $e ) {
          $output = $e->get_prp_nice_error();
        }

      } else {

        // Cache retrieved, so get information from resulting array

        $this->version = $result[ 'version' ];
        $this->plugin_name = $result[ 'name' ];

        // prp_log( 'version', $this->version );
        // prp_log( 'plugin name', $this->plugin_name );
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

      try {
        $result = prp_toggle_global_shortcodes( $this->content );
        if ( is_wp_error( $result ) ) {
          prp_log( 'result', $result );
          // throw new PRP_Exception( PRP_Exception::set_error_code_as_string( $result->get_error_message() ), $result->get_error_code() );
        }
      } catch ( PRP_Exception $e ) {
        throw $e;
      }

      return do_shortcode( $output );
    }

    private function parse_readme_info(): void {

      // Get the file

      try {
        $this->file_data = $this->get_readme( $this->name );
        $this->plugin_name = $this->file_data[ 'name' ];
        $this->get_plugin_name_and_version();
        $this->set_cache();

      } catch ( PRP_Exception $e ) {
        $this->file_data = false;
        throw $e;
      }

    }

    /**
     * Normalises the quotation marks to straight ones from curly ones.
     * Fixes the erroneous array member created by having a space in 'Upgrade
     * Notice' and names of plugins.
     *
     * @param  $parameters  array  The text to normalise the quotation marks in.
     * @return        array  The text containing normalised quotation marks.
     */
    private function normalise_parameters( string|array|null $parameters = null ): null|WP_Error {

      // prp_log( __( 'Parameters (raw)', plugin_readme_parser_domain), $parameters );

      // $parameters = array(
      //   'data' => 'download,version,wordpress,forum',
      // );

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
              if ( isset( $normalised_parameters[ 'exclude' ] ) ) {
                $normalised_parameters['exclude'] .= ' ' . $normalised_parameters[0];
              } else if ( isset( $normalised_parameters[ 'include' ] ) ) {
                $normalised_parameters['include'] .= ' ' . $normalised_parameters[0];
              } else if ( isset( $normalised_parameters[ 'name' ] ) ) {
                $normalised_parameters['name'] .= ' ' . $normalised_parameters[0];
              } else {
                // prp_log( __( 'Erroneous parameter found', plugin_readme_parser_domain ) );
              }
              unset( $normalised_parameters[0] );
            }
            $this->parameters = $normalised_parameters;
            // prp_log( __( 'Parameter array (normalised)', plugin_readme_parser_domain), $this->parameters );
          break;
          case 'string':
            $normalised_parameters = str_replace(array_keys(self::QUOTES), array_values(self::QUOTES), $parameters);
            $this->parameters = $parameters;
            // prp_log( __( 'Parameter string (normalised)', plugin_readme_parser_domain), '\'' . $this->parameters . '\'' );
          break;
          default:
            prp_log( 'HERE', __FUNCTION__ );
            $this->parameters = null;//$parameters;

            $error = new WP_Error();

            $error->add( PRP_Exception::get_error_code_as_string( PRP_Exception::PRP_ERROR_BAD_INPUT ), 'Wrong plugin. Expected <samp><kbd>' . plugin_readme_parser_domain . '</kbd></samp>; got <samp><kbd>' . $file . '</kbd></samp>' );

            // throw new PRP_Exception( 'Shortcode parameters: wanted <samp><kbd>string|array|null</kbd></samp>; got <samp><kbd>' . gettype( $parameters ) . '</kbd></samp>: <pre>' . print_r( $parameters, true ) . '</pre>', PRP_Exception::PRP_ERROR_BAD_INPUT );
          if ( $error->has_errors() ) {
            return $error;
          }
        }
      }
      return null;
    }

    /**
     * Determines whether the links should be shown or not.
     */
    private function should_links_be_shown(): void {

      // $this->show_links = false;
      if ( '' != $this->include ) {
        if ( prp_is_it_excluded( 'links', $this->include ) ) {
          $this->show_links = true;
        }
      } else {
        if ( !prp_is_it_excluded( 'links', $this->exclude ) ) {
          $this->show_links = true;
        }
      }
      // prp_log( __( 'show links (class)    ', plugin_readme_parser_domain ), ( $this->show_links ? 'true' : 'false' ) );
    }

    /**
     */
    private function should_head_be_shown(): void {

      // $this->show_head = false;
      // $this->show_meta = false;

      $this->head_explicitly_excluded = prp_is_it_excluded( 'head', $this->exclude );
      $this->head_explicitly_included = prp_is_it_excluded( 'head', $this->include );
      $this->meta_explicitly_excluded = prp_is_it_excluded( 'meta', $this->exclude );
      $this->meta_explicitly_included = prp_is_it_excluded( 'meta', $this->include );

      // prp_log( __( 'head exp exc', plugin_readme_parser_domain ), ( $this->head_explicitly_excluded ? 'true' : 'false' ) );
      // prp_log( __( 'head exp inc', plugin_readme_parser_domain ), ( $this->head_explicitly_included ? 'true' : 'false' ) );
      // prp_log( __( 'meta exp exc', plugin_readme_parser_domain ), ( $this->meta_explicitly_excluded ? 'true' : 'false' ) );
      // prp_log( __( 'meta exp inc', plugin_readme_parser_domain ), ( $this->meta_explicitly_included ? 'true' : 'false' ) );

      if ( !$this->head_explicitly_excluded ) {
        if ( !$this->meta_explicitly_excluded ) {
          if ( $this->meta_explicitly_included ) {
            $new_include = str_replace( 'meta', 'head', $this->include );
            prp_log( __( 'Cannot include the meta data part of the head without the summary part:', plugin_readme_parser_domain ), '', true, false );
            prp_log( __( '  Parameters supplied', plugin_readme_parser_domain ), 'include="' . $this->include . '"', true, false );
            prp_log( __( '  Parameters changed to', plugin_readme_parser_domain ), 'include="' . $new_include . '"', true, false );
            // prp_log( __( 'Cannot include the meta data part of the head without the summary part.\n  Parameters supplied:   include="' . $this->include . '"\n  Parameters changed to: include="' . $new_include . '"', plugin_readme_parser_domain ), '', true, true );
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

    private function validate_parameters(): void {

      if ( ( '' != $this->exclude ) &&
           ( '' != $this->include ) ) {
        throw new PRP_Exception( 'Parameters \'include\' and \'exclude\' cannot both be specified in the same shortcode', PRP_Exception::PRP_ERROR_BAD_INPUT );
      // } else {
      //   return '';
      }
    }

    private function read_file_array(): void {

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
        //   prp_log( 'Just before \'head\', $this->add_to_output===' . ( $this->add_to_output ? 'true' : 'false' ) );
        // }

        $this->read_head( $i );

        // if ( 'Description' === $this->section ) {
        //   prp_log( 'ADD TO OUTPUT', $this->add_to_output );
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

    private function display_links_section(): void {

      if ( ( $this->show_links ) &&
           ( 'bottom' == $this->links ) ) {
        $this->file_combined .= prp_display_links( $this->download, $this->target, $this->nofollow, $this->version, $this->mirror, $this->plugin_name );
      }
    }

    private function write_html(): void {

      $titles_found = 0;
      $count = count( $this->file_array );

      for ( $i = 0; $i < $count; $i++ ) {

        // If Content Reveal plugin is active

        $titles_found = $this->write_content_reveal_plugin( $i, $titles_found );

        $this->normalise_html_code_tags( $i );

        if ( '' != $this->file_array[ $i ] ) {
          $this->my_html .= $this->file_array[ $i ] . self::LINE_END;
        }
      }

      // Modify <CODE> and <PRE> with class to suppress translation

      $this->my_html = str_replace( '<code>', '<code class="notranslate">', str_replace( '<pre>', '<pre class="notranslate">', $this->my_html ) );
    }

    private function reset(): void {
      $this->parameters = null;
      $this->content = '';

      $this->file_array = array();
      $this->file_data = '';

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

      // prp_log( __('all the things have been reset', plugin_readme_parser_domain), $this );
    }

    private function standardise_headings_markup( $i ): void {

      if ( '=== ' == substr( $this->file_array [$i ], 0, 4 ) ) {
        $this->file_array[ $i ] = str_replace( '===', '#', $this->file_array[ $i ] );
        $this->section = prp_get_section_name( $this->file_array[ $i ], 1 );
        // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
      } else {
        if ( '== ' == substr( $this->file_array[ $i ], 0, 3 ) ) {
          $this->file_array[ $i ] = str_replace( '==', '##' , $this->file_array[ $i ] );
          $this->section = prp_get_section_name( $this->file_array[ $i ], 2 );
          // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
        } else {
          if ( '= ' == substr( $this->file_array[ $i ], 0, 2 ) ) {
            $this->file_array[ $i ] = str_replace( '=', '###', $this->file_array[ $i ] );
            // // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );
          }
        }
      }
    }

    private function standardise_lists( $i ): void {

      if ( ( '*' == substr( $this->file_array[ $i ], 0, 1 ) ) &&
           ( ' ' != substr( $this->file_array[ $i ], 0, 2 ) ) &&
           ( false === strpos( $this->file_array[ $i ], '*', 1 ) ) ) {
        $this->file_array[ $i ] = '* ' . substr( $this->file_array[ $i ], 1 );
      }
    }

    private function track_current_section(): void {

      if ( ( $this->section != $this->prev_section ) &&
           ( '' == $this->prev_section ) ) {

        // If a plugin name was not specified attempt to use the name parameter. If that's not set, assume
        // it's the one in the readme file header

        // // prp_log( __( 'name (from args)', plugin_readme_parser_domain ), $this->name );

        if ( '' == $this->plugin_name ) {
          if ( '' == $this->name ) {
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

      if ( '' != $this->include ) {
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

      // Is this an included section?

      if ( prp_is_it_excluded( $this->section, $this->include ) ) {
        // prp_log( __( 'included', plugin_readme_parser_domain ), $this->section );

        if ( $this->section != $this->prev_section ) {
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

      // Is this an excluded section?

      if ( prp_is_it_excluded( $this->section, $this->exclude ) ) {
        $this->add_to_output = false;
        // prp_log( __( 'excluded', plugin_readme_parser_domain ), $this->section );
      } else {
        if ( $this->section != $this->prev_section ) {
          if ( $this->div_written ) {
            $this->file_combined .= '</div>' . self::LINE_END;
          }
          $this->file_combined .= self::LINE_END . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $this->section ) ) ) . '">' . self::LINE_END;
          $this->div_written = true;
        }
      }
    }

    private function read_excluded_line(): void {

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

      if ( ( $this->links == strtolower( $this->section ) ) &&
           ( $this->section != $this->prev_section ) ) {
        if ( $this->show_links ) {
          $this->file_array[ $i ] = prp_display_links( $this->download, $this->target, $this->nofollow, $this->version, $this->mirror, $this->plugin_name ) . $this->file_array[ $i ];
        }
      }
    }

    private function read_download_link( $i ): void {

      if ( 'Stable tag:' == substr( $this->file_array[ $i ], 0, 11 ) ) {

        $this->version = substr( $this->file_array[ $i ], 12 );
        // prp_log( __( 'version', plugin_readme_parser_domain ), $this->version );
        $this->download = 'https://downloads.wordpress.org/plugin/' . $this->plugin_name . '.' . $this->version . '.zip';
        // // prp_log( __( 'download link', plugin_readme_parser_domain ), $this->download );

      }
    }

    private function read_head( $i ): void {

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
          $this->add_to_output = prp_add_head_meta_data_to_output( $this->show_head, $this->show_meta, $this->file_array[ $i ], $this->metadata );
        }
      }
    }

    private function read_screenshots(): void {

      if ( 'Screenshots' === $this->section ) {
        // Do not display screenshots: any attempt to access the screenshots on WordPress' SVN servers is met with an HTTP 403 (forbidden) error.
        $this->add_to_output = false;
        // prp_log( __( 'Can\'t output screenshots.', plugin_readme_parser_domain ), '', true, false );
      }
    }

    private function add_current_line_to_output( $i ): void {

      if ( ( '' != $this->file_array[ $i ] or !$this->last_line_blank ) &&
         $this->add_to_output ) {
        $this->file_combined .= $this->file_array[ $i ] . self::LINE_END;
        // prp_log_truncated_line( 'Adding l.' . $i . ' ' . $this->file_array[ $i ] );

        if ( '' == $this->file_array[ $i ] ) {
          $this->last_line_blank = true;

        } else {
          $this->last_line_blank = false;
        }

      // } else {
        // prp_log_truncated_line( 'Not adding l.' . $i . ' ' . $this->file_array[ $i ] );

        }
    }

    private function write_html_title( $i ): void {

      $this->title = substr( $this->file_array[ $i ], 4, strpos( $this->file_array[ $i ], '</h2>' ) - 4 );
      if ( prp_is_it_excluded( strtolower( $this->title ), $this->hide ) ) {
        $state = 'hide';
      } else {
        $state = 'show';
      }
    }

    private function normalise_html_code_tags( $i ): void {

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
        if ( '' == ltrim( strip_tags( substr( $this->file_array[ $i ], 0, strpos( $this->file_array[ $i ], '<code>', 0 ) ) ) ) ) {
          $this->file_array[ $i ] = str_replace( 'code>', 'pre>', $this->file_array[ $i ] );
        }
      }
    }

    private function write_content_reveal_plugin( $i, $titles_found ): int {

      include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
      if ( is_plugin_active( 'simple-content-reveal/simple-content-reveal.php' ) ) {

        // If line is a sub-heading add the first part of the code

        if ( '<h2>' == substr( $this->file_array[ $i ], 0, 4 ) ) {

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

    private function write_content_reveal_heading( $i, $titles_found ): int {

      $this->file_array[ $i ] = acr_start( '<h2>%image% ' . $this->title . '</h2>', $this->title, $this->state, $scr_url, $scr_ext );
      return ++$titles_found;
    }

    private function write_content_reveal_end( $i, $titles_found ): void {

      if ( ( '</div>' == $this->file_array[ $i ] ) && ( 0 < $titles_found ) ) {
        $this->file_array[ $i ] = acr_end() . self::LINE_END . $this->file_array[ $i ];
      }
    }

    private function process_valid_file(): void {

      // prp_log( __( 'file_data', plugin_readme_parser_domain ), $this->file_data );

      if ( isset( $this->file_data[ 'name' ] ) ) {
        $this->plugin_name = $this->file_data[ 'name' ];
      } else {
        $this->plugin_name = '';
      }
      // prp_log( __( 'plugin name', plugin_readme_parser_domain ), $this->plugin_name );

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

      if ( ( 0 < strlen( $this->file_data[ 'file' ] ) ) &&
           ( 0 == substr_count( $this->file_data[ 'file' ], "\n" ) ) ) {

        throw new PRP_Exception( 'The readme file for \'' . $this->name . '\' is invalid: there are no carriage returns', PRP_Exception::PRP_ERROR_BAD_FILE );

      } else {
        throw new PRP_Exception( 'The readme file for \'' . $this->name . '\' is either missing or invalid', PRP_Exception::PRP_ERROR_BAD_FILE );

      }
    }

    private function set_cache( bool $save_this_content = false ): void {

      $cached_info = array();
      if ( is_numeric( $this->cache ) ) {
        if ( false === $save_this_content ) {
          $cached_info = array(
            'version' => $this->version,
            'name'    => $this->plugin_name,
          );
        } else {
          $cached_info = $this->content;
        }
        set_transient( $this->cache_key, $cached_info, 60 * $this->cache );
      }
    }

    private function get_cache( string $cache_key ): bool|array|string {

      if ( is_numeric( $this->cache ) ) {
        $this->cache_key = $cache_key;
        $result = get_transient( $this->cache_key );
        return $result;
      }
      return false;
    }

    /**
     * Get the readme file
     *
     * Function to work out the filename of the readme and get it
     *
     * @since  1.2
     *
     * @param  $plugin_url   string  readme name or URL
     * @return       string  False or array containing readme and plugin name
     */
    private function get_readme( $plugin_url, $version = '' ): mixed {

      // prp_log( __( '  Get readme:', plugin_readme_parser_domain ) );
      // prp_log( __( '  title:      \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );

      // Work out filename and fetch the contents

      // $plugin_url = 'example-plugin';
      // prp_log( 'url contains \'://\': ' . strpos( $plugin_url, '://' ) );

      if ( strpos( $plugin_url, '://' ) === false ) {
        $array[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
        $this->plugin_url = 'https://plugins.svn.wordpress.org/' . $array[ 'name' ] . '/';
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
        $this->file_data = prp_get_file( $this->plugin_url );

        // Ensure the file is valid

        if ( ( $this->file_data[ 'rc' ] === 0 ) &&
             ( $this->file_data[ 'file' ] !== '' ) &&
             ( substr( $this->file_data[ 'file' ], 0, 9 ) != '<!DOCTYPE' ) &&
             ( substr_count( $this->file_data[ 'file' ], "\n" ) != 0 ) ) {

          // Return values

          $array[ 'file' ] = $this->file_data[ 'file' ];

          return $array;

        } else {

          throw new PRP_Exception( 'The readme file for \'' . $this->name . '\' is invalid', PRP_Exception::PRP_ERROR_BAD_FILE );

          // prp_log( __( '  readme file is invalid', plugin_readme_parser_domain ) );

          // If not valid, return false

          // return false;
        }

      } catch ( PRP_Exception $e ) {
        $e->get_prp_nice_error();
        return false;
      } catch ( Exception $e ) {
        echo '<p class="error">' . print_r( $e->getMessage(), true ) . '</p>';
        error_log( print_r( $e->getMessage(), true ) );
        return false;
      }

      // prp_log( __( '  file data:   contents of readme file', plugin_readme_parser_domain ) );
      // prp_log( $this->file_data, '  file data:' );


    }

    private function get_plugin_name_and_version(): void {

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

      $output = '';

      // prp_log( 'data="' . $this->data . '"' );

      $msg = '';
      $code = PRP_Exception::PRP_ERROR_NONE;

      if ( 'download' === $this->data ) {
        $plugin_name_found = '' !== $this->plugin_name;
        $version_found = '' !== $this->version;
        if ( $plugin_name_found &&
             $version_found ) {
          $output = '<a href="https://downloads.wordpress.org/plugin/' . $this->plugin_name . '.' . $this->version . '.zip" target="' . $this->target . '"' . $this->nofollow . '>' . $this->content. '</a>';

        } else if ( $plugin_name_found &&
                    !$version_found ) {
          $msg = 'The plugin version could not be found in the readme file. It\'s needed to determine the link for the download file';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;

        } else if ( !$plugin_name_found &&
                    $version_found ) {
          $msg = 'The name could not be found in the readme file. It\'s needed to determine the link for the download file';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;

        } else {
          $msg = 'The name and version number could not be found in the readme file. They\'re needed to determine the link for the download file';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;
        }


      } else if ( 'version' === $this->data ) {
        if ( '' !== $this->version ) {
          $output = $this->version;
        } else {
          $msg = 'The version number could not be found in the readme file';
          $code = PRP_Exception::PRP_ERROR_BAD_FILE;
        }

      } else if ( 'forum' === $this->data ) {
        if ( '' !== $this->plugin_name ) {
          $output = '<a href="https://wordpress.org/support/plugin/' . $this->plugin_name . '" target="' . $this->target . '"' . $this->nofollow . '>' . $this->content . '</a>';
        } else {
          $msg = 'The name of the plugin was not given in the shortcode parameters. It\'s needed to obtain the link for the support forum';
          $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
        }

      } else if ( 'wordpress' === $this->data ) {
        if ( '' !== $this->plugin_name ) {
          $output = '<a href="https://wordpress.org/extend/plugins/' . $this->plugin_name . '/" target="' . $this->target . '"' . $this->nofollow . '>' .$this->content . '</a>';
        } else {
          $msg = 'The name of the plugin was not given in the shortcode parameters. It\'s needed to determine the link to the plugin in the WordPress plugin directory';
          $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
        }

      } else {
        throw new PRP_Exception( 'The data parameter in the shortcode is invalid' . ( '' === $this->data ? '' : ': <samp><kbd>data="' . $this->data . '"</kbd></samp>' ), PRP_Exception::PRP_ERROR_BAD_INPUT );

      }
      if ( '' == $output ) {
        $msg = 'The data parameter in the shortcode is invalid or missing' . ( '' === $this->data ? '' : ': <samp><kbd><kbd>data="' . $this->data . '"</kbd></samp>' );
        $code = PRP_Exception::PRP_ERROR_BAD_INPUT;
      }

      if ( '' !== $msg &&
           PRP_Exception::PRP_ERROR_NONE !== $code ) {
        throw new PRP_Exception( $msg, $code );
      }
      return $output;
    }

  }

  $generator = new Generate_Output();

  if ( !function_exists( 'readme_parser' )) {
    function readme_parser( $paras = '', $content = '' ) {
      try {
        global $generator;
        // $generator = new Generate_Output();
        return $generator->readme_parser( $paras, $content );
      } catch ( PRP_Exception $e ) {
        return $e->get_prp_nice_error();
      } catch ( Exception $e ) {
        echo print_r( plugin_readme_parser_name . ': something went wrong with the <samp><kbd>readme</kbd></samp> shortcode', true );
      }
    }
    add_shortcode( 'readme', 'readme_parser' );
  }
  if ( !function_exists( 'readme_info' )) {
    function readme_info( $paras = '', $content = '' ) {
      try {
        global $generator;
        // $generator = new Generate_Output();
        return $generator->readme_info( $paras, $content);
      } catch ( PRP_Exception $e ) {
        return $e->get_prp_nice_error();
      } catch ( Exception $e ) {
        return print_r( '<p class="error">' . plugin_readme_parser_name . ': something went wrong with the <samp><kbd>readme_info</kbd></samp> shortcode: ' . $e->getMessage() . '.</p>', true );
      }
    }
    add_shortcode( 'readme_info', 'readme_info' );
  }

}
?>
