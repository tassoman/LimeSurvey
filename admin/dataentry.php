<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
 	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.							#
	#############################################################
	#															#
	#	Copyright (C) 2003  Jason Cleeland						#
	#															#
	# This program is free software; you can redistribute 		#
	# it and/or modify it under the terms of the GNU General 	#
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
	#															#
	# This program is distributed in the hope that it will be 	#
	# useful, but WITHOUT ANY WARRANTY; without even the 		#
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
	# PARTICULAR PURPOSE.  See the GNU General Public License 	#
	# for more details.											#
	#															#
	# You should have received a copy of the GNU General 		#
	# Public License along with this program; if not, write to 	#
	# the Free Software Foundation, Inc., 59 Temple Place - 	#
	# Suite 330, Boston, MA  02111-1307, USA.					#
	#############################################################	
*/
$action = $_GET['action']; if (!$action) {$action = $_POST['action'];}
$sid = $_GET['sid']; if (!$sid) {$sid = $_POST['sid'];}
$id = $_GET['id']; if (!$id) {$id = $_POST['id'];}
$surveytable = $_GET['surveytable']; if (!$surveytable) {$surveytable = $_POST['surveytable'];}

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ") 
echo $htmlheader;
echo "<table width='100%' border='0' bgcolor='#555555'><tr><td align='center'><font color='white'><b>Data Entry</b></td></tr></table>\n";
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<center><b><font color='red'>ERROR: Surveyor database does not exist</font></b><br /><br />\n";
	echo "It appears that your surveyor script has not yet been set up properly.<br />\n";
	echo "Contact your System Administrator</center>\n";
	echo "</body>\n</html>";
	exit;
	}
if (!$sid && !$action)
	{
	echo "You have not selected a survey.";
	exit;
	}

if ($action == "insert")
	{
	echo "<center><b>Inserting data into Survey $sid, tablename $surveytable</b><br /><br />\n";
	$iquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	while ($irow = mysql_fetch_array($iresult))
		{
		if ($irow['type'] != "M" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "P" && $irow['type'] != "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$col_name .= "$fieldname, \n";
			if (get_magic_quotes_gpc())
				{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$fieldname2 = $fieldname . "comment";
			$col_name .= "$fieldname, \n$fieldname2, \n";
			if (get_magic_quotes_gpc())
				{$insertqr .= "'" . $_POST[$fieldname] . "', \n'" . $_POST[$fieldname2] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n'" . mysql_real_escape_string($_POST[$fieldname2]) . "', \n";
					}
				else
					{
					$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n'" . mysql_escape_string($_POST[$fieldname2]) . "', \n";
					}
				}
			}
		else
			{
			$i2query = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$irow['qid']} AND questions.sid=$sid ORDER BY code";
			//echo $i2query . "<br />\n";
			$i2result = mysql_query($i2query);
			while ($i2row = mysql_fetch_array($i2result))
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
				$col_name .= "$fieldname, \n";
				if (get_magic_quotes_gpc())
					{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				$otherexists = "";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($irow['type'] == "P")
					{
					$fieldname2 = $fieldname."comment";
					$col_name .= "$fieldname2, \n";
					if (get_magic_quotes_gpc())
						{$insertqr .= "'" . $_POST[$fieldname2] . "', \n";}
					else
						{
						if (phpversion() >= "4.3.0")
							{
							$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname2]) . "', \n";
							}
						else
							{
							$insertqr .= "'" . mysql_escape_string($_POST[$fieldname2]) . "', \n";
							}
						}
					}
				}
			if ($otherexists == "Y") 
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
				$col_name .= "$fieldname, \n";
				if (get_magic_quotes_gpc())
					{$insertqr .= "'" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$insertqr .= "'" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$insertqr .= "'" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				}
			}
		}
	
	$col_name = substr($col_name, 0, -3); //Strip off the last comma-space
	$insertqr = substr($insertqr, 0, -3); //Strip off the last comma-space
	
	if ($_POST['token']) //handle tokens if survey needs them
		{
		$col_name .= ", token\n";
		$insertqr .= ", '{$_POST['token']}'";
		}
	
	$SQL = "INSERT INTO $surveytable \n($col_name) \nVALUES \n($insertqr)";
	echo $SQL;
	$iinsert = mysql_query($SQL) or die ("Could not insert your data:<br />\n" . mysql_error() . "\n<pre style='text-align: left'>$SQL</pre>\n</body>\n</html>");
	
	echo "<font color='green'><b>Insert Was A Success</b><br />\n";
	
	$fquery = "SELECT id FROM $surveytable ORDER BY id DESC LIMIT 1";
	$fresult = mysql_query($fquery);
	while ($frow = mysql_fetch_array($fresult))
		{
		echo "This record has been assigned the ID number, {$frow['id']}<br />\n";
		}
	
	echo "</font><br />[<a href='dataentry.php?sid=$sid'>Add another record</a>]<br />\n";
	echo "[<a href='browse.php?sid=$sid&action=all&limit=100'>Browse Surveys</a>]<br />\n";
	echo "</center>\n";
	//echo "<pre style='text-align: left'>$SQL</pre><br />\n"; //Debugging info
	echo "</body>\n</html>";
	
	}

