<?php

use PHPUnit\Framework\TestCase;

require_once 'vendor/autoload.php'; // Adjust the path as per your project's structure

class LoginTest extends TestCase
{
    public function testValidLogin()
    {
        // Mock necessary dependencies
        $_POST['customer_email'] = 'valid@email.com';
        $_POST['customer_pwd'] = 'validpassword';
        $_POST['csrf_token'] = 'valid_csrf_token';
        $_SESSION['csrf_token'] = 'valid_csrf_token';

        // Mock PredisClient behavior for Redis interaction
        $redisMock = $this->getMockBuilder(\Predis\Client::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $redisMock->expects($this->once())
                  ->method('get')
                  ->willReturn(null); // No IP block

        // Mock MySQL behavior for database interaction
        $mysqliMock = $this->getMockBuilder(mysqli::class)
                           ->disableOriginalConstructor()
                           ->getMock();
        
        // Mock result set for the query
        $resultMock = $this->getMockBuilder(stdClass::class)
                           ->setMethods(['fetch_assoc'])
                           ->getMock();
        $resultMock->expects($this->once())
                   ->method('fetch_assoc')
                   ->willReturn(['customer_id' => 1, 'customer_password' => password_hash('validpassword', PASSWORD_DEFAULT)]);
        
        $mysqliMock->expects($this->once())
                   ->method('prepare')
                   ->willReturn(true);
        $mysqliMock->expects($this->once())
                   ->method('get_result')
                   ->willReturn($resultMock);

        // Replace original objects with mocks
        require_once 'src/process/process_login.php'; // Include your script to test
        
        // Assertions based on expected behavior
        $this->assertArrayHasKey('customer_email', $_SESSION);
        $this->assertEquals('valid@email.com', $_SESSION['customer_email']);
        $this->assertEquals('ga_verify', $_SESSION['login_step']);
    }

}

?>