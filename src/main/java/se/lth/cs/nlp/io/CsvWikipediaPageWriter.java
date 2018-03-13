package se.lth.cs.nlp.io;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOError;
import java.io.IOException;
import java.nio.charset.Charset;
import java.util.List;

import com.csvreader.CsvWriter;

import se.lth.cs.nlp.mediawiki.model.WikipediaPage;
import se.lth.cs.nlp.pipeline.Sink;

/**
 * WikipediaPage UTF-8 CSV writer, only writes WikipediaPages with content.
 * <p>
 * It uses the javacsv library [1] to escape and write the Wikipedia page content. 
 * <p>
 * [1] javacsv:  javaDoc http://javacsv.sourceforge.net/com/csvreader/CsvWriter.html ,
 *     project page https://www.csvreader.com/java_csv.php
 */
public class CsvWikipediaPageWriter implements Sink<WikipediaPage> {
    private final File output;
    private CsvWriter writer;

    /**
     * Default constructor
     * @param output which csv file to write to
     * 
     */
    public CsvWikipediaPageWriter(File output) {
        try {
        	Charset charset = Charset.forName("UTF-8");
        	char separator = ',';
            this.writer = new CsvWriter(new BufferedOutputStream(new FileOutputStream(output)), separator, charset);
            this.writer.setEscapeMode(CsvWriter.ESCAPE_MODE_BACKSLASH);
            this.writer.setTextQualifier('"');
            this.writer.setUseTextQualifier(true);
            this.output = output;
            this.writer.write("page_id");
            this.writer.write("title");
            this.writer.write("revision");
            this.writer.write("type");
            this.writer.write("ns_id");
            this.writer.write("ns_name");
            this.writer.write("text");
			this.writer.endRecord();
        } catch (FileNotFoundException e) {
        	throw new IOError(e);
        } catch (IOException e) {
            throw new IOError(e);
        }
    }

    @Override
    public synchronized void process(List<WikipediaPage> batch) {
        if(writer == null)
            return;

        try {
            if(batch.size() == 0) {
                writer.flush();
                writer.close();;
                writer = null;
                return;
            }

            for (WikipediaPage wikipediaPage : batch) {
                if(wikipediaPage.getText().length() > 0) {
                    writer.write( String.valueOf(wikipediaPage.getId()));
                    writer.write(String.valueOf(wikipediaPage.getTitle()));
                    writer.write(String.valueOf(wikipediaPage.getRevision()));
                    writer.write(String.valueOf(wikipediaPage.getFormat()));
                    writer.write(String.valueOf(wikipediaPage.getNamespace()));

                    String name = wikipediaPage.getHeader().getSiteinfo().getNamespaces().get(wikipediaPage.getNamespace());
                    if (name == null)
                        writer.write("?");
                    else
                        writer.write(name);

                    writer.write(wikipediaPage.getText());
                    writer.endRecord();
                }
            }
        } catch (IOException e) {
            throw new IOError(e);
        }
    }

    @Override
    public String toString() {
        return String.format("CVS Writer { target: %s }", output.getAbsolutePath());
    }
}
