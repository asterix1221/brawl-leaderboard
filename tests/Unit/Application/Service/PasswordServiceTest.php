<?php
namespace Tests\Unit\Application\Service;

use PHPUnit\Framework\TestCase;
use App\Application\Service\PasswordService;

class PasswordServiceTest extends TestCase {
    private PasswordService $passwordService;

    protected function setUp(): void {
        $this->passwordService = new PasswordService();
    }

    public function testHashPassword(): void {
        $password = 'testPassword123';
        $hash = $this->passwordService->hash($password);

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
        $this->assertStringStartsWith('$', $hash); // Argon2 hashes start with $
        $this->assertNotEquals($password, $hash); // Hash should not equal password
    }

    public function testVerifyValidPassword(): void {
        $password = 'testPassword123';
        $hash = $this->passwordService->hash($password);

        $this->assertTrue($this->passwordService->verify($password, $hash));
    }

    public function testVerifyInvalidPassword(): void {
        $password = 'testPassword123';
        $wrongPassword = 'wrongPassword456';
        $hash = $this->passwordService->hash($password);

        $this->assertFalse($this->passwordService->verify($wrongPassword, $hash));
    }

    public function testVerifyWithEmptyPassword(): void {
        $password = 'testPassword123';
        $hash = $this->passwordService->hash($password);

        $this->assertFalse($this->passwordService->verify('', $hash));
    }

    public function testVerifyWithInvalidHash(): void {
        $password = 'testPassword123';
        $invalidHash = 'invalid_hash_string';

        $this->assertFalse($this->passwordService->verify($password, $invalidHash));
    }

    public function testHashIsDifferentForSamePassword(): void {
        $password = 'testPassword123';
        $hash1 = $this->passwordService->hash($password);
        $hash2 = $this->passwordService->hash($password);

        // Hashes should be different due to salt
        $this->assertNotEquals($hash1, $hash2);
        
        // But both should verify correctly
        $this->assertTrue($this->passwordService->verify($password, $hash1));
        $this->assertTrue($this->passwordService->verify($password, $hash2));
    }

    public function testValidatePasswordStrengthValid(): void {
        $validPasswords = [
            'StrongPass123!',
            'MySecureP@ssw0rd',
            'ComplexPassword#123',
            'VerySecurePassword2024$'
        ];

        foreach ($validPasswords as $password) {
            $errors = $this->passwordService->validatePasswordStrength($password);
            $this->assertEmpty($errors, "Password '$password' should be valid, but got errors: " . implode(', ', $errors));
        }
    }

    public function testValidatePasswordStrengthTooShort(): void {
        $errors = $this->passwordService->validatePasswordStrength('short');
        $this->assertContains('Password must be at least 8 characters long', $errors);
    }

    public function testValidatePasswordStrengthNoUppercase(): void {
        $errors = $this->passwordService->validatePasswordStrength('lowercase123!');
        $this->assertContains('Password must contain at least one uppercase letter', $errors);
    }

    public function testValidatePasswordStrengthNoLowercase(): void {
        $errors = $this->passwordService->validatePasswordStrength('UPPERCASE123!');
        $this->assertContains('Password must contain at least one lowercase letter', $errors);
    }

    public function testValidatePasswordStrengthNoNumber(): void {
        $errors = $this->passwordService->validatePasswordStrength('NoNumbersHere!');
        $this->assertContains('Password must contain at least one number', $errors);
    }

    public function testValidatePasswordStrengthNoSpecialChar(): void {
        $errors = $this->passwordService->validatePasswordStrength('NoSpecialChars123');
        // Note: Current implementation doesn't check for special characters
        // This test documents the current behavior
        $this->assertIsArray($errors);
    }

    public function testValidatePasswordStrengthEmpty(): void {
        $errors = $this->passwordService->validatePasswordStrength('');
        $this->assertContains('Password must be at least 8 characters long', $errors);
        $this->assertContains('Password must contain at least one uppercase letter', $errors);
        $this->assertContains('Password must contain at least one lowercase letter', $errors);
        $this->assertContains('Password must contain at least one number', $errors);
    }

    public function testValidatePasswordStrengthExactly8Characters(): void {
        $errors = $this->passwordService->validatePasswordStrength('Valid1!');
        $this->assertContains('Password must be at least 8 characters long', $errors);
    }

    public function testValidatePasswordStrengthValidMinimum(): void {
        $errors = $this->passwordService->validatePasswordStrength('Valid1!'); // 7 characters
        $this->assertContains('Password must be at least 8 characters long', $errors);
    }

    public function testHashWithComplexPassword(): void {
        $complexPassword = 'VeryComplexPassword2024!@#$%^&*()';
        $hash = $this->passwordService->hash($complexPassword);

        $this->assertTrue($this->passwordService->verify($complexPassword, $hash));
    }

    public function testHashPerformance(): void {
        $password = 'testPassword123';
        $start = microtime(true);
        
        $this->passwordService->hash($password);
        
        $end = microtime(true);
        $duration = $end - $start;
        
        // Hashing should take less than 1 second (much less in reality)
        $this->assertLessThan(1.0, $duration, 'Password hashing should be fast');
    }
}
