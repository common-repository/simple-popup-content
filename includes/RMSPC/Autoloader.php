<?php

    if ( ! class_exists( 'RMSPC__Autoloader' ) ) {
        /**
         * Class RMSPC__Autoloader
         *
         * Allows for autoloading of Richied plugins classes.
         *
         * Example usage:
         *
         *      // will be `/var/www/site/wp-content/plugins/the-events-calendar'
         *      $this_dir = dirname(__FILE__);
         *
         *      // gets hold of the singleton instance of the class
         *      $autoloader = RMSPC__Autoloader::instance();
         *
         *      // register one by one or use `register_prefixes` method
         *      $autoloader->register_prefix( 'RMSPC__Admin__', $this_dir . '/src/Richied/admin' );
         *      $autoloader->register_prefix( 'RMSPC__Admin__', $this_dir . '/src/Richied/another-dir' );
         *      $autoloader->register_prefix( 'RMSPC__Utils__', $this_dir . '/src/Richied/another-dir' );
         *
         *      // register a direct class to path
         *      $autoloader->register_class( 'RMSPC__Some_Class', $this_dir . '/some/path/to/Some_Class.php' );
         *
         *      // register a fallback dir to be searched for the class before giving up
         *      $autoloader->add_fallback_dir( $this_dir . '/all-the-classes' );
         *
         *      // calls `spl_autoload_register`
         *      $autoloader->register_autoloader();
         *
         *      // class will be searched in the path
         *      // `/var/www/site/wp-content/plugins/the-events-calendar/src/Richied/admin/Some_Class.php'
         *      // and
         *      // `/var/www/site/wp-content/plugins/the-events-calendar/src/Richied/another-dir/Some_Class.php'
         *      $i = new RMSPC__Admin__Some_Class();
         *
         *      // class will be searched in the path
         *      // `/var/www/site/wp-content/plugins/the-events-calendar/utils/some-dir/Some_Util.php'
         *      $i = new RMSPC__Utils__Some_Util();
         *
         *      // class will be searched in the path
         *      // `/var/www/site/wp-content/plugins/the-events-calendar/deprecated/Richied_DeprecatedClass.php'
         *      $i = new Richied_DeprecatedClass();
         */
        class RMSPC__Autoloader {

            /**
             * @var RMSPC__Autoloader
             */
            protected static $instance;

            /**
             * An arrays of arrays each containing absolute paths.
             *
             * Paths are stored trimming any trailing `/`.
             * E.g. `/var/www/Richied-pro/wp-content/plugins/the-event-calendar/src/Richied`
             *
             * @var string[][]
             */
            protected $prefixes;

            /**
             * The string acting as a directory separator in a class name.
             *
             * E.g.: given `__` as `$dir_separator` then `Admin__Metabox__Some_Metabox`
             * will map to `/Admin/Metabox/SomeMetabox.php`.
             *
             * @var string
             */
            protected $dir_separator = '__';

            /** @var string[] */
            protected $fallback_dirs = array();

            /**
             * @var array
             */
            protected $class_paths = array();

            /**
             * Returns the singleton instance of the class.
             *
             * @return RMSPC__Autoloader
             */
            public static function instance() {
                if ( ! self::$instance instanceof RMSPC__Autoloader ) {
                    self::$instance = new self();
                }

                return self::$instance;
            }

            /**
             * Registers prefixes and root dirs using an array.
             *
             * Same as calling `register_prefix` on each one.
             *
             * @param array $prefixes_to_root_dirs
             */
            public function register_prefixes( array $prefixes_to_root_dirs ) {
                
                foreach ( $prefixes_to_root_dirs as $prefix => $root_dir ) {

                    $this->register_prefix( $prefix, $root_dir );
                }
            }

            /**
             * Associates a class prefix to an absolute path.
             *
             * @param string $prefix   A class prefix, e.g. `RMSPC__Admin__`
             * @param string $root_dir The absolute path to the dir containing
             *                         the prefixed classes.
             */
            public function register_prefix( $prefix, $root_dir ) {
                $root_dir = $this->normalize_root_dir( $root_dir );
                if ( ! isset( $this->prefixes[ $prefix ] ) ) {
                    $this->prefixes[ $prefix ] = array();
                }
                $this->prefixes[ $prefix ][] = $root_dir;
            }

            /**
             * Triggers the registration of the autoload method in the SPL
             * autoload register.
             */
            public function register_autoloader() {
                spl_autoload_register( array( $this, 'autoload' ) );
            }

            /**
             * Includes the file defining a class.
             *
             * This is the function that's registered as an autoloader.
             *
             * @param string $class
             */
            public function autoload( $class ) {
                $include_path = $this->get_class_path( $class );
                if ( ! empty( $include_path ) ) {

                    include_once( $include_path );
                }
            }

            private function normalize_root_dir( $root_dir ) {
                return rtrim( $root_dir, '/' );
            }

            protected function get_prefixed_path( $class ) {
                foreach ( $this->prefixes as $prefix => $dirs ) {
                    if ( strpos( $class, $prefix ) !== 0 ) {
                        continue;
                    }
                    $class_name = str_replace( $prefix, '', $class );
                    $class_path_frag = implode( '/', explode( $this->dir_separator, $class_name ) ) . '.php';
                    foreach ( $dirs as $dir ) {
                        $path = $dir . '/' . $class_path_frag;
                        if ( ! file_exists( $path ) ) {
                            // check if the file exists in lowercase
                            $class_path_frag = strtolower( $class_path_frag );
                            $path = $dir . '/' . $class_path_frag;
                        }
                        if ( ! file_exists( $path ) ) {
                            continue;
                        }

                        return $path;
                    }
                }
                return false;
            }

            protected function get_fallback_path( $class ) {
                foreach ( $this->fallback_dirs as $fallback_dir ) {
                    $include_path = $fallback_dir . '/' . $class . '.php';
                    if ( ! file_exists( $include_path ) ) {
                        // check if the file exists in lowercase
                        $class = strtolower( $class );
                        $include_path = $fallback_dir . '/' . $class . '.php';
                    }
                    if ( ! file_exists( $include_path ) ) {
                        continue;
                    }

                    return $include_path;
                }
            }

            /**
             * Gets the absolute path to a class file.
             *
             * @param string $class The class name
             *
             * @return string Either the absolute path to the class file or an
             *                empty string if the file was not found.
             */
            public function get_class_path( $class ) {
                $prefixed_path = $this->get_prefixed_path( $class );
                if ( $prefixed_path ) {
                    return $prefixed_path;
                }

                $class_path = ! empty( $this->class_paths[ $class ] ) ? $this->class_paths[ $class ] :false;
                if ( $class_path ) {
                    return $class_path;
                }

                $fallback_path = $this->get_fallback_path( $class );

                return $fallback_path ? $fallback_path : '';
            }

            /**
             * Adds a folder to search for classes that were not found among
             * the prefixed ones.
             *
             * This is the method to use to register a directory of deprecated
             * classes.
             *
             * @param string $dir An absolute path dto a dir.
             */
            public function add_fallback_dir( $dir ) {
                if ( in_array( $dir, $this->fallback_dirs ) ) {
                    return;
                }
                $this->fallback_dirs[] = $this->normalize_root_dir( $dir );
            }

            /**
             * @return string
             */
            public function get_dir_separator() {
                return $this->dir_separator;
            }

            /**
             * @param string $dir_separator
             */
            public function set_dir_separator( $dir_separator ) {
                $this->dir_separator = $dir_separator;
            }

            public function register_class( $class, $path ) {
                $this->class_paths[ $class ] = $path;
            }
        }
    }