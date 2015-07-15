<?php
include_once("config.php");

// fetch channel id from databse
   // define("min","1");
   // define("max","200");
	$result = mysqli_query($con,'SELECT cid FROM youtubeChannelsList WHERE sno >=min AND sno <=max');
	$result_array = array(array());
		$count=0;
		
	
	 while($row = mysqli_fetch_assoc($result))  // stored channel id in an array(result_array);
	{
    $result_array[$count][0] = $row['cid'];
     $result_array[$count][1] = '';
       $count++;
	} 
	unset($result);
	mysqli_close($con);
	// declare variables 
	
	$v=1;
	$ch=array();
		
	// create multiple parallel connection using curl_multi_* function
	
do {
	$num=count($result_array);
	$count=0;
	$p=0;
	echo "num=".$num."<br>";
	                 	$mh = curl_multi_init(); //initialize curl and made 100 different channel
						for($j=0;$j<100;$j++)				
						{
							if($num > 0)
							{
							$ch[$j] = curl_init();								//initialze curl
							curl_setopt($ch[$j], CURLOPT_URL, "https://www.googleapis.com/youtube/v3/subscriptions?part=snippet,id,contentDetails&pageToken=".$result_array[$count][1]."&fields=nextPageToken,items(id,snippet(publishedAt,resourceId(channelId),channelId))&channelId=".$result_array[$count][0]."&maxResults=50&key=AIzaSyAi5EGrpxTLFuADJ2jqut5ftAsfKG-VPVI");
							curl_setopt($ch[$j], CURLOPT_RETURNTRANSFER,1);			//	CURLOPT_RETURNTRANSFER return the content of channel	
							curl_setopt($ch[$j], CURLOPT_HEADER, false);
							
							curl_multi_add_handle($mh, $ch[$j]);					//add all channel in multichannel
								$count++;
								$num--;
								$p++;
							}												
						}
						
					// execute multi curl
							$running=0;													
							do {
								curl_multi_exec($mh, $running);
							 } while($running > 0);
	
										
							$data=array();
							       for($j=0;$j<=$p;$j++)                //get the content of all channel and stored in a data() array
								   {
									   $k=0;
										$data[$j]=curl_multi_getcontent($ch[$j]);
										$obj = json_decode($data[$j]);				//convert the json text into object and stored in an object
										if(isset($obj->items))
										{			
									       $result= count($obj->items);                  //count total number of element (i.e. item)in json
										   $file = fopen("test7.txt","a+");			//create and open file in append mod
												while($result>0)
												{												// check whether any element exist in item or not
														if(isset($obj->items[$k]))		//convert from object to text form
														{
															fwrite($file, trim(json_encode($obj->items[$k]))."\n");  //write the value of item in file
																 $k++;
														}	
													$result--;	
												}
												fclose($file);								//close file
																									
											  if(isset($obj->nextPageToken))     //check whether any nextPageToken or not if yes then store in a array i.e. nextToken
											  {										 
												  $result_array[$j][1]= $obj->nextPageToken;
													echo  $result_array[$j][1]."<br>";
													//echo $result_array[$j]."<br>";		 
												} 
												else
												{
													unset($result_array[$j][0]);
													unset($result_array[$j][1]);
													
												}		//end if condition	
														
										 }
										curl_multi_remove_handle($mh, $ch[$j]);     //remove all chanel handle
								 } //End of while loop
											
      curl_multi_close($mh);
	
		 $result_array= array_values($result_array);
			$v= count($result_array);
			echo "after unset:".$v."<br>";
} while(0 < count($result_array));

?>



