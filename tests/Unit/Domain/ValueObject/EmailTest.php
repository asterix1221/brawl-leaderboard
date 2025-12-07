<?php
namespace Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Email;

class EmailTest extends TestCase {
    public function testEmailWithValidFormat(): void {
        $email = new Email('test@example.com');
        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testEmailWithValidSubdomain(): void {
        $email = new Email('user@mail.example.com');
        $this->assertEquals('user@mail.example.com', $email->getValue());
    }

    public function testEmailWithValidComplexFormat(): void {
        $email = new Email('john.doe+tag@example.co.uk');
        $this->assertEquals('john.doe+tag@example.co.uk', $email->getValue());
    }

    public function testEmailWithEmptyValueMustThrow(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('');
    }

    public function testEmailWithInvalidFormatNoAt(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('invalid-email');
    }

    public function testEmailWithInvalidFormatNoDomain(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('user@');
    }

    public function testEmailWithInvalidFormatNoLocal(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('@example.com');
    }

    public function testEmailWithInvalidFormatSpaces(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('test @example.com');
    }

    public function testEmailWithInvalidFormatSpecialChars(): void {
        $this->expectException(\InvalidArgumentException::class);
        
        new Email('test@example!.com');
    }

    public function testEmailEquals(): void {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        $email3 = new Email('different@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
        $this->assertFalse($email2->equals($email3));
    }

    public function testEmailEqualsWithSameObject(): void {
        $email = new Email('test@example.com');
        $this->assertTrue($email->equals($email));
    }

    public function testEmailCaseSensitivity(): void {
        $email1 = new Email('Test@Example.com');
        $email2 = new Email('test@example.com');

        // Email validation is case-insensitive for domain part
        $this->assertEquals('Test@Example.com', $email1->getValue());
        $this->assertEquals('test@example.com', $email2->getValue());
        $this->assertFalse($email1->equals($email2)); // Different strings
    }

    public function testEmailValueIsImmutable(): void {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');
        
        // Values should be equal but objects are different
        $this->assertEquals($email1->getValue(), $email2->getValue());
        $this->assertNotSame($email1, $email2);
    }
}
