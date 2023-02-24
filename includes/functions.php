<?php
/**
 * Functions
 *
 * Functions called by main output generator
 *
 * @package  Pandammonium-Readme-Parser
 * @since  1.2
 */



/**
 * Prints a message to the debug log file.
 *
 * @since 2.0.0
 *
 * @param string / array $message    The message to be logged.
 * @param string  (optional) A name to associate with the message. This is useful if logging multiple messages.
 * @param bool  $echo whether or not the The function should exit after writing to the log
 *
 */
function prp_log( $message, $message_name = '', $echo = true ) {
  // if ( true === defined( 'WP_DEBUG' ) ) {
  //   if ( false === defined( 'WP_DEBUG_LOG' ) ) {
  //     $echo = false;
  //   } else if ( true === defined( 'WP_DEBUG_DISPLAY' ) ) {
  //     $echo = true;
  //   }
  //   if ( is_array( $message ) ) {
  //     if ( '' !== $message_name ) {
  //       error_log( print_r( 'PRP | ' . $message_name, $echo ) );
  //     }
  //     error_log( print_r( $message, $echo ) );
  //   } else {
  //     if ( '' !== $message_name ) {
  //       error_log( print_r( 'PRP | ' . $message_name . ': ' . $message, $echo ) );
  //     } else {
  //       error_log( print_r( 'PRP | ' . $message, $echo ) );
  //     }
  //   }
  // }
  if ( true === defined( 'WP_DEBUG' ) && true === defined( 'WP_DEBUG_LOG' ) ) {
    if ( is_array( $message ) ) {
      error_log( print_r( 'PRP | ' . $message_name, true ) );
      error_log( print_r( $message, true ) );
    } else {
      error_log( print_r( 'PRP | ' . $message, true ) );
    }
    // if ( $shouldNotDie ) {
    //   exit;
    // }
  }
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

function prp_get_readme( $plugin_url, $version = '' ) {

  prp_log( '  Get readme:' );
  prp_log( '  title:      \'' . $plugin_url . '\'' );

  // Work out filename and fetch the contents

  if ( strpos( $plugin_url, '://' ) === false ) {
    $array[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
    $plugin_url = 'http://plugins.svn.wordpress.org/' . $array[ 'name' ] . '/';
    prp_log( '  url:        \'' . $plugin_url . '\'' );
  if ( is_numeric( $version ) ) {
    $plugin_url .= 'tags/' . $version;
    prp_log( '  tag url:    \'' . $plugin_url . '\'' );
  } else {
    $plugin_url .= 'trunk';
    prp_log( '  trunk url:  \'' . $plugin_url . '\'' );
  }
  $plugin_url .= '/readme.txt';
  prp_log( '  readme.txt: \'' . $plugin_url . '\'' );
  }

  $file_data = prp_get_file( $plugin_url );
  prp_log( '  file data:   contents of readme file' );
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

    prp_log( '  readme file is invalid' );

    // If not valid, return false

    return false;
  }
}

/**
 * Is It Excluded?
 *
 * Function to check if the current section is excluded or not
 *
 * @since  1.0
 *
 * @param  $tofind   string  Section name
 * @param  $exclude    string  List of excluded sections
 * @return       string  true or false, depending on whether the section was valid
 */

function prp_is_it_excluded( $tofind, $exclude ) {

  // prp_log( '  Is \'' . strtolower( $tofind ) . '\' excluded?' );
  // prp_log( '  exclusion list: \'' . $exclude . '\'' );

  $tofind = strtolower( $tofind );
  $return = true;

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
  // prp_log( '  \'' . $tofind . '\' is ' . ( $return ? 'excluded' : 'included' ) );
  return $return;
}

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

  prp_log( '  Get section name:' );
  prp_log( '  section name: \'' . $section . '\'' );

  return $section;
}

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

  prp_log( '  Display links:' );
  prp_log( '  download link: \'' . $download . '\'' );
  prp_log( '  target:        \'' . $target . '\'' );
  prp_log( '  nofollow:      \'' . $nofollow . '\'' );
  prp_log( '  version:       \'' . $version . '\'' );

  $crlf = "\r\n";

  $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

  if ( $version != '' ) {
    $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version</a> (' . $version . ')<br /><br />' . $crlf;

    prp_log( '  version found; outputting download link' );

    // If mirrors exist, add them to the output

    if ( $mirror[ 0 ] > 0 ) {
      for ( $m = 1; $m <= $mirror[ 0 ]; $m++ ) {
        $output .= '<a class="np-download-link" href="' . $mirror[ $m ] . '" target="' . $target . '"' . $nofollow . '>Download from mirror ' . $m . '</a><br />' . $crlf;
        prp_log( '  mirror[' . $m . ']: ' . $mirror[ $m ] );
      }
      $output .= '<br />';
    } else {
      prp_log( '  mirror:        \'none\'' );
    }

  } else {

    prp_log( '  no version, therefore no download link' );

    $output .= '<span class="np-download-link" style="color: #f00;">No download link is available as the version number could not be found</span><br /><br />' . $crlf;
  }

  $output .= '<a href="http://wordpress.org/extend/plugins/' . $plugin_name . '/" target="' . $target . '"' . $nofollow . '>Visit the official WordPress plugin page</a><br />' . $crlf;
  $output .= '<a href="http://wordpress.org/tags/' . $plugin_name . '" target="' . $target . '"' . $nofollow . '>View for WordPress forum for this plugin</a><br />' . $crlf . '</div>' . $crlf;

  return $output;
}

