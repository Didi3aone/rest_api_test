<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Uploader {
    
    protected $CI;
    protected $key;
    protected $multiple;


    public function __construct() {
        //Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }

    
    /**
    $config = array(
        "allowed_types"     =>  "png|gif|jpg",
        "file_ext_tolower"  =>  true,
        "overwrite"         =>  false,
        "max_size"          =>  MAX_UPLOAD_IMAGE_SIZE_IN_KB,
        "file_target"       =>  array(
                "upload_path"       =>  "upload",
                "filename"          =>  "filename",
        )
    );
    */
    public function upload_files($key = null, $multiple = false, $config = null) {
        
        //load helper libs.
        $this->CI->load->library('upload');
        
        //return var.
        $return = "";
        $is_error = false;
        $result = array();
        
        //validates.
        if ($key === null) {
            $is_error = true;
            $result[] = array(
                "error_msg"     =>  "Key must be set.",
                "error_code"    =>  1,
            );
            //make the return.
            $return = ["is_error" => $is_error, "result" => $result];
            return $return;
        } else {
            $this->key = $key;
        }
        if ($multiple !== false) {
            $this->multiple = true;
        } else {
            $this->multiple = false;
        }
        
        //default upload file config.
        $upload_config['upload_path'] = "upload";
        $upload_config['file_ext_tolower'] = true;
        $upload_config['allowed_types'] = "*";
        $upload_config['overwrite'] = true;
        $upload_config['remove_spaces'] = true;
        $upload_config['detect_mime'] = true;
        $upload_config['mod_mime_fix'] = true;
            
        //check the config.
        if ($config != null) {
            //use the config to make the upload file config.
            if (isset($config["file_target"]["upload_path"])) $upload_config['upload_path'] = $config["file_target"]["upload_path"];
            if (isset($config["file_target"]["filename"])) $upload_config['file_name'] = $config["file_target"]["filename"];
            if (isset($config["allowed_types"])) $upload_config['allowed_types'] = $config["allowed_types"];
            if (isset($config["file_ext_tolower"])) $upload_config['file_ext_tolower'] = $config["file_ext_tolower"];
            if (isset($config["overwrite"])) $upload_config['overwrite'] = $config["overwrite"];
            if (isset($config["max_size"]) && $config["max_size"] > 0) $upload_config['max_size'] = $config["max_size"];
        }
        
        //processing multiple upload or single.
        if ($this->multiple) {
            
            //multiple files (batch mode).
            if (isset($_FILES[$this->key]['size']) && !empty($_FILES[$this->key]['size'])) {
                //file exists.

                //looping for every files.
                for ($i = 0; $i < count($_FILES[$this->key]['size']); $i++) {
                    
                    //initialize uploader.
                    $this->CI->upload->initialize($upload_config, true);
                    
                    //assign upload value to dummyfile temp.
                    $_FILES['dummyfile']['name'] = $_FILES[$this->key]['name'][$i];
                    $_FILES['dummyfile']['type'] = $_FILES[$this->key]['type'][$i];
                    $_FILES['dummyfile']['tmp_name'] = $_FILES[$this->key]['tmp_name'][$i];
                    $_FILES['dummyfile']['error'] = $_FILES[$this->key]['error'][$i];
                    $_FILES['dummyfile']['size'] = $_FILES[$this->key]['size'][$i];
                    
                    //try to upload the dummyfile.
                    if (!$this->CI->upload->do_upload('dummyfile')) {
                        //if error.
                        //save the error msg.
                        $is_error = true;
                        $result[] = array(
                            "error_msg"     =>  $this->CI->upload->display_errors('',''),
                            "error_code"    =>  1,
                        );
                        
                    } else {
                        
                        //success uploaded.
                        //get the result uploaded info.
                        $uploaded_info = $this->CI->upload->data();
                        
                        //file mode, just return the result.
                        $result[] = array(
                            "filename"      =>  $uploaded_info['file_name'],
                            "full_path"     =>  $uploaded_info['full_path'],
                            "file_path"     =>  $uploaded_info['file_path'],
                            "uploaded_path" =>  "/".$upload_config['upload_path']."/".$uploaded_info['file_name'],
                            "raw_name"      =>  $uploaded_info['raw_name'],
                            "ext"           =>  $uploaded_info['file_ext'],
                            "file_type"     =>  $uploaded_info['file_type'],
                        );
                        
                    }//end of success file upload.
                }//end of looping for every file.
            } else { 
                //file not exists?
                $is_error = true;
                $result[] = array(
                    "error_msg"     =>  "File not exists.",
                    "error_code"    =>  0,
                );
            }
            //end of multiple file upload.
        } else {
            //single file mode.
            //
            //check if file exists within the key.
            if (isset($_FILES[$this->key]['size']) && $_FILES[$this->key]['size'] > 0) {
                //file exists.
                
                //initialize uploader.
                $this->CI->upload->initialize($upload_config, true);
                
                //try to upload.
                if (!$this->CI->upload->do_upload($this->key)) {
                    //if error.
                    //save the error msg.
                    $is_error = true;
                    $result[] = array(
                        "error_msg"     =>  $this->CI->upload->display_errors('',''),
                        "error_code"    =>  1,
                    );
                    
                } else {
                    
                    //success uploaded.
                    //get the result uploaded info.
                    $uploaded_info = $this->CI->upload->data();
                    
                    //file mode, just return the result.
                    $result[] = array(
                        "filename"      =>  $uploaded_info['file_name'],
                        "full_path"     =>  $uploaded_info['full_path'],
                        "file_path"     =>  $uploaded_info['file_path'],
                        "uploaded_path" =>  "/".$upload_config['upload_path']."/".$uploaded_info['file_name'],
                        "raw_name"      =>  $uploaded_info['raw_name'],
                        "ext"           =>  $uploaded_info['file_ext'],
                        "file_type"     =>  $uploaded_info['file_type'],
                    );
                    
                }//end of success file upload.
            //end of file is exists.
            } else { 
                //file not exists?
                $is_error = true;
                $result[] = array(
                    "error_msg"     =>  "File not exists.",
                    "error_code"    =>  0,
                );
            }
        }//end of single upload.
        
        //make the return.
        $return = ["is_error" => $is_error, "result" => $result];
        return $return;
    }

    
    /**
    $config = array(
        "image_targets"     =>  array(
                "preset1"   =>  array(
                    "target_path"   =>  "upload/report/preset1",
                    "filename"      =>  "dummy_filename",
                    "target_width"  =>  90,
                    "target_height" =>  90,
                    "crop_data"     =>  $data_image,
                    "bg_color"      =>  array(
                            "red"   =>  0,
                            "green" =>  0,
                            "blue"  =>  0,
                            "alpha" =>  127,
                    ),
                ),
                "preset2"   =>  array(
                    "target_path"   =>  "upload/report/preset2",
                    "filename"      =>  "dummy_filename",
                    "target_width"  =>  45,
                    "target_height" =>  45,
                    "crop_data"     =>  $data_image,
                    "bg_color"      =>  array(
                            "red"   =>  255,
                            "green" =>  255,
                            "blue"  =>  255,
                            "alpha" =>  0,
                    ),
                ),
        )
    );
    */
    public function crop_images($source_path = null, $delete_original = false, $config = null) {
        
        //vars.
        $image_source = "";
        $image_type = null;
        $image_extension = null;
        $filename = "";
        $fullpath = "";
        $filepath = "";
        
        //return var.
        $return = "";
        $is_error = false;
        $result = array();
        
        //validates.
        if ($source_path === null) {
            $is_error = true;
            $result[] = array(
                "error_msg"     =>  "Image file source must be set.",
                "error_code"    =>  1,
            );
            //make the return.
            $return = ["is_error" => $is_error, "result" => $result];
            return $return;
            
        } else if (file_exists(FCPATH.$source_path)) {
            $image_source = FCPATH.$source_path;
        } else if (file_exists($source_path)) {
            $image_source = $source_path;
        } else {
            //file not exists at path.
            $is_error = true;
            $result[] = array(
                "error_msg"     =>  "Image file source is not exists.",
                "error_code"    =>  1,
            );
            //make the return.
            $return = ["is_error" => $is_error, "result" => $result];
            return $return;
        }
        
        //check if file is image.
        if (!getimagesize($image_source)) {
            //file is not an image.
            $is_error = true;
            $result[] = array(
                "error_msg"     =>  "The file in the path is not an image.",
                "error_code"    =>  1,
            );
            //make the return.
            $return = ["is_error" => $is_error, "result" => $result];
            return $return;
        }
        
        //delete original or not.
        if ($delete_original !== false) {
            $delete_original = true;
        }
        
        //
        //begin processing.
        //
        //getting config.
        if ($config === null || !isset($config['image_targets']) || count($config['image_targets']) == 0) {
            
            //config and image preset must present.
            $is_error = true;
            $result[] = array(
                "error_msg"     =>  "Config and image preset must be set.",
                "error_code"    =>  1,
            );
            //make the return.
            $return = ["is_error" => $is_error, "result" => $result];
            return $return;
        }
        
        //getting image type and extension.
        $image_type = exif_imagetype($image_source);
        
        if ($image_type) {
            $image_extension = image_type_to_extension($image_type);
        }
        
        //looping for every image preset.
        foreach ($config['image_targets'] as $preset => $settings) {
            
            //check and regenerated missing settings.            
            //target path.
            if (!isset($settings['target_path'])) {
                $filepath = "upload";
            } else {
                $filepath = $settings['target_path'];
            }
            $this->validate_upload_path($filepath);
            $fullpath = realpath($filepath);
            
            //filename.
            if (!isset($settings['filename'])) {
                //will use original filename + current timestamp.
                $info = pathinfo($image_source); //getting dirname, basename, extension and filename.
                $filename = $info['filename']."_".strtotime("now");
            } else {
                $filename = $settings['filename'];
            }
            
            //target width and height.
            if (!isset($settings['target_width']))$settings['target_width'] = 100;
            if (!isset($settings['target_height']))$settings['target_height'] = 100;
            
            //crop data.
            if (!isset($settings['crop_data'])) {
                $settings['crop_data'] = json_encode(array(
                    'x' => 0,
                    'y' => 0,
                    'width' => 0,
                    'height' => 0,
                    'rotate' => 0,
                ));
            }
            
            //bg color.
            if (!isset($settings['bg_color'])) {
                $settings['bg_color'] = array(
                    "red"   =>  0,
                    "green" =>  0,
                    "blue"  =>  0,
                    "alpha" =>  127,
                );
            }
            
            //create image resource from the path.
            switch ($image_type) {
                case IMAGETYPE_GIF:
                    $image_resource = imagecreatefromgif($image_source);
                    break;

                case IMAGETYPE_JPEG:
                    $image_resource = imagecreatefromjpeg($image_source);
                    break;

                case IMAGETYPE_PNG:
                    $image_resource = imagecreatefrompng($image_source);
                    break;
            }
            
            //check image conversion.
            if (!$image_resource) {
                //file is not an image (cannot generate resource).
                $is_error = true;
                $result[] = array(
                    "error_msg"     =>  "Failed to read the image file.",
                    "error_code"    =>  1,
                );
                
            } else {
                //image can be regenerated as resource.
                
                //get and decode crop data.
                $data = $this->decodeData($settings['crop_data']);
                
                $size = getimagesize($image_source);
                $size_w = $size[0]; // natural width.
                $size_h = $size[1]; // natural height.

                $src_img_w = $size_w;
                $src_img_h = $size_h;

                $degrees = $data -> rotate;
                
                // Rotate the source image first (if the degress != 0).
                if (is_numeric($degrees) && $degrees != 0) {
                    // PHP's degrees is opposite to CSS's degrees.
                    $new_img = imagerotate($image_resource, -$degrees, imagecolorallocatealpha($image_resource, 0, 0, 0, 127) );

                    imagedestroy($image_resource);
                    $image_resource = $new_img;

                    $deg = abs($degrees) % 180;
                    $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

                    $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
                    $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

                    // Fix rotated image miss 1px issue when degrees < 0
                    $src_img_w -= 1;
                    $src_img_h -= 1;
                }
                
                $tmp_img_w = $data -> width;
                $tmp_img_h = $data -> height;
                $dst_img_w = $settings['target_width'];
                $dst_img_h = $settings['target_height'];

                $src_x = $data -> x;
                $src_y = $data -> y;
                
                //calculating something i don't know.
                if ($data -> width != 0 && $data -> height != 0) {
                    if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                        $src_x = $src_w = $dst_x = $dst_w = 0;
                    } else if ($src_x <= 0) {
                        $dst_x = -$src_x;
                        $src_x = 0;
                        $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
                    } else if ($src_x <= $src_img_w) {
                        $dst_x = 0;
                        $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
                    }
                    
                    //and again...
                    if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                        $src_y = $src_h = $dst_y = $dst_h = 0;
                    } else if ($src_y <= 0) {
                        $dst_y = -$src_y;
                        $src_y = 0;
                        $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
                    } else if ($src_y <= $src_img_h) {
                        $dst_y = 0;
                        $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
                    }
                    
                    // Scale to destination position and size.
                    $ratio = $tmp_img_w / $dst_img_w;
                    $dst_x /= $ratio;
                    $dst_y /= $ratio;
                    $dst_w /= $ratio;
                    $dst_h /= $ratio;
                    
                } else {
                    
                    # taller
                    if ($src_img_h > $dst_img_h) {
                        $dst_w = ($dst_img_h / $src_img_h) * $src_img_w;
                        $dst_h = $dst_img_h;
                    }

                    # wider
                    if ($src_img_w > $dst_img_w) {
                        $dst_w = ($dst_img_w / $src_img_w) * $src_img_h;
                        $dst_h = $dst_img_w;
                    }
                    
                    #if same size with original
                    if ($src_img_h == $dst_img_h && $src_img_w == $dst_img_w) {
                        $dst_w = $dst_img_w;
                        $dst_h = $dst_img_h;
                    }
                    
                    $dst_x = $dst_y = $src_x = $src_y = 0;
                    
                    $src_w = $size_w;
                    $src_h = $size_h;
                }
                
                $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);
                
                //Add background color to destination image.
                $background = $settings['bg_color'];
                imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, $background['red'], $background['green'], $background['blue'], $background['alpha']));
                imagesavealpha($dst_img, true);

                //finalizing image and saving to target destination.
                $resampled = imagecopyresampled($dst_img, $image_resource, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

                //saving...
                if ($resampled) {
                    if (!imagepng($dst_img, $filepath."/".$filename.".png", 9)) {
                        //failed saving.
                        $is_error = true;
                        $result[] = array(
                            "error_msg"     =>  "Failed to save the cropped image file.",
                            "error_code"    =>  1,
                        );
                    } else {
                        //success saving.
                        $result[] = array(
                            "filename"      =>  $filename.".png",
                            "full_path"     =>  $fullpath,
                            "file_path"     =>  $filepath,
                            "uploaded_path" =>  $filepath."/".$filename.".png",
                            "raw_name"      =>  $filename,
                            "ext"           =>  "png",
                        );
                    }
                } else {
                    //failed to crop.
                    $is_error = true;
                    $result[] = array(
                        "error_msg"     =>  "Failed to crop the image file.",
                        "error_code"    =>  1,
                    );
                }

                //destroy the memory cache.
                imagedestroy($image_resource);
                imagedestroy($dst_img);
            }

        }//end foreach preset.
        
        //should we delete original source file.
        if ($delete_original) {
            if (!unlink($image_source)) {
                //failed to delete original file.
                $is_error = true;
                $result[] = array(
                    "error_msg"     =>  "Failed to delete original file.",
                    "error_code"    =>  1,
                );
            }
        }
        
        //make the return.
        $return = ["is_error" => $is_error, "result" => $result];
        return $return;
    }
    
    
    /**
     * function to decode "crop data" sent from the javascript as json.
     * format is usually {x:0, y:0, width:100, heigh:100, rotate:0}
     */
    private function decodeData($data) {
        if (!empty($data)) {
            return json_decode(stripslashes($data));
        }
    }
    
    
    /**
     * function to automaticaly create folders within the path.
     * the folders will be created using correct access rules.
     */
    private function validate_upload_path($upload_path) {

        if ($upload_path === '')
        {
            return 'upload_no_filepath';
        }
        if (realpath($upload_path) !== FALSE)
        {
            $upload_path = str_replace('\\', '/', realpath($upload_path));
        }
        if ( ! is_dir($upload_path))
        {
            // EDIT: make directory and try again
            if ( ! mkdir ($upload_path, 0777, TRUE))
            {
                return 'upload_no_filepath';
            }
        }
        if ( ! is_really_writable($upload_path))
        {
            // EDIT: change directory mode
            if ( ! chmod($upload_path, 0777))
            {
                return 'upload_not_writable';
            }
        }
        $upload_path = preg_replace('/(.+?)\/*$/', '\\1/',  $upload_path);
        return true;
    }
    
    
}
