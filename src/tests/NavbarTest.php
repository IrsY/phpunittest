<?php
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase {
    
    public function testDropdownMenuItems() {
        // Simulate capturing output of navbar
        ob_start();
        include 'src/components/nav.inc.php'; // Include the file containing your navbar HTML
        $output = ob_get_clean();
        
        // Test for existence of dropdown toggle
        $this->assertStringContainsString('<a class="nav-link dropdown-toggle"', $output);
        $this->assertStringContainsString('id="dropdown03"', $output);
        
        // Test for existence of dropdown menu
        $this->assertStringContainsString('<ul class="dropdown-menu"', $output);
        $this->assertStringContainsString('aria-labelledby="dropdown03"', $output);
        
        // Test each dropdown item
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
        $response = file_get_contents('https://mechkeys.ddns.net/' . $url); // Replace 'http://localhost/' with your base URL
        $this->assertStringContainsString('<title>' . $linkText, $response); // Example check for page title containing link text
    }
    
    // Helper function to assert session variable initialization
    protected function assertSessionVariableInitialized($variableName) {
        $this->assertArrayHasKey($variableName, $_SESSION);
        $this->assertNotEmpty($_SESSION[$variableName]);
    }
}
?>