/**
 * Check image exists
 *
 * Function to check if an image files with a specific extension exists
 * This fumction results in an HTTP 403 (Forbidden) erro from the
 * server
 *
 * @since  1.2
 *
 * @param  $filename   string  Filename
 * @param  $ext    string  File extension
 * @return       string  Valid extension or blank
 */

function prp_check_img_exists( $filename, $ext ) {

  prp_log( '  Check image exists:' );
  prp_log( '  image file: \'' . $filename . $ext . '\'' );

  prp_log( '  mime type:  \'' . mime_content_type( $filename . $ext ) . '\'' );

  if ( mime_content_type( $filename . $ext ) === 'image/' . $ext ) {
    prp_log( '\'' . $filename . $ext . '\' exists: true' );
    return $ext;
  } else {
    prp_log( '\'' . $filename . $ext . '\' exists: false' );
    return false;
  }
}

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

  prp_log( '  Strip list:' );
  prp_log( '  list:     \'' . $list. '\'' );
  prp_log( '  type:     \'' . $type . '\'' );
  prp_log( '  target:   \'' . $target . '\'' );
  prp_log( '  nofollow: \'' . $nofollow . '\'' );

  if ( $type == 'c' ) { $url = 'http://profiles.wordpress.org/users/'; } else { $url = 'http://wordpress.org/extend/plugins/tags/'; }

  $startpos = 0;
  $number = 0;
  $endpos = strpos( $list, ',', 0 );
  $return = '';

  while ( $endpos !== false ) {
    ++$number;
    $name = trim( substr( $list, $startpos, $endpos - $startpos ) );
    prp_log( '  name:     \'' . $name . '\'' );
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

  prp_log( '  Get file:' );
  prp_log( '  file in:     \'' . $filein. '\'' );
  prp_log( '  header:      ' . ( $header ? 'true' : 'false' ) );

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

  prp_log( '  file out:    contents of readme file' );
  // prp_log( $fileout, '  file out:' );
  prp_log( '  file return: contents of readme file' );
  // prp_log( $file_return, '  file return:' );

  return $file_return;
}

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

  prp_log( '  Get \'' . $type . '\' list:' );
  prp_log( '  input:     \'' . $input . '\'' );
  prp_log( '  separator: \'' . $separator . '\'' );
  prp_log( '  type:      \'' . $type . '\'' );

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
  prp_log( $content[0], '  content[0]:' );
  return $content;
}

