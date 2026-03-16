package ihris.pagebuilder.pageformautoview;

import org.openqa.selenium.By;
import org.openqa.selenium.support.ui.Select;

import ihris.iHRISTest;

public class ChildFormsTest extends iHRISTest {
	
	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Add a child form to a Page to a page with no child forms
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The page has no existing child forms
	 */
	public void testAddChildFormToPageWithNone() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Assert the page doesn't contain existing child forms
		assertTrue(driver.getPageSource().contains("There are no child forms associated with the primary form."));
		
		// Click the "Add a new child form" link 
		driver.findElement(By.linkText("Add a new child form")).click();
		
		// Select the csd_address child form type
		Select typeDropdown = new Select(driver.findElement(By.id("add_childform_type_selector")));
		typeDropdown.selectByVisibleText("csd_address");
		// Make the child form's display name "Test"
		driver.findElement(By.id("childform_displayname")).sendKeys("Test");
		// Click the Add button and close out the update message
		driver.findElement(By.id("swissFactory:values:/Test_Page/args/auto_template/child_forms:add_childform")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the new csd_address form
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		
		// Assert the title is "Test"
		assertEquals("Test", driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:title")).getAttribute("value"));
		
	}
	
	/**
	 * BBTest ID: Add a child form to a Page to a page with all child forms
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "view_csd_provider" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - There are 7 child forms on view_csd_provider
	 */
	public void testAddChildFormToPageWithAll() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the view_csd_provider
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/view_csd_provider']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the "Add a new child form" link 
		driver.findElement(By.linkText("Add a new child form")).click();
		// Make the child form's display name "Test"
		driver.findElement(By.id("childform_displayname")).sendKeys("Test");
		// Select the all availabe forms already exist child form type
		Select typeDropdown = new Select(driver.findElement(By.id("add_childform_type_selector")));
		typeDropdown.selectByVisibleText("All available forms already exist");
		// Click the Add button and close out the update message
		driver.findElement(By.id("swissFactory:values:/view_csd_provider/args/auto_template/child_forms:add_childform")).click();
		driver.findElement(By.linkText("Close")).click();
		
		assertTrue(true);
	}
	
