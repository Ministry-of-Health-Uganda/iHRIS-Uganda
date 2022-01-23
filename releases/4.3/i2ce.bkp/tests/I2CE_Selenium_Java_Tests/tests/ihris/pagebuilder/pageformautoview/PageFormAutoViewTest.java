package ihris.pagebuilder.pageformautoview;

import ihris.iHRISTest;

import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.Select;

public class PageFormAutoViewTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTestID: Create a PageFormAutoViewPage
	 * Preconditions:
	 *  - The PageBuilder module has been activated
	 *  - The PageFormAutoView module has been activated
	 *  - There is no existing existing page with the name "Test_Page"
	 */
	public void testCreatePageFormAutoViewPage() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Create a new page named Test_Page
		driver.findElement(By.name("swissFactory:values:/:name")).sendKeys("Test_Page");
		// Make the page of the class PageFormAutoView
		Select classDropdown = new Select(driver.findElement(By.name("swissFactory:values:/:class")));
		classDropdown.selectByVisibleText("I2CE_PageFormAutoView");
		// Click the 'Update' Button
		driver.findElement(By.id("swiss_update_button")).click();
		// Click 'Close' on the update box
		driver.findElement(By.linkText("Close")).click();
		
		// Assert that the new page appears
		assertTrue(driver.getPageSource().contains("Test_Page"));
	}
}
