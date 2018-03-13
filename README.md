Wikiforia
=========

What is it?
-----------
It is a library and a tool for parsing Wikipedia XML dumps and converting them into plain text for other tools to use.

Why use it?
-----------
Subjectivly generates good results and is reasonably fast, on my laptop (4 physical cores, 8 logical threads, 2.3 Ghz Haswell Core i7) it achieves an average of 6000 pages/sec or 10 minutes for a 2014-08-18 Swedish Wikipedia dump. Your results may of course vary.

How to use?
-----------
Download a multistreamed wikipedia bzip2 dump. It consists of two files: one index and one with the pages.

For a Swedish Wikipedia dump 2014-08-18 it has the following file names:

	svwiki-20140818-pages-articles-multistream-index.txt.bz2
	svwiki-20140818-pages-articles-multistream.xml.bz2

Make sure their names are intact because otherwise the automatic language resolving does not work. The default language is English and it does affect the parsing quality.

Both compressed files must be placed in the directory for the command below to work properly.

To run it all: go to the dist/ directory in your terminal and run

	java -jar wikiforia-1.0-SNAPSHOT.jar
	     -pages [path to the file ending with multistream.xml.bz2]
	     -output [output xml path]

This will run with default settings i.e. the number of cores you have and a batch size of 100. These settings can be overriden, for a full listing just run:

	java -jar wikiforia-1.0-SNAPSHOT.jar

Output
------
The output from the tool is an XML with the following structure (example data)

	<?xml version="1.0" encoding="utf-8"?>
	<pages>

	<page id="4" title="Alfred" revision="1386155063000" type="text/x-wiki" ns-id="0" ns-name="">Alfred,
	with a new line</page>

	<page id="10" title="Template:Infobox" revision="1386155040000" type="text/x-wiki" ns-id="10" ns-name="Template">Template stuff</page>
	</pages>

### Attribute information ###
<dl>
  <dt>id</dt>
  <dd>Wikipedia Page id</dd>

  <dt>title</dt>
  <dd>The title of the Wikipedia page</dd>

  <dt>revision</dt>
  <dd>The revision as given by the dump, but in milliseconds since UNIX epoch time</dd>

  <dt>type</dt>
  <dd>the format, will always be text/x-wiki in this version of the tool</dd>

  <dt>ns-id</dt>
  <dd>The namespace id, 0 is the principal namespace which contains all articles, take a look at Namespaces at [Wikipedia for more information](http://en.wikipedia.org/wiki/Wikipedia:Namespace)</dd>

  <dt>ns-name</dt>
  <dd>Localized name for the namspace, for 0 it is usually just an empty string</dd>
</dl>

Plaintext export
----------------
Contributed by @smhumayun, support for Plain Text output format on top of existing XML format.
Use Case: extract text only from the Wikipedia e.g in order to use it as a Corpus for different Machine Learning experiments.

To run it: Download wikiforia-x.y.z.jar from dist/ directory, open your terminal, go/cd to download location and run

	java -jar wikiforia-x.y.z.jar
	     -pages [path to the file ending with multistream.xml.bz2]
	     -output [output xml path]
	     -outputformat plain-text

CVS export
----------------
Contributed by @hinneburg, support for CSV output format on top of existing XML format. The generated CSV file is in UTF-8 encoding, uses `,` as delimiter, double quotes `"` as text qualifier and backslash `\` as escape character. The first line of the file contains the header with the column names:
```
page_id,title,revision,type,ns_id,ns_name,text
```
According to the CSV standard, the strings in the text column are wrapped in double quotes. Those record elements span over multiple lines as most Wikipedia pages contain newline characters. Thus, it is best to use a capable csv library like [javacsv](https://www.csvreader.com/java_csv_samples.php) or [Apache commons-csv](https://commons.apache.org/proper/commons-csv/) to read the extracted data.

Use Case: extract same information as contained in the XML output from the Wikipedia to use it as input for further analysis in big data platforms like [Apache Spark](https://spark.apache.org/) that can handle CSV files easier than XML.

To run it: Download wikiforia-x.y.z.jar from dist/ directory, open your terminal, go/cd to download location and run

	java -jar wikiforia-x.y.z.jar
	     -pages [path to the file ending with multistream.xml.bz2]
			 -index [path to the file ending with multistream-index.txt.bz2]
	     -output [output csv file]
	     -outputformat csv

### Remarks ###
Empty articles, for which no text could be found is not included. This includes redirects and most of the templates and categories, because they have no useful text. If you use the API you can extract this bit of information.

Language support
----------------
270 language specific configurations have been generated from the Wikimedia source tree that is publicly available. The quality of these autogenerations are uncertain as they are not tested. Kindly confirm or report if your language does not work so that I could possibly mitigate the issue.

The English language is used as fallback when parsing.

API
---
The code can also be used directly to extract more information.

More information about this will be added, but for now take a look at se.lth.cs.nlp.wikiforia.App and the convert method to get an idea of how to use the code.

Credits
-------
**Peter Exner**, the author of [KOSHIK](https://github.com/peterexner/KOSHIK). The Sweble code is partially based on the KOSHIK version.

**[Sweble](http://sweble.org)**, developed by the Open Source Research Group at the Friedrich-Alexander-University of Erlangen-Nuremberg. This library is used to parse the Wikimarkup.

**[Woodstox](http://woodstox.codehaus.org)**, Quick XML parser, used to parse the XML and write XML output.

**[Apache Commons](http://commons.apache.org)**, a collection of useful and excellent libraries. Used CLI for the options.

**[Wikipedia](http://www.wikipedia.org)**, without it, this project would be useless. Testdata has been extracted from Swedish Wikipedia and is covered by [CC BY-SA 3.0](http://creativecommons.org/licenses/by-sa/3.0/deed.en) licence.

Licence
-------
The licence is GPLv2.
