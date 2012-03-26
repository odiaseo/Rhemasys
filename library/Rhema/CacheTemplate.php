<?php

	class Rhema_CacheTemplate {
		public static $_library;
		public static $_template = array(
										'page-apc',
										'page-memcached',
										'class-apc',
										'class-memcached',
										'page-memcached',
										'class-file',
										'page-file',
										'default-apc',
		                                'default-file'
									);

		public static function setCacheTemplates($base = null){
				$cacheDir = realpath(APPLICATION_PATH . '/../sites/') . '/' . SITE_DIR . '/cache/db' ;
				if(!file_exists($cacheDir)){
					@mkdir($cacheDir, 0777, true);
				}
				$base     = ($base === null) ? '/' . SITE_DIR : $base ;
				if(!file_exists($cacheDir)){
					@mkdir($cacheDir, 0777, true);
				}

				self::$_library['page']      =  array(
												'name'	=> 'Page',
												'options'	=> array(
													            'lifetime' 			=> 60*60*24*30,
													            'debug_header'		=> (APPLICATION_ENV == 'development') ? true : false,
													            'default_options' 	=> array( 'cache' => true
													            							 ,'cache_with_get_variables' => true
													            							 ,'cache_with_post_variables' => true
													            							 ,'cache_with_files_variables' => true
													            							 ,'cache_with_cookie_variables' => true
													            							 ,'cache_with_session_variables' => true
													            							 ,'make_id_with_session_variables' => false
													            							 ,'make_id_with_cookie_variables' => false
													            							 ,'make_id_with_files_variables' => false
													            							 ,'make_id_with_post_variables' => true
													            							 ,'make_id_with_get_variables' => true
													            					),
													            'regexps' 			=> array(
													            	 '^'.$base.'/(.*)?'   	        => array('cache' => true),
													                '^/master/?(.*)$' 	=> array('cache' => false),
													                '^/(.*)?(login|logout|auth|contact-us|rest|soap|register)/?[^/]?$'
													            							=> array('cache' => false)

													            )
												)
						        ) ;
			//=======================================================================================================================
				self::$_library['default']  =
	       		self::$_library['core']     = array('name'		=> 'Core',
						'options'	=> array(
									'lifetime'	=> 60*60*24,
									'automatic_serialization' 	=>true
				));
			//=======================================================================================================================
				self::$_library['class']     = array('name'		=> 'Class',
											'options'	=> array(
														'lifetime'	=> 60*60*24,
														'automatic_serialization' 	=>true
									));
			//==========================================================================================================================
				self::$_library['file']      = array('name'	=> 'File',
										'options'	=> array(
														'cache_dir' => $cacheDir,
														//'cache_dir' => realpath(APPLICATION_PATH . '/../data/cache/db'),
														'hashed_directory_level' => 2
												 	))	;
			//=======================================================================================================================
			   self::$_library['memcached']    = array(
			        					'name' => 'Memcached',
			        					'options' => array(
			            					'servers' => array(
								                array(
								                    'host' => 'localhost',
								                    'port' => 11211,
								                    'persistent' => true,
								                    'weight' => 1,
								                    'timeout' => 5,
								                    'retry_interval' => 15,
								                    'status' => true
								                )
								              )
								           )
								    );

			 self::$_library['apc']		= array('name' => 'Apc');
			//==========================================================================================================================


		}
	}