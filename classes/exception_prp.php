<?php
/**
 * Handles errors.
 *
 * @package Pandammonium-Readme-Parser
 * @author pandammonium <internet@pandammonium.org>
 * @since 2.0.0
 *
 * @todo See if any of these can be replaced by built-in PHP
 * Exception classes.
 * @todo Check any chained exceptions use the third argument of the
 * constructor.
 * @todo Thoroughly test every instance of use.
 */

// If this file is called directly, abort:
defined( 'ABSPATH' ) or die();
defined( 'WPINC' ) or die();

if ( !class_exists( 'PRP_Exception' ) ) {

  class PRP_Exception extends Exception {

    private const PRP_DIVIDER = ' | ';
    private const PRP_PREFIX = 'PRP' . self::PRP_DIVIDER;

    public const PRP_ERROR_NONE = -1;
    public const PRP_ERROR_UNKNOWN = 100;
    public const PRP_ERROR_BAD_INPUT = 201;
    public const PRP_ERROR_BAD_FILE = 202;
    public const PRP_ERROR_BAD_URL = 203;
    public const PRP_ERROR_BAD_DATA = 204;
    public const PRP_ERROR_BAD_CACHE = 205;
    public const PRP_ERROR_DEPRECATED = 205;
    protected $code;

    /**
     * This method is called when a new exception object is created. It is
     * used to set the error message and any other properties of the exception.
     */
    public function __construct( $message, $code = PRP_ERROR_UNKNOWN, Throwable $previous = null ) {
      parent::__construct($message, $code, $previous);
      $this->set_code( $code );
    }

    private function set_code( $code ): void {
      switch( $code ) {
        case self::PRP_ERROR_UNKNOWN:
        case self::PRP_ERROR_BAD_INPUT:
        case self::PRP_ERROR_BAD_FILE:
        case self::PRP_ERROR_BAD_URL:
        case self::PRP_ERROR_BAD_DATA:
        case self::PRP_ERROR_BAD_CACHE:
          $this->code = $code;
        break;
        case self::PRP_ERROR_NONE:
          $this->code = $code;
          throw new InvalidArgumentException( 'Code ' . $code . ' indicates there is no ' . __CLASS__ . ' error' );
        break;
        case self::PRP_ERROR_DEPRECATED:
        case E_USER_DEPRECATED:
          $this->code = self::PRP_ERROR_DEPRECATED;
          trigger_error( plugin_readme_parser_name . ': ' . wp_strip_all_tags( $this->get_prp_message() ), E_USER_DEPRECATED );
        break;
        default:
          $this->code = self::PRP_ERROR_UNKNOWN;
          throw new InvalidArgumentException( 'Code ' . $code . ' is not an error code used in ' . __CLASS__ );
        break;
      }
    }

    private function get_code(): int {
      return $this->code;
    }

    public static function get_code_as_string( int $code ): string {
      switch( $code ) {
        case self::PRP_ERROR_UNKNOWN:
        return 'Unknown error';
        case self::PRP_ERROR_BAD_INPUT:
        return 'Bad input';
        case self::PRP_ERROR_BAD_FILE:
        return 'Bad file';
        case self::PRP_ERROR_BAD_URL:
        return 'Bad URL';
        case self::PRP_ERROR_BAD_DATA:
        return 'Bad data';
        case self::PRP_ERROR_BAD_CACHE:
        return 'Bad cache';
        case self::PRP_ERROR_DEPRECATED:
        return 'Deprecated';
        case self::PRP_ERROR_NONE:
        return 'None';
        default:
          throw new InvalidArgumentException( 'Invalid error code used in ' . __CLASS__ );
        break;
      }
    }

    /**
     * This method returns the error code associated with the exception. It is
     * used to provide additional information about the error
     * that occurred.
     *
     * @since 2.0.0
     */
    public function get_prp_code(): int {
      return parent::getCode();
    }

    /**
     * This method returns the file in which the exception was thrown.
     *
     * @since 2.0.0
     */
    public function get_prp_file(): string {
      return parent::getFile();
    }

    /**
     * This method returns the line number at which the exception was thrown.
     *
     * @since 2.0.0
     */
    public function get_prp_line(): int {
      return parent::getLine();
    }

    /**
     * This method returns the error message associated with the exception.
     *
     * @since 2.0.0
     */
    public function get_prp_message(): string {
      return parent::getMessage();
    }

    /**
     * This method returns the error message associated with the exception.
     *
     * @since 2.0.0
     */
    private function get_prp_message_stripped_of_tags(): string {
      $output = wp_strip_all_tags( parent::getMessage() );
      $output = str_ireplace( '&lt;', '<', $output );
      $output = str_ireplace( '&gt;', '>', $output );

      return $output;
    }

    /**
     * This method returns an array containing the backtrace that led to the
     * exception.
     *
     * @since 2.0.0
     */
    public function get_prp_trace(): array {
      $trace = parent::getTrace();
      array_push( $trace, plugin_readme_parser_name );
      // array_unshift( $trace, plugin_readme_parser_name );
      return $trace;
    }

    /**
     * This method returns a string containing the backtrace that led to the
     * exception.
     *
     * @since 2.0.0
     */
    public function get_prp_trace_as_string(): string {
      return self::PRP_PREFIX . parent::getTraceAsString();
    }

    /**
     * This method returns the previous exception that was thrown, if any.
     *
     * @since 2.0.0
     */
    public function get_prp_previous(): ?Throwable {
      return parent::getPrevious();
    }

    /**
     * This method returns a string representation of the exception.
     *
     * @since 2.0.0
     */
    public function __prp_to_string(): string {
      return self::PRP_PREFIX . parent::__toString();
    }

    /**
     * This method builds a coherent message and writes out to the screen and
     * to the error log (options in wp-confog.php permitting).
     */
    public function get_prp_nice_error(): string {

      if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) &&
           ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ) {
        error_log( self::PRP_PREFIX .
          "ERROR " . print_r( $this->get_code(), true ) .
          " " . print_r( $this->get_prp_message_stripped_of_tags(), true ) );
        error_log( self::PRP_PREFIX .
          "in " . print_r( $this->get_prp_file() .
          " on line " . print_r( $this->get_prp_line(), true ), true ) );
      }

      $display = '<p><span class="error">' . plugin_readme_parser_name . '</span>: ' . print_r( 'ERROR ' . $this->get_prp_code(), true ) . ' ' . print_r( $this->get_prp_message(), true ) . '.</p>';
      $previous = $this->get_prp_previous();
      if ( $previous ) {
        $display .= '<p><span class="error">' . plugin_readme_parser_name . '</span>: ' . print_r( 'ERROR ' . $previous->get_prp_code(), true ) . ' ' . print_r( $previous->get_prp_message(), true ) . '.</p>';
          }
      $delim = ':';
      $pos = strpos( $display, $delim );
      if ( false !== $pos ) {
        $display = '<b>' . str_replace( $delim, $delim . '</b>', $display );
      }

      return print_r( $display, true );
    }

  }

}
?>
