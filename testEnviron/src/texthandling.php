<?php
/**
 * Created by PhpStorm.
 * User: waefwerf
 * Date: 3/8/18
 * Time: 11:21 AM
 */

function char_at($str, $pos) {
    return mb_substr($str, $pos, 1);
}

function remove_todos($line) {
    $len = mb_strlen($line);
    $pos = mb_strpos($line, "\\todo");
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
        $pos = mb_strpos($line, "\\todo");
    }
    return $line;
}
function manhunt($line) {
    $line = str_replace("\t", "\\t",$line);
    echo(sprintf("Filtered line: %s\n", $line));
    $filtered_line = remove_todos($line);
    if(str_split($filtered_line)[0] === '+'){
        if(preg_match('/[\s+][mM]an[\W]/', $filtered_line)  === 1){
            echo(sprintf("%s\n", $line));
            return $line;
        }
        /*if(preg_match('/[\s+][Vv]i\W/', $line)  === 1){
            $vi_lines[] = $line;
            echo $line;
        }*/
    }
    echo("No mans found\n");
    return false;
}

