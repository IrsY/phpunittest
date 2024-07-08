<?php
use PHPUnit\Framework\TestCase;

class NavbarTest extends TestCase {
    
    public function testDropdownMenuItems() {
        ob_start();
        include '../index.php'; // Include the file containing your navbar HTML
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
    }
}
?>
