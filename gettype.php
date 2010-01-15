<?PHP
function m_get_type($f_filename){
	$pos = strrpos($f_filename, '.');
	$tmp = strtolower(substr($f_filename, $pos+1));
	switch ($tmp):
	    case "shtml":
			$m_type = 'text/html';
			break;
	    case "stm":
			$m_type = 'text/html';
			break;
	    case "hdml":
			$m_type = 'text/hdml';
			break;
	    case "html":
			$m_type = 'text/html';
			break;
	    case "htm":
			$m_type = 'text/html';
			break;
	    case "xml":
			$m_type = 'text/xml';
			break;
	    case "csv":
			$m_type = 'text/plain';
			break;
	    case "txt":
			$m_type = 'text/plain';
			break;
	    case "rtx":
			$m_type = 'text/richtext';
			break;
	    case "css":
			$m_type = 'text/css';
			break;
	    case "gif":
			$m_type = 'image/gif';
			break;
	    case "jpeg":
			$m_type = 'image/jpeg';
			break;
	    case "jpg":
			$m_type = 'image/jpeg';
			break;
	    case "png":
			$m_type = 'image/png';
			break;
	    case "bmp":
			$m_type = 'image/bmp';
			break;
	    case "tiff":
			$m_type = 'image/tiff';
			break;
	    case "tif":
			$m_type = 'image/tiff';
			break;
	    case "ico":
			$m_type = 'image/x-icon';
			break;
	    case "midi":
			$m_type = 'audio/midi';
			break;
	    case "mid":
			$m_type = 'addio/midi';
			break;
	    case "mp2":
			$m_type = 'audio/mpeg';
			break;
	    case "mp3":
			$m_type = 'audio/mpeg';
			break;
	    case "wav":
			$m_type = 'audio/x-wav';
			break;
	    case "au":
			$m_type = 'audio/basic';
			break;
	    case "zip":
			$m_type = 'application/zip';
			break;
	    case "lzh":
			$m_type = 'application/x-lzh';
			break;
	    case "lha":
			$m_type = 'application/x-lzh';
			break;
	    case "swf":
			$m_type = 'application/x-shockwave-flash';
			break;
	    case "js":
			$m_type = 'application/x-javascript';
			break;
	    case "tar":
			$m_type = 'application/x-tar';
			break;
	    case "gz":
			$m_type = 'application/x-gzip';
			break;
	    case "pdf":
			$m_type = 'application/pdf';
			break;
	    case "avi":
			$m_type = 'video/x-msvideo';
			break;
	    case "mpeg":
			$m_type = 'video/mpeg';
			break;
	    case "mpg":
			$m_type = 'video/mpeg';
			break;
	    case "qt":
			$m_type = 'video/quicktime';
			break;
	    case "mov":
			$m_type = 'video/quicktime';
			break;
	    default:
	        $m_type = 'application/octet-stream';
	endswitch;
	return($m_type);
}
// nao-pon