	/**
	 * BBTest ID: Edit an Existing Child Form
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 */
	public void testEditExistingChildForm() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the csd_address form
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		
		// Change the title to "XXX"
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:title")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:title")).sendKeys("XXX");
		// Enter "xxx" for link filter
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link_filter")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link_filter")).sendKeys("xxx");
		// Enter "xxx" for link
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link")).sendKeys("xxx");
		// Select the "can_view_all_database_lists" permission for the child form
		Select permissionsDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:task")));
		permissionsDropdown.selectByVisibleText("can_view_all_database_lists");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Assert results
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:title")).getAttribute("value");
		assertEquals("XXX", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link")).getAttribute("value");
		assertEquals("xxx", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:link_filter")).getAttribute("value");
		assertEquals("xxx", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:task")).getAttribute("value");
		assertEquals("can_view_all_database_lists", result);
	}
	
	/**
	 * BBTest ID: Add Action Link on a Child Form
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 */
	public void testAddActionLinkToChildForm() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links > Add a new action link
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		driver.findElement(By.linkText("Add a new action link")).click();
		
		// Enter Test for the unique identifier
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:key_name")).sendKeys("Test");
		// Enter "edit_csd_provider?id=" for Link location
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:linkloc")).sendKeys("edit_csd_provider?id=");
		// Enter "Edit Health Worker Details" for link text
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:linktext")).sendKeys("Edit Health Worker Details");
		
		// Click the add button
		driver.findElement(By.id("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:add_actionlink")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links > Action Link Name: Test (Test) 
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		// Assert results
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linkloc")).getAttribute("value");
		assertEquals("edit_csd_provider?id=", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linktext")).getAttribute("value");
		assertEquals("Edit Health Worker Details", result);
	}
	
	/**
	 * BBTest ID: Add Permission to Action Link on a Child Form
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 *  - The Action Link "Test" has been created on "Test_Page"
	 */
	public void testAddActionLinkPermissionToChildForm() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links > Action Link Name: Test (Test) 
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		// Select the "custom_reports_admin" permission for the action link
		Select permissionsDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:task")));
		permissionsDropdown.selectByVisibleText("custom_reports_admin");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Assert results
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:task")).getAttribute("value");
		assertEquals("custom_reports_admin", result);
	}
	
	/**
	 * BBTest ID: Edit Child Form Action Link
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 *  - The Action Link "Test" has been created on for the csd_address child form
	 */
	public void testEditChildFormActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links > Action Link Name: Test (Test) 
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		driver.findElement(By.linkText("Action Link Name: Test (Test)")).click();
		
		// Change form field to xxx:ID
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:formfield")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:formfield")).sendKeys("xxx:ID");
		// Change Link location to xxx
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linkloc")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linkloc")).sendKeys("xxx");
		// Change Link text to xxx
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linktext")).clear();
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linktext")).sendKeys("xxx");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Assert results
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:formfield")).getAttribute("value");
		assertEquals("xxx:ID", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linkloc")).getAttribute("value");
		assertEquals("xxx", result);
		result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test:linktext")).getAttribute("value");
		assertEquals("xxx", result);
	}
	
	/**
	 * BBTest ID: Add Duplicate Child Form Action Link
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 *  - The Action Link "Test" has been created for csd_address child form
	 */
	public void testAddDuplicateChildFormActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links > Add a new action link
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		driver.findElement(By.linkText("Add a new action link")).click();
		
		// Enter Test for the unique identifier
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:key_name")).sendKeys("Test");
		// Enter "edit_csd_provider?id=" for Link location
		driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:linkloc")).sendKeys("edit_csd_provider?id=");
		
		// Assert error message
		String msg = driver.findElement(By.id("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address/action_links:key_name_submission_error_message")).getText();
		assertEquals("The value is already in use. Used values are: Test", msg);
	}
	
	/**
	 * BBTest ID: Delete Child Form Action Link
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 *  - The Action Link "Test" has been created for csd_address child form
	 */
	public void testDeleteChildFormActionLink() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		
		// Delete the Test Action link
		driver.findElement(By.xpath("//a[@href='index.php/PageBuilder/delete/Test_Page/args/auto_template/child_forms/csd_address/action_links/Test']")).click();

		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form > Action Links
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		driver.findElements(By.linkText("Action Links")).get(1).click();
		
		// Assert test link no longer exists
		assertFalse(driver.getPageSource().contains("Action Link Name: Test (Test)"));
	}
	
	/**
	 * BBTest ID: Remove Permission from an existing child form
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The child form "Csd Address (csd_address)" exists on Test_Page
	 *  - The child form "Csd Address (csd_address)" has a permission selected
	 */
	public void testRemoveChildFormPermission() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Go to csd_address form
		driver.findElement(By.linkText("Csd Address (csd_address)")).click();
		
		// Select the "No Task Selected" permission for the child form
		Select permissionsDropdown = new Select(driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:task")));
		permissionsDropdown.selectByVisibleText("No Task Selected");
		
		// Click update
		driver.findElement(By.id("swiss_update_button")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Assert change
		String result = driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_address:task")).getAttribute("value");
		assertEquals("", result);
	}
	
	/**
	 * BBTest ID: Add child form to page with existing child form
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The page has child form csd_address
	 *  - Page does not have child form csd_otherid
	 */
	public void testAddChildFormToPageWithExistingForm() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Assert the page does contain existing child forms
		assertTrue(driver.getPageSource().contains("Existing Child Forms"));
		
		// Click the "Add a new child form" link 
		driver.findElement(By.linkText("Add a new child form")).click();
		
		// Select the csd_otherid child form type
		Select typeDropdown = new Select(driver.findElement(By.id("add_childform_type_selector")));
		typeDropdown.selectByVisibleText("csd_otherid");
		// Make the child form's display name "test_two"
		driver.findElement(By.id("childform_displayname")).sendKeys("test_two");
		// Click the Add button and close out the update message
		driver.findElement(By.id("swissFactory:values:/Test_Page/args/auto_template/child_forms:add_childform")).click();
		driver.findElement(By.linkText("Close")).click();
		
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the new csd_address form
		driver.findElement(By.linkText("Csd Otherid (csd_otherid)")).click();
		
		// Assert the title is "test_two"
		assertEquals("test_two", driver.findElement(By.name("swissFactory:values:/Test_Page/args/auto_template/child_forms/csd_otherid:title")).getAttribute("value"));
		
	}
	
	/**
	 * BBTest ID: Delete child form from page with at least two forms
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The page has child form csd_address
	 *  - Page has child form csd_otherid
	 */
	public void testDeleteChildFormWithTwoExisting() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the Delete link for csd_otherid
		driver.findElement(By.xpath("//a[@href='index.php/PageBuilder/delete/Test_Page/args/auto_template/child_forms/csd_otherid']")).click();
		
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Assert that the csd_address link still exists and the csd_otherid does not
		String pageSource = driver.getPageSource();
		assertTrue(pageSource.contains("Csd Address"));
		assertFalse(pageSource.contains("Csd Otherid"));
		
	}
	
	/**
	 * BBTest ID: Delete only child form from page
	 * Preconditions:
	 *  - The PageBuilder and PageFormAutoView modules have been activated
	 *  - The Page "Test_Page" has been created
	 *  - The primary form is "csd_provider" for the page
	 *  - The page has child form csd_address
	 *  - Page has no other child forms
	 */
	public void testDeleteOnlyChildForm() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Go to Configure System page
		driver.findElement(By.id("nav_actions")).click();
		driver.findElement(By.linkText("Configure System")).click();
		
		// Click the Page Builder link
		driver.findElement(By.linkText("Page Builder")).click();
		// Click the edit link for the Test_Page
		driver.findElement(By.xpath("//div/ul[@id='pages']/li/a[@href='index.php/PageBuilder/edit/Test_Page']")).click();
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Click the Delete link for csd_address
		driver.findElement(By.xpath("//a[@href='index.php/PageBuilder/delete/Test_Page/args/auto_template/child_forms/csd_address']")).click();
		
		// Go to Primary Form Configurations > Additional Display Configurations > Child Forms
		driver.findElement(By.linkText("Page and Primary Form Configurations")).click();
		driver.findElement(By.linkText("Additional Display Configurations")).click();
		driver.findElement(By.linkText("Child Forms")).click();
		
		// Assert that there are no more child forms
		String pageSource = driver.getPageSource();
		assertTrue(pageSource.contains("There are no child forms associated with the primary form."));
		
	}
}
