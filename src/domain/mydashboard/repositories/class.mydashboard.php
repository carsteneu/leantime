<?php

/**
 * mydashboard class
 *
 * @author  Dr. Carsten Euwens <ce@papoo.de>
 * @version 1.0
 * @package classes
 */
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class mydashboard
    {

        /**
         * @access public
         * @var    object
         */
        public $db;

        /**
         * @access private
         * @var    array
         */
        private $defaultWidgets = array( 1, 3, 9 );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

    }


}