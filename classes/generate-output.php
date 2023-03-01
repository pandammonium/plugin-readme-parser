<?php
/**
 * Generate output
 *
 * Functions to generate required output
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.0
 */

if ( !class_exists( 'Generate_Output' ) ) {
  /**
   * The plugin-readme parser.
   */
  class Generate_Output {

    private $parameters = '';
    private $content = '';

    private array $file_array = array();
    private $file_data = '';

    private $plugin_url = '';
    private $plugin_name = '';
    private $plugin_title = '';

    private $cache = '';
    private $cache_key = '';
    private $exclude = '';
    private $include = '';
    private $hide = '';
    private $links = '';
    private $ignore = '';
    private $mirror = '';
    private $nofollow = '';
    private $version = '';
    private $target = '';
    private $download = '';
    private $metadata = '';

    private $show_links = false;
    private $show_head = false;
    private $show_meta = false;

    private $head_explicitly_excluded = false;
    private $head_explicitly_included = false;
    private $meta_explicitly_excluded = false;
    private $meta_explicitly_included = false;

    private $section = '';
    private $prev_section = '';
    // private $last_line_blank = true;
    // private $div_written = false;
    private $code = false;
    private $file_combined = '';
    private const LINE_END = "\r\n";

    private $my_html = '';


    private $meta = '';

    // private static $c = 0;
    // private const COLOURS = array (
    //   0 => 'red',
    //   1 => 'orange',
    //   2 => 'yellow',
    //   3 => 'green',
    //   4 => 'blue',
    // );

    private const QUOTES = array(
     '“' => '',
     '”' => '',
     '‘' => '',
     '’' => '',
     '&#8220;' => '',
     '&#8221;' => ''
    );

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
     * @uses   prp_report_error    Output a formatted error
     * @uses   prp_strip_list      Strip a user or tag list and add links
     * @uses   prp_log             Output debug info to the WP error log
     *
     * @param  string    $content  readme filename
     * @param  string    $paras  Parameters
     * @return   string          Output
     */
    public function readme_parser( array $paras = array(), string $content = '' ): string {

      // prp_log( __( '---------------- README PARSER ----------------', plugin_readme_parser_domain ) );
      // prp_log( __( '---------------- ' . self::COLOURS[ self::$c++ ], plugin_readme_parser_domain ) );

      $this->content = $content;
      prp_toggle_global_shortcodes( $this->content );

      $this->my_html = '';

      // Extract parameters

      $this->parameters = $this->normalise_parameters( $paras );

      extract( shortcode_atts( array( 'exclude' => '', 'hide' => '', 'include' => '', 'target' => '_blank', 'nofollow' => '', 'ignore' => '', 'cache' => '', 'version' => '', 'mirror' => '', 'links' => 'bottom', 'name' => '' ), $this->parameters ) );

      // Get cached output

      $result = false;
      if ( is_numeric( $cache ) ) {
        $cache_key = 'prp_' . md5( $exclude . $hide . $include . $target . $nofollow . $ignore . $cache . $version . $mirror . $content );
        $result = get_transient( $cache_key );
      }

      // prp_log( __( 'shortcode content', plugin_readme_parser_domain ), $content );
      // prp_log( __( 'shortcode parameters', plugin_readme_parser_domain ), $this->parameters );

      if ( !$result ) {

        // prp_log( __( 'transient not cached', plugin_readme_parser_domain ) );

        // Set parameter values

        $this->plugin_url = $content;

        $this->exclude = strtolower( $exclude );
        $this->include = strtolower( $include );
        $this->hide = strtolower( $hide );
        $this->links = strtolower( $links );
        $this->ignore = prp_get_list( $ignore, ',,', 'ignore' );
        $this->mirror = prp_get_list( $mirror, ',,', 'mirror' );
        $this->version = $version;
        $this->target = $target;

        // prp_log( __( 'Sections to be included', plugin_readme_parser_domain), $include );
        // prp_log( __( 'Sections to be excluded', plugin_readme_parser_domain), $exclude );

        if ( 'yes' == strtolower( $nofollow ) ) {
          $this->nofollow = ' rel="nofollow"';
        }

        $this->should_links_be_shown();

        // Work out in advance whether head should be shown

        $this->show_head = false;
        $this->show_meta = false;

        $this->head_explicitly_excluded = prp_is_it_excluded( 'head', $exclude );
        $this->head_explicitly_included = prp_is_it_excluded( 'head', $include );
        $this->meta_explicitly_excluded = prp_is_it_excluded( 'meta', $exclude );
        $this->meta_explicitly_included = prp_is_it_excluded( 'meta', $include );

        // prp_log( __( 'head exp exc', plugin_readme_parser_domain ), ( $this->head_explicitly_excluded ? 'true' : 'false' ) );
        // prp_log( __( 'head exp inc', plugin_readme_parser_domain ), ( $this->head_explicitly_included ? 'true' : 'false' ) );
        // prp_log( __( 'meta exp exc', plugin_readme_parser_domain ), ( $this->meta_explicitly_excluded ? 'true' : 'false' ) );
        // prp_log( __( 'meta exp inc', plugin_readme_parser_domain ), ( $this->meta_explicitly_included ? 'true' : 'false' ) );

        if ( !$this->head_explicitly_excluded ) {
          if ( !$this->meta_explicitly_excluded ) {
            if ( $this->meta_explicitly_included ) {
              $new_include = str_replace( 'meta', 'head', $this->include );
              // prp_log( __( "Cannot include the meta data part of the head without the summary part.\n  Parameters supplied:   include=\"" . $this->include . "\"\n  Parameters changed to: include=\"" . $new_include . "\"", plugin_readme_parser_domain ), '', true, false );
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
        // prp_log( __( 'show meta', plugin_readme_parser_domain ), ( $show_meta ? 'true' : 'false' ) );

        // Ensure EXCLUDE and INCLUDE parameters aren't both included

        if ( ( '' != $this->exclude ) &&
             ( '' != $this->include ) ) {
          return prp_report_error( __( '\'include\' and \'exclude\' parameters cannot both be specified', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
        }

        // Work out filename and fetch the contents

        $this->file_data = prp_get_readme( $this->plugin_url, $this->version );

        // Ensure the file is valid

        if ( false !== $this->file_data ) {

          // prp_log( __( 'file_data', plugin_readme_parser_domain ), $file_data );

          if ( isset( $this->file_data[ 'name' ] ) ) {
            $this->plugin_name = $this->file_data[ 'name' ];
          } else {
            $this->plugin_name = '';
          }
          // prp_log( __( 'plugin name', plugin_readme_parser_domain ), $plugin_name );

          // Split file into array based on CRLF

          $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $this->file_data[ 'file' ] );
          // prp_log( __( 'file_array', plugin_readme_parser_domain ), $this->file_array, false, true );

          // // Set initial variables // Initialised with these values

          // $this->section = '';
          // $this->prev_section = '';
          $last_line_blank = true;
          $div_written = false;
          // $this->code = false;
          // self::LINE_END = "\r\n";
          // $this->file_combined = '';

          // Count the number of lines and read through the array

          $count = count( $this->file_array );
          // prp_log( __( 'readme file has ' . $count . ' lines', plugin_readme_parser_domain ) );
          for ( $i = 0; $i < $count; $i++ ) {
            // prp_log_truncated_line( $this->file_array[ $i ], $i );

            $add_to_output = true;

            // Remove non-visible character from input - various characters can sneak into
            // text files and this can affect output

            $this->file_array[ $i ] = rtrim( ltrim( ltrim( $this->file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

            // If the line begins with equal signs, replace with the standard hash equivalent

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

            // If an asterisk is used for a list, but it doesn't have a space after it, add one!
            // This only works if no other asterisks appear in the line

            if ( ( '*' == substr( $this->file_array[ $i ], 0, 1 ) ) &&
                 ( ' ' != substr( $this->file_array[ $i ], 0, 2 ) ) &&
                 ( false === strpos( $this->file_array[ $i ], '*', 1 ) ) ) {
              $this->file_array[ $i ] = '* ' . substr( $this->file_array[ $i ], 1 );
            }

            // Track current section. If very top, make it "head" and save as plugin name

            if ( ( $this->section != $this->prev_section ) &&
                 ( '' == $this->prev_section ) ) {

              // If a plugin name was not specified attempt to use the name parameter. If that's not set, assume
              // it's the one in the readme header

              // // prp_log( __( 'name (from args)', plugin_readme_parser_domain ), $this->name );

              if ( '' == $this->plugin_name ) {
                if ( '' == $this->name ) {
                  $this->plugin_name = str_replace( ' ', '-', strtolower( $this->section ) );
                } else {
                  $this->plugin_name = $this->name;
                }
              }

              $this->plugin_title = $this->section;
              $add_to_output = false;
              $this->section = 'head';
              // prp_log( __( 'section', plugin_readme_parser_domain ), $this->section );

            }

            if ( '' != $this->include ) {

              // Is this an included section?

              if ( prp_is_it_excluded( $this->section, $this->include ) ) {
                // prp_log( __( 'included', plugin_readme_parser_domain ), $this->section );

                if ( $this->section != $this->prev_section ) {
                  if ( $div_written ) {
                    $this->file_combined .= '</div>' . self::LINE_END;
                  }
                  $this->file_combined .= self::LINE_END . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $this->section ) ) ) . '">' . self::LINE_END;
                  $div_written = true;
                  if ( 'Description' === $this->section ) {
                    // prp_log( 'A. ADD TO OUTPUT', $add_to_output );
                  }
                }
              } else {
                $add_to_output = false;
                if ( 'Description' === $this->section ) {
                  // prp_log( 'B. ADD TO OUTPUT', $add_to_output );
                }
              }

            } else {

              // Is this an excluded section?

              if ( prp_is_it_excluded( $this->section, $this->exclude ) ) {
                $add_to_output = false;
                // prp_log( __( 'excluded', plugin_readme_parser_domain ), $this->section );
                if ( 'Description' === $this->section ) {
                  // prp_log( 'C. ADD TO OUTPUT', $add_to_output );
                }
              } else {
                if ( $this->section != $this->prev_section ) {
                  if ( $div_written ) {
                    $this->file_combined .= '</div>' . self::LINE_END;
                  }
                  $this->file_combined .= self::LINE_END . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $this->section ) ) ) . '">' . self::LINE_END;
                  $div_written = true;
                  if ( 'Description' === $this->section ) {
                    // prp_log( 'D. ADD TO OUTPUT', $add_to_output );
                  }
                }
              }
            }

            // Is it an excluded line?

            if ( $add_to_output ) {
              $exclude_loop = 1;
              while ( $exclude_loop <= $this->ignore[ 0 ] ) {
                if ( false !== strpos( $this->file_array[ $i ], $this->ignore[ $exclude_loop ], 0 ) ) {
                  $add_to_output = false;
                }
              $exclude_loop++;
              }
            }

            if ( ( $this->links == strtolower( $this->section ) ) &&
                 ( $this->section != $this->prev_section ) ) {
              if ( $this->show_links ) {
                $this->file_array[ $i ] = prp_display_links( $this->download, $this->target, $this->nofollow, $this->version, $this->mirror, $this->plugin_name ) . $this->file_array[ $i ];
              }
            }

            $this->prev_section = $this->section;
            // prp_log( __( '(previous) section', plugin_readme_parser_domain ), $this->prev_section );

            // Get the download link for the most recent version

            if ( 'Stable tag:' == substr( $this->file_array[ $i ], 0, 11 ) ) {

              $this->version = substr( $this->file_array[ $i ], 12 );
              // prp_log( __( 'version', plugin_readme_parser_domain ), $this->version );
              $this->download = 'https://downloads.wordpress.org/plugin/' . $this->plugin_name . '.' . $this->version . '.zip';
              // // prp_log( __( 'download link', plugin_readme_parser_domain ), $this->download );

            }

            if ( $add_to_output ) {

              // prp_log( __( 'SECTION', plugin_readme_parser_domain ), $this->section );

              if ( 'head' === $this->section ) {
                $metadata = array(
                  'exclude' => $this->exclude,
                  'nofollow' => $this->nofollow,
                  'version' => $this->version,
                  'download' => isset( $this->download ) ? $this->download : '',
                  'target' => $this->target,
                );
                $add_to_output = prp_add_head_meta_data_to_output( $this->show_head, $this->show_meta, $this->file_array[ $i ], $this->metadata );
              }
            }

            // if ( 'Description' === $this->section ) {
            //   prp_log( 'ADD TO OUTPUT', $add_to_output );
            // }

            if ( 'Screenshots' === $this->section ) {
              // Do not display screenshots: any attempt to access the screenshots on WordPress' SVN servers is met with an HTTP 403 (forbidden) error.
              $add_to_output = false;
            }

            // Add current line to output, assuming not compressed and not a second blank line

            // prp_log( __( 'test', plugin_readme_parser_domain ), array(
            //   'line no.'        => $i,
            //   'line'            => $this->file_array[ $i ],
            //   'last line blank' => $last_line_blank,
            //   'add to output'   => $this->add_to_output
            // ) );

            if ( ( '' != $this->file_array[ $i ] or !$last_line_blank ) &&
               $add_to_output ) {
              $this->file_combined .= $this->file_array[ $i ] . self::LINE_END;
              // prp_log_truncated_line( 'Adding l.' . $i . ' ' . $this->file_array[ $i ] );

              if ( '' == $this->file_array[ $i ] ) {
                $last_line_blank = true;

              } else {
                $last_line_blank = false;
              }

            } else {
              // prp_log_truncated_line( 'Not adding l.' . $i . ' ' . $this->file_array[ $i ] );

            }

          }

          $this->file_combined .= '</div>' . self::LINE_END;

          // Display links section

          if ( ( $this->show_links ) &&
               ( 'bottom' == $this->links ) ) {
            $this->file_combined .= prp_display_links( $this->download, $this->target, $this->nofollow, $this->version, $this->mirror, $this->plugin_name );
          }

          // Call Markdown code to convert

          $this->my_html = \Michelf\MarkdownExtra::defaultTransform( $this->file_combined );

          // Split HTML again

          $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $this->my_html );
          $this->my_html = '';

          // Count lines of code and process one at a time

          $titles_found = 0;
          $count = count( $this->file_array );

          for ( $i = 0; $i < $count; $i++ ) {

            // If Content Reveal plugin is active

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            if ( is_plugin_active( 'simple-content-reveal/simple-content-reveal.php' ) ) {

              // If line is a sub-heading add the first part of the code

              if ( '<h2>' == substr( $this->file_array[ $i ], 0, 4 ) ) {

                // Extract title and check if it should be hidden or shown by default

                $this->title = substr( $this->file_array[ $i ], 4, strpos( $this->file_array[ $i ], '</h2>' ) - 4 );
                if ( prp_is_it_excluded( strtolower( $this->title ), $this->hide ) ) {
                  $state = 'hide';
                } else {
                  $state = 'show';
                }

                // Call Content Reveal with heading details and replace current line

                $this->file_array[ $i ] = acr_start( '<h2>%image% ' . $this->title . '</h2>', $this->title, $this->state, $scr_url, $scr_ext );
                $titles_found++;
              }

              // If a DIV is found and previous section is not hidden add the end part of code

              if ( ( '</div>' == $this->file_array[ $i ] ) && ( 0 < $titles_found ) ) {
                $this->file_array[ $i ] = acr_end() . self::LINE_END . $this->file_array[ $i ];
              }
            }

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

            if ( '' != $this->file_array[ $i ] ) {
              $this->my_html .= $this->file_array[ $i ] . self::LINE_END;
            }
          }

          // Modify <CODE> and <PRE> with class to suppress translation

          $this->my_html = str_replace( '<code>', '<code class="notranslate">', str_replace( '<pre>', '<pre class="notranslate">', $this->my_html ) );


        } else {

          if ( ( 0 < strlen( $this->file_data[ 'file' ] ) ) &&
               ( 0 == substr_count( $this->file_data[ 'file' ], "\n" ) ) ) {

            $this->my_html = prp_report_error( __( 'invalid readme file: no carriage returns found', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

          } else {

            $this->my_html = prp_report_error( __( 'the readme file for the ' . $this->plugin_url . ' plugin is either missing or invalid: \'' . $this->file_data[ 'file' ] . '\'', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

          }
        }

        // Send the resultant code back, plus encapsulating DIV and version comments. Use double quotes to permit linebreaks (\n)

        $this->content = "\n<!-- " . plugin_readme_parser_name . " v" . plugin_readme_parser_version . " -->\n<div class=\"np-notepad\">" . $this->my_html . "</div>\n<!-- End of " . plugin_readme_parser_name . " code -->\n";

        // Cache the results

        if ( is_numeric( $this->cache ) ) {
          // // prp_log( __( 'caching transient', plugin_readme_parser_domain ) );
          set_transient( $this->cache_key, $this->content, 3600 * $this->cache );
        }

      } else {

        // // prp_log( __( 'transient already cached', plugin_readme_parser_domain ) );

        $this->content = $result;
      }

      prp_toggle_global_shortcodes( $this->content );

      // prp_log( __( '---------------- README PARSER -- end ---------', plugin_readme_parser_domain ) );

      return $this->content;
    }

    /**
     * Display a readme banner
     *
     * Function to output a banner associated with a readme
     *
     * @uses   prp_check_img_exists  Check if an image exists
     * @uses   prp_report_error    Return a formatted error message
     *
     * @param  string    $para     Parameters
     * @param  string    $content  Plugin name or URL
     * @param  string          Output
     */
    public function readme_banner( $paras = '', $content = '' ): string {

      // prp_log( __( 'Readme banner:', plugin_readme_parser_domain ) );


      prp_toggle_global_shortcodes( $content );

      $this->parameters = $this->normalise_parameters( $paras );

      extract( shortcode_atts( array( 'nofollow' => '' ), $this->parameters ) );

      $output = '';

      // Validate the plugin name

      if ( '' == $content ) {

        // Report error if no name found

        return prp_report_error( __( 'No plugin name was supplied for banner', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

      } else {

        $file_found = true;

        if ( 'yes' == strtolower( $nofollow ) ) { $nofollow = ' rel="nofollow"'; }

        $name = str_replace( ' ', '-', strtolower( $content ) );

        // Build the 1544 banner URL

        $url = 'https://plugins.svn.wordpress.org/' . $name . '/assets/banner-1544x500.';
        $ext = 'png';

        // Check if the PNG banner exists

        $img_check = prp_check_img_exists( $url, $ext );

        // Check if the JPG banner exists

        if ( !$img_check ) {

          $ext = 'jpg';
          $img_check = prp_check_img_exists( $url, $ext );

          if ( !$img_check ) {

            // Build the banner 772 URL

            $url = 'https://plugins.svn.wordpress.org/' . $name . '/assets/banner-772x250.';
            $ext = 'png';

            // Check if the PNG banner exists

            $img_check = prp_check_img_exists( $url, $ext );

            // Check if the JPG banner exists

            if ( !$img_check ) {

              $ext = 'jpg';
              $img_check = prp_check_img_exists( $url, $ext );

              if ( !$img_check ) {
                $file_found = false;
              }

            }
          }
        }

        // If the file was found now return the correct image HTML

        if ( $file_found ) {

            $output = '<div style="max-width: 100%;"><img src="' . $url . $ext . '" alt="' . $content . ' Banner" title="' . $content . ' Banner" /></div>';
        }
      }

      prp_toggle_global_shortcodes( $content );

      return $output;
    }

    /**
     * readme information
     *
     * Function to output a piece of requested readme information
     *
     * @uses   prp_get_readme      Fetch the readme file
     * @uses   prp_report_error    Return a formatted error message
     *
     * @param  string    $para     Parameters
     * @param  string    $content  Post content
     * @param  string          Output
     */
    public function readme_info( $paras = '', $content = '' ): string {

      // prp_log( __( 'Readme Info', plugin_readme_parser_domain ) );


      prp_toggle_global_shortcodes( $content );

      $this->parameters = $this->normalise_parameters( $paras );

      extract( shortcode_atts( array( 'name' => '', 'target' => '_blank', 'nofollow' => '', 'data' => '', 'cache' => '5' ), $this->parameters ) );

      $result = false;
      $output = '';
      $data = strtolower( $data );
      if ( 'yes' == strtolower( $nofollow ) ) {
        $nofollow = ' rel="nofollow"';
      }

      // Get the cache

      if ( is_numeric( $this->cache ) ) {
        $this->cache_key = 'prp_info_' . md5( $name . $this->cache );
        $result = get_transient( $this->cache_key );
      }

      if ( !$result ) {

        // Get the file

        $file_data = prp_get_readme( $name );
        $plugin_name = $file_data[ 'name' ];

        if ( false !== $file_data ) {

          // Split file into array based on CRLF

          $this->file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $file_data[ 'file' ] );

          // Loop through the array

          $count = count( $this->file_array );
          for ( $i = 0; $i < $count; $i++ ) {

            // Remove non-visible character from input - various characters can sneak into
            // text files and this can affect output

            $this->file_array[ $i ] = rtrim( ltrim( ltrim( $this->file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

            // If first record extract plugin name

            if ( ( '' == $plugin_name ) &&
                 ( 0 == $i ) ) {

              $pos = strpos( $this->file_array [ 0 ], ' ===' );
              if ( false !== $pos ) {
                $plugin_name = substr( $this->file_array[ 0 ], 4, $pos - 4 );
                $plugin_name = str_replace( ' ', '-', strtolower( $plugin_name ) );
              }
            }

            // Extract version number

            if ( 'Stable tag:' == substr( $this->file_array[ $i ], 0, 11 ) ) {
              $version = substr( $this->file_array[ $i ], 12 );
            }
          }

          // Save cache

          if ( is_numeric( $this->cache ) ) {
            $result[ 'version' ] = $version;
            $result[ 'name' ] = $plugin_name;
            set_transient( $this->cache_key, $result, 3600 * $this->cache );
          }

        } else {
          // prp_log( __( '*** PLUGIN URL', plugin_readme_parser_domain ), $plugin_url, true );

          $output = prp_report_error( __( 'readme file could not be found or is malformed; name: \'' . $file_data[ 'name' ] . '\'', plugin_readme_parser_domain ) . ' - ' . $name, plugin_readme_parser_name, false );
        }
      } else {

        // Cache retrieved, so get information from resulting array

        $version = $result[ 'version' ];
        $plugin_name = $result[ 'name' ];

      }

      if ( $output == '' ) {

        // If download link requested build the URL

        if ( 'download' == $data ) {
          if ( ( '' != $plugin_name ) && ( '' != $version ) ) {
            $output = '<a href="https://downloads.wordpress.org/plugin/' . $plugin_name . '.' . $version . '.zip" target="' . $target . '"' . $nofollow . '>' . $content. '</a>';
          } else {
            $output = prp_report_error( __( 'The name and/or version number could not be found in the readme', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
          }
        }

        // If version number requested return it

        if ( 'version' == $data ) {
          if ( '' != $version ) {
            $output = $version;
          } else {
            $output = prp_report_error( __( 'Version number not found in the readme', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
          }
        }

        // If forum link requested build the URL

        if ( 'forum' == $data ) {
          if ( '' != $plugin_name ) {
            $output = '<a href="https://wordpress.org/tags/' . $plugin_name . '" target="' . $target . '"' . $nofollow . '>' . $content . '</a>';
          } else {
            $output = prp_report_error( __( 'Plugin name not supplied', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
          }
        }

        // If WordPress link requested build the URL

        if ( 'wordpress' == $data ) {
          if ( '' != $plugin_name ) {
            $output = '<a href="https://wordpress.org/extend/plugins/' . $plugin_name . '/" target="' . $target . '"' . $nofollow . '>' . $content . '</a>';
          } else {
            $output = prp_report_error( __( 'Plugin name not supplied', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
          }
        }

        // Report an error if the data parameter was invalid or missing

        if ( '' == $output ) { $output = prp_report_error( __( 'The data parameter was invalid or missing', plugin_readme_parser_domain ), plugin_readme_parser_name, false ); }

      }

      prp_toggle_global_shortcodes( $content );

      return do_shortcode( $output );
    }

    /**
     * Normalises the quotation marks to straight ones from curly ones.
     * Fixes the erroneous array member created by having a space in 'Upgrade
     * Notice'.
     *
     * @param  $parameters  array  The text to normalise the quotation marks in.
     * @return        array  The text containing normalised quotation marks.
     */
    private function normalise_parameters( array $parameters ): array {

      // prp_log( __( 'Parameters (raw)', plugin_readme_parser_domain), $parameters );

      if ( is_array($parameters ) ) {
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
          } else {
            // prp_log( __( 'Erroneous parameter found', plugin_readme_parser_domain ) );
          }
          unset( $normalised_parameters[0] );
        }
        // prp_log( __( 'Parameters (normalised)', plugin_readme_parser_domain), $this->parameters );
        return $normalised_parameters;

      } else {
        prp_log( 'Normalise: wanted a string or an array; got \'' . gettype( $parameters ) . '\'', $parameters, true );
        return $parameters;
      }
    }

    /**
     * Determines whether the links should be shown or not.
     */
    private function should_links_be_shown(): void {

      // Work out in advance whether links should be shown

      $this->show_links = false;
      if ( '' != $this->include ) {
        if ( prp_is_it_excluded( 'links', $this->include ) ) {
          $this->show_links = true;
        }
      } else {
        if ( !prp_is_it_excluded( 'links', $this->exclude ) ) {
          $this->show_links = true;
        }
      }
      // prp_log( __( 'show links', plugin_readme_parser_domain ), ( $this->show_links ? 'true' : 'false' ) );
    }


  }

  $generator = new Generate_Output();

  if ( !function_exists( 'readme_parser' )) {
    function readme_parser( $paras = '', $content = '' ) {
      global $generator;
      return $generator->readme_parser( $paras, $content );
    }
  }
  if ( !function_exists( 'readme_banner' )) {
    function readme_banner( $paras = '', $content = '' ) {
      return $generator->readme_banner( $paras, $content );
    }
  }
  if ( !function_exists( 'readme_info' )) {
    function readme_info( $paras = '', $content = '' ) {
      global $generator;
      return $generator->readme_info( $paras, $content);
    }
  }

  add_shortcode( 'readme', 'readme_parser' );
  add_shortcode( 'readme_banner', 'readme_banner' );
  add_shortcode( 'readme_info', 'readme_info' );

}
?>
