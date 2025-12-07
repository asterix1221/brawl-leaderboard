<?php
namespace Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObject\Trophy;

class TrophyTest extends TestCase {
    public function testTrophyWithValidValue(): void {
        $trophy = new Trophy(100);
        $this->assertEquals(100, $trophy->getValue());
    }

    public function testTrophyWithZeroValue(): void {
        $trophy = new Trophy(0);
        $this->assertEquals(0, $trophy->getValue());
    }

    public function testTrophyWithHighValue(): void {
        $trophy = new Trophy(50000);
        $this->assertEquals(50000, $trophy->getValue());
    }

    public function testTrophyWithNegativeValueMustThrow(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trophy count cannot be negative');
        
        new Trophy(-1);
    }

    public function testTrophyWithLargeNegativeValueMustThrow(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trophy count cannot be negative');
        
        new Trophy(-1000);
    }

    public function testTrophyEquals(): void {
        $trophy1 = new Trophy(100);
        $trophy2 = new Trophy(100);
        $trophy3 = new Trophy(200);

        $this->assertTrue($trophy1->equals($trophy2));
        $this->assertFalse($trophy1->equals($trophy3));
        $this->assertFalse($trophy2->equals($trophy3));
    }

    public function testTrophyEqualsWithSameObject(): void {
        $trophy = new Trophy(150);
        $this->assertTrue($trophy->equals($trophy));
    }

    public function testTrophyValueIsImmutable(): void {
        $trophy1 = new Trophy(100);
        $trophy2 = new Trophy(100);
        
        // Values should be equal but objects are different
        $this->assertEquals($trophy1->getValue(), $trophy2->getValue());
        $this->assertNotSame($trophy1, $trophy2);
    }
}
