import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashSet;
import java.util.Set;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;



public class extractBig {
	
	public static void appendToFile(ArrayList<String> wordList) throws IOException
	{
		BufferedWriter writer = new BufferedWriter(new FileWriter("/home/jianfei_william_wang/Reuters/extractBig.txt",true));
		for(String x: wordList)
		{
		    writer.write(x+"\n");
		}
        writer.close();
	}
	
	public static void main(String args[]) throws FileNotFoundException, IOException, SAXException, TikaException 
    {
        System.out.println("changed");
        File directoryPath= new File("/home/jianfei_william_wang/Reuters/reutersnews/");
          
        for (File file: directoryPath.listFiles()){
            
          BodyContentHandler handler = new BodyContentHandler(-1);
	      Metadata metadata = new Metadata();
	      FileInputStream inputstream = new FileInputStream(file);
	      ParseContext pcontext = new ParseContext();

	      HtmlParser htmlparser = new HtmlParser();
	      htmlparser.parse(inputstream, handler, metadata,pcontext);
	      String myString = handler.toString();
	      ArrayList resultList = new ArrayList(Arrays.asList(myString.split("\\W+")));
          
          appendToFile(resultList);
          
        }
		}
		// TODO Auto-generated catch bloc
}