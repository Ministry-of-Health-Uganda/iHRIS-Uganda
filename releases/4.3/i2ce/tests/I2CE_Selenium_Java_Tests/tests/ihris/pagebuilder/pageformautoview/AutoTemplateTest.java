package ihris.pagebuilder.pageformautoview;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

import ihris.iHRISTest;

public class AutoTemplateTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Update Title and Form Display Name
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The Primary Form is csd_address
	 */
	public void testUpdateTitleAndFormDisplayName() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		
		// Change the AutoTemplate Title to 'XXX'
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:title")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:title")).sendKeys("XXX");
		// Change the Form Display Name to 'XXX'
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:form_display_name")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:form_display_name")).sendKeys("XXX");
		
		// Click the 'Update' button and close the update message
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:title")).getAttribute("value");
		assertEquals("XXX", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:form_display_name")).getAttribute("value");
		assertEquals("XXX", result);
	}
	
	/**
	 * BBTest ID: Add Permissions to a Page
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 */
	public void testAddPermissions() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		
		// Check the "cached_forms_can_administer" checkbox
		WebElement box = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[cached_forms_can_administer]"));
		if (!box.isSelected()) {
			box.click();
		}
		// Check the "can_build_form_classes" box
		box = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[can_build_form_classes]"));
		if (!box.isSelected()) {
			box.click();
		}
		
		// Click the 'Update' button and close the update message
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		assertTrue(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[cached_forms_can_administer]")).isSelected());
		assertTrue(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[can_build_form_classes]")).isSelected());
	}
	
	/**
	 * BBTest ID: Remove Permissions
	 * Preconditions:
	 *  - An administrator with username: i2ce_admin and password: manage exists
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - "cached_forms_can_administer" and ""can_build_form_classes" should be selected permissions on "Test_Page"
	 */
	public void testRemoveAllPermissions() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
				
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		
		// Click the edit link for the Test_Page page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Page and Primary Form Configurations > Additional Display Configurations
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		
		// Uncheck the "cached_forms_can_administer" checkbox
		WebElement box = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[cached_forms_can_administer]"));
		if (box.isSelected()) {
			box.click();
		}
		// Unheck the "can_build_form_classes" box
		box = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[can_build_form_classes]"));
		if (box.isSelected()) {
			box.click();
		}
		
		// Click the 'Update' button and close the update message
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		assertFalse(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[cached_forms_can_administer]")).isSelected());
		assertFalse(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template:task[can_build_form_classes]")).isSelected());
	}
}
