<?php

namespace App\Services;

require_once '../../vendor/autoload.php';

// import the Intervention Image Manager Class
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Define a custom exception class
 */
class Uploader
{
	public $doc;
    public $root;
	public $full_path;
	public $sub_folder;
	
	//This is the folder from the public folder where 
	public $docParentFolder = "/uploads";
	public $width = 550;
	public $height = 505;
	public $valid_mimes = ["image/jpeg", "image/png", "image/gif"];
	public $max_file_upload_size = 5242880;//5MB
	public $percent = 0.5;
	public $max_no_of_file_to_upload = 5;
	public $name_of_file;
	
	//These are configurations for outside the web root where errors will be logged
	public $parentFolder = "modal";
	public $log_file_name = "admin_logs";
	public $log_file_ext = "txt";
	public $use_root = true;
	public $log_full_path;
	
	// construction function
	//It is assumed we're uploading products for display or sale but can be changed to anything else. This (folder) is actually a sub folder of $docParentFolder located in the public html folder.
	public function __construct($sub_folder="", int|array $user_defined_valid_mimes=[], $override_mimes = false)
	{
		if(!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT']);
		$this->doc = doc."/";
		
		if (!defined("root")) define("root",__DIR__);
		$this->root = root."/../../";
		
		if(!empty($sub_folder))
		{
			$this->sub_folder = $sub_folder;
		
		    //This shall be strictly for storing images and photos.
		    $this->full_path = rootDir().$this->docParentFolder."/".$sub_folder."/";
		}
		else
		{
			$this->full_path = rootDir().$this->docParentFolder."/";
		}
		
		//By default administrative logging should be done outside the webroot but if User chooses otherwise then it'll be done there.
		if($this->use_root == false)
		{
			$this->log_full_path = rootDir().$this->parentFolder."/".$this->log_file_name.".".$this->log_file_ext."";//for logging
		}
		else
		{
			$this->log_full_path = $this->root.$this->parentFolder."/".$this->log_file_name.".".$this->log_file_ext."";//for logging
		}
		
		//We've a list of predefined allowed meme types, User can choose to either add to it or override and create custom
		if($override_mimes == true)
		{
			$this->valid_mimes = $user_defined_valid_mimes;
		}
		else
		{
			if(!empty($user_defined_valid_mimes))
			{
				for($i=0;$i<count($user_defined_valid_mimes);$i++)
				{
					$this->valid_mimes []= $user_defined_valid_mimes[$i];
				}
			}
		}
	}
	
	
    function unique_name($lenght = 13) 
	{
		$prefix = "mekus_".date("Ymd");
		// uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($lenght / 2));
        }   
		elseif(function_exists("openssl_random_pseudo_bytes")) 
		{
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } 
		else 
		{
			throw new \Exception("no cryptographically secure random function available");
        }
        return substr($prefix.bin2hex($bytes), 0, $lenght);
    }
	
