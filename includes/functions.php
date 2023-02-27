<?php
/**
 * Functions
 *
 * Functions called by main output generator
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.2
 */

if ( !function_exists( 'prp_log' ) ) {
/**
 * Prints a message to the debug log file.
 *
 * @since 2.0.0
 *
 * @param string  message_name  (optional)  A name to associate with the
 * message. This is useful if logging multiple messages.
 * @param string / array $message    The message to be logged.
 * @param bool  $error  Whether the message is about an error or not.
 *
 */
function prp_log( $message_name, $message = '', $error = false, $echo = false ) {

    $debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
    $log_file = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
    $log_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

    // error_log( print_r( '  WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_LOG:     ' . ($log_file ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  WP_DEBUG_DISPLAY: ' . ($log_display ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  error:            ' . ($error ? 'true' : 'false' ), true ) );
    // error_log( print_r( '  echo:             ' . ($echo ? 'true' : 'false' ), true ) );

    // echo '<pre>' .
    //   print_r( 'WP_DEBUG:         ' . ($debugging ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_LOG:     ' . ($log_file ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( 'WP_DEBUG_DISPLAY: ' . ($log_display ? 'true' : 'false' ), true ) . '<hr>' .
    //   print_r( '$error:           ' . ($error ? 'true' : 'false' ), true ) . '<br>' .
    //   print_r( '$echo:            ' . ($echo ? 'true' : 'false' ), true ) .
    //  '</pre>';

    $prefix = 'PRP | ';
    $header = ( '' === $message_name ) ? '' : $message_name;
    $error_style = $error ? ' class="error"' : '';
    $divider = ( '' === $message ) ? '' : ': ';
    $message_type = gettype( $message );
    $output = '';
    switch ( $message_type ) {
      case 'array':
        $output = print_r( $header, true ) . $divider . print_r( $message, true );
      break;
      default:
        $output = $header . $divider . $message;
      break;
    }

    if ( $debugging ) {
      if ( $log_file ) {
        error_log( $prefix . $output );
      }
      if ( ( $error && $echo ) ||
           $log_display ) {

        $delim = ':';
        $pos = strpos( $output, $delim );
        if ( $pos !== false ) {
          $output = '<b>' . str_replace( $delim, $delim . '</b>', $output );
        }
        if ( 'array' === $message_type ) {
          echo '<pre' . $error_style . '>' . $output . '</pre>';
        } else {
          echo '<p' . $error_style . '>' . $output . '</p>';
        }
      }
    }

  }
}

