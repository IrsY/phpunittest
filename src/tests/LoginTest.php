<?php
use PHPUnit\Framework\TestCase;
require 'src/process/process_login.php';
class LoginTest extends TestCase
{
    protected static $redisMock;
    protected static $mysqliMock;

    public static function setUpBeforeClass(): void
    {
        // Mocking Redis client
        self::$redisMock = $this->createMock(PredisClient::class);

        // Mocking MySQLi connection
        self::$mysqliMock = $this->createMock(mysqli::class);
    }

    public function testSuccessfulLogin()
    {
        // Mock successful login scenario
        $mockResult = [
            'customer_id' => 1,
            'customer_password' => password_hash('correctpassword', PASSWORD_DEFAULT)
        ];

        self::$mysqliMock->expects($this->once())
            ->method('prepare')
            ->willReturnSelf();

        self::$mysqliMock->expects($this->once())
            ->method('bind_param')
            ->willReturn(true);

        self::$mysqliMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $mockResultObj = $this->createMock(mysqli_result::class);

        $mockResultObj->expects($this->once())
            ->method('fetch_assoc')
            ->willReturn($mockResult);

        self::$mysqliMock->expects($this->once())
            ->method('get_result')
            ->willReturn($mockResultObj);

        $loginController = new LoginController(self::$redisMock, self::$mysqliMock);

        // Simulate POST request with valid credentials
        $_POST['customer_email'] = 'test@example.com';
        $_POST['customer_pwd'] = 'correctpassword';
        $_POST['csrf_token'] = 'valid_csrf_token';

        $_SESSION['csrf_token'] = 'valid_csrf_token';

        ob_start();
        $loginController->processLogin();
        $output = ob_get_clean();

        // Assert that successful login redirects to gaverify.php
        $this->assertStringContainsString('Location: ../gaverify.php', $output);
    }

    // Add more test cases for other scenarios (invalid password, invalid email, CSRF token mismatch, etc.)
}
?>
