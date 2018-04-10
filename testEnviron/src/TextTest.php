<?php
/**
 * Created by PhpStorm.
 * User: waefwerf
 * Date: 3/8/18
 * Time: 12:19 PM
 */

use PHPUnit\Framework\TestCase;

include "texthandling.php";

class TextTest extends TestCase
{
    function testNoTodos()
    {
        $inputstr = "Hello world";
        $this->assertTrue($inputstr === remove_todos($inputstr));
    }

    function testRemoveSingleTodo()
    {
        $inputstr = "Hello \\todo{goodbye}";
        $res = remove_todos($inputstr);
        $this->assertTrue(remove_todos($inputstr) === "Hello ");
    }

    function testHandlesNestedBraces()
    {
        $inputstr = "This is nested\\todo{{hello}}";
        $res = remove_todos($inputstr);
        $this->assertTrue($res == "This is nested");
    }

    function testHandlesMultipleTodos()
    {
        $inputstr = "Hi\\todo{hello} there\\todo{lel}";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "Hi there");
    }

    function testHandlesOptArgs()
    {
        $inputstr = "Optional \\todo[asd]{adadsdaw}";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "Optional ");
    }

    function testSpaceBeforeBrace()
    {
        $inputstr = "Optional \\todo[asd] {adadsdaw}";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "Optional ");
    }

    function testSpaceBeforeOptArgs()
    {
        $inputstr = "Optional \\todo [asd]{adadsdaw}";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "Optional ");
    }

    function testTextAfterTodo()
    {
        $inputstr = "\\todo{asdlkajd} der skal være tekst her.";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === " der skal være tekst her.");
    }

    function testEmptyString()
    {
        $inputstr = "";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "");
        $inputstr = '';
        $res = remove_todos($inputstr);
        $this->assertTrue($res === '');
    }
    function testEmptyStringPlus()
    {
        $inputstr = "+";
        $res = remove_todos($inputstr);
        $this->assertTrue($res === "+");
    }
    function testManhunt_NoMans()
    {
        $inputstr = "Der er intet galt her";
        echo manhunt($inputstr);
        $this->assertTrue(manhunt($inputstr) === false);
    }

    function testManhunt_CapitalMan()
    {
        $inputstr = "+ Man burde teste";
        $this->assertTrue(manhunt($inputstr) !== false);
    }

    function testManhunt_lowercaseMan()
    {
        $inputstr = "+ Hvis man ser her";
        $this->assertTrue(manhunt($inputstr) !== false);
    }

    function testManhunt_doesntRemoveTodos()
    {
        $inputstr = "+ Man burde teste det her\todo{asd}";
        echo($inputstr);
        $this->assertTrue(manhunt($inputstr) === "+ Man burde teste det her\\todo{asd}");
    }
    function testManhuntRealInput()
    {
        $inputstr = "\renewcommand{\\thepage}{\alphalph{\value{page}}}";
        $this->assertTrue(manhunt($inputstr) === false);
        $inputstr = "		\includegraphics[width=\linewidth]{figures/dotexample.pdf}\n";
        $this->assertTrue(manhunt($inputstr) === false);
    }
    function testRemovesTodosInComments()
    {
        $inputstr = "%\\todo[inline]{Syntes der bliver brugt for lidt energi på biblioteket. Hvorfor er der ikke et kodeeksempel på dette, og hvilke fordele/ulemper er der ved det? Hvis Java eller CS fikser de nævnte problemer, hvorfor er de så ikke overvejet nærmere?}";
        $this->assertTrue(remove_todos($inputstr) == "%");
    }
    function testDumbLine()
    {
        $inputstr = '+%Sidst er sproget meget specifikt, så det kræver en grafisk brugergrænseflade, og det er svært at generalisere algoritmer\todo{Citation needed}, hvoraf sidstnævnte vil være en fordel ved f.eks. Dijkstras algoritme, jf. afsnit \ref{sec:problemanalyse_graphproblems_shortestpath}\todo{Ref til Dijkstra afsnit når merged}, hvor vægten på en kant kan opfattes som en variabel på hver kant eller en funktion for vægten mellem to knuder.';
        $expect = '+%Sidst er sproget meget specifikt, så det kræver en grafisk brugergrænseflade, og det er svært at generalisere algoritmer, hvoraf sidstnævnte vil være en fordel ved f.eks. Dijkstras algoritme, jf. afsnit \ref{sec:problemanalyse_graphproblems_shortestpath}, hvor vægten på en kant kan opfattes som en variabel på hver kant eller en funktion for vægten mellem to knuder.';
        $this->assertTrue(remove_todos($inputstr) === $expect);
    }
    function testManhuntGitDiff()
    {
        $inputstr = file_get_contents("gitdiff", FILE_USE_INCLUDE_PATH);
        $lines = explode("\n", $inputstr);
        for ($i = 1136; $i < count($lines); ++$i) {
            echo(sprintf("Lines: %s\tLength: %d\n", $i+1, strlen($lines[$i])));
            manhunt($lines[$i]);
        }
        $this->assertTrue(true);
    }
    function testMaybeBrokenLine()
    {
        $inputstr = "+Ved at implementere disse som standartfunktioner vil man kunne minimere arbejdsbyrden for programmøren.";
        $this->assertTrue(manhunt($inputstr) !== false);
    }
}


