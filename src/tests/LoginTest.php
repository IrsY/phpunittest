<?php
use PHPUnit\Framework\TestCase;
require_once 'src/process/process_login.php';

class LoginTest extends TestCase
{
    public function testValidLogin()
    {
        $_POST['customer_email'] = 'test@example.com';
        $_POST['customer_pwd'] = 'ValidPassword123';
        $_POST['csrf_token'] = 'valid_token';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = 'valid_token';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        ob_start();
        $output = ob_get_clean();

        $this->assertEquals($_SESSION['customer_email'], 'test@example.com');
        $this->assertEquals($_SESSION['login_step'], 'ga_verify');
        $this->assertStringContainsString('Location: ../gaverify.php', xdebug_get_headers());
    }

    // Additional tests...
}
?>
