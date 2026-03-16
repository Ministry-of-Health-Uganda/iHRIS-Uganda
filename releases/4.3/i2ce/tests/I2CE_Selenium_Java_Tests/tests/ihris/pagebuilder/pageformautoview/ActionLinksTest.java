package ihris.pagebuilder.pageformautoview;

import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.Select;

import ihris.iHRISTest;

public class ActionLinksTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Add Action Link to a Page
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - An action link named "Test" does not exist for the page
	 */
	public void testAddActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links > Add a new action link
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		driver.findElement(By.linkText("Add a new action link")).click();
		
		// Input "Test" for the Unique identifier field
		driver.findElement(By.id("key_name")).sendKeys("Test");
		// Type "edit_csd_provider?id=" in the Link Location field
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links:linkloc")).sendKeys("edit_csd_provider?id=");
		// Type "Edit Health Worker Details" in the Link Text field
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links:linktext")).sendKeys("Edit Health Worker Details");
		// Click the Add button and close the update message
		driver.findElement(By.id("swissFactory:values:/Test_Page/args/auto_template/action_links:add_actionlink")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		
		// Click the "Test" action link
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linkloc")).getAttribute("value");
		assertEquals("edit_csd_provider?id=", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linktext")).getAttribute("value");
		assertEquals("Edit Health Worker Details", result);
		
	}
	
	/**
	 * BBTest ID: Add Permission to Action Link
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - An action link named "Test" exists for the page
	 */
	public void testAddPermissionToActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		
		// Click the "Test" action link
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		// Select the "custom_reports_admin" permission for the action link
		Select permissionsDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:task")));
		permissionsDropdown.selectByVisibleText("custom_reports_admin");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		assertEquals("custom_reports_admin", driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:task")).getAttribute("value"));
	}
	
	/**
	 * BBTest ID: Edit an Action Link
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - An action link named "Test" exists for the page
	 */
	public void testEditActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		
		// Click the "Test" action link
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		// Enter "xxx:ID" for Form field
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:formfield")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:formfield")).sendKeys("xxx:ID");
		// Enter "xxx" for the Link Location
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linkloc")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linkloc")).sendKeys("xxx");
		// Enter "xxx" for the Link Text
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linktext")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linktext")).sendKeys("xxx");
		// Choose No Task Selected for the permissions
		Select permissionsDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:task")));
		permissionsDropdown.selectByVisibleText("No Task Selected");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Assert the fields for the action link have been changed
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:formfield")).getAttribute("value");
		assertEquals("xxx:ID", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linkloc")).getAttribute("value");
		assertEquals("xxx", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:linktext")).getAttribute("value");
		assertEquals("xxx", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links/Test:task")).getAttribute("value");
		assertEquals("", result);
	}
	
	/**
	 * BBTest ID: Add Duplicate Action Link
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - An action link named "Test" exists for the page
	 */
	public void testAddDupeActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links > Add a new action link
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		driver.findElement(By.linkText("Add a new action link")).click();
		
		// Input "Test" for the Unique identifier field
		driver.findElement(By.id("key_name")).sendKeys("Test");
		// Type "edit_csd_provider?id=" in the Link Location field
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links:linkloc")).sendKeys("edit_csd_provider?id=");
		// Type "Edit Health Worker Details" in the Link Text field
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/action_links:linktext")).sendKeys("Edit Health Worker Details");
		
		// Assert an error message displays not allowing you to add the link
		assertTrue(driver.getPageSource().contains("The value is already in use. Used values are: Test"));
		
	}
	
	/**
	 * BBTest ID: Delete Action Link on Page
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - An action link named "Test" exists for the page
	 */
	public void testDeleteActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		
		// Click the delete link for the "Test" Action link
		driver.findElement(By.xpath("//a[@href='index.php/PageBuilder/delete/Test_Page/args/auto_template/action_links/Test']")).click();
		
		// Go to Page and Primary Form Configurations > Additional Display Configurations > Action Links
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Action Links")).click();
		
		// Assert the link no longer appears
		assertFalse(driver.getPageSource().contains("Action Link Name: Test (Test)"));
	}
}