if ( !function_exists( 'prp_normalise_quotation_marks' ) ) {
/**
 * Normalises the quotation marks to straight ones from curly ones.
 *
 * @param  $text  string  The text to normalise the quotation marks in.
 * @return        string  The text containing normalised quotation marks.
 */
  function prp_normalise_quotation_marks( $text ) {

    if ( is_string( $text ) ) {
      $normalised_text = str_replace(array("“", "”"), array('"', '"'), $text);
      $normalised_text = str_replace(array("‘", "’"), array('\'', '\''), $normalised_text);

      prp_log( 'Normalised ' . $text );
      prp_log( '        to ' . $normalised_text  );
      return $normalised_text;
    } else {
      prp_log( 'Wanted a string; got a ' . gettype( $text ) );
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
    prp_log( 'Plugin directory: ' . $file );
    if ( defined( 'can_toggle_shortcodes' ) &&
         str_contains( $file, pandammonium_readme_parser_filename ) ) {

      static $original_shortcodes = array();

      prp_log( '# original shortcodes: ' . count ( $original_shortcodes ) );
      prp_log( '# global shortcodes:   ' . count ( $GLOBALS['shortcode_tags'] ) );

      // prp_log( 'Shortcode content: ' . $content );

      if ( count ( $original_shortcodes ) === 0 ) {

        $original_shortcodes = $GLOBALS['shortcode_tags'];
        $GLOBALS['shortcode_tags'] = array();

        // Need to put some of this plugin's ones back, otherwise it all breaks; it's unclear as to why this combination works:

        if ( ( str_contains( $content, '[readme ' ) ) ||
             ( str_contains( $content, '[readme]' ) ) ) {
          prp_log( 'Content contains \'[readme \' or \'[readme]\'' );

          $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
          $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';
          $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';

        } else if  ( str_contains( $content, '[readme_info' ) ) {
          prp_log( 'Content contains \'[readme_info\'' );

          $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';

        } else if  ( str_contains( $content, '[readme_banner' ) ) {
          prp_log( 'Content contains \'[readme_banner\'' );

          // Need to check this combo once banner display is working.

          $GLOBALS['shortcode_tags']['readme'] = 'readme_parser';
          $GLOBALS['shortcode_tags']['readme_banner'] = 'readme_banner';
          $GLOBALS['shortcode_tags']['readme_info'] = 'readme_info';


        } else {

          prp_log( 'Failed to find Plugin-readme Parser shortcode' );
          // We're in the wild, not writing out a readme with this plugin, so all the shortcodes need to be functional:
          prp_log( 'Toggling ALL global shortcodes ON' );
          $GLOBALS['shortcode_tags'] = $original_shortcodes;
          return $content;

        }

        prp_log( 'Toggling global shortcodes OFF except for:' );
        prp_log( $GLOBALS['shortcode_tags'], 'Global shortcodes:' );

      } else {

        prp_log( 'Toggling global shortcodes ON' );

        $GLOBALS['shortcode_tags'] = $original_shortcodes;
        prp_log( 'Repopulating GLOBAL shortcodes with original shortcodes' );

      }
    } else {
      prp_log( '***** Wrong plugin supplied *****' );
    }
    return $content;
  }

  add_filter( 'the_content', 'prp_toggle_global_shortcodes', -1 );
  add_filter( 'the_content', 'prp_toggle_global_shortcodes', PHP_INT_MAX );
}

/**
 * Report an error (1.4)
 *
 * Logs an error message along with the plugin name and the error text, and
 * returns an HTML-formatted error message.
 *
 * @since  1.0
 *
 * @param  $error    string  The error message to report.
 * @param  $plugin_name  string  The name of the plugin where the error
 * occurred.
 * @param  $echo   string  A boolean value indicating whether to output the
 * error message immediately using echo. If false, the function returns the
 * formatted error message instead of echoing it.
 * @return     string / true  If $echo === true, the function outputs the error message using echo and returns true. If $echo is false, the function returns the formatted error message instead of echoing it.
 */

function prp_report_error( $error, $plugin_name, $echo = true ) {

  prp_log( 'Error:' );
  prp_log( 'plugin name: ' . $plugin_name );
  prp_log( 'error:       \'' . $error . '\'' );

  $output = '<p style="color: #990000;">' . $plugin_name . ': ' . $error . "</p>\n";

  if ( $echo ) {
    echo $output;
    return true;
  } else {
    return $output;
  }

}
?>
