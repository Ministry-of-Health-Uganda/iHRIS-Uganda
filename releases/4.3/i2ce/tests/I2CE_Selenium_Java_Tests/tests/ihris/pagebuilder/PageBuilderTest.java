package ihris.pagebuilder;

import ihris.iHRISTest;

import org.openqa.selenium.By;

public class PageBuilderTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Access PageBuilder Module
	 * Preconditions: 
	 * 	- An administrator with username: i2ce_admin and password: manage exists
	 * 	- The PageBuilder module has been activated
	 */
	public void testAccessPageBuilder() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Assert the page that opens contains options to create and edit pages
		assertTrue(driver.getPageSource().contains("Create A New Page"));
		assertTrue(driver.getPageSource().contains("Edit an Existing Page"));
	}
	
	/**
	 * BBTest ID: Create a Page
	 * Preconditions: 
	 * 	- An administrator with username: i2ce_admin and password: manage exists
	 * 	- The PageBuilder module has been activated
	 *  - There is no existing page with the name "Test_Page"
	 */
	public void testCreatePage() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Create a new page named Test_Page
		driver.findElement(By.name("swissFactory:values:/:name")).sendKeys("Test_Page");
		driver.findElement(By.id("swiss_update_button")).click();
		// Click 'Close' on the update box
		driver.findElement(By.linkText("Close")).click();
		
		// Assert that the new page appears
		assertTrue(driver.getPageSource().contains("Test_Page"));
	}
	
	/**
	 * BBTest ID: Create a Duplicate Page
	 * Preconditions:
	 * 	- An administrator with username: i2ce_admin and password: manage exists
	 * 	- The PageBuilder module has been activated
	 *  - The page Test_Page exists in the Existing Pages (See Create a Page)
	 */
	public void testCreateDuplicatePage() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Create a new page named Test_Page
		driver.findElement(By.name("swissFactory:values:/:name")).sendKeys("Test_Page");
		driver.findElement(By.id("swiss_update_button")).click();
		
		// Assert that error message appears
		assertTrue(driver.getPageSource().contains("The value is already in use"));
	}
	
	/**
	 * BBTest ID: Delete a Page
	 * Preconditions:
	 * 	- An administrator with username: i2ce_admin and password: manage exists
	 * 	- The PageBuilder module has been activated
	 *  - The page Test_Page exists in the Existing Pages (See Create a Page)
	 */
	public void testDeletePage() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Create a new page named Test_Page
		driver.findElement(By.xpath("//a[href='index.php/PageBuilder/delete/Test_Page']")).click();
		
		// Assert that Test_Page is no longer there
		assertFalse(driver.getPageSource().contains("Test_Page"));
	}
}
