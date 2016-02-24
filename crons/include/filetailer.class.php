<?php

/*
	Author: Furs
	Description: tails a file ;P

---[Example]---------------------------------------------
	$ft = new filetailer();

	if ($ft->add('test','/tmp/example.log')) {
		while (1) {
			while(($ft->fetch('test',$row))) {
				print $row;
			
			}
			sleep(5);
		}

	}
--------------------------------------------------------

*/

class filetailer {

	public $handle = array();
	
	/*
	$handle = array(
		'cleanfilterd'	=>	array(
			'file'	=>	'',
			'loc'	=>	'',
			'fh'	=>	'',
			'size'	=>	'',
		),
	);
	*/

	public function __construct() {
	
	}
	
	public function add($handle,$file,$loc = 0) {
		if (array_key_exists($handle,$this->handle)) {
			return false;
		}
		if (!is_readable($file)) {
			return false;
		}
		if (!is_numeric($loc)) {
			return false;
		}
		$this->handle[$handle] = array(
			'file'	=>	$file,
			'loc'	=>	$loc,
			'fh'	=>	'',
			'size'	=>	'',
		);
		return true;
	}
	public function del($handle) {
		if (array_key_exists($handle,$this->handle)) {
			unset($this->handle[$handle]);
		}
	}

	public function fetch($handle,&$data = false) {
		if (!array_key_exists($handle,$this->handle)) {
			$data = "Handle not found";
			return false;
		}
		
		if ($this->handle[$handle]['fh'] == '') {
			if (FALSE === ($this->getHandle($handle))) { 
				$data = "Error creating handle";
				return false;
			}
		}
		if (!feof($this->handle[$handle]['fh'])) {
			$data = fgets($this->handle[$handle]['fh'],4096);
			return true;
		} else {
			$this->closeHandle($handle);
			$data = '';
			return false;
		}
	
	}

	private function getHandle($handle) {
		if (!is_readable($this->handle[$handle]['file'])) {
			return false;
		}
		if (FALSE === ($this->handle[$handle]['fh'] = fopen($this->handle[$handle]['file'],'r'))) {
			return false;
		}
		$tmpct = fstat($this->handle[$handle]['fh']);
		if ($this->handle[$handle]['loc'] != 0) {
			$tmpct = fstat($this->handle[$handle]['fh']);
			if ($tmpct['size'] < $this->handle[$handle]['size']) {
				//file got smaller? Must be a sign that it got truncated or rotated? ;)
				$this->handle[$handle]['loc'] = 1;
				$this->handle[$handle]['size'] = $tmpct['size'];
			}
		
		} else {
			$this->handle[$handle]['size'] = $tmpct['size'];
		}
		if ($this->handle[$handle]['loc'] != 0) {
			fseek($this->handle[$handle]['fh'],$this->handle[$handle]['loc']);
		} else {
			fseek($this->handle[$handle]['fh'],0,SEEK_END);
		}

	}
	private function closeHandle($handle) {
		$this->handle[$handle]['loc'] = ftell($this->handle[$handle]['fh']);
		fclose($this->handle[$handle]['fh']);
		$this->handle[$handle]['fh'] = '';
	}
}

?>
