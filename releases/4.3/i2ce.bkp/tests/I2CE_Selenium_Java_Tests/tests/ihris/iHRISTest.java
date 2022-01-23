package ihris;

import java.util.concurrent.TimeUnit;

import junit.framework.TestCase;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxProfile;

/**
 * 
 * @author Alex.Wang
 *
 */
public class iHRISTest extends TestCase {
	
	/** The url of the application homepage used to test with */
	public static final String MAIN_ADDRESS = "http://openhie-pr.ihris.org/openhie-pr-awang4/";
	
	
	/** Default load time */
	public static final int DEFAULT_LOAD_TIME = 5;
	/** Amount of milliseconds in a second */
	public static final int MILLIS_IN_SECOND = 1000;
	
	/** The webdriver used to drive the tests */
	public WebDriver driver;
	/** The load/wait time between loading pages. Used with Thread.sleep() to pause execution between pages */
	public int ldtime;
	/** The time to implicitly wait for elements to load */
	public int ildtime;
	
	@Override
	protected void setUp() {
		
		// Initialize the load times to the default value.
		ldtime = DEFAULT_LOAD_TIME * MILLIS_IN_SECOND;
		ildtime = DEFAULT_LOAD_TIME;
		
		// Initialize the webdriver with Firefox and create an implicit wait time of DEFAULT_LOAD_TIME seconds
		FirefoxProfile profile = new FirefoxProfile();
		profile.setEnableNativeEvents(true);
		driver = new FirefoxDriver(profile);
		// Set implicit load time
		driver.manage().timeouts().implicitlyWait(ildtime, TimeUnit.SECONDS);
		// Maximize window
		driver.manage().window().maximize();
	}
	
	@Override
	protected void tearDown() {
		// Close firefox browser and webdriver
		driver.quit();
	}
	
	public void login(String username, String password) {
		// Enter the url for ihris
		driver.get(MAIN_ADDRESS);
	
		//Sign in using username and password
		driver.findElement(By.name("username")).sendKeys(username);
		driver.findElement(By.name("password")).sendKeys(password);
		driver.findElement(By.name("submit")).click();
	}
}
