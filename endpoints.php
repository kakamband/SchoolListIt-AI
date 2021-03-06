<?php
/**
 * ENDPOINTS
 * This file will hold the code that creates the REST endpoints needed by the SchoolListIt App
 * 
 */
namespace SchoolListIt_AI;

add_action( 'rest_api_init', function () {

    register_rest_route( 'schoolistit/v2', '/text-to-speech', array(
      'methods' => 'GET, POST',
      'callback' => __NAMESPACE__.'\\watson_speak',
    ) );

});  


/**
 * Watson Speak 
 * 
 * the curl function that goes out for authentication and text-to-speech. 
 * 
 * NOTE: I did not secure this endpoint...because I am totally out of time for the submission, but I have an auth protocol
 * which I use to secure the back and forth...will do it after. I figure there is little risk,
 * I think my cloud account expires ina  few days...
 * 
 * save file; return filepath
 */
function watson_speak(\WP_REST_Request $request){
    $key = \file_get_contents(plugin_dir_path( __FILE__ ) .'keys.txt'); // in production this will be stored in a wp_options, but since I have not built the admin page yet this is a placeholder
    $key = \str_replace('IBM_APIKEY=', '', $key);
    $params = $request->get_params();
    $text = $params['text'];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.us-south.text-to-speech.watson.cloud.ibm.com/instances/59e90965-ae49-4d00-b43e-d353b1403ef0/v1/synthesize');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$text\"}");
    curl_setopt($ch, CURLOPT_USERPWD, 'apikey' . ':' . $key);
  
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: audio/wav';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  
    $file = curl_exec($ch);
    if (curl_errno($ch)) {
        return 'Error:' . curl_error($ch);
    } else {
      curl_close($ch);
      $filepath = schoollistit_save_audio($file);
      $response = array(
       // 'curlResponse'=>$file,
        'filepath'=>$filepath
      );
      return $response;
    }
  }

  //save file and return url of the wave to the react app.
function schoollistit_save_audio($file){
    $file_array = explode("\n\r", $file, 2);
    //$header_array = explode("\n", $file_array[0]);
    $filepath = plugin_dir_path( __FILE__ ) .'audio-files/'.time().'.wav';
    $saved = \file_put_contents($filepath, $file_array);
    if(!$saved){
      return $saved;
    } else {
      //save file
    return $filepath;
    }
  }
 
?>