if ( !function_exists( 'prp_get_readme' ) ) {
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
  function prp_get_readme( $plugin_url, $version = '' ) {

    // prp_log( __( '  Get readme:', plugin_readme_parser_domain ) );
    // prp_log( __( '  title:      \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );

    // Work out filename and fetch the contents

    if ( strpos( $plugin_url, '://' ) === false ) {
      $array[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
      $plugin_url = 'https://plugins.svn.wordpress.org/' . $array[ 'name' ] . '/';
      // prp_log( __( '  url:        \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    if ( is_numeric( $version ) ) {
      $plugin_url .= 'tags/' . $version;
      // prp_log( __( '  tag url:    \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    } else {
      $plugin_url .= 'trunk';
      // prp_log( __( '  trunk url:  \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    }
    $plugin_url .= '/readme.txt';
    // prp_log( __( '  readme.txt: \'' . $plugin_url . '\'', plugin_readme_parser_domain ) );
    }

    $file_data = prp_get_file( $plugin_url );
    // prp_log( __( '  file data:   contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $file_data, '  file data:' );

    // Ensure the file is valid

    if ( ( $file_data[ 'rc' ] == 0 ) &&
         ( $file_data[ 'file' ] != '' ) &&
         ( substr( $file_data[ 'file' ], 0, 9 ) != '<!DOCTYPE' ) &&
         ( substr_count( $file_data[ 'file' ], "\n" ) != 0 ) ) {

      // Return values

      $array[ 'file' ] = $file_data[ 'file' ];

      return $array;

    } else {

      // prp_log( __( '  readme file is invalid', plugin_readme_parser_domain ) );

      // If not valid, return false

      return false;
    }
  }
}

if ( !function_exists( 'prp_is_it_excluded' ) ) {
  /**
   * Is It Excluded?
   *
   * Function to check if the current section is excluded or not.
   * The screenshots section is always excluded because this type of access to
   *  the WordPress SVN server is forbidden.
   *
   * @since  1.0
   *
   * @param  $tofind   string  Section name
   * @param  $exclude    string  List of excluded sections
   * @return       string  true or false, depending on whether the section was valid
   */
  function prp_is_it_excluded( $tofind, $exclude ) {

    // prp_log( __( '  Is \'' . strtolower( $tofind ) . '\' excluded?', plugin_readme_parser_domain ) );
    // prp_log( __( '  exclusion list: \'' . $exclude . '\'', plugin_readme_parser_domain ) );

    $tofind = strtolower( $tofind );
    $return = true;

    if ( 'screenshots' == $tofind ||
         'screenshot' == $tofind ) {

      $return = true;

    } else {

      if ( $tofind != $exclude ) {

        // Search in the middle

        $pos = strpos( $exclude, ',' . $tofind . ',' );
        if ( $pos === false ) {

          // Search on the left

          $pos = strpos( substr( $exclude, 0, strlen( $tofind ) + 1 ), $tofind . ',' );
          if ( $pos === false ) {

            // Search on the right

            $pos = strpos( substr( $exclude, ( strlen( $tofind ) + 1 ) * -1, strlen( $tofind ) + 1 ), ',' . $tofind );
            if ( $pos === false ) {
              $return = false;
            }
          }
        }
      }
    }
    // prp_log( __( '  \'' . $tofind . '\' is ' . ( $return ? 'excluded' : 'included' ), plugin_readme_parser_domain ) );
    return $return;
  }
}

if ( !function_exists( 'prp_get_section_name' ) ) {
  /**
   * Get Section Name
   *
   * Function to get name of readme section
   *
   * @since  1.0
   *
   * @param  $readme_line  string  Line from readme
   * @param  $start_pos    string  Position of line to look from
   * @return       string  Section name
   */
  function prp_get_section_name( $readme_line, $start_pos ) {

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
}

if ( !function_exists( 'prp_display_links' ) ) {
/**
 * Display links section
 *
 * Return the section that displays download links and links to assorted
 * WordPress sections
 *
 * @since  1.2
 *
 * @param  $download   string  Download link
 * @param  $target     string  Link target
 * @param  $nofollow   string  Link nofollow
 * @param  $version    string  Version number
 * @param  $mirror     string  Array of mirrors
 * @param  $plugin_name  string  Plugin name
 * @return       string  Output
 */
  function prp_display_links( $download, $target, $nofollow, $version, $mirror, $plugin_name ) {

    // prp_log( __( '  Display links:', plugin_readme_parser_domain ) );
    // prp_log( __( '  download link: \'' . $download . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:        \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow:      \'' . $nofollow . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  version:       \'' . $version . '\'', plugin_readme_parser_domain ) );

    $crlf = "\r\n";

    $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

    if ( $version != '' ) {
      $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version</a> (' . $version . ')<br /><br />' . $crlf;

      // prp_log( __( '  version found; outputting download link', plugin_readme_parser_domain ) );

      // If mirrors exist, add them to the output

      if ( $mirror[ 0 ] > 0 ) {
        for ( $m = 1; $m <= $mirror[ 0 ]; $m++ ) {
          $output .= '<a class="np-download-link" href="' . $mirror[ $m ] . '" target="' . $target . '"' . $nofollow . '>Download from mirror ' . $m . '</a><br />' . $crlf;
          // prp_log( __( '  mirror[' . $m . ']: ', plugin_readme_parser_domain ) . $mirror[ $m ] );
        }
        $output .= '<br />';
      } else {
        // prp_log( __( '  mirror:        \'none\'', plugin_readme_parser_domain ) );
      }

    } else {

      // prp_log( __( '  no version, therefore no download link', plugin_readme_parser_domain ) );

      $output .= '<span class="np-download-link error">No download link is available as the version number could not be found</span><br /><br />' . $crlf;

      // $output .= prp_report_error( __( '', '' ), '', false );

      // $output .= prp_report_error( __( '<span class="np-download-link>No download link is available as the version number could not be found</span>', plugin_readme_parser_domain ), plugin_readme_parser_name, false );

    }

    $output .= '<a href="https://wordpress.org/extend/plugins/' . $plugin_name . '/" target="' . $target . '"' . $nofollow . '>Visit the official WordPress plugin page</a><br />' . $crlf;
    $output .= '<a href="https://wordpress.org/support/plugin/' . $plugin_name . '" target="' . $target . '"' . $nofollow . '>View for WordPress forum for this plugin</a><br />' . $crlf . '</div>' . $crlf;

    return $output;
  }
}

if ( !function_exists( 'prp_check_img_exists' ) ) {
  /**
   * Check image exists
   *
   * Function to check if an image files with a specific extension exists
   * This fumction results in an HTTP 403 (Forbidden) error from the
   * server, therefore all it does (for) now is return false
   *
   * @since  1.2
   *
   * @param  $filename   string  Filename
   * @param  $ext    string  File extension
   * @return       string  Valid extension or blank
   */
  function prp_check_img_exists( $filename, $ext ) {

    // prp_log( __( '  Check image exists:', plugin_readme_parser_domain ) );
    // prp_log( __( '  image file: \'' . $filename . $ext . '\'', plugin_readme_parser_domain ) );
    $file_url = $filename . $ext;

    // prp_log( __( '  mime type:  \'' . mime_content_type( $filename . $ext ) . '\'', plugin_readme_parser_domain ) );


    // $file_exists = @mime_content_type( $file_url ) === 'image/' . $ext;
    // $file_exists = @file_exists( $file_url );

    // $file_exists = (bool)@file_get_contents($file_url, false, stream_context_create([
    //     'http' => [
    //         'method' => 'HEAD',
    //         'ignore_errors' => true,
    //     ],
    // ]));


    return false;

    // if ( $file_exists ) {
    //   prp_log( __( '\'' . $filename . $ext . '\' exists: true', plugin_readme_parser_domain ) );
    //   return $ext;
    // } else {
    //   prp_log( __( '\'' . $filename . $ext . '\' exists: false', plugin_readme_parser_domain ) );
    //   return false;
    // }
  }
}

if ( !function_exists( 'prp_strip_list' ) ) {
  /**
   * Strip List
   *
   * Function to strip user or tag lists and add links
   *
   * @since  1.0
   *
   * @param  $list     string  Provided list
   * @param  $type   string  Type of list
   * @param  $target   string  Link target
   * @param  $nofollow   string  Link nofollow
   * @return       string  HTML output
   */
  function prp_strip_list( $list, $type, $target, $nofollow ) {

    // prp_log( __( '  Strip list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  list:     \'' . $list. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  type:     \'' . $type . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  target:   \'' . $target . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  nofollow: \'' . $nofollow . '\'', plugin_readme_parser_domain ) );

    if ( $type == 'c' ) {
      $url = 'https://profiles.wordpress.org/users/';
    } else if ( $type == 't' ) {
      $url = 'https://wordpress.org/extend/plugins/tags/';
    } else {
      // prp_log( __( 'Invalid type found.', '', plugin_readme_parser_domain ), true );
      $url = '';
    }

    $startpos = 0;
    $number = 0;
    $endpos = strpos( $list, ',', 0 );
    $return = '';

    while ( $endpos !== false ) {
      ++$number;
      $name = trim( substr( $list, $startpos, $endpos - $startpos ) );
      // prp_log( __( '  name:     \'' . $name . '\'', plugin_readme_parser_domain ) );
      if ( $number > 1 ) {
        $return .= ', ';
      }
      $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';
      $startpos = $endpos + 1;
      $endpos = strpos( $list, ',', $startpos );
    }

    $name = trim( substr( $list, $startpos ) );
    if ( $number > 0 ) {
      $return .= ', ';
    }
    $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';

    return $return;
  }
}

if ( !function_exists( 'prp_get_file' ) ) {
  /**
   * Fetch a file (1.6)
   *
   * Use WordPress API to fetch a file and check results
   * RC is 0 to indicate success, -1 a failure
   *
   * @since  [version number]
   *
   * @param  string  $filein   File name to fetch
   * @param  string  $header   Only get headers?
   * @return string    Array containing file contents and response
   */
  function prp_get_file( $filein, $header = false ) {

    // prp_log( __( '  Get file:', plugin_readme_parser_domain ) );
    // prp_log( __( '  file in:     \'' . $filein. '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  header:      ' . ( $header ? 'true' : 'false' ), plugin_readme_parser_domain ) );

    $rc = 0;
    $error = '';
    if ( $header ) {
      $fileout = wp_remote_head( $filein );
      if ( is_wp_error( $fileout ) ) {
        $error = 'Header: ' . $fileout -> get_error_message();
        $rc = -1;
      }
    } else {
      $fileout = wp_remote_get( $filein );
      if ( is_wp_error( $fileout ) ) {
        $error = 'Body: ' . $fileout -> get_error_message();
        $rc = -1;
      } else {
        if ( isset( $fileout[ 'body' ] ) ) {
        $file_return[ 'file' ] = $fileout[ 'body' ];
        }
      }
    }

    $file_return[ 'error' ] = $error;
    $file_return[ 'rc' ] = $rc;
    if ( !is_wp_error( $fileout ) ) {
      if ( isset( $fileout[ 'response' ][ 'code' ] ) ) {
        $file_return[ 'response' ] = $fileout[ 'response' ][ 'code' ];
      }
    }

    // prp_log( __( '  file out:    contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $fileout, '  file out:' );
    // prp_log( __( '  file return: contents of readme file', plugin_readme_parser_domain ) );
    // prp_log( $file_return, '  file return:' );

    return $file_return;
  }
}

if ( !function_exists( 'prp_get_list' ) ) {
  /**
   * Extract parameters to an array
   *
   * Function to extract parameters from an input string and
   * add to an array
   *
   * @since  1.0
   *
   * @param  $input    string  The input string that needs to be split.
   * @param  $separator  string  The separator character used to split
   * the input string. If not specified, it defaults to a comma (,).
   * @param  $type       string  Indicates the type of list; only used for debug purposes.
   * @return     string  Array of parameters.
   */
  function prp_get_list( $input, $separator = '', $type = '' ) {   // Version 1.2

    // prp_log( __( '  Get \'' . $type . '\' list:', plugin_readme_parser_domain ) );
    // prp_log( __( '  input:     \'' . $input . '\'', plugin_readme_parser_domain ) );
    // prp_log( __( '  separator: \'' . $separator . '\'', plugin_readme_parser_domain ) );

    if ( $separator == '' ) {
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

    if ( $input != '' ) {
      $item++;
      $content[ $item ] = substr( $input, 0 );
    }

    $content[ 0 ] = $item;
    // prp_log( $content[0], '  content[0]:' );
    return $content;
  }
}

if ( !function_exists( 'prp_normalise_parameters' ) ) {
/**
 * Normalises the quotation marks to straight ones from curly ones.
 * Fixes the erroneous array member created by having a space in 'Upgrade
 * Notice'.
 *
 * @param  $text  string  The text to normalise the quotation marks in.
 * @return        string  The text containing normalised quotation marks.
 */
  define( 'QUOTES', array(
   '“' => '',
   '”' => '',
   '‘' => '',
   '’' => '',
   '&#8220;' => '',
   '&#8221;' => ''
  ) );
  function prp_normalise_parameters( $text ) {

    if ( is_string( $text ) ) {
      $normalised_text = str_replace(array_keys(QUOTES), array_values(QUOTES), $text);
      // prp_log( __( 'Normalised ', plugin_readme_parser_domain ) . $text );
      // prp_log( __( '        to ', plugin_readme_parser_domain ) . $normalised_text  );
      return $normalised_text;

    } else if ( is_array($text ) ) {
      $normalised_text = array();
      foreach ( $text as $key => $value ) {
        // prp_log( $key . ': ' . $value );
        $normalised_text[$key] = str_replace(array_keys(QUOTES), array_values(QUOTES), $text[$key]);
        // prp_log( $key . ': ' . $normalised_text[$key] );
      }
      if ( isset( $normalised_text[0] ) ) {
        if ( isset( $normalised_text[ 'exclude' ] ) ) {
          $normalised_text['exclude'] .= ' ' . $normalised_text[0];
        } else if ( isset( $normalised_text[ 'include' ] ) ) {
          $normalised_text['include'] .= ' ' . $normalised_text[0];
        } else {
          // prp_log( __( 'Erroneous parameter found', plugin_readme_parser_domain ) );
        }
        unset( $normalised_text[0] );
      }
      return $normalised_text;

    } else {
      // prp_log( $text, 'Normalise: wanted a string or an array; got \'' . gettype( $text ) . '\':'  );
      return $text;
    }
  }
}

if ( !function_exists( 'prp_toggle_global_shortcodes' ) ) {
/**
 * Toggle the shortcodes so that any shortcodes in the readme file
 * aren't expanded. Expanded shortcodes in the readme files cause problems if
 * they're used to provide examples of use of the plugin's shortcode.
 *
 * Some plugins change this filter’s priority, so clear the global list of
 * registered shortcodes temporarily, except for this plugin's readme_info
 * and readme_banner, which are needed.
 *
 * @since  2.0.0
 * @link   https://wordpress.stackexchange.com/a/115176
 *
 * @param  $content     array  The readme file content
 * @param  $exceptions  array  The shortcodes to keep active
 * @return array  The readme file content
 */
  function prp_toggle_global_shortcodes( $content ) {

    $file = plugin_dir_path( __DIR__ );
    // prp_log( __( 'Plugin directory: ', plugin_readme_parser_domain ) . $file );
    if ( str_contains( $file, plugin_readme_parser_filename ) ) {

      static $original_shortcodes = array();

      // prp_log( __( '# original shortcodes: ', plugin_readme_parser_domain ) . count ( $original_shortcodes ) );
      // prp_log( __( '# global shortcodes:   ' . count ( $GLOBALS['shortcode_tags', plugin_readme_parser_domain )] ) );

      // prp_log( __( 'Shortcode content: ', plugin_readme_parser_domain ) . $content );

      if ( count ( $original_shortcodes ) === 0 ) {
        // Toggle the shortcodes OFF

        $original_shortcodes = $GLOBALS['shortcode_tags'];
        $GLOBALS['shortcode_tags'] = array();

        $current_theme_supports_blocks = wp_is_block_theme();

        if ( $current_theme_supports_blocks ) {
          // prp_log( __( 'This theme DOES support blocks', plugin_readme_parser_domain ) );
          //   // prp_log( __( 'Toggling ALL global shortcodes OFF', plugin_readme_parser_domain ) );
          if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'Content contains \'[readme_info\'', plugin_readme_parser_domain ) );
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
            // prp_log( __( 'Toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
            // prp_log( $GLOBALS['shortcode_tags'], 'Global shortcodes:' );
          }

        } else {
          // prp_log( __( 'This theme DOES NOT support blocks', plugin_readme_parser_domain ) );

          // Need to put some of this plugin's ones back, otherwise it all breaks; it's unclear as to why and as to why these combinations work:

          if ( ( str_contains( $content, '[readme ' ) ) ||
               ( str_contains( $content, '[readme]' ) ) ) {
            // prp_log( __( 'Content contains \'[readme \' or \'[readme]\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
            $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';

          } else if  ( str_contains( $content, '[readme_info' ) ) {
            // prp_log( __( 'Content contains \'[readme_info\'', plugin_readme_parser_domain ) );

            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else if  ( str_contains( $content, '[readme_banner' ) ) {
            // prp_log( __( 'Content contains \'[readme_banner\'', plugin_readme_parser_domain ) );

            // Need to check this combo once banner display is working.

            $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
            $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';
            $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

          } else {
            // prp_log( __( 'Failed to find Plugin-readme Parser shortcode', plugin_readme_parser_domain ) );

            // We're in the wild, not writing out a readme with this plugin, so all the shortcodes need to be functional:
            // prp_log( __( 'Toggling ALL global shortcodes ON', plugin_readme_parser_domain ) );
            // prp_log( __( '# original shortcodes: ', plugin_readme_parser_domain ) . count ( $original_shortcodes ) );
            // prp_log( __( '# global shortcodes:   ' . count ( $GLOBALS['shortcode_tags'] ), plugin_readme_parser_domain ) );
            $GLOBALS['shortcode_tags'] = $original_shortcodes;
            return $content;

          }

          // prp_log( __( 'Toggling global shortcodes OFF except for:', plugin_readme_parser_domain ) );
          // prp_log( $GLOBALS['shortcode_tags'], 'Global shortcodes:' );
        }

      } else {
        // Toggle the shortcodes ON

        // prp_log( __( 'Toggling global shortcodes ON', plugin_readme_parser_domain ) );

        $GLOBALS['shortcode_tags'] = $original_shortcodes;
        $original_shortcodes = array();
        // prp_log( __( 'Repopulating GLOBAL shortcodes with original shortcodes', plugin_readme_parser_domain ) );

      }
    } else {
      prp_report_error( __( 'wrong plugin supplied', plugin_readme_parser_domain), plugin_readme_parser_name );
      // prp_log( __( '***** Wrong plugin supplied *****', plugin_readme_parser_domain ) );
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}

if ( !function_exists( 'prp_report_error' ) ) {
  /**
   * Report an error (1.4)
   *
   * Logs an error message along with the plugin name and the error text, and
   * returns an HTML-formatted error message.
   *
   * @since  1.0
   *
   * @param  $plugin_name  string  The name of the plugin where the error
   * occurred.
   * @param  $error    string  The error message to report.
   * @param  $echo   string  A boolean value indicating whether to output the
   * error message immediately using echo. If false, the function returns the
   * formatted error message instead of echoing it.
   * @return     string / true  If $echo === true, the function outputs the error message using echo and returns true. If $echo is false, the function returns the formatted error message instead of echoing it.
   */
  function prp_report_error( $plugin_name, $error, $echo = true ) {

    // prp_log( $error, $plugin_name, true, $echo );

    // $output = '<p class="error">' . $plugin_name . ': ' . $error . '</p>';

    // if ( $echo ) {
    //   echo $output;
    //   return true;
    // } else {
    //   return $output;
    // }

  }
}
?>
