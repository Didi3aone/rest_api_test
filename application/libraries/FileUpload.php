<?php
class FileUpload {
    
    private $folder;
    private $key;
    private $filename;
    
    function init ($params) {
        $this->folder = $params['folder'];
        $this->key = $params['key'];
        $this->filename = $params['file_name'];
        
        $this->create_folder($this->folder);
        $result = $this->upload($this->key,$this->folder);
        
        return $result;
    }
    
    function upload ($key,$folder) {
        if(isset($_FILES[$key])){
            $ret = array();

            $error = $_FILES[$key]["error"];
            //You need to handle  both cases
            //If Any browser does not support serializing of multiple files using FormData() 
            if(!is_array($_FILES[$key]["name"])) //single file
            {
                if (isset($this->filename) && $this->filename != "") {
                    //get extension of file
                    $path_parts = pathinfo($_FILES[$key]["name"]);
                    $ext = $path_parts['extension'];
                    //explode filename if filename is blbla.pdf
                    $exlodefilename = explode(".",$this->filename);
                    if (count ($exlodefilename) > 1) array_pop($exlodefilename);
                    $this ->filename = implode('.', $exlodefilename);
                    //set filename with extention
                    $fileName = $this->filename . "_" . strtotime(date("Y-m-d H:i:s")) . "." .$ext;
                } else {
                    $fname = explode(".",$_FILES[$key]['name']);
                    array_pop($fname);
                    $fname = implode(".",$fname);
                    $path_parts = pathinfo($_FILES[$key]["name"]);
                    $ext = $path_parts['extension'];
                    $fileName = $fname."_".strtotime(date("Y-m-d H:i:s")) . "." .$ext;
                }
                move_uploaded_file($_FILES[$key]["tmp_name"],$folder.$fileName);
                $ret[] = array(
                            'file' => "/".$folder.$fileName,
                            'filename' => $fileName,
                        );
            }
            else  //Multiple files, file[]
            {
              $fileCount = count($_FILES[$key]["name"]);
              for($i=0; $i < $fileCount; $i++)
              {
                if (isset($this->filename[$i]) && $this->filename[$i] != "") {
                    //get extension of file
                    $path_parts = pathinfo($_FILES[$key]["name"][$i]);
                    $ext = $path_parts['extension'];
                    //explode filename if filename is blbla.pdf
                    $exlodefilename = explode(".",$this->filename[$i]);
                    if (count ($exlodefilename) > 1) array_pop($exlodefilename);
                    $this ->filename[$i] = implode('.', $exlodefilename);
                    //set filename with extention
                    $fileName = $this->filename[$i] . "_" . strtotime(date("Y-m-d H:i:s")) . "." .$ext;
                } else {
                    $fname = explode(".",$_FILES[$key]['name'][$i]);
                    array_pop($fname);
                    $fname = implode(".",$fname);
                    $path_parts = pathinfo($_FILES[$key]["name"][$i]);
                    $ext = $path_parts['extension'];
                    $fileName = $fname."_".strtotime(date("Y-m-d H:i:s")) . "." .$ext;
                }
                
                move_uploaded_file($_FILES[$key]["tmp_name"][$i],$folder.$fileName);
                $ret[] = array(
                            'file' => "/".$folder.$fileName,
                            'filename' => $fileName,
                        );
              }
            
            }
            
            return $ret;
        }
          
    }
    
    function create_folder ($folder) {
        //create folder if not exists yet.
        if (!file_exists(FCPATH.$folder)) {
            mkdir(FCPATH.$folder, 0777, true);
            chmod(FCPATH.$folder , 0777);
        } 
    }
      
}