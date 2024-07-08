<?php
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase {
    
    public function setUp(): void {
        // Ensure session is not started automatically by PHPUnit
        $this->withoutMiddleware();
        parent::setUp();
        
        // Start output buffering to capture output
        ob_start();
        
        // Mock session data as needed
        $_SESSION['role'] = 'customer'; // Example session data
        
        // Include your navbar file containing the HTML
        include 'src/index.php';
        
        // Capture output and end buffering
        $this->output = ob_get_clean();
    }
    
    public function testDropdownMenuItems() {
        // Test for existence of dropdown toggle
        $this->assertStringContainsString('<a class="nav-link dropdown-toggle"', $this->output);
        $this->assertStringContainsString('id="dropdown03"', $this->output);
        
        // Test for existence of dropdown menu
        $this->assertStringContainsString('<ul class="dropdown-menu"', $this->output);
        $this->assertStringContainsString('aria-labelledby="dropdown03"', $this->output);
        
        // Test each dropdown item
        $this->assertStringContainsString('<a class="dropdown-item" href="barebone.php">Barebone</a>', $this->output);
        $this->assertStringContainsString('<a class="dropdown-item" href="cables.php">Cables</a>', $this->output);
        $this->assertStringContainsString('<a class="dropdown-item" href="keyboard.php">Keyboard</a>', $this->output);
        $this->assertStringContainsString('<a class="dropdown-item" href="keycaps.php">Keycaps</a>', $this->output);
        $this->assertStringContainsString('<a class="dropdown-item" href="switches.php">Switches</a>', $this->output);
    }
}
?>