elseif ($action == "edit")
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups, surveys WHERE questions.gid=groups.gid AND questions.sid=surveys.sid AND questions.sid='$sid'";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<br /><br />\n";
	
	$arows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_assoc($fnresult)) {$fnrows[] = $fnrow; $private=$fnrow['private'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	// $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist) 
	$fnames[] = array("id", "id", "id", "id", "id", "id", "id");

	if ($private == "N") //show token info if survey not private
		{
		$fnames[] = array ("token", "Token ID", "Token", "token", "TID", "");
		}
	

	foreach ($fnrows as $fnrow)
		{
		$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
		$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
		$fquestion = $fnrow['question'];
		if ($fnrow['type'] == "M" || $fnrow['type'] == "A" || $fnrow['type'] == "B" || $fnrow['type'] == "C" || $fnrow['type'] == "P")
			{
			$fnrquery = "SELECT * FROM answers WHERE qid={$fnrow['qid']} ORDER BY code";
			$fnrresult = mysql_query($fnrquery);
			while ($fnrrow = mysql_fetch_array($fnrresult))
				{
				$fnames[] = array("$field{$fnrrow['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field{$fnrrow['code']}"."comment", "$ftitle"."comment", "{$fnrow['question']}(comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
					}
				}
			if ($fnrow['other'] == "Y")
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field"."othercomment", "$ftitle"."othercomment", "{$fnrow['question']}(other comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
					}
				}
			}
		elseif ($fnrow['type'] == "O")
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
			$field2 = $field."comment";
			$ftitle2 = $ftitle."[Comment]";
			$longtitle = "{$fnrow['question']}<br />(Comment)";
			$fnames[] = array("$field2", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
			}
		else
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}");
			}
		//$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}");
		//echo "$field | $ftitle | $fquestion<br />\n";
		}
	//echo "<pre>"; print_r($fnames); echo "</pre>"; //Debugging info
	$nfncount = count($fnames)-1;

	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE id=$id";
	$idresult = mysql_query($idquery) or die ("Couldn't get individual record<br />$idquery<br />".mysql_error());
	echo "<table>\n";
	echo "<form method='post' action='dataentry.php'>\n";
	echo "\t<tr><td colspan='2' bgcolor='#EEEEEE' align='center'>$setfont<b>Editing Answer ID $id ($nfncount)</td></tr>\n";
	echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";

	while ($idrow = mysql_fetch_assoc($idresult))
		{
		for ($i=0; $i<$nfncount+1; $i++)
			{
			$answer = $idrow[$fnames[$i][0]];
			echo "\t<tr>\n";
			echo "\t\t<td bgcolor='#EEEEEE' valign='top' align='right' width='20%'>$setfont<b>\n";
			if ($fnames[$i][3] != "A" && $fnames[$i][3] != "B" && $fnames[$i][3]!="C" && $fnames[$i][3]!="P" && $fnames[$i][3] != "M") 
				{
				echo "\t\t\t{$fnames[$i][2]}\n";
				}
			else
				{
				echo "\t\t\t{$fnames[$i][2]}\n";
				}
			echo "\t\t</td>\n";
			echo "\t\t<td valign='top'>\n";
			//echo "\t\t\t-={$fnames[$i][3]}=-"; //Debugging info
			switch ($fnames[$i][3])
				{
				case "id":
					echo "\t\t\t{$idrow[$fnames[$i][0]]} <font color='red' size='1'>Cannot be altered</font>\n";
					break;
				case "5": //5 POINT CHOICE radio-buttons
					for ($x=1; $x<=5; $x++)
						{
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$x'";
						if ($idrow[$fnames[$i][0]] == $x) {echo " checked";}
						echo " />$x \n";
						}
					break;
				case "D": //DATE
					echo "\t\t\t<input type='text' size='10' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' />\n";
					break;
				case "G": //GENDER drop-down list
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">Please choose..</option>\n";
					echo "\t\t\t\t<option value='F'";
					if ($idrow[$fnames[$i][0]] == "F") {echo " selected";}
					echo ">Female</option>\n";
					echo "\t\t\t\t<option value='M'";
					if ($idrow[$fnames[$i][0]] == "M") {echo " selected";}
					echo ">Male</option>\n";
					echo "\t\t\t<select>\n";
					break;
				case "L": //LIST drop-down/radio-button list
					$lquery = "SELECT * FROM answers WHERE qid={$fnames[$i][7]} ORDER BY code";
					$lresult = mysql_query($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">Please choose..</option>\n";
					
					while ($llrow = mysql_fetch_array($lresult))
						{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
						}
					echo "\t\t\t</select>\n";
					break;
				case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$lquery = "SELECT * FROM answers WHERE qid={$fnames[$i][7]} ORDER BY code";
					$lresult = mysql_query($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">Please choose..</option>\n";
					
					while ($llrow = mysql_fetch_array($lresult))
						{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
						}
					echo "\t\t\t</select>\n";
					$i++;
					echo "\t\t\t<br />\n";
					echo "\t\t\t<textarea cols='45' rows='5' name='{$fnames[$i][0]}'>";
					echo htmlspecialchars($idrow[$fnames[$i][0]]) . "</textarea>\n";
					break;
				case "M": //MULTIPLE OPTIONS checkbox
					while ($fnames[$i][3] == "M")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						//echo substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5)."<br />\n";
						if (substr($fnames[$i][0], -5) == "other")
							{
							echo "\t\t\t$setfont<input type='text' name='{$fnames[$i][0]}' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							}
						else
							{
							echo "\t\t\t$setfont<input type='checkbox' name='{$fnames[$i][0]}' value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}<br />\n";
							}
						$i++;
						}
					$i--;
					break;
				case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table>\n";
					while ($fnames[$i][3] == "P")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						if (substr($fnames[$i][0], -7) == "comment")
							{
							echo "\t\t<td>$setfont<input type='text' name='{$fnames[$i][0]}' size='50' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></td>\n";
							echo "\t</tr>\n";
							}
						elseif (substr($fnames[$i][0], -5) == "other")
							{
							echo "\t<tr>\n";
							echo "\t\t<td>\n";
							echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' style='width: ";
							echo strlen($idrow[$fnames[$i][0]])."em' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							echo "\t\t</td>\n";
							echo "\t\t<td>\n";
							$i++;
							echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' size='50' value='";
							echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
							echo "\t\t</td>\n";
							echo "\t</tr>\n";
							}
						else
							{
							echo "\t<tr>\n";
							echo "\t\t<td>$setfont<input type='checkbox' name=\"{$fnames[$i][0]}\" value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}</td>\n";
							}
						$i++;
						}
					echo "</table>\n";
					$i--;
					break;
				case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='";
					echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
					break;
				case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea rows='5' cols='45' name='{$fnames[$i][0]}'>";
					echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
				case "Y": //YES/NO radio-buttons
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n";
					echo "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">Please choose..</option>\n";
					echo "\t\t\t\t<option value='Y'";
					if ($idrow[$fnames[$i][0]] == "Y") {echo " selected";}
					echo ">Yes</option>\n";
					echo "\t\t\t\t<option value='N'";
					if ($idrow[$fnames[$i][0]] == "N") {echo " selected";}
					echo ">No</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					echo "<table>\n";
					while ($fnames[$i][3] == "A")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						for ($j=1; $j<=5; $j++)
							{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					echo "</table>\n";
					$i--;
					break;
				case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					echo "<table>\n";
					while ($fnames[$i][3] == "B")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						for ($j=1; $j<=10; $j++)
							{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					echo "<table>\n";
					while ($fnames[$i][3] == "C")
						{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$fnames[$i][6]}</td>\n";
						echo "\t\t<td>$setfont\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
						echo " />Yes&nbsp;\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='U'";
						if ($idrow[$fnames[$i][0]] == "U") {echo " checked";}
						echo " />Uncertain&nbsp\n";
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='N'";
						if ($idrow[$fnames[$i][0]] == "N") {echo " checked";}
						echo " />No&nbsp;\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						$i++;
						}
					$i--;
					echo "</table>\n";
					break;
				default: //This really only applies to tokens for non-private surveys
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='";
					echo $idrow[$fnames[$i][0]] . "'>\n";
					break;
				}
			//echo "\t\t\t$setfont{$idrow[$fnames[$i][0]]}\n"; //Debugging info
			//echo $fnames[$i][0], $fnames[$i][1], $fnames[$i][2], "\n"; //Debugging info
			echo "\t\t</td>\n";
			echo "\t</tr>\n";
			echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
			}
		}
	echo "</table>\n";
	echo "<table width='100%'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td $singleborderstyle bgcolor='#EEEEEE' align='center'>\n";
	echo "\t\t\t<input type='submit' value='Update'>\n";
	echo "\t\t\t<input type='hidden' name='id' value='$id'>\n";
	echo "\t\t\t<input type='hidden' name='sid' value='$sid'>\n";
	echo "\t\t\t<input type='hidden' name='action' value='update'>\n";
	echo "\t\t\t<input type='hidden' name='surveytable' value='survey_$sid'>\n";
	echo "\t\t</td>\n";
	echo "\t\t</form>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}
	

elseif ($action == "update")
	{
	echo "$surveyoptions";
	echo "<center><br /><b>Updating data for Survey $sid, tablename $surveytable - Record No $id</b><br /><br />\n";
	$iquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	$updateqr = "UPDATE $surveytable SET \n";
	
	while ($irow = mysql_fetch_array($iresult))
		{
		if ($irow['type'] != "M" && $irow['type'] != "P" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			if (get_magic_quotes_gpc())
				//{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
				{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		elseif ($irow['type'] == "O")
			{
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
			$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";
			$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}comment";
			if (get_magic_quotes_gpc())
				{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
			else
				{
				if (phpversion() >= "4.3.0")
					{
					$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
					}
				else
					{
					$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
					}
				}
			}
		else
			{
			$i2query = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$irow['qid']} AND questions.sid=$sid ORDER BY code";
			//echo $i2query;
			$i2result = mysql_query($i2query);
			$otherexists = "";
			while ($i2row = mysql_fetch_array($i2result))
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
				$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($irow['type'] == "P")
					{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}comment";
					if (get_magic_quotes_gpc())
						{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
					else
						{
						if (phpversion() >= "4.3.0")
							{
							$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
							}
						else
							{
							$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
							}
						}
					}
				}
			if ($otherexists == "Y") 
				{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
				if (get_magic_quotes_gpc())
					{$updateqr .= "$fieldname = '" . $_POST[$fieldname] . "', \n";}
				else
					{
					if (phpversion() >= "4.3.0")
						{
						$updateqr .= "$fieldname = '" . mysql_real_escape_string($_POST[$fieldname]) . "', \n";
						}
					else
						{
						$updateqr .= "$fieldname = '" . mysql_escape_string($_POST[$fieldname]) . "', \n";
						}
					}
				}
			}	
		}
	$updateqr = substr($updateqr, 0, -3);
	$updateqr .= " WHERE id=$id";
	$updateres = mysql_query($updateqr) or die("Update failed:<br />\n" . mysql_error() . "\n<pre style='text-align: left'>$updateqr</pre>");
	echo "<br />\n<b>Record has been updated.</b><br /><br />\n";
	echo "<a href='browse.php?sid=$sid&action=id&id=$id'>View record again</a>\n<br />\n";
	echo "<a href='browse.php?sid=$sid&action=all'>Browse all records</a>\n";
	//echo "<pre style='text-align: left'>$updateqr</pre>"; //Debugging info
	echo "</body>\n</html>\n";
	}

elseif ($action == "delete")
	{
	echo "<table width='100%' border='0' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#000080'>\n";
	echo "\t\t<td colspan='3' align='center'><font color='white'>\n";
	echo "\t\t\t<b>$surveyname</b><br />\n";
	echo "\t\t\t$setfont$surveydesc\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	$delquery = "DELETE FROM $surveytable WHERE id=$id";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'><br />$setfont<b>Deleting Record $id</b><br /><br />\n";
	$delresult = mysql_query($delquery) or die ("Couldn't delete record $id<br />\n".mysql_error());
	echo "\t\t\tRecord succesfully deleted.<br /><br />\n<a href='browse.php?sid=$sid&action=all'>Back to Browse</a>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>\n";
	}
	
else
	{
	// PRESENT SURVEY DATAENTRY SCREEN
	$desquery = "SELECT * FROM surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery);
	while ($desrow = mysql_fetch_array($desresult))
		{
		$surveyname = $desrow['short_title'];
		$surveydesc = $desrow['description'];
		$surveyactive = $desrow['active'];
		$surveyprivate = $desrow['private'];
		$surveytable = "survey_{$desrow['sid']}";
		}
	if ($surveyactive == "Y") {echo "$surveyoptions\n";}
	echo "<table width='100%' border='0' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#000080'>\n";
	echo "\t\t<td colspan='3' align='center'><font color='white'>\n";
	echo "\t\t\t<b>$surveyname</b>\n";
	echo "\t\t\t<br>$setfont$surveydesc\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<form action='dataentry.php' name='addsurvey' method='post'>\n";
	
	if ($surveyprivate == "N") //Give entry field for token id
		{
		echo "\t<tr>\n";
		echo "\t\t<td valign='top' width='1%'></td>\n";
		echo "\t\t<td valign='top' align='right' width='30%'>$setfont<b>Token ID:</b></font></td>\n";
		echo "\t\t<td valign='top' style='padding-left: 20px'>$setfont\n";
		echo "\t\t\t<input type='text' name='token'>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		}
	
	// SURVEY NAME AND DESCRIPTION TO GO HERE
	$degquery = "SELECT * FROM groups WHERE sid=$sid ORDER BY group_name";
	$degresult = mysql_query($degquery);
	// GROUP NAME
	while ($degrow = mysql_fetch_array($degresult))
		{
		$deqquery = "SELECT * FROM questions WHERE sid=$sid AND gid={$degrow['gid']}";
		$deqresult = mysql_query($deqquery);
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#AAAAAA'>$setfont<b>{$degrow['group_name']}</td>\n";
		echo "\t</tr>\n";
		$gid = $degrow['gid'];
		
		//Alternate bgcolor for different groups
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
		else {$bgc = "#EEEEEE";}
		if (!$bgc) {$bgc = "#EEEEEE";}
		
		$deqrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
		while ($deqrow = mysql_fetch_array($deqresult)) {$deqrows[] = $deqrow;} //Get table output into array
		
		// Perform a case insensitive natural sort on group name then question title of a multidimensional array
		usort($deqrows, 'CompareGroupThenTitle');
		
		foreach ($deqrows as $deqrow)
			{
			$qid = $deqrow['qid'];
			$fieldname = "$sid"."X"."$gid"."X"."$qid";
			echo "\t<tr bgcolor='$bgc'>\n";
			echo "\t\t<td valign='top' width='1%'>$setfont{$deqrow['title']}</td>\n";
			echo "\t\t<td valign='top' align='right' width='30%'>$setfont<b>{$deqrow['question']}</b></font></td>\n";
			echo "\t\t<td valign='top' style='padding-left: 20px'>$setfont\n";
			//DIFFERENT TYPES OF DATA FIELD HERE
			if ($deqrow['help'])
				{
				$hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
				$hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
				echo "\t\t\t<img src='help.gif' alt='Help about this question' align='right' onClick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
				}
			switch($deqrow['type'])
				{
				case "5": //5 POINT CHOICE radio-buttons
					echo "\t\t\t<input type='radio' name='$fieldname' value='1' />1 \n";
					echo "\t\t\t<input type='radio' name='$fieldname' value='2' />2 \n";
					echo "\t\t\t<input type='radio' name='$fieldname' value='3' />3 \n";
					echo "\t\t\t<input type='radio' name='$fieldname' value='4' />4 \n";
					echo "\t\t\t<input type='radio' name='$fieldname' value='5' />5 \n";
					break;
				case "D": //DATE
					echo "\t\t\t<input type='text' name='$fieldname' size='10' />\n";
					break;
				case "G": //GENDER drop-down list
					echo "\t\t\t<select name='$fieldname'>\n";
					echo "\t\t\t\t<option selected value=''>Please Choose..</option>\n";
					echo "\t\t\t\t<option value='F'>Female</option>\n";
					echo "\t\t\t\t<option value='M'>Male</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "L": //LIST drop-down/radio-button list
					$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = mysql_fetch_array($dearesult))
						{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
						}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>Please choose..</option>\n";}
					echo "\t\t\t</select>\n";
					break;
				case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = mysql_fetch_array($dearesult))
						{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
						}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>Please choose..</option>\n";}
					echo "\t\t\t</select>\n";
					echo "\t\t\t<br />Comment:<br />\n";
					echo "\t\t\t<textarea cols='40' rows='5' name='$fieldname";
					echo "comment'>$idrow[$i]</textarea>\n";
					break;
				case "M": //MULTIPLE OPTIONS checkbox (Quite tricky really!)
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}<br />\n";
						}
					if ($deqrow['other'] == "Y")
						{
						echo "\t\t\tOther: <input type='text' name='$fieldname";
						echo "other' />\n";
						}
					break;
				case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table border='0'>\n";
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}\n";
						echo "\t\t</td>\n";
						//This is the commments field:
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname{$mearow['code']}comment' size='50' />\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					if ($deqrow['other'] == "Y")
						{
						echo "\t<tr>\n";
						echo "\t\t<td style='padding-left: 22px'>$setfont"."Other:</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname"."other' size='50'/>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='$fieldname' />\n";				
					break;
				case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea cols='40' rows='5' name='$fieldname'></textarea>\n";
					break;
				case "Y": //YES/NO radio-buttons
					echo "\t\t\t<Select name='$fieldname'>\n";
					echo "\t\t\t\t<option selected value=''>Please choose..</option>\n";
					echo "\t\t\t\t<option value='Y'>Yes</option>\n";
					echo "\t\t\t\t<option value='N'>No</option>\n";
					echo "\t\t\t</select>\n";
					break;
				case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>$setfont\n";
						for ($i=1; $i<=5; $i++)
							{
							echo "\t\t\t<input type='radio' name='$fieldname{$mearow['code']}' value='$i'";
							if ($idrow[$i] == $i) {echo " checked";}
							echo " />$i&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						for ($i=1; $i<=10; $i++)
							{
							echo "\t\t\t$setfont<input type='radio' name='$fieldname{$mearow['code']}' value='$i'";
							if ($idrow[$i] == $i) {echo " checked";}
							echo " />$i&nbsp;\n";
							}
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
						}
					echo "</table>\n";
					break;
				case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<table>\n";
					while ($mearow = mysql_fetch_array($mearesult))
						{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t$setfont<input type='radio' name='$fieldname{$mearow['code']}' value='Y'";
						if ($idrow[$i]== "Y") {echo " checked";}
						echo " />Yes&nbsp;\n";
						echo "\t\t\t$setfont<input type='radio' name='$fieldname{$mearow['code']}' value='U'";
						if ($idrow[$i]== "U") {echo " checked";}
						echo " />Uncertain&nbsp;\n";
						echo "\t\t\t$setfont<input type='radio' name='$fieldname{$mearow['code']}' value='N'";
						if ($idrow[$i]== "N") {echo " checked";}
						echo " />No&nbsp;\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
						}
					echo "</table>\n";
					break;
				}
			//echo " [$sid"."X"."$gid"."X"."$qid]";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";
			echo "\t<tr><td colspan='3' height='2' bgcolor='silver'></td></tr>\n";		
			}		
		}
	
	if ($surveyactive == "Y")
		{
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#AAAAAA'>\n";
		echo "\t\t\t<input type='submit' value='Submit Survey' />\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		}
	else
		{
		echo "\t<tr>\n";
		echo "\t\t<td colspan='3' align='center' bgcolor='#AAAAAA'>\n";
		echo "\t\t\t<font color='red'><b>This is a test survey only - it is not yet activated\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";	
		}
	echo "\t<input type='hidden' name='action' value='insert' />\n";
	echo "\t<input type='hidden' name='surveytable' value='$surveytable' />\n";
	echo "\t<input type='hidden' name='sid' value='$sid' />\n";
	echo "\t</form>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	}

?>