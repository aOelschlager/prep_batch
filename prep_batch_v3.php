/*
    Script to prep batches for ingest.
    v1 - 4/8/2020
    v2 - 4/10/2020
    v3 - 4/17/2020
*/

<?php

    // gets file path from user, trims new line, and tests
    // if directory exist
    print "type a file path:  ";
    
    $dir = fgets(STDIN);
    $dir = trim($dir);
    
    if (!is_dir($dir)) {
        print "The directory $dir does not exist.\n";
        print "Exiting program.\n";
        
    } else {
        
        // makes array to hold the error information from function
        // that gets written to a file. Creates variables for file
        // and directory names.
        $print_array = array();
        $end_array = array();
        $end = " ";
        $flag = false;

        $dir_parts = explode("/", $dir);
        $directory_name = end($dir_parts);

        $write_file_name = $dir . DIRECTORY_SEPARATOR . $directory_name . "_batch_report.txt";
        $other_files = $directory_name . "_not_batch_files";
        $other_files_folder = $dir . DIRECTORY_SEPARATOR . $other_files;
        
        if (!mkdir($other_files_folder, 0777)) {
            array_push($print_array, $directoryname, "Could not create folder" );
        }
        
        print "\n1.) Would you like to prep a directory of directories?\n";
        print "2.) Would you like to prep a range of directories?\n";
        print "3.) Would you like to prep a single directory?\n\n";
        print "Select 1, 2, or 3:  ";
        $answer = fgets(STDIN);
        $answer = trim($answer);
        $answer = strtolower($answer);
        
        if ($answer === "1") {
            $end_array = dirToArray($print_array, $dir, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
            
        } elseif ($answer === "2") {
            print "\nWhat is the beginning directory:  ";
            $beg = fgets(STDIN);
            $beg = trim($beg);
            print "\nWhat is the ending directory:  ";
            $end = fgets(STDIN);
            $end = trim($end);

            $end = $dir . DIRECTORY_SEPARATOR . $end;
            $beg = $dir . DIRECTORY_SEPARATOR . $beg;
            
            if (is_dir($beg) & is_dir($end)) {
                $end_array = dirToArray($print_array, $dir, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                
            } else {
                exit('One or both directories do not exist. Exiting program.');
            }
            
        } elseif ($answer === "3") {
            print "\nWhat is the directory:  ";
            $d = fgets(STDIN);
            $d = trim($d);
            $dir = $dir . DIRECTORY_SEPARATOR . $d;
            
            if (is_dir($dir)) {
                $end_array = dirToArray($print_array, $dir, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                
            } else {
                exit('Directory does not exist. Exiting program.');
            }
            
        } else {
            exit('That was not a selection option. Exiting program.');
        }
        
        $fp = fopen($write_file_name, 'w');
        fwrite($fp, print_r($end_array, TRUE));
        fclose($fp);
    }

    /* Loops through directories and preps files. */
    function dirToArray(&$print_array, $dir, $other_files_folder, $directory_name, $beg, $end, $answer, $flag) {
        $cdir = scandir($dir);
        $counter = 0;
        $next_dir = " ";
        
        // for each value check if it is a directory, tif file, xmll file,
        // or anything other formate. If it is a directory call this fuction
        // again. If it is a tif, xml, or other file formate prep the files.
        foreach ($cdir as $key => $value) {
            
            if (!in_array($value,array(".",".."))) {
                
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    
                    if ($answer === "2") {
                        $check_range_dir = $dir . DIRECTORY_SEPARATOR . $value;
                        
                        if (strcmp($beg, $check_range_dir) === 0) {
                            print "Working on: " . $dir . DIRECTORY_SEPARATOR . $value . "\n";
                            $flag = true;
                            $counter = 0;
                            dirToArray($print_array ,$dir . DIRECTORY_SEPARATOR . $value, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                            
                        } elseif (strcmp($end, $check_range_dir) === 0) {
                            print "Working on: " . $dir . DIRECTORY_SEPARATOR . $value . "\n";
                            $next_dir = array_key_exists($key + 1, $cdir) ? $cdir[$key +1] : false;

                            if ($next_dir) {
                                $next_dir = $dir . DIRECTORY_SEPARATOR . $next_dir;
                                
                            } else {
                                $next_dir = " ";
                            }
                            $counter = 0;
                            dirToArray($print_array, $dir . DIRECTORY_SEPARATOR . $value, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                            
                        } elseif (strcmp($next_dir, $check_range_dir) === 0) {
                            $flag = false;
                            $counter = 0;
                            dirToArray($print_array, $dir . DIRECTORY_SEPARATOR . $value, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                            
                        } elseif ($flag === true) {
                            print "Working on: " . $dir . DIRECTORY_SEPARATOR . $value . "\n";
                            $counter = 0;
                            dirToArray($print_array, $dir . DIRECTORY_SEPARATOR . $value, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                            
                        } else {
                            continue;
                        }
                        
                    } else {
                        $dir_parts = explode("/", $dir . DIRECTORY_SEPARATOR . $value);
                        $sub_dir = end($dir_parts);
                        $sub_dir = $other_files_folder . DIRECTORY_SEPARATOR . $sub_dir;

                        if (strcmp($dir . DIRECTORY_SEPARATOR . $value, $other_files_folder) === 0 | strcmp($dir . DIRECTORY_SEPARATOR . $value, $sub_dir) === 0) {
                            continue;
                            
                        } else {
                            print "Working on: " . $dir . DIRECTORY_SEPARATOR . $value . "\n";
                            $counter = 0;
                            dirToArray($print_array ,$dir . DIRECTORY_SEPARATOR . $value, $other_files_folder, $directory_name, $beg, $end, $answer, $flag);
                        }
                    }
                    
                } else {
                    $val_exten =  substr(strrchr($value, '.'), 1);
                    $val_exten = strtolower($val_exten);
                    
                    if ($val_exten === "tif" | $val_exten === "tiff") {
                        $file_path = $dir . DIRECTORY_SEPARATOR . $value;
                        
                        if (!$exif = exif_read_data($file_path, "FILE,COMPUTED,ANY_TAG,IFD0,THUMBNAIL,COMMENT,EXIF", true)) {
                            array_push($print_array, $file_path, "File is corrupted" );
                        }
                        $counter += 1;
                        $directoryname = $dir . DIRECTORY_SEPARATOR . $counter;

                        /*
                        if (file_exists($directoryname)) {
                            print "The file $directoryname exists\n";
                        } else {
                            print "The file $directoryname does not exist\n";
                        }
                        */
                        
                        if (!mkdir($directoryname, 0777)) {
                            array_push($print_array, $directoryname, "Could not create folder" );
                        } else {
                            $file_path = $dir . DIRECTORY_SEPARATOR . $value;
                            $file_name_change = $dir . DIRECTORY_SEPARATOR . $counter . DIRECTORY_SEPARATOR . "OBJ.tif";

                            if (!rename($file_path,$file_name_change)) {
                                array_push($print_array, $file_path, "Name change failed" );
                                array_push($print_array, $file_path, "Could not move file" );
                            }
                        }
                        
                    } elseif ($val_exten === "xml") {
                        
                        $file_path = $dir . DIRECTORY_SEPARATOR . $value;
                        $file_name_change = $dir . DIRECTORY_SEPARATOR . "MODS.xml";
                        
                        if (file_exists($file_name_change)) {
                            print "The file $file_name_change exists\n";
                            array_push($print_array, $file_name_change, "The file already exists" );
                            
                        } else {
                            
                            if (!rename($file_path,$file_name_change)) {
                                array_push($print_array, $file_path, "Name change failed" );
                            }
                        }
                        
                    } else {
                        $file_path = $dir . DIRECTORY_SEPARATOR . $value;
                        $dir_parts = explode("/", $file_path);
                        end($dir_parts);
                        $dir_name = prev($dir_parts);
                        $temp_dir = prev($dir_parts);

                        if (strcmp($temp_dir, $directory_name) !==0) {
                            $directoryname = $other_files_folder . DIRECTORY_SEPARATOR . $dir_name;

                            if (!is_dir($directoryname)) {

                                if (!mkdir($directoryname, 0777)) {
                                    array_push($print_array, $directoryname, "Could not create folder" );
                                    
                                } else {
                                    $file_name_change = $directoryname . DIRECTORY_SEPARATOR . $value;

                                    if (!rename($file_path,$file_name_change)) {
                                            array_push($print_array, $file_path, "Could not move file" );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $print_array;
    }
?>
