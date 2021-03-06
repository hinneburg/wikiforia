<?php
/**
 * This file is part of Wikiforia.
 * (C) Copyright Marcus Klang 2015
 *
 * Wikiforia is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Wikiforia is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wikiforia. If not, see <http://www.gnu.org/licenses/>.
 */

/* Usage: 
 This tool is placed at the root of the Wikimedia source-tree and then php is executed standalone via e.g. bash 
*/
date_default_timezone_set("UTC");

if(count($argv) < 2) {
	echo "Error: Please specify target directory for the sources!";
	echo "autogenerate-configs.php [target-directory]";
	return;
}

$targetdir = rtrim($argv[1],"/\\");
if(!file_exists($targetdir)) {
	if(!mkdir($targetdir, 0777, true)) {
		echo "Failed to create directory " . $targetdir . "\n";
		return;
	}
}

class Extraction
{
	public $dict;
	public $magicWords;
}

function parseMessages($path) {
	include("includes/Defines.php");
	include($path);

	$extraction = new Extraction();
	$extraction->dict = array();
	if(!isset($magicWords)) {
		$extraction->magicWords = array();
	}
	else {
		$extraction->magicWords = $magicWords;
	}
	

	if(isset($namespaceNames)) {
		foreach($namespaceNames as $key => $value) {
			$extraction->dict[$key] = array($value);
		}
	}

	if(isset($namespaceAliases)) {
		foreach($namespaceAliases as $key => $value) {
			if(!isset($extraction->dict[$value])) {
				$extraction->dict[$value] = array();
			}

			array_push($extraction->dict[$value], $key);
		}
	}

	if(isset($namespaceGenderAliases)) {
		foreach($namespaceGenderAliases as $key => $value) {
			foreach($value as $gender => $label) {
				array_push($extraction->dict[$key], $label);
			}
		}
	}

	return $extraction;
}

function parseAll($path) {
	$extractions = array(parseMessages($path));
	include("includes/Defines.php");
	include($path);

	if(isset($fallback)) {
		foreach(explode(",", $fallback) as $lang) {
			$subpath = "Messages" . str_replace("-", "_", trim($lang)) . ".php";
			if(file_exists($subpath)) {
				array_push($extractions, parseMessages($subpath));
			}
		}
	}

	return mergeMessages($extractions);
}

function mergeMessages($extracts) {
	$extractions = new Extraction();
	$extractions->dict       = array();
	$extractions->magicWords = array();

	foreach($extracts as $extractdata) {
		foreach($extractdata->dict as $key => $value) {
			if(!isset($extractions->dict[$key])) {
				$extractions->dict[$key] = $value;
			}
			else {
				foreach($value as $item) {
					array_push($extractions->dict[$key], $item);
				}
			}
		}

		foreach($extractdata->magicWords as $key => $arr) {
			if(isset($extractions->magicWords[$key])) {
				$extractions->magicWords[$key][] = array_slice($arr, 1);
			}
			else {
				$extractions->magicWords[$key] = $arr;
			}
		}
	}

	return $extractions;
}

function getJava($lang, $extraction) {
	ob_start();
?>
/**
 * This file is part of Wikiforia.
 *
 * Wikiforia is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Wikiforia is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wikiforia. If not, see <http://www.gnu.org/licenses/>.
 */
 package se.lth.cs.nlp.wikipedia.lang;

<?php
	echo "//Autogenerated from Wikimedia sources at " . date("c") . "\n\n";
	echo "public class " . ucfirst($lang) . "Config extends TemplateConfig {\n";

	echo "\tpublic " . ucfirst($lang) . "Config() {\n";

	foreach($extraction->dict as $key => $arr) {
		echo "\t\t" . 'addNamespaceAlias(' . $key;
		foreach($arr as $item) {
			echo ', "' . str_replace("$1", "Wikipedia", $item) . '"';
		}
		echo ");\n";
	}

	echo "\n";
	foreach($extraction->magicWords as $key => $arr) {
		if($arr[0] == '1') {
			echo "\t\t" . 'addI18nAlias("';
		}
		else if($arr[0] == '0') {
			echo "\t\t" . 'addI18nCIAlias("';
		}

		echo $key . '"';

		foreach(array_slice($arr,1) as $alias) {
			echo ', "' . $alias . '"';
		}
		echo ");\n";
	}

	echo "\t}\n\n";
	echo "\t@Override\n";
	echo "\tprotected String getSiteName() {\n";
	echo "\t\treturn" . ' "Wikipedia"' . ";\n";
	echo "\t}\n\n";

	echo "\t@Override\n";
	echo "\tprotected String getWikiUrl() {\n";
	echo "\t\treturn " . '"http://' . $lang . '.wikipedia.org/"' . ";\n";
	echo "\t}\n\n";

	echo "\t@Override\n";
	echo "\tpublic String getIso639() {\n";
	echo "\t\treturn " . '"' . $lang . '"' . ";\n";
	echo "\t}\n";
	echo "}\n";
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
}

$i = 0;

$langs = array();

$searchpath = dirname(__FILE__) . "/languages/messages";
foreach(scandir($searchpath) as $file) {
	if(is_file($searchpath . "/" . $file)) {
		if(preg_match("/Messages([A-Za-z]+)\\.php$/", $file, $matches)) {
			array_push($langs, strtolower($matches[1]));
			file_put_contents($targetdir . "/" . $matches[1] . "Config.java", getJava(strtolower($matches[1]), parseAll($searchpath . "/" . $file)));
			$i++;
		}
	}
}

ob_start();
?>
package se.lth.cs.nlp.wikipedia.lang;

import java.util.HashMap;
/**
 * This file is part of Wikiforia.
 *
 * Wikiforia is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Wikiforia is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Wikiforia. If not, see <http://www.gnu.org/licenses/>.
 */
 public class LangFactory {
 	private static final HashMap<String,Class<?php echo "<? extends TemplateConfig>>"; ?> languages = new HashMap<String,Class<?php echo "<? extends TemplateConfig>>"; ?>();

 	static {<?php echo "\n";
 		foreach($langs as $lang) {
 			echo "\t\t" . 'languages.put("' . $lang . '", ' . ucfirst($lang) . 'Config.class);' . "\n";
 		}?>
 	}

 	public static Class<?php echo "<? extends TemplateConfig>"; ?> get(String lang) {
 		return languages.get(lang);
 	}
 }
<?php
$contents = ob_get_contents();
ob_end_clean();

file_put_contents($targetdir . "/LangFactory.java", $contents);
echo "Generated configurations for " . $i . " languages.\n";

?>