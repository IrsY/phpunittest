<?php
use PHPUnit\Framework\TestCase;
require_once 'src/vendor/autoload.php';
class LoginTest extends TestCase {

    public function testValidLogin() {
        $_POST['csrf_token'] = 'valid_csrf_token'; // Set a valid CSRF token
        $_SESSION['csrf_token'] = 'valid_csrf_token'; // Set the same CSRF token in session
        $_POST['customer_email'] = 'test@example.com'; // Set a valid email
        $_POST['customer_pwd'] = 'valid_password'; // Set a valid password

        // Mock the Redis client to avoid actual network calls
        $redisMock = $this->createMock(\Predis\Client::class);
        $redisMock->method('get')->willReturn(false); // Simulate no block on IP

        // Mock the MySQL connection
        $connMock = $this->createMock(mysqli::class);
        $connMock->method('connect_error')->willReturn(false); // Simulate successful connection

        // Mock the result set for the database query
        $resultMock = $this->createMock(mysqli_result::class);
        $resultMock->method('fetch_assoc')->willReturn(['customer_id' => 1, 'customer_password' => password_hash('valid_password', PASSWORD_DEFAULT)]);

        $stmtMock = $this->createMock(mysqli_stmt::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('get_result')->willReturn($resultMock);

        $connMock->method('prepare')->willReturn($stmtMock);

        // Replace actual dependencies with mocks
        $this->setPrivateVariableValue($GLOBALS['redis'], $redisMock);
        $this->setPrivateVariableValue($GLOBALS['conn'], $connMock);

        ob_start(); // Start output buffering to capture header() calls
        include_once 'src/process/process_login.php'; // Execute the login script

        // Assert that the session variables are set and a redirection happens
        $this->assertArrayHasKey('customer_email', $_SESSION);
        $this->assertEquals('test@example.com', $_SESSION['customer_email']);
        $this->assertEquals('ga_verify', $_SESSION['login_step']);
        $this->assertContains('Location: ../gaverify.php', ob_get_clean()); // Check if redirection header is sent
    }

    private function setPrivateVariableValue(&$object, $value) {
        $refObj = new ReflectionObject($object);
        $refProp = $refObj->getProperty('redis'); // Adjust property name accordingly
        $refProp->setAccessible(true);
        $refProp->setValue($object, $value);
    }
}
?>
