package ihris.pagebuilder.pageformautoview;

import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.Select;

import ihris.iHRISTest;

public class PageArgsTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Add a Primary Form and Title to a Page
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 */
	public void testAddPrimaryFormAndTitle() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		
		// Select csd_provider form
		Select formDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args:primary_form")));
		formDropdown.selectByVisibleText("csd_provider");
		// Enter Health Worker title
		driver.findElement(By.name("swissFactory:values:/Test_Page/args:title")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args:title")).sendKeys("Health Worker");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args:primary_form")).getAttribute("value");
		assertEquals("csd_provider", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args:title")).getAttribute("value");
		assertEquals("Health Worker", result);
		
	}
	
	
}
