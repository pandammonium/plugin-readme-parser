<?php
/**
 * Handles errors.
 *
 * @package Pandammonium-ReadmeParser-Exceptions
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
    public const PRP_ERROR_BAD_INPUT = 200;
    public const PRP_ERROR_BAD_FILE = 201;
    public const PRP_ERROR_BAD_URL = 202;
    public const PRP_ERROR_BAD_DATA = 203;
    public const PRP_ERROR_BAD_CACHE = 204;
    public const PRP_ERROR_DEPRECATED = 400;
    public const PRP_WARNING_BAD_CALL = 300;
    public const PRP_WARNING_BAD_DATA = 503;
    protected $code;

    /**
     * This method is called when a new exception object is created. It is
     * used to set the error message and any other properties of the exception.
     */
    public function __construct( $message, $code = self::PRP_ERROR_UNKNOWN, Throwable $previous = null ) {
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
        case self::PRP_WARNING_BAD_CALL:
        case self::PRP_WARNING_BAD_DATA:
          $this->code = $code;
        break;
        case self::PRP_ERROR_NONE:
          $this->code = $code;
          throw new InvalidArgumentException( 'Code ' . $code . ' indicates there is no ' . __CLASS__ . ' error' );
        break;
        case E_USER_WARNING:
          $this->code = self::PRP_WARNING_BAD_CALL;
          trigger_error( plugin_readme_parser_name . ': ' . wp_strip_all_tags( $this->get_prp_message() ), E_USER_WARNING );
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

    private static function get_severity( int $code ): string {
      $severity = '';
      switch( $code ) {
        case self::PRP_ERROR_UNKNOWN:
        case self::PRP_ERROR_BAD_INPUT:
        case self::PRP_ERROR_BAD_FILE:
        case self::PRP_ERROR_BAD_URL:
        case self::PRP_ERROR_BAD_DATA:
        case self::PRP_ERROR_BAD_CACHE:
        case self::PRP_ERROR_DEPRECATED:
          $severity = 'Error';
        break;
        case self::PRP_WARNING_BAD_CALL:
        case self::PRP_WARNING_BAD_DATA:
          $severity = 'Warning';
        break;
        case self::PRP_ERROR_NONE:
          $severity = '';
        default:
          throw new InvalidArgumentException( 'Invalid error code used in ' . __CLASS__ . ': ' . $code );
        break;
      }
      return strtoupper( $severity ) . ' ';
    }

    private static function get_prefix( bool $echo ): string {
      return $echo ? plugin_readme_parser_name : self::PRP_PREFIX;
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
    private function get_previous(): ?Throwable {
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

      $this_msg = self::PRP_PREFIX .
          self::get_severity( $this->get_prp_code() ) .
          print_r( $this->get_code(), true ) .
          " " . print_r( $this->get_prp_message_stripped_of_tags(), true );
      $this_loc = self::PRP_PREFIX .
          "in " . print_r( $this->get_prp_file() .
          " on line " . print_r( $this->get_prp_line(), true ), true );

      if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) &&
           ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ) {
        error_log( $this->get_prefix( false ) . $this_msg );
        error_log( $this->get_prefix( false ) . $this_loc );
        $previous = $this->get_previous();
        if ( $previous ) {
          $previous_msg = $this->get_prefix( false ) .
            self::get_severity( $previous->get_prp_code() ) . print_r( $previous->get_code(), true ) .
            " " . print_r( $previous->get_prp_message_stripped_of_tags(), true );
          $previous_loc = $this->get_prefix( false ) .
            "in " . print_r( $previous->get_prp_file() .
            " on line " . print_r( $previous->get_prp_line(), true ), true );
          error_log( $previous_msg );
          error_log( $previous_loc );
        }
      }

      $display = '<p><span class="error"><b>' . $this->get_prefix( true ) . '</b></span>: ' . print_r( self::get_severity( $this->get_prp_code() ) . $this->get_prp_code(), true ) . ' ' . print_r( $this->get_prp_message(), true ) . '.</p>';
      $previous = $this->get_previous();
      if ( $previous ) {
        $display .= '<p><span class="error"><b>' . $this->get_prefix( true ) . '</b></span>: ' . print_r( self::get_severity( $previous->get_prp_code() ) . $previous->get_prp_code(), true ) . ' ' . print_r( $previous->get_prp_message(), true ) . '.</p>';
      }

      return print_r( $display, true );
    }

  }

}
?>
