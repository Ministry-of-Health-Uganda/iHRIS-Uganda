package ihris;

public class MainPageTest extends iHRISTest {

	protected void setUp() {
		super.setUp();
	}
	
	/**
	 * BBTest ID: Administrator Log In
	 * Preconditions: None
	 */
	public void testAdminLogin() {
		// Login as i2ce_admin
		login("i2ce_admin", "manage");
		
		// Assert that the message "Welcome, System Administrator" appears
		assertTrue(driver.getPageSource().contains("Welcome, System Administrator"));
	}
	
	/**
	 * BBTest ID: User Fails to Log In
	 * Preconditions: None
	 */
	public void testFailLogin() {
		// Attempt to login as user named "regular" with password "regpw"
		login("regular", "regpw");
		
		//Assert that the message "Invalid username or password" appears
		assertTrue(driver.getPageSource().contains("Invalid Username or Password"));
	}
}
