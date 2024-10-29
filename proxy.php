<?php

	function read_media($file_path, $type)	{
		if (file_exists($file_path)) {
			// Set headers untuk video
			header("Content-Type: video/$type");
			header("Accept-Ranges: bytes");
			
			// inisialisasi ukuran file dan awal range
			$size = filesize($file_path);
			$range = 0;
			$length = $size;
			
			if (isset($_SERVER['HTTP_RANGE'])) {
				// Ambil nilai range header dari client jika ada
				// Range: bytes=<start>-<end>
				// contoh = Range: bytes=5000-10000
				$range = str_replace('bytes=', '', $_SERVER['HTTP_RANGE']);
				// 5000-100000
				$range = explode('-', $range);
				// 0 = 5000
				// 1 = 10000
				$range = intval($range[0]);

				// Hitung panjang potongan yang diminta
				$length = $size - $range;
				http_response_code(206); // status Partial Content
			} 
			
			// Set detail headers untuk partial content
			// Content-Range: bytes <start>-<end>/<total>
			// contoh = Content-Range: bytes 5000-9999/10000
			header("Content-Range: bytes $range-" . ($size - 1) . "/$size");
			header("Content-Length: $length");
			
			// Buka file trus geser lokasi baca ke posisi yg diminta
			$fp = fopen($file_path, 'rb');
			fseek($fp, $range);
			
			// Kirim file dalam bentuk potongan (chunks)
			// $buffer_size = 16384; // potongan 16KB
			$buffer_size = 8192; // potongan 8KB
			while (!feof($fp) && $length > 0) {
				$buffer = fread($fp, min($buffer_size, $length));
				// min(8192, 10000) => hasil = 8192
				// min(8192, 5000) => hasil = 5000
				echo $buffer;
				flush();
				$length -= strlen($buffer);
			}

			fclose($fp); 
		}
		else {
			http_response_code(404);
			echo "File not found.";
		}
	}
	
	
	read_media("D:/_web_dev_php/aplikasi/videos/memeng.mp4", "mp4"); 
	

?>