<?php
 
	class Admin_Service_Pdf {
		
		public $pdf;
		public $options = array();
		
		public function __construct(){
			define('DOMPDF_TEMP_DIR', realpath(APPLICATION_PATH . '/../data/cache/temp'));
			require_once('Rhema/Dompdf/dompdf_config.inc.php');
			
			$this->pdf = new DOMPDF();
		}
		
		public function toPdf($filename, $html = ''){			
			$this->pdf->load_html($html);
			$this->pdf->render();
			$this->pdf->stream($filename, $this->options);
			die();
		}
	}