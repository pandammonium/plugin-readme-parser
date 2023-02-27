<?php
/**
 * Generate output
 *
 * Functions to generate required output
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.0
 */

if ( !function_exists( 'readme_parser' ) ) {
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
  function readme_parser( $paras = '', $content = '' ) {

    prp_check_img_exists( plugin_dir_path( __DIR__ ) . 'readme', '.txt' );

    // prp_log( __( 'Readme parser:', plugin_readme_parser_domain ) );

    prp_toggle_global_shortcodes( $content );

    // Extract parameters

    // prp_log( __( 'Parameters (raw)', plugin_readme_parser_domain), $paras );
    $paras = prp_normalise_parameters( $paras );
    // prp_log( __( 'Parameters (normalised)', plugin_readme_parser_domain), $paras );

    extract( shortcode_atts( array( 'exclude' => '', 'ext' => '', 'hide' => '', 'include' => '', 'target' => '_blank', 'nofollow' => '', 'ignore' => '', 'cache' => '', 'version' => '', 'mirror' => '', 'links' => 'bottom', 'name' => '' ), $paras ) );

    // Get cached output

    $result = false;
    if ( is_numeric( $cache ) ) {
      $cache_key = 'prp_' . md5( $exclude . $ext . $hide . $include . $target . $nofollow . $ignore . $cache . $version . $mirror . $content );
      $result = get_transient( $cache_key );
    }

    // prp_log( __( 'shortcode content', plugin_readme_parser_domain ), $content );
    // prp_log( __( 'shortcode parameters', plugin_readme_parser_domain ), $paras );

    if ( !$result ) {

      // prp_log( __( 'transient not cached', plugin_readme_parser_domain ) );

      // Set parameter values

      $plugin_url = $content;

      $exclude = strtolower( $exclude );
      $include = strtolower( $include );
      $hide = strtolower( $hide );
      $links = strtolower( $links );

      prp_log( 'Sections to be included', $include );

      $ignore = prp_get_list( $ignore, ',,', 'ignore' );
      $mirror = prp_get_list( $mirror, ',,', 'mirror' );

      if ( 'yes' == strtolower( $nofollow ) ) {
        $nofollow = ' rel="nofollow"';
      }

      if ( '' == $ext ) {
        $ext = 'png';
      } else {
        $ext = strtolower( $ext );
      }

      // Work out in advance whether links should be shown

      $show_links = false;
      if ( '' != $include ) {
        if ( prp_is_it_excluded( 'links', $include ) ) {
          $show_links = true;
        }
      } else {
        if ( !prp_is_it_excluded( 'links', $exclude ) ) {
          $show_links = true;
        }
      }
      // prp_log( __( 'show links', plugin_readme_parser_domain ), $show_links );

      // Work out in advance whether meta should be shown

      $show_meta = false;
      if ( '' != $include ) {
        if ( prp_is_it_excluded( 'meta', $include ) ) {
          $show_meta = true;
        }
      } else {
        if ( !prp_is_it_excluded( 'meta', $exclude ) ) {
          $show_meta = true;
        }
      }
      // prp_log( __( 'show meta', plugin_readme_parser_domain ), ( $show_meta ? 'true' : 'false' ) );

      // Ensure EXCLUDE and INCLUDE parameters aren't both included

      if ( ( '' != $exclude ) &&
           ( '' != $include ) ) {
        return prp_report_error( __( '\'include\' and \'exclude\' parameters cannot both be specified', plugin_readme_parser_domain ), plugin_readme_parser_name, false );
      }

      // Work out filename and fetch the contents

      $file_data = prp_get_readme( $plugin_url, $version );

      // Ensure the file is valid

      if ( false !== $file_data ) {

        // prp_log( __( 'file_data', plugin_readme_parser_domain ), $file_data );

        if ( isset( $file_data[ 'name' ] ) ) {
          $plugin_name = $file_data[ 'name' ];
        } else {
          $plugin_name = '';
        }
        // prp_log( __( 'plugin name', plugin_readme_parser_domain ), $plugin_name );

        // Split file into array based on CRLF

        $file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $file_data[ 'file' ] );
        // prp_log( __( 'file_array', plugin_readme_parser_domain ), $file_array, false, true );

        // Set initial variables

        $section = '';
        $prev_section = '';
        $last_line_blank = true;
        $div_written = false;
        $code = false;
        $crlf = "\r\n";
        $file_combined = '';

        // Count the number of lines and read through the array

        $count = count( $file_array );
        // prp_log( __( 'readme file has ' . $count . ' lines', plugin_readme_parser_domain ) );
        for ( $i = 0; $i < $count; $i++ ) {
          // prp_log( __( '  line', plugin_readme_parser_domain ), $i + 1 );
          $add_to_output = true;

          // Remove non-visible character from input - various characters can sneak into
          // text files and this can affect output

          $file_array[ $i ] = rtrim( ltrim( ltrim( $file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

          // If the line begins with equal signs, replace with the standard hash equivalent

          if ( '=== ' == substr( $file_array [$i ], 0, 4 ) ) {
            $file_array[ $i ] = str_replace( '===', '#', $file_array[ $i ] );
            $section = prp_get_section_name( $file_array[ $i ], 1 );
            // // prp_log( __( 'section', plugin_readme_parser_domain ), $section );
          } else {
            if ( '== ' == substr( $file_array[ $i ], 0, 3 ) ) {
              $file_array[ $i ] = str_replace( '==', '##' , $file_array[ $i ] );
              $section = prp_get_section_name( $file_array[ $i ], 2 );
              // // prp_log( __( 'section', plugin_readme_parser_domain ), $section );
            } else {
              if ( '= ' == substr( $file_array[ $i ], 0, 2 ) ) {
                $file_array[ $i ] = str_replace( '=', '###', $file_array[ $i ] );
                // // prp_log( __( 'section', plugin_readme_parser_domain ), $section );
              }
            }
          }

          // If an asterisk is used for a list, but it doesn't have a space after it, add one!
          // This only works if no other asterisks appear in the line

          if ( ( '*' == substr( $file_array[ $i ], 0, 1 ) ) &&
               ( ' ' != substr( $file_array[ $i ], 0, 2 ) ) &&
               ( false === strpos( $file_array[ $i ], '*', 1 ) ) ) {
            $file_array[ $i ] = '* ' . substr( $file_array[ $i ], 1 );
          }

          // Track current section. If very top, make it "head" and save as plugin name

          if ( ( $section != $prev_section ) &&
               ( '' == $prev_section ) ) {

            // If a plugin name was not specified attempt to use the name parameter. If that's not set, assume
            // it's the one in the readme header

            // // prp_log( __( 'name (from args)', plugin_readme_parser_domain ), $name );

            if ( '' == $plugin_name ) {
              if ( '' == $name ) {
                $plugin_name = str_replace( ' ', '-', strtolower( $section ) );
              } else {
                $plugin_name = $name;
              }
            }

            $plugin_title = $section;
            $add_to_output = false;
            $section = 'head';
            prp_log( __( 'section', plugin_readme_parser_domain ), $section );

          }

          if ( '' != $include ) {

            // Is this an included section?

            if ( prp_is_it_excluded( $section, $include ) ) {


              if ( $section != $prev_section ) {
                if ( $div_written ) {
                  // prp_log( __( 'included: ' . '\'' . $section, plugin_readme_parser_domain ) );
                  $file_combined .= '</div>' . $crlf;
                }
                $file_combined .= $crlf . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $section ) ) ) . '">' . $crlf;
                $div_written = true;
              }
            } else {
              $add_to_output = false;
            }

          } else {

            // Is this an excluded section?

            if ( prp_is_it_excluded( $section, $exclude ) ) {
              $add_to_output = false;
            } else {
              if ( $section != $prev_section ) {
                if ( $div_written ) {
                  // prp_log( __( 'excluded ' . '\'' . $section, plugin_readme_parser_domain ) );
                  $file_combined .= '</div>' . $crlf;
                }
                $file_combined .= $crlf . '<div markdown="1" class="np-' . htmlspecialchars( str_replace( ' ', '-', strtolower( $section ) ) ) . '">' . $crlf;
                $div_written = true;
              }
            }
          }

          // Is it an excluded line?

          if ( $add_to_output ) {
            $exclude_loop = 1;
            while ( $exclude_loop <= $ignore[ 0 ] ) {
              if ( false !== strpos( $file_array[ $i ], $ignore[ $exclude_loop ], 0 ) ) {
                $add_to_output = false;
              }
            $exclude_loop++;
            }
          }

          if ( ( $links == strtolower( $section ) ) &&
               ( $section != $prev_section ) ) {
            if ( $show_links ) {
              $file_array[ $i ] = prp_display_links( $download, $target, $nofollow, $version, $mirror, $plugin_name ) . $file_array[ $i ];
            }
          }

          $prev_section = $section;
          // prp_log( __( '(previous) section', plugin_readme_parser_domain ), $prev_section );

          // Get the download link for the most recent version

          if ( 'Stable tag:' == substr( $file_array[ $i ], 0, 11 ) ) {

            $version = substr( $file_array[ $i ], 12 );
            // prp_log( __( 'version', plugin_readme_parser_domain ), $version );
            $download = 'https://downloads.wordpress.org/plugin/' . $plugin_name . '.' . $version . '.zip';
            // // prp_log( __( 'download link', plugin_readme_parser_domain ), $download );

          }

          $meta_text = '';
          $head_data_item_found = false;
          if ( 'head' === $section ) {
            $head_data = array(
              'cont\'s  ' => 'Contributors:' == substr( $file_array[ $i ], 0, 13 ),
              'donate  ' => 'Donate link:' == substr( $file_array[ $i ], 0, 12 ),
              'tags    ' => 'Tags:' == substr( $file_array[ $i ], 0, 5 ),
              'wp ver. ' => 'Requires at least:' == substr( $file_array[ $i ], 0, 18 ),
              'php ver.' => 'Requires PHP:' == substr( $file_array[ $i ], 0, 13 ),
              'tested  ' => 'Tested up to:' == substr( $file_array[ $i ], 0, 13 ),
              'st. tag ' => 'Stable tag:' == substr( $file_array[ $i ], 0, 11 ),
              'lic. uri' => 'License URI:' == substr( $file_array[ $i ], 0, 12 ),
              'licence ' => 'License:' == substr( $file_array[ $i ], 0, 8 )
            );
            $head_data_item_found = ( in_array( true, $head_data ) );
            prp_log( __( 'l.' . $i . ' found', plugin_readme_parser_domain ), ( $head_data_item_found ? $file_array[ $i ] : 'nothing' ) );
          }
          if ( $add_to_output ) {
            // prp_log( __( 'Current line' ), $file_array[ $i ] );

            // Process meta data from top

            if ( $head_data_item_found ) {

              if ( ( 'Requires at least:' == substr( $file_array[ $i ], 0, 18 ) ) &&
                   ( prp_is_it_excluded( 'requires', $exclude ) ) ) {
                $add_to_output = false;
              } else if ( ( 'Requires PHP:' == substr( $file_array[ $i ], 0, 18 ) ) &&
                   ( prp_is_it_excluded( 'requires php', $exclude ) ) ) {
                $add_to_output = false;
              } else if ( ( 'Tested up to:' == substr( $file_array[ $i ], 0, 13 ) ) &&
                   ( prp_is_it_excluded( 'tested', $exclude ) ) ) {
                $add_to_output = false;
              } else if ( ( 'License:' == substr( $file_array[ $i ], 0, 8 ) ) &&
                   ( prp_is_it_excluded( 'license', $exclude ) ) ) {
                $add_to_output = false;
              } else if ( 'Contributors:' == substr( $file_array[ $i ], 0, 13 ) ) {
                if ( prp_is_it_excluded( 'contributors', $exclude ) ) {
                  $add_to_output = false;
                } else {
                  // Show contributors using links to WordPress pages
                  $file_array[ $i ] = substr( $file_array[ $i ], 0, 14 ) . prp_strip_list( substr( $file_array[ $i ], 14 ), 'c', $target, $nofollow );
                }
              } else if ( 'Tags:' == substr( $file_array[ $i ], 0, 5 ) ) {
                if ( prp_is_it_excluded( 'tags', $exclude ) ) {
                  $add_to_output = false;
                } else {
                  // Show tags using links to WordPress pages
                  $file_array[ $i ] = substr( $file_array[ $i ], 0, 6 ) . prp_strip_list( substr( $file_array[ $i ], 6 ), 't', $target, $nofollow );
                }
              } else if ( 'Donate link:' == substr( $file_array[ $i ], 0, 12 ) ) {
                if ( prp_is_it_excluded( 'donate', $exclude ) ) {
                  $add_to_output = false;
                } else {
                  // Convert the donation link to a hyperlink
                  $text = substr( $file_array[ $i ], 13 );
                  $file_array[ $i ] = substr( $file_array[ $i ], 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
                }
              } else if ( 'License URI:' == substr( $file_array[ $i ], 0, 12 ) ) {
                if ( prp_is_it_excluded( 'license uri', $exclude ) ) {
                  $add_to_output = false;
                } else {
                  // Convert the licence URL to a hyperlink
                  $text = substr( $file_array[ $i ], 13 );
                  $file_array[ $i ] = substr( $file_array[ $i ], 0, 13 ) . '<a href="' . $text . '">' . $text . '</a>';
                }
              } else if ( 'Stable tag:' == substr( $file_array[ $i ], 0, 11 ) ) {
                if ( prp_is_it_excluded( 'stable', $exclude ) ) {
                  $add_to_output = false;
                } else {
                  // Add a hyperlink to the download file
                  $file_array[ $i ] = substr( $file_array[ $i ], 0, 12 ) . '<a href="' . $download.'" style="max-width: 100%;">' . $version . '</a>';
                }
              }

              // If one of the header tags, add a BR tag to the end of the line
              // prp_log( __(  'head data: ' . $file_array[ $i ], plugin_readme_parser_domain ) );

              $file_array[ $i ] .= '<br />';
            }
          } else {

            // Display the meta data if it is to be included (without the head); i.e. the include parameter contains'meta', not 'head'.
            // Make sure the line doesn't correspond with any of the named data items in the head, e.g. stable tag, contributors, licence.
            // Make sure the line isn't blank.
            // Mak sure the line isn't a title (i.e. begins with '#').

            if ( $show_meta &&
                 !$head_data_item_found &&
                 'head' === $section &&
                 '' !== $file_array[ $i ] &&
                 '#' !== $file_array[ $i ][ 0 ] ) {
              prp_log( __( 'Current line ' . $i, plugin_readme_parser_domain ), $file_array[ $i ] );
              $add_to_output = true;
            }
          }


          if ( 'Screenshots' === $section ) {
            // Do not display screenshots: any attempt to access the screenshots on WordPress' SVN servers is met with an HTTP 403 (forbidden) error.
            $add_to_output = false;
          }

          // Add current line to output, assuming not compressed and not a second blank line

          if ( ( '' != $file_array[ $i ] or !$last_line_blank ) &&
             $add_to_output ) {
            // prp_log( __(  'current line: ' . $file_array[ $i ], plugin_readme_parser_domain ) );
            $file_combined .= $file_array[ $i ] . $crlf;
            if ( '' == $file_array[ $i ] ) {
              $last_line_blank = true; } else { $last_line_blank = false;
            }
          }

        }

        $file_combined .= '</div>' . $crlf;

        // Display links section

        if ( ( $show_links ) &&
             ( 'bottom' == $links ) ) {
          $file_combined .= prp_display_links( $download, $target, $nofollow, $version, $mirror, $plugin_name );
        }

        // Call Markdown code to convert

        $my_html = \Michelf\MarkdownExtra::defaultTransform( $file_combined );

        // Split HTML again

        $file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $my_html );
        $my_html = '';

        // Count lines of code and process one at a time

        $titles_found = 0;
        $count = count( $file_array );

        for ( $i = 0; $i < $count; $i++ ) {

          // If Content Reveal plugin is active

          include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
          if ( is_plugin_active( 'simple-content-reveal/simple-content-reveal.php' ) ) {

            // If line is a sub-heading add the first part of the code

            if ( '<h2>' == substr( $file_array[ $i ], 0, 4 ) ) {

              // Extract title and check if it should be hidden or shown by default

              $title = substr( $file_array[ $i ], 4, strpos( $file_array[ $i ], '</h2>' ) - 4 );
              if ( prp_is_it_excluded( strtolower( $title ), $hide ) ) {
                $state = 'hide';
              } else {
                $state = 'show';
              }

              // Call Content Reveal with heading details and replace current line

              $file_array[ $i ] = acr_start( '<h2>%image% ' . $title . '</h2>', $title, $state, $scr_url, $scr_ext );
              $titles_found++;
            }

            // If a DIV is found and previous section is not hidden add the end part of code

            if ( ( '</div>' == $file_array[ $i ] ) && ( 0 < $titles_found ) ) {
              $file_array[ $i ] = acr_end() . $crlf . $file_array[ $i ];
            }
          }

          // If first line of code multi-line, replace CODE with PRE tag

          if ( ( strpos( $file_array[ $i ], '<code>', 0 ) ) && ( !strpos( $file_array[ $i ], '</code>', 0 ) ) ) {
            $file_array[ $i ] = str_replace( '<code>', '<pre>', $file_array[ $i ] );
          }

          // If final line to code multi-line, replace /CODE with /PRE tag

          if ( ( strpos( $file_array[ $i ], '</code>', 0 ) ) && ( !strpos( $file_array[ $i ], '<code>', 0 ) ) ) {
            $file_array[ $i ] = str_replace( '</code>', '</pre>', $file_array[ $i ] );
          }

          // If all code is one line, replace CODE with PRE tags

          if ( ( strpos( $file_array[ $i ], '<code>', 0 ) ) && ( strpos( $file_array[ $i ], '</code>', 0 ) ) ) {
            if ( '' == ltrim( strip_tags( substr( $file_array[ $i ], 0, strpos( $file_array[ $i ], '<code>', 0 ) ) ) ) ) {
              $file_array[ $i ] = str_replace( 'code>', 'pre>', $file_array[ $i ] );
            }
          }

          if ( '' != $file_array[ $i ] ) {
            $my_html .= $file_array[ $i ] . $crlf;
          }
        }

        // Modify <CODE> and <PRE> with class to suppress translation

        $my_html = str_replace( '<code>', '<code class="notranslate">', str_replace( '<pre>', '<pre class="notranslate">', $my_html ) );


      } else {

        if ( ( 0 < strlen( $file_data[ 'file' ] ) ) &&
             ( 0 == substr_count( $file_data[ 'file' ], "\n" ) ) ) {

          $my_html = prp_report_error( __( 'invalid readme file: no carriage returns found', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

        } else {

          $my_html = prp_report_error( __( 'the readme file for the ' . $plugin_url . ' plugin is either missing or invalid: \'' . $file_data[ 'file' ] . '\'', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

        }
      }

      // Send the resultant code back, plus encapsulating DIV and version comments. Use double-quotes to permit linebreaks (\n)

      $content = "\n<!-- " . plugin_readme_parser_name . " v" . plugin_readme_parser_version . " -->\n<div class=\"np-notepad\">" . $my_html . "</div>\n<!-- End of " . plugin_readme_parser_name . " code -->\n";

      // Cache the results

      if ( is_numeric( $cache ) ) {
        // // prp_log( __( 'caching transient', plugin_readme_parser_domain ) );
        set_transient( $cache_key, $content, 3600 * $cache );
      }

    } else {

      // // prp_log( __( 'transient already cached', plugin_readme_parser_domain ) );

      $content = $result;
    }

    prp_toggle_global_shortcodes( $content );


    return $content;
  }

  add_shortcode( 'readme', 'readme_parser' );
}

if ( !function_exists( 'readme_banner' ) ) {
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
  function readme_banner( $paras = '', $content = '' ) {

    // prp_log( __( 'Readme banner:', plugin_readme_parser_domain ) );


    prp_toggle_global_shortcodes( $content );

    extract( shortcode_atts( array( 'nofollow' => '' ), $paras ) );

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

  add_shortcode( 'readme_banner', 'readme_banner' );
}

if ( !function_exists( 'readme_info' ) ) {
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
  function readme_info( $paras = '', $content = '' ) {

    // prp_log( __( 'Readme Info', plugin_readme_parser_domain ) );


    prp_toggle_global_shortcodes( $content );

    extract( shortcode_atts( array( 'name' => '', 'target' => '_blank', 'nofollow' => '', 'data' => '', 'cache' => '5' ), $paras ) );

    $result = false;
    $output = '';
    $data = strtolower( $data );
    if ( 'yes' == strtolower( $nofollow ) ) {
      $nofollow = ' rel="nofollow"';
    }

    // Get the cache

    if ( is_numeric( $cache ) ) {
      $cache_key = 'prp_info_' . md5( $name . $cache );
      $result = get_transient( $cache_key );
    }

    if ( !$result ) {

      // Get the file

      $file_data = prp_get_readme( $name );
      $plugin_name = $file_data[ 'name' ];

      if ( false !== $file_data ) {

        // Split file into array based on CRLF

        $file_array = preg_split( "/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $file_data[ 'file' ] );

        // Loop through the array

        $count = count( $file_array );
        for ( $i = 0; $i < $count; $i++ ) {

          // Remove non-visible character from input - various characters can sneak into
          // text files and this can affect output

          $file_array[ $i ] = rtrim( ltrim( ltrim( $file_array[ $i ], "\x80..\xFF" ), "\x00..\x1F" ) );

          // If first record extract plugin name

          if ( ( '' == $plugin_name ) &&
               ( 0 == $i ) ) {

            $pos = strpos( $file_array [ 0 ], ' ===' );
            if ( false !== $pos ) {
              $plugin_name = substr( $file_array[ 0 ], 4, $pos - 4 );
              $plugin_name = str_replace( ' ', '-', strtolower( $plugin_name ) );
            }
          }

          // Extract version number

          if ( 'Stable tag:' == substr( $file_array[ $i ], 0, 11 ) ) {
            $version = substr( $file_array[ $i ], 12 );
          }
        }

        // Save cache

        if ( is_numeric( $cache ) ) {
          $result[ 'version' ] = $version;
          $result[ 'name' ] = $plugin_name;
          set_transient( $cache_key, $result, 3600 * $cache );
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

  add_shortcode( 'readme_info', 'readme_info' );
}
?>
