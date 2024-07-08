<?php
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase {

    protected function setUp(): void {
        // Start output buffering to capture output
        ob_start();
        include 'src/components/nav.inc.php'; // Include the file containing your navbar HTML
    }

    protected function tearDown(): void {
        // Clean the output buffer
        ob_end_clean();
    }
    
    public function testDropdownMenuItems() {
        // Get the content from the output buffer
        $output = ob_get_contents();
        ob_end_clean(); // Clean the output buffer again to ensure it's empty
        
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
    }
}
?>
