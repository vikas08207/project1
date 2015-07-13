<?php
include_once("config.php");
// fetch channel id from databse
	$result = mysqli_query($con,'SELECT cid FROM youtubeChannelsList WHERE sno >=10001 AND sno <=11000');
	$result_array = array();
		$i=0;
	
	 while($row = mysqli_fetch_assoc($result))  // stored channel id in an array(result_array);
	{
    $result_array[$i] = $row['cid'];
       $i++;
	} 
	
	// declare variables 
	$y=10001;
	$z=0;
	$ch=array();
	$nextToken=array();
	$index=array();
	
	// create multiple parallel connection using curl_multi_* function
	
while($y <= 11000)										
{
	$z = $y;
	                                            //initialize curl and made 50 different channel
				$mh = curl_multi_init();
						for($j=1;$j<100;$j++)				
						{
							$ch[$j] = curl_init();								//initialze curl
							curl_setopt($ch[$j], CURLOPT_URL, "https://www.googleapis.com/youtube/v3/subscriptions?part=snippet,id,contentDetails&fields=nextPageToken,items(id,snippet(publishedAt,resourceId(channelId),channelId))&channelId=".$result_array[$z]."&maxResults=50&key=AIzaSyAi5EGrpxTLFuADJ2jqut5ftAsfKG-VPVI");
							curl_setopt($ch[$j], CURLOPT_RETURNTRANSFER,1);			//	CURLOPT_RETURNTRANSFER return the content of channel	
							curl_setopt($ch[$j], CURLOPT_HEADER, false);
							
							curl_multi_add_handle($mh, $ch[$j]);					//add all channel in multichannel
							$z++;
													
						}
						
					// execute multi curl
							$running=0;													
							do {
								curl_multi_exec($mh, $running);
							 } while($running > 0);
	
							$x=0;
							$k=1;
							$data=array();
							       for($j=1;$j<100;$j++)                //get the content of all channel and stored in a data() array
								   {
										$data[$j]=curl_multi_getcontent($ch[$j]);
										$obj = json_decode($data[$j]);				//convert the json text into object and stored in an object
											if(isset($obj))
											{			
									   $result= count($obj->items);                  //count total number of element (i.e. item)in json
										$file = fopen("test5.txt","a+");			//create and open file in append mod
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
																										
													if(json_encode($obj->nextPageToken))     //check whether any nextPageToken or not if yes then store in a array i.e. nextToken
													{									 
													 $nextToken[$x]= $obj->nextPageToken;
													 $index[$x] = $j;
													 $x++;
															 
													}		//end if condition	
														
										}
										curl_multi_remove_handle($mh, $ch[$j]);     //remove all chanel handle
								} //End of while loop
										
								if($x > 0)     //check whether any nextPageToken or not if yes then store in a array called nextToken
							  {			            			
								 goto nextpage;
							  }	
											
curl_multi_close($mh); 
$y = $z;			
		// herewe do process again till if there  exist nextPageToken 							
			nextpage:	
				{			$p=1;				
						$mh = curl_multi_init();
						for($j=1;$j<50 ;$j++)			
						{
							if($x > 0)										
							{
							$n = $index[$x];
							
							$ch[$j] = curl_init();								//initialze curl
							curl_setopt($ch[$j], CURLOPT_URL, "https://www.googleapis.com/youtube/v3/subscriptions?part=snippet,id,contentDetails&pageToken=".$nextToken[$x]."&fields=items(id,snippet(publishedAt,resourceId(channelId),channelId))&channelId=".$result_array[$n]."&maxResults=50&key=AIzaSyAi5EGrpxTLFuADJ2jqut5ftAsfKG-VPVI");
							curl_setopt($ch[$j], CURLOPT_RETURNTRANSFER,1);			//	CURLOPT_RETURNTRANSFER return the content of channel	
							curl_setopt($ch[$j], CURLOPT_HEADER, false);
							
							curl_multi_add_handle($mh, $ch[$j]);					//add all channel in multichannel
							//echo $result_array[$n];
							$p++;												// calculate total number of channel
							$x--;
						}
					  }					
						
						
					// execute multi curl
							$running=0;													
							do {
								curl_multi_exec($mh, $running);
							 } while($running > 0);
	
						  $x=0;
							$k=1;
							$data=array();
							       for($j=1;$j<50;$j++)                //get the content of all channel and stored in a data() array
								   { 
									   if($p>1)
									   {
										$data[$j]=curl_multi_getcontent($ch[$j]);
										$obj = json_decode($data[$j]);					//convert the json text into object and stored in an object
									     if(isset($obj))
											{			
									   $result= count($obj->items);                  //count total number of element (i.e. item)in json
										$file = fopen("test5.txt","a+");			//create and open file in append mod
												while($result>0)
													 {												// check whether any element exist in item or not
														if(isset($obj->items[$k]))		//convert from object to text form
															{
															fwrite($file, trim(json_encode($obj->items[$k]))."\n"); //write the value of item in file
																 $k++;
															}	
													$result--;	
													}
													fclose($file);								//close file
																										
													if(json_encode($obj->nextPageToken))     //check whether any nextPageToken or not if yes then store in a array i.e. nextToken
													{									 
													 $nextToken[$x]= $obj->nextPageToken;
													 $index[$x] = $j;
													 $x++;
															 
													} 
												}	
												$p--;
										   }
										} 
								curl_multi_remove_handle($mh, $ch[$j]);  			 //remove all chanel handle
	
								if($x > 0)     //check whether any nextPageToken or not if yes then store in a array called nextToken
							  {			            			
								 goto nextpage;
							  }	
						
						$y = $z;	
			}   
	
	
	curl_multi_close($mh); 
	
}

?>



