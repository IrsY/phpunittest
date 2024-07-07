<?php
require_once 'src/process/process_login.php'; // Include the PHP script to be tested

use PHPUnit\Framework\TestCase;

class LoginProcessTest extends TestCase
{
    // Mock objects for testing
    private $mockRedis;
    private $mockMysqliStmt;
    private $mockMysqliResult;

    protected function setUp(): void
    {
        // Initialize mocks
        $this->mockRedis = $this->getMockBuilder(PredisClient::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->mockMysqliStmt = $this->getMockBuilder(mysqli_stmt::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->mockMysqliResult = $this->getMockBuilder(mysqli_result::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();
    }

    public function testValidLogin()
    {
        // Simulate valid POST request and CSRF token
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'valid_csrf_token';
        $_SESSION['csrf_token'] = 'valid_csrf_token';

        // Mock Redis behavior
        $this->mockRedis->expects($this->any())
                        ->method('get')
                        ->willReturn(false); // Simulate IP not blocked

        $this->mockRedis->expects($this->any())
                        ->method('incr')
                        ->willReturn(1); // Simulate login attempts increment

        // Mock MySQL statement and result behavior
        $this->mockMysqliStmt->expects($this->once())
                             ->method('execute')
                             ->willReturn(true);

        $this->mockMysqliResult->expects($this->once())
                               ->method('fetch_assoc')
                               ->willReturn(['customer_id' => 1, 'customer_password' => password_hash('password123', PASSWORD_DEFAULT)]); // Simulate user found

        // Replace dependencies with mocks
        global $redis, $conn;
        $redis = $this->mockRedis;
        $conn = $this->getMockBuilder(mysqli::class)
                     ->disableOriginalConstructor()
                     ->getMock();
        $conn->expects($this->once())
             ->method('prepare')
             ->willReturn($this->mockMysqliStmt);
        $conn->expects($this->once())
             ->method('close');

        // Capture output from header
        $this->expectOutputString("Location: ../gaverify.php");

        // Simulate login attempt
        $_POST['customer_email'] = 'test@example.com';
        $_POST['customer_pwd'] = 'password123';

        ob_start();
        process_login(); // Call the function to be tested
        ob_end_clean();

        // Assertions
        $this->assertArrayHasKey('customer_email', $_SESSION);
        $this->assertEquals('test@example.com', $_SESSION['customer_email']);
        $this->assertArrayHasKey('login_step', $_SESSION);
        $this->assertEquals('ga_verify', $_SESSION['login_step']);
    }

    // Add more tests for edge cases, error handling, etc.
}
?>
