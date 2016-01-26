<?php defined('BASEPATH') or exit('No direct script access allowed');

function find_img($img) {
	$file = find_file($img, APPPATH.'static/uploads/'); // Try to find the image in static/img
	if($file == null) { // We can't read the in uploads
		foreach(array(APPPATH, 'pinet/') as $d) {
			$file = find_file($img, $d.'static/img/'); // Try to find the image in static/img
			if($file != null)
				break;
		}
	}
	return $file;
}

function find_file($file, $path, $basepath = FCPATH) {
	$f = null;
	if(file_exists($basepath.$path.$file)) { // Test if the file is exists in the static img folder
		$f = $basepath.$path.$file;
	} 
	else if(file_exists($file)) { // Test if the file exists
		$f = $file;
	} 
	return $f;
}

function inner_create_image_thumbnail($orig, $width, $height = 0) {
	$output_dir = 'application/cache/img/'.$width.'x'.$height.'/'; // All the thumbnails is located in cache

	$out_file = find_file($orig, $output_dir); // Try to find the file from output dir
	if($out_file != null) { // If the cached file is exists, return it
		return $out_file;
	}

	$file = find_img($orig);
	if($file == null) // We can't read the file {
		return null;
	if($width == 'normal')
		return $file;

	if(!file_exists($output_dir)) { // If the output dir is not exists, then create it
		mkdir($output_dir, 0777, true);
	}

	$path_parts = pathinfo($file);
	$name = $path_parts['filename'];
	$ext = $path_parts['extension'];
    $out_file = FCPATH.$output_dir.$orig;
    $dir = dirname($out_file);
    if(!file_exists($dir)) {
    	mkdir($dir, 0755, true);
    }

	if (extension_loaded('imagick')) {
		Imagick::setResourceLimit(6, 1); // Set the thread limit to image magick
		
		$img = new Imagick($file);
		$img->thumbnailImage($width, $height);
		$img->writeImage($out_file);
		return $out_file;
	}
	if (extension_loaded('gd')) {
		if($ext == 'jpg' || $ext == 'jpeg')
			$src_img=imagecreatefromjpeg($file);
		else
			$src_img=imagecreatefrompng($file);

		$old_x=imageSX($src_img);
		$old_y=imageSY($src_img);
	
		if($height == 0) {
			$height = $old_y * $width / $old_x;
		}

		$dst_img=ImageCreateTrueColor($width,$height);
		imagecopyresampled($dst_img,$src_img,0,0,0,0,$width,$height,$old_x,$old_y);
		if($ext == 'jpg' || $ext == 'jpeg')
			imagejpeg($dst_img, $out_file);
		else
			imagepng($dst_img,$out_file);
		imagedestroy($dst_img);
		imagedestroy($src_img);
        return $out_file;
	}
	return null;
}

function render_image_thumbnail($orig, $width, $height = 0) {
	$file = inner_create_image_thumbnail($orig, $width, $height);
	if($file) {
		if(get_ci_config('enable_cache') && cache_support($file))
			return;
		$out = fopen('php://output', 'wb');
		$in = fopen($file, 'r');
		stream_copy_to_stream($in, $out);
		fclose($in);
		return;
	}
	trigger_error('No image generated!!!!');
}

function create_image_thumbnail($orig, $width, $height = 0) {
	$file = inner_create_image_thumbnail($orig, $width, $height);
	if($file)
		return file_get_contents($file);
	return null;
}
