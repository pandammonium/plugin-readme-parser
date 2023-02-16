<?php
/**
* Functions
*
* Functions called by main output generator
*
* @package	Pandammonium-README-Parser
* @since	1.2
*/

/**
* Get the README file
*
* Function to work out the filename of the README and get it
*
* @since	1.2
*
* @param	$plugin_url 	string	README name or URL
* @return       			string	False or array containing README and plugin name
*/

function arp_get_readme( $plugin_url, $version = '' ) {

    // Work out filename and fetch the contents

    if ( strpos( $plugin_url, '://' ) === false ) {
        $array[ 'name' ] = str_replace( ' ', '-', strtolower( $plugin_url ) );
        $plugin_url = 'http://plugins.svn.wordpress.org/' . $array[ 'name' ] . '/';
        if ( is_numeric( $version ) ) {
            $plugin_url .= 'tags/' . $version;
        } else {
            $plugin_url .= 'trunk';
        }
        $plugin_url .= '/readme.txt';
    }

    $file_data = arp_get_file( $plugin_url );

    // Ensure the file is valid

    if ( ( $file_data[ 'rc' ] == 0 ) && ( $file_data[ 'file' ] != '' ) && ( substr( $file_data[ 'file' ], 0, 9 ) != '<!DOCTYPE' ) && ( substr_count( $file_data[ 'file' ], "\n" ) != 0 ) ) {

        // Return values

        $array[ 'file' ] = $file_data[ 'file' ];

        return $array;

    } else {

        // If not valid, return false

        return false;
    }
}

/**
* Is It Excluded?
*
* Function to check if the current section is excluded or not
*
* @since	1.0
*
* @param	$tofind			string	Section name
* @param	$exclude    	string	List of excluded sections
* @return       			string	True or false, depending on whether the section was valid
*/

function arp_is_it_excluded( $tofind, $exclude ) {

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
                if ( $pos === false ) { $return = false; }
            }
        }
    }
    return $return;
}

/**
* Get Section Name
*
* Function to get name of README section
*
* @since	1.0
*
* @param	$readme_line	string	Line from README
* @param	$start_pos    	string	Position of line to look from
* @return       			string	Section name
*/

function arp_get_section_name( $readme_line, $start_pos ) {

    $hash_pos = strpos( $readme_line, '#', $start_pos + 1 );

    if ( $hash_pos ) {
        $section = substr( $readme_line, $start_pos + 1, $hash_pos - $start_pos - 2 );
    } else {
        $section = substr( $readme_line, $start_pos + 1 );
    }

    return $section;
}

/**
* Display links section
*
* Return the section that displays download links and links to assorted WordPress sections
*
* @since	1.2
*
* @param	$download	    string	Download link
* @param	$target	        string	Link target
* @param	$nofollow	    string	Link nofollow
* @param	$version	    string	Version number
* @param	$mirror	        string	Array of mirrors
* @param	$plugin_name	string	Plugin name
* @return       			string	Output
*/

function arp_display_links( $download, $target, $nofollow, $version, $mirror, $plugin_name ) {

    $crlf = "\r\n";

    $output = '<div markdown="1" class="np-links">' . $crlf . '## Links ##' . $crlf . $crlf;

    if ( $version != '' ) {
        $output .= '<a class="np-download-link" href="' . $download . '" target="' . $target . '"' . $nofollow . '>Download the latest version</a> (' . $version . ')<br /><br />' . $crlf;

        // If mirrors exist, add them to the output

        if ( $mirror[ 0 ] > 0 ) {
            for ( $m = 1; $m <= $mirror[ 0 ]; $m++ ) {
                $output .= '<a class="np-download-link" href="' . $mirror[ $m ] . '" target="' . $target . '"' . $nofollow . '>Download from mirror ' . $m . '</a><br />' . $crlf;
            }
            $output .= '<br />';
        }

    } else {

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
*
* @since	1.2
*
* @param	$filename   string	Filename
* @param	$ext    	string	File extension
* @return       		string	Valid extension or blank
*/

function arp_check_img_exists( $filename, $ext ) {

    if ( @GetImageSize( $filename . $ext ) ) {
        return $ext;
    } else {
        return false;
    }
}

/**
* Strip List
*
* Function to strip user or tag lists and add links
*
* @since	1.0
*
* @param	$list       string	Provided list
* @param	$type    	string	Type of list
* @param    $target     string  Link target
* @param    $nofollow   string  Link nofollow
* @return       		string	HTML output
*/

function arp_strip_list( $list, $type, $target, $nofollow ) {

    if ( $type == 'c' ) { $url = 'http://profiles.wordpress.org/users/'; } else { $url = 'http://wordpress.org/extend/plugins/tags/'; }

    $startpos = 0;
    $number = 0;
    $endpos = strpos( $list, ',', 0 );
    $return = '';

    while ( $endpos !== false ) {
        $number++;
        $name = trim( substr( $list, $startpos, $endpos - $startpos ) );
        if ( $number > 1 ) { $return .= ', '; }
        $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';
        $startpos = $endpos + 1;
        $endpos = strpos( $list, ',', $startpos );
    }

    $name = trim( substr( $list, $startpos ) );
    if ( $number > 0 ) { $return .= ', ';}
    $return .= '<a href="' . $url . $name . '" target="' . $target . '"' . $nofollow . '>' . $name . '</a>';

    return $return;
}

/**
* Fetch a file (1.6)
*
* Use WordPress API to fetch a file and check results
* RC is 0 to indicate success, -1 a failure
*
* @since	[version number]
*
* @param	string	$filein		File name to fetch
* @param	string	$header		Only get headers?
* @return	string				Array containing file contents and response
*/

function arp_get_file( $filein, $header = false ) {

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

	return $file_return;
}

/**
* Extract parameters to an array
*
* Function to extract parameters from an input string and
* add to an array
*
* @since	1.0
*
* @param	$input	    string	Input string
* @param	$seperator	string	Seperator
* @return			    string	Array of parameters
*/

function arp_get_list( $input, $seperator = '' ) {     // Version 1.2

    if ( $seperator == '' ) { $seperator = ','; }
    $comma = strpos( strtolower( $input ), $seperator );

    $item = 0;
    while ( $comma !== false ) {
        $item++;
        $content[ $item ] = substr( $input, 0, $comma );
        $input = substr( $input, $comma + strlen( $seperator ) );
        $comma = strpos( $input, $seperator );
    }

    if ( $input != '' ) {
        $item++;
        $content[ $item ] = substr( $input, 0 );
    }

    $content[ 0 ] = $item;
    return $content;
}

/**
* Report an error (1.4)
*
* Function to report an error
*
* @since	1.0
*
* @param	$error			string	Error message
* @param	$plugin_name	string	The name of the plugin
* @param	$echo			string	True or false, depending on whether you wish to return or echo the results
* @return					string	True
*/

function arp_report_error( $error, $plugin_name, $echo = true ) {

	$output = '<p style="color: #f00; font-weight: bold;">' . $plugin_name . ': ' . $error . "</p>\n";

	if ( $echo ) {
		echo $output;
		return true;
	} else {
		return $output;
	}

}
?>
