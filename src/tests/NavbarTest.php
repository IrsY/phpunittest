<?php
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase {
    
    protected function setUp(): void {
        parent::setUp();
        ob_start(); // Start output buffering
        session_start(); // Start session
        
        // Set session variables
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    protected function tearDown(): void {
        parent::tearDown();
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        ob_end_clean(); // Clean (end) the output buffer
    }
    
    public function testDropdownMenuItems() {
        // Include the file containing your navbar HTML
        include 'src/components/nav.inc.php';
        
        $output = ob_get_clean(); // Clean the output buffer
        
        // Test assertions here
        $this->assertStringContainsString('<a class="nav-link dropdown-toggle"', $output);
        $this->assertStringContainsString('id="dropdown03"', $output);
        
        // Ensure correct handling of dropdown items
        $this->assertStringContainsString('<a class="dropdown-item" href="barebone.php">Barebone</a>', $output);
        $this->assertStringContainsString('<a class="dropdown-item" href="cables.php">Cables</a>', $output);
        $this->assertStringContainsString('<a class="dropdown-item" href="keyboard.php">Keyboard</a>', $output);
        $this->assertStringContainsString('<a class="dropdown-item" href="keycaps.php">Keycaps</a>', $output);
        $this->assertStringContainsString('<a class="dropdown-item" href="switches.php">Switches</a>', $output);
        
        // Functional test for Barebone dropdown item
        $this->assertRedirectsTo('barebone.php', 'Barebone');
        $this->assertSessionVariableInitialized('csrf_token');
    }
    
    // Helper function to assert redirection
    protected function assertRedirectsTo($url, $linkText) {
        // Replace 'https://mechkeys.ddns.net/' with your base URL
        $response = file_get_contents('https://mechkeys.ddns.net/' . $url);
        $this->assertStringContainsString('<h1 class="title">' . $linkText, $response);
    }
    
    // Helper function to assert session variable initialization
    protected function assertSessionVariableInitialized($variableName) {
        $this->assertArrayHasKey($variableName, $_SESSION);
        $this->assertNotEmpty($_SESSION[$variableName]);
    }
}
?>