	public function file_size_convert($bytes)
	{
		$bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

		$result= "";

        foreach($arBytes as $arItem)
        {
			if($bytes >= $arItem["VALUE"])
            {
				$result = $bytes / $arItem["VALUE"];
			    $result = strval(round($result, 2))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

	
	
    //Returns an array/string with the renamed file as value.
    public function upload($file_upload_name="stock", $ignore=false)
    {
		if(!isset($_FILES[$file_upload_name]) or empty($_FILES[$file_upload_name]["name"]))
		{
			if($ignore == true)
			{
				return;
			}
			else
			{
				response("Please upload an image or file.", 422);
			    exit;
			}
		}
		
	    $file = $_FILES[$file_upload_name];
		//dd($file);
		//We expect a maximum number of $max_no_of_file_to_upload images to upload, if it's more than then we issue an error warning.
        if(is_array($file["name"]) and (count($file["name"]) > $this->max_no_of_file_to_upload))
        {
			response("Exceeded maximum number of products to upload. You can only upload ".$this->max_no_of_file_to_upload." maximum number of products.", 422);
        }   
		
		
	    //We check the mime type of the image.
		$info = new \finfo(FILEINFO_MIME_TYPE);
		
		if(is_array($file["name"]))
		{
			//This will house the new name of the images and will be sent back to the Controller.
	        $photos = [];
		
		    //Users are allowed to upload a certain number of files so that the $_FILES array is filled accordingly. But User can skip the first box for where to place the file in the html form and go for the second, third, etc. In this case the all the arrays will be set and with the same exact count but array["Name"][0] will be empty alongside other than keys pertaining the 0-th file and this will cause a bug in the script so we wanna carter for it first.
		    for($i=0;$i<count($file["name"]);$i++)
            {
				if(!empty($file["name"][$i]))
				{
					if(!isset($file["tmp_name"][$i]))
		            {
						continue;
		            }
					
					switch ($file['error'][$i]) 
					{
						case UPLOAD_ERR_OK:
						    break;
                        case UPLOAD_ERR_NO_FILE:
						    throw new \RuntimeException("No file sent.");
						case UPLOAD_ERR_INI_SIZE:
						case UPLOAD_ERR_FORM_SIZE:
						    throw new \RuntimeException("{$file["name"][$i]} exceeded filesize limit.");
                        default:
                            throw new \RuntimeException("Unknown error");
                    }

	
	                $file_type = $info->file($file["tmp_name"][$i]);
			
			        //if file type is not any of these specified types
			        if(!in_array($file_type, $this->valid_mimes))
					{
						response("Invalid image format detected for ".$file["name"][$i].".", 422);
			        }
		    
		            if(filesize($file["tmp_name"][$i]) > $this->max_file_upload_size)//if file is larger than a specified size.
					{
						//Incase there any of the batch photos have been uploaded already we delete 'em.
						if(!empty($photos))
						{
							$this->file_delete($photos);
						}
						
						response($file["name"][$i]." is too large. Please make sure any uploaded image is less than or equal to {$this->file_size_convert($this->max_file_upload_size)}. Current size is {$this->file_size_convert($file["size"][$i])}", 422);
	                }
	
	                $tmp_name = $file["tmp_name"][$i];//This is the default dir on server where files are stored.
	                $stock = $file["name"][$i];// takes name of file 'AS IS' from user's computer in this variable.
	                $separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				    if(empty($this->name_of_file))
					{
						$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                        $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				    }
				    else
					{
						//The name is mainly set when it involves upload of document for account verification. The process below helps to mitigate overriding.
					    $index = $i+1;
					    $separate[0] = $this->name_of_file.$index;
				    }
				
	                $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.
                   
                    $img = Image::make($tmp_name)->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
												$constraint->upsize();
                              })->encode($separate[1]);
                    
				    // save file as jpg with medium quality
				    $img->save($this->full_path.$new_name, 75);
				
                    $photos []= $new_name;
		        }
            }
		    return $photos;
	    }	
		
		//This should accomodate for single Upload(s) that don't need to be joined together or kept in an array
		else
		{
			if(!empty($file["name"]))
			{
				switch ($file['error']) 
				{
					case UPLOAD_ERR_OK:
						break;
                    case UPLOAD_ERR_NO_FILE:
						throw new \RuntimeException("No file sent.");
				    case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new \RuntimeException("{$file["name"]} exceeded filesize limit.");
                    default:
                        throw new \RuntimeException("Unknown errors");
                }
					
	            $file_type = $info->file($file["tmp_name"]);
			
			    //if file type is not any of these specified types
			    if(!in_array($file_type, $this->valid_mimes))
				{
					response("invalid image format detected for ".$file["name"].".", 422);
			    }
		    
		        if(filesize($file["tmp_name"]) > $this->max_file_upload_size)//if file is larger than a specified size.
		        {
					response($file["name"]." is too large. Please make sure it's less than or equal to {$this->file_size_convert($this->max_file_upload_size)}", 422);
	            }
	
	            $tmp_name = $file["tmp_name"];//This is the default dir on server where files are stored.
	            $stock = $file["name"];// takes name of file 'AS IS' from user's computer in this variable.
	            $separate = explode(".", $stock);//separates file name('image') from base-name(e.g '.jpg').
				
				if(empty($this->name_of_file))
				{
					$uniq_name = $this->unique_name(35);//a unique name for files to be stored on server to avoid file overwriting.
                    $separate[0] = $uniq_name;// rename file from user's computer to be stored on server using the generated unique id.
				}
				else
				{
					$separate[0] = $this->name_of_file;
				}
				
	            $new_name = $separate[0].".".$separate[1];//joins new file name now with base-name/extension of the image.
                
				$img = Image::make($tmp_name)->resize($this->width, $this->height, function ($constraint) {
                                                $constraint->aspectRatio();
												$constraint->upsize();
                              })->encode($separate[1]);
                    
				// save file as jpg with medium quality
				$img->save($this->full_path.$new_name, 75);
				
				return $new_name;
		    }
		}	
    }
	
    function file_delete($file)
	{
		//var_dump($file);exit;
		if(is_array($file))
		{
			for($i=0;$i<count($file);$i++)
			{
				if(!@unlink($this->full_path.$file[$i]))//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
				{
					//We log it. We'll manually delete it ourselves.
			        $content = "Failed to delete ".$this->full_path."/{$file[$i]} ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			        $this->writeToFile($content);
	            }
			}
		}
		else
		{
			if(!@unlink($this->full_path."$file"))//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
	        {
				//We log it. We'll manually delete it ourselves.
			    $content = "Failed to delete ".$this->full_path."/$file, ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			    $this->writeToFile($content);
	        }
		}
		return;
    }
	
	
	function writeToFile($content)
	{
		$fp = fopen($this->log_full_path, 'a');
        
		fwrite($fp, $content);
        fclose($fp);
	    return;
	}
		
}
?>