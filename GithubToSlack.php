<?php
/*
MIT License

Copyright (c) 2017 Simon Virenfeldt

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
/*
This script acts as a webhook from github and if certain criteria is met it pushes a message to a slack webhook.
*/
$owner = "REPO_OWNER";//Insert the name of the owner of the repo here.
$repo = "REPO_NAME";//Insert the repo name here
$string = file_get_contents('php://input');
if($string !== NULL){
	
	//Decode incomming json_decode
	$payload = json_decode($string, true);
	$commits = $payload['commits'];
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(	"Authorization: token OAUTH_TOKEN",//Insert oauth token here
												"Accept: application/vnd.github.v3+json",
												"User-Agent: USER_AGENT"));//Insert an user agent name here
	$man_lines = array();
	$vi_lines = array();
	foreach($commits as $commit){
		if($commit['distinct']){
			curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/".$owner."/".$repo."/commits/".$commit['id']);
			$raw_commit_info = curl_exec($ch);
			$commit_info = json_decode($raw_commit_info, true);
			foreach($commit_info['files'] as $file){
				$lines = explode("\n", $file['patch']);
				foreach($lines as $line){
					$commands = array("\\todo", "\\tanker", "\\brian", "\\brianrettet");
                    $line = str_replace("\t", "\\t", $line);
                    $filtered_line = remove_commands($line, $commands);
                    if(str_split($filtered_line)[0] === '+'){
						if(preg_match('/[\s+][mM]an[\W]/', $filtered_line)  === 1){
							$man_lines[] = $filtered_line;
							echo $line;
						}
						/*if(preg_match('/[\s+][Vv]i\W/', $line)  === 1){
							$vi_lines[] = $line;
							echo $line;
						}*/
					}
				}
			}
		}
	}
	if(count($man_lines)>0){
		
		$output='{"text": "'.$payload['pusher']['name'].' har pushet et man!","attachments": [';
		
		$output = formatLines($man_lines, $output);
		
		$output.= ']}';
		echo $output;
		curlTo($output);
	}
	if(count($vi_lines)>0){
		
		$output='{"text": "'.$payload['pusher']['name'].' har pushet et vi!","attachments": [';
		
		$output = formatLines($vi_lines, $output);
		
		$output.= ']}';
		echo $output;
		curlTo($output);
	}
}else{
	echo "There was not POST'ed any string.";
}
function curlTo($message){
	$ch = curl_init("https://hooks.slack.com/services/SLACK_WEBHOOK");//Insert slack webhook here
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(	"Content-type: application/json",
												"Content-Length: ".strlen($message),
												"User-Agent: USER_AGENT"));//Insert user agent here
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
	curl_exec($ch);
}

function formatLines($lines, $output){
	$line_count = count($lines);
	echo $line_count;
	for($i=0; $i<$line_count; $i++){
		$output .= '{"color": "#36a64f","mrkdwn_in": ["text"],"text":"`'.$lines[$i].'`"}';
		if($i+1<$line_count){
			$output .= ',';
		}
	}
	return $output;
}
function char_at($str, $pos) {
    return mb_substr($str, $pos, 1);
}

function remove_commands($line, $cmdNames) {
    for ($i = 0; $i < count($cmdNames); $i++) {
        remove_command($line, $cmdNames[$i]);
    }
    return $line;
}

function remove_todos($line) { return remove_command($line, "\\todo"); }

function remove_command($line, $cmdname) {
    $len = mb_strlen($line);
    $pos = mb_strpos($line, "$cmdname");
    $stack = new SplStack();
    while ($pos < $len && $pos !== false  ) {
        echo($line."\n");
        echo(sprintf("pos == %s\n", $pos));
        $startpos = $pos;
        echo("Character at pos: ".char_at($line,$pos)."\n");
        echo("Startpos = ".$startpos."\n");
        //Skip to opening brace
        while (char_at($line, $pos) !== "{") {
            $pos++;
        }
        //Use stack to track opened braces.
        $stack->push(char_at($line, $pos));
        //Stack should be empty on closing brace.
        while (!$stack->isEmpty()) {
            $pos++;
            if (char_at($line, $pos) === "{") {
                $stack->push(char_at($line, $pos));
            }
			elseif (char_at($line, $pos) === "}") {
                $stack->pop();
            }
        }
        echo(sprintf("Substring \"%s\" at pos %d to %d\n", $line, $startpos, $pos));
        //Remove contents
        $line = str_replace(mb_substr($line, $startpos, $pos - $startpos + 1), "", $line);
        echo(sprintf("After removal: %s\n", $line));
        //Setup for next iteration
        $pos = mb_strpos($line, "$cmdname");
    }
    return $line;
}
?>